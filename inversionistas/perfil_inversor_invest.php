<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!--########### ZONA SCRIPTS JS ##############-->

<!--##########################################-->
</HEAD>
<?
//############# LOGICA PAGINA ##################
$vobj_mae = new maestros;
$vobj_seg = new seguridad;
$vobj_cuentas = new cuentas;

$varr_tipodoc = $vobj_mae->get_tipos('TIPOIDENTIF');
$varr_usuario = $vobj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);

if ($_SESSION['user']['empresaid'] > 0){ 
    $varr_inversor = $vobj_mae->get_datos_inversor($_SESSION['user']['empresaid']);
    $v_inversor_id = $_SESSION['user']['empresaid'];
} elseif ($_SESSION['user']['empresaid'] < 0){
    $v_inversor_id = $_SESSION['user']['empresaid'] * -1;
    $varr_inversor = $vobj_mae->get_datos_inversor($v_inversor_id);
} else{ 
    $varr_inversor = $vobj_mae->get_datos_inversor($_SESSION['user']['usuarioid']);
    $v_inversor_id = $_SESSION['user']['usuarioid'];
}

// SI ES USUARIO ADMINISTRADOR BUSCO LOS BROKERS
if ($_SESSION['user']['perfilid'] == 2) $varr_brokers = $vobj_mae->get_brokers($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);

$v_comision = ($varr_inversor['comision'] * 100).' %';

$varr_cuentas_banco = $vobj_cuentas->get_cuentas_banco_inversor($v_inversor_id);

if ($varr_inversor['estado_id'] == 7 || $varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64){
    $v_readonly = 'readonly';
    $v_disabled = 'disabled';
} else {
    $v_readonly = '';
    $v_disabled = '';
}
//##############################################
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'inversionistas/perfil_inversor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--##################### BLOQUE PRNCIPAL ########################-->
    <div class="contenedor_principal">

        <div style="text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);max-width:700px;margin: 0px auto;padding: 10px;">
            Perfil <?php echo $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];?>
        </div>

        <div class="contenedor_formulario" id="formulario" style="margin-bottom:20px;">
            <form name='frm_datos' method='post' id='frm_datos' enctype="multipart/form-data">
            <input type="hidden" id="user_id" value="<?=$_SESSION['user']['usuarioid']?>">
            <input type="hidden" id="emp_id" value="<?=$_SESSION['user']['empresaid']?>">
            <input type="hidden" id="estado_id" value="<?=$varr_inversor['estado_id']?>">
            <input type="hidden" id="inversor_id" name="inversor_id" value="<?=$v_inversor_id?>">
            <input type="hidden" id="accion" name="accion" value="guarda_datos">

            <!--==== SECCIONES DEL PERFIL DEL USUARIO ====-->

            <div class="contenedor_formulario_column" style="height: 20px; align-items: center;margin-bottom: 10px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="datosPerson()">
                    <i class="fa-solid fa-user"></i> Datos Personales
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="cuentasBanco()">
                    <i class="fa-solid fa-building-columns"></i> Cuentas Banco
                </button>
                <button style="font-size:12px;background-color:var(--color-amarillo);border:none;margin-right: 5px;color: #000;" type="button" class="btn btn-primary">
                    <i class="fa-solid fa-money-bill-trend-up"></i> Perfil Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="brokerInvest()">
                    <i class="fa-solid fa-arrows-down-to-people"></i> Brokers de Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="contratoSeccion()">
                    <i class="fa-solid fa-scale-balanced"></i> Contrato
                </button>
            </div>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA DE PERFIL DE INVERSION 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $varr_inversor['estado_id'] == 64){    
        $vobj_inversiones = new inversiones;

        $varr_moneda_nac = $vobj_mae->get_parametro_detalle(49);
        $v_monedanac_id = $varr_moneda_nac['valornum'];
        $varr_monedas = $vobj_mae->get_tipos('MONEDA');

        for ($i=0; $i<count($varr_monedas); $i++){
            if ($v_monedanac_id == $varr_monedas[$i]['id']) $v_moneda_simbolo = $varr_monedas[$i]['dato1'];
        }

        $varr_perfil_inversor = $vobj_inversiones->get_perfil_inversor($v_inversor_id);
        
        if ($varr_perfil_inversor['count'] > 0){
            //$varr_perfil_inversor_detalle = $vobj_inversiones->get_perfil_inversor_detalle($v_inversor_id);
            $v_perfil_check = 'checked';
            
            if ($varr_perfil_inversor['invierte_auto'] == 1) $v_automatico = 'checked'; else $v_automatico = '';
            if ($varr_perfil_inversor['notifica_email'] == 1) $v_notifica_email_legal = 'checked'; else $v_notifica_email_legal = '';
            if ($varr_perfil_inversor['notifica_msg'] == 1) $v_notifica_msg_legal = 'checked'; else $v_notifica_msg_legal = '';
            if ($varr_perfil_inversor['notifica_email_oport'] == 1) $v_notifica_email_opor = 'checked'; else $v_notifica_email_opor = '';
            if ($varr_perfil_inversor['notifica_msg_oport'] == 1) $v_notifica_msg_opor = 'checked'; else $v_notifica_msg_opor = '';
            if ($varr_perfil_inversor['notifica_cada_oport'] == 1) $v_notifica_cada = 'checked'; else $v_notifica_cada = '';
            $v_monto_minimo = $varr_perfil_inversor['monto_min'];
            $v_monto_maximo = $varr_perfil_inversor['monto_max'];
            $v_tea_automatica = $varr_perfil_inversor['tea_automatica'];
        } else {
            $v_perfil_check = '';
            $v_automatico = '';
            $v_notifica_email_legal = 'checked';
            $v_notifica_msg_legal = 'checked';
            $v_notifica_email_opor = 'checked';
            $v_notifica_msg_opor = 'checked';
            $v_notifica_cada = 'checked';
            $v_monto_minimo = 0;
            $v_monto_maximo = 0;
            $v_tea_automatica = 0;
        }
?>
            <div class="contenedor_formulario_column">
                <div style="width:100%;display: inline-flex;">
                    <input type="checkbox" id="activa_perfil" name="activa_perfil" value="1" class="formulario_control" <?php echo $v_perfil_check;?>>
                    <label for="activa_perfil" style="margin-right:30px;margin-left:15px;">
                        <i class="fa-solid fa-id-card-clip"></i> Perfil de inversión 
                        <abbr title="Activa su nivel de inversion donde solo le pareceran las oportunidades que se ajusten a su perfil"><i class="fa-solid fa-circle-question"></i></abbr>
                    </label>
                    <input type="checkbox" id="activa_inversion_atm" name="activa_inversion_atm" value="1" class="formulario_control" <?php echo $v_automatico;?> onchange="inversion_automatica()">
                    <label for="activa_inversion_atm" style="margin-left:15px;">
                        <i class="fa-solid fa-robot"></i> Inversión automática 
                        <abbr title="Activa la inversión automática en las oportunidades que cumplen con su perfil"><i class="fa-solid fa-circle-question"></i></abbr>
                    </label>
                </div>
            </div>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_email">Notifica por Email comunicados de Legal</label>
                    <input type="checkbox" class="formulario_control" id="notifica_email" name="notifica_email" value="1" <?php echo $v_notifica_email_legal;?> disabled>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_msg">Notifica Mensajes comunicados de Legal</label>
                    <input type="checkbox" class="formulario_control" id="notifica_msg" name="notifica_msg" value="1" <?php echo $v_notifica_msg_legal;?> disabled>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_email_opor">Notifica por Email resumen de Oportunidades</label>
                    <input type="checkbox" class="formulario_control" id="notifica_email_opor" name="notifica_email_opor" value="1" <?php echo $v_notifica_email_opor;?>>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_msg_opor">Notifica Mensajes resumen de Oportunidades</label>
                    <input type="checkbox" class="formulario_control" id="notifica_msg_opor" name="notifica_msg_opor" value="1" <?php echo $v_notifica_msg_opor;?>>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_cada_opor">Notifica Cada Vez que hay Oportunidad</label>
                    <input type="checkbox" class="formulario_control" id="notifica_cada_opor" name="notifica_cada_opor" value="1" <?php echo $v_notifica_cada;?>>
                </div>
            </div>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="monto_minimo">Monto minimo inversion en <abbr title="Se realizara la conversion a todas las monedas disponibles"><?php echo $v_moneda_simbolo;?></abbr></label>
                    <input type="number" class="formulario_control" id="monto_minimo" name="monto_minimo" value="<?=$v_monto_minimo?>" style="text-align:right;">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="monto_maximo">Monto maximo inversion en <abbr title="Se realizara la conversion a todas las monedas disponibles"><?php echo $v_moneda_simbolo;?></abbr></label>
                    <input type="number" class="formulario_control" id="monto_maximo" name="monto_maximo" value="<?=$v_monto_maximo?>" style="text-align:right;">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="tea_automatica">% TEA automática</label>
                    <input type="number" class="formulario_control" id="tea_automatica" name="tea_automatica" value="<?=$v_tea_automatica?>" style="text-align:right;">
                </div>
            </div>
            <div class="contenedor_formulario_column">
<?php
        $varr_variables = $vobj_mae->get_tipos('VARIABLE PERFIL INV');

        for ($i=0; $i<count($varr_variables); $i++){
            $v_sin_variable = 0;

        echo '  <div class="formulario_grupo_row" style="width:200px;">
                    <input type="hidden" value="'.$varr_variables[$i]['id'].'" id="'.$varr_variables[$i]['nombre'].'-1" name="'.$varr_variables[$i]['nombre'].'-1">
                    <label for="'.$varr_variables[$i]['nombre'].'[]">'.$varr_variables[$i]['nombre'].'</label>
                    <select name="'.$varr_variables[$i]['nombre'].'[]" id="'.$varr_variables[$i]['nombre'].'[]" class="form-control basic-multiple" multiple="multiple" name="'.$varr_variables[$i]['nombre'].'[]">';

            $varr_variable_valor = $vobj_mae->get_variables_valor($varr_variables[$i]['nombre'],0);     // todas
            // las variables particulares del inversor
            if ($_SESSION['user']['empresaid'] > 0) $varr_variable_valor_inv = $vobj_mae->get_variables_valor($varr_variables[$i]['nombre'], $_SESSION['user']['empresaid']);
            else $varr_variable_valor_inv = $vobj_mae->get_variables_valor($varr_variables[$i]['nombre'], $_SESSION['user']['usuarioid']);
            
            if (count($varr_variable_valor_inv) == 0) $v_sin_variable = 1;

            for ($j=0; $j<count($varr_variable_valor); $j++){
                if ($v_sin_variable > 0)
                echo '  <option value="'.$varr_variable_valor[$j]['id'].'">'.$varr_variable_valor[$j]['nombre'].'</option>';
                else{
                    $v_encontrado = 0;

                    for ($z=0; $z<count($varr_variable_valor_inv); $z++){
                        if ($varr_variable_valor[$j]['id'] == $varr_variable_valor_inv[$z]['id']) $v_encontrado = 1;
                    }

                    if ($v_encontrado > 0) echo '  
                        <option value="'.$varr_variable_valor[$j]['id'].'" selected>'.$varr_variable_valor[$j]['nombre'].'</option>';
                    else echo '  
                        <option value="'.$varr_variable_valor[$j]['id'].'">'.$varr_variable_valor[$j]['nombre'].'</option>';
                }
            }

        echo '      </select>
                </div>
                <input type="hidden" id="variables_count" value="'.count($varr_variables).'">';
        }
?>
            </div>
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="guardaPerfilInversion()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Perfil Inversión
                </button>
            </div>
<?php
    }
?>

            </form>
        </div> <!-- CONTENEDOR FORMULARIO -->

    </div> <!-- CONTENEDOR PRINCIPAL -->
    <!--###################################################-->
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    ZONA MODAL 
    @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
    <div class="modal fade" id="PerfilInversor" tabindex="-1" role="dialog">
        <div class="modal-dialog">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">DETALLE DE PROPUESTA</h5></li>
                        <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
                    </ul>
                </div>
                <div class="modal-body">
                </div>
                <!--<div class="modal-footer">-->
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
                <!--</div>-->
            </div>
        </div>
    </div>

    <!--#################### ZONA JS ###################-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
    <script>
        
        $(document).ready(function () {
            $('.basic-multiple').select2();
        });

        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES VISUALES
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        function focusFunction(obj){
            if(obj.value == "") obj.style = "border:1px solid var(--color-rojo)";
            else obj.style = "";
        }

        function focusFunctionFile(obj){
            if(obj.value == "") obj.style = "border-color: var(--color-rojo); font-size: 12px;";
            else obj.style = "font-size: 12px;";
        }

        function focusFunctionText(obj){
            if(obj.value == "") obj.style = "transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;";
            else obj.style = "transition: var(--color-gris-oscuro) .15s easy-in-out; font-size: 12px;";
        }

        function cambiaCondicion(){
            var v_condicion = $('#condicion_laboral').val();
            var obj = document.getElementById('condicion_laboral');
            obj.style = "font-size: 12px;";

            var container_justificacion = document.getElementById('container_justificacion');
            var container_kwc_docs = document.getElementById('container_kwc_docs');
            var container_movimientos_banco = document.getElementById('container_movimientos_banco');
            var container_explicacion = document.getElementById('container_explicacion');
            var container_registro_mercantil = document.getElementById('container_registro_mercantil');

            var justificacion_fondos, kwc_docs, explicacion_actividad, movimientos_banco, registro_mercantil;

            if (v_condicion == 113){
                // ASALARIADO
                justificacion_fondos = '<label for="ec_nomina"><abbr title="Ultimos 3 estados de cuenta de nomina">E.C. Nomina</abbr></label><input type="file" name="ec_nomina" id="ec_nomina" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo);font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="ec_nomnina_path" id="ec_nomnina_path">';

                kwc_docs = '<label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">F. Servicios</abbr></label><input type="file" name="f_servicios" id="f_servicios" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="f_servicios_path" id="f_servicios_path">';

                container_justificacion.innerHTML = justificacion_fondos;
                container_kwc_docs.innerHTML = kwc_docs;
                container_movimientos_banco.innerHTML = '';
                container_explicacion.innerHTML = '';
                container_registro_mercantil.innerHTML = '';
            }

            if (v_condicion == 114){
                // INDEPENDIENTE
                justificacion_fondos = '<label for="declaracion_impuestos"><abbr title="Ultima declaracion de impuestos del pais que residio el ultimo año">Impuestos</abbr></label><input type="file" name="declaracion_impuestos" id="declaracion_impuestos" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;"><input type="hidden" name="declaracion_impuestos_path" id="declaracion_impuestos_path">';

                kwc_docs = '<label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">F. Servicios</abbr></label><input type="file" name="f_servicios" id="f_servicios" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;"><input type="hidden" name="f_servicios_path" id="f_servicios_path">';

                explicacion_actividad = '<label for="explica_actividad">Explicación Actividad</label><textarea name="explica_actividad" id="explica_actividad" class="formulario_control" onkeypress="focusFunctionText(this)" onchange="focusFunctionText(this)" placeholder="Descripcion de actividad economica" cols="80"></textarea>';

                movimientos_banco = '<label for="movimientos_banco"><abbr title="Movimientos bancarios de los 6 ultimos meses">Mov Bancarios</abbr></label><input type="file" name="movimientos_banco" id="movimientos_banco" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;"><input type="hidden" name="movimientos_banco_path" id="movimientos_banco_path">';

                container_justificacion.innerHTML = justificacion_fondos;
                container_kwc_docs.innerHTML = kwc_docs;
                container_explicacion.innerHTML = explicacion_actividad;
                container_movimientos_banco.innerHTML = movimientos_banco;
                container_registro_mercantil.innerHTML = '';
            }

            if (v_condicion == 115){
                // EMPRESARIO
                justificacion_fondos = '<label for="eeff"><abbr title="Estados Financieros auditados del último año o IR2 del último año">EEF / IR2</abbr></label><input type="file" name="eeff" id="eeff" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="eeff_path" id="eeff_path">';

                kwc_docs = '<label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">F. Servicios</abbr></label><input type="file" name="f_servicios" id="f_servicios" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="f_servicios_path" id="f_servicios_path">';

                movimientos_banco = '<label for="movimientos_banco"><abbr title="Movimientos bancarios de los 6 ultimos meses">Mov Bancarios</abbr></label><input type="file" name="movimientos_banco" id="movimientos_banco" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="movimientos_banco_path" id="movimientos_banco_path">';

                registro_mercantil = '<label for="registro_mercantil"><abbr title="Registro Mercantil vigente">Registro Mercantil</abbr></label><input type="file" name="registro_mercantil" id="registro_mercantil" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="registro_mercantil_path" id="registro_mercantil_path">';

                container_justificacion.innerHTML = justificacion_fondos;
                container_kwc_docs.innerHTML = kwc_docs;
                container_movimientos_banco.innerHTML = movimientos_banco;
                container_registro_mercantil.innerHTML = registro_mercantil;
                container_explicacion.innerHTML = '';
            }
        }

        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES DE NEGOCIO
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        
        function guardaPerfilInversion(){
            var vobj_accion = document.getElementById('accion');
            vobj_accion.value = 'perfil_inversion';
            
            var formData = new FormData(document.getElementById("frm_datos"));
            
            $.ajax({
                url:"perfil_inversor_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html",
                success:function(data,status){
                    $('#formulario').fadeIn(1000).html(data);
                    $('#formulario').modal('hide');
                    refresh_page();
                }
            });
        }

        function refresh_page(){
            location.href = "perfil_inversor_invest.php";
        }

        function grabarInformacion(){
            alert('guardado');

        }

        function inversion_automatica(){
            var activa_invatm = document.getElementById('activa_inversion_atm');

            if (activa_invatm.checked) alert('Usted ha activado la inversión automática, considere que cada que vez que se registre una factura de manera automática se colocara una posición suya bajo los parametros que está indicando.');
        }

        function cuentasBanco(){
            location.href = 'perfil_inversor_cuentas_banco.php';
        }

        function brokerInvest(){
            location.href = 'perfil_inversor_broker.php';
        }

        function contratoSeccion(){
            location.href = 'perfil_inversor_contrato.php';
        }

        function datosPerson(){
            location.href = 'perfil_inversor.php';
        }
    </script>
    <!--###################################################-->
    
</BODY>
</HTML>