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

$v_comision = ($varr_inversor['comision'] * 100).' %';

if ($varr_inversor['estado_id'] == 7 || $varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64 || $varr_inversor['estado_id'] == 6){
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
                <button style="font-size:12px;background-color:var(--color-amarillo);border:none;color: #000;margin-right: 5px;" type="button" class="btn btn-primary">
                    <i class="fa-solid fa-user"></i> Datos Personales
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="cuentasBanco()">
                    <i class="fa-solid fa-building-columns"></i> Cuentas Banco
                </button>
<?php
    if ($varr_inversor['estado_id'] == 64){
?>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="perfilInvest()">
                    <i class="fa-solid fa-money-bill-trend-up"></i> Perfil Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="brokerInvest()">
                    <i class="fa-solid fa-arrows-down-to-people"></i> Brokers de Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="contratoSeccion()">
                    <i class="fa-solid fa-scale-balanced"></i> Contrato
                </button>
<?php
    }
?>
            </div>

<?php
    if ($_SESSION['user']['empresaid'] > 0 && $varr_usuario['perfiltipo'] == 10 && $_SESSION['user']['empresaid'] != $_SESSION['user']['usuarioid']){
        //@@@@ INVERSOR EMPRESA
        $varr_empresa = $vobj_mae->get_datos_empresa($_SESSION['user']['empresaid']);

        echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:600px;">
                    <label for="empresa_nombre">Nombre Empresa</label>
                    <input type="text" class="formulario_control" id="empresa_nombre" value="'.$varr_empresa['nombre'].'" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="empresa_rnc">RNC</label>
                    <input type="text" class="formulario_control" id="empresa_rnc" value="'.$varr_empresa['identificacion'].'" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:420px;">
                    <label for="empresa_direccion">Dirección Empresa</label>
                    <input type="text" class="formulario_control" id="empresa_direccion" value="'.$varr_empresa['direccion'].'">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="empresa_telefono">Telefono</label>
                    <input type="text" class="formulario_control" id="empresa_telefono" value="'.$varr_empresa['telf_contacto'].'">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="empresa_email">Email</label>
                    <input type="text" class="formulario_control" id="empresa_email" value="'.$varr_empresa['email_contacto'].'">
                </div>
            </div>';
    }
?>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             INFORMACION DE LA PERSONA O REPRESENTANTE LEGAL
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="formulario_control" id="nombre" name="nombre" value="<?=$_SESSION['user']['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="apellido">Apellidos</label>
                    <input type="text" class="formulario_control" id="apellido" name="apellido" value="<?=$_SESSION['user']['apellido']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="tipo_doc_nom">Tipo Documento</label>
<?php
    /*if ($varr_inversor['estado_id'] == 6){      // REGISTRADO
        echo '      <select id="tipo_doc_nom" name="tipo_doc_nom" class="formulario_control">';

        for ($i=0; $i<count($varr_tipodoc); $i++){
            if ($varr_tipodoc[$i]['id'] == $varr_usuario['tipodoc'])
                echo '      <option value="'.$varr_tipodoc[$i]['id'].'" selected>'.$varr_tipodoc[$i]['nombre'].'</option>';
            else
                echo '      <option value="'.$varr_tipodoc[$i]['id'].'">'.$varr_tipodoc[$i]['nombre'].'</option>';
        }

        echo '      </select>';
    } else{*/
        echo '      <input type="text" class="formulario_control" id="tipo_doc_nom" name="tipo_doc_nom" value="'.$varr_usuario['tipodoc_nombre'].'" readonly>
                    <input type="hidden" id="tipo_doc" name="tipo_doc" value="'.$varr_usuario['tipodoc'].'">';
    //}
?>                
                </div>
<?php
    if ($varr_usuario['perfiltipo'] == 10 && $varr_inversor['documento'] != '')     // ADMINISTRADOR
        $v_documento_identidad = '<a href="'.$varr_inversor['documento'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:22px;"></i></a>';
    else $v_documento_identidad = '';

    if ($varr_usuario['email'] != "") $v_mail_color = ""; else $v_mail_color = "border-color: var(--color-rojo);";
    if ($varr_inversor['telefono'] != "") $v_telefono_color = ""; else $v_telefono_color = "border-color: var(--color-rojo);";
?>                
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="nro_doc">Nro Documento</label>
                    <input type="text" class="formulario_control" id="nro_doc" name="nro_doc" value="<?=$varr_usuario['identificacion']?>" <?echo $v_readonly;?>>
                    <input type="hidden" name="archivo_documento" id="archivo_documento" value="<?=$varr_inversor['documento']?>">
                </div>
                <div class="formulario_grupo_row" style="width:50px;">
                    <label for="doc">Doc</label>
                    <span id="doc"><?echo $v_documento_identidad;?></span>
                </div>
            
            </div> <!-- INFORMACION PERSONAL -->

            <!--==== INFORMACION PERSONAL ADICIONAL ====-->
            <div class="contenedor_formulario_column">

                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="email_persona">Email</label>
                    <input type="text" class="formulario_control" id="email_persona" name="email_persona" value="<?=$varr_usuario['email']?>" style="transition: var(--color-gris-oscuro) .15s easy-in-out;<?echo $v_mail_color;?>" onkeypress="focusFunction(this)" onchange="focusFunction(this)" <?echo $v_readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="telefono_persona">Telefono</label>
                    <input type="text" class="formulario_control" id="telefono_persona" name="telefono_persona" value="<?=$varr_inversor['telefono']?>" style="transition: var(--color-gris-oscuro) .15s easy-in-out;<?echo $v_telefono_color;?>" onkeypress="focusFunction(this)" onchange="focusFunction(this)" <?echo $v_readonly;?>>
                </div>

            </div>

            <!--==== INFORMACION condicion legal ====-->
            <div class="contenedor_formulario_column">

                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="pep">PEP</label>
<?php
    if ($varr_inversor['pep'] == 1){
?>
                    <input type="text" class="formulario_control" id="pep" name="pep" value="SI" style="text-align:center;" readonly>
<?php
    } else {
?>
                    <input type="text" class="formulario_control" id="pep" name="pep" value="NO" style="text-align:center;" readonly>
<?php
    }
?>
                </div>
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $_SESSION['user']['empresaid'] == 0){
        if ($varr_inversor['ocupacion_id'] > 0) $v_ocupacion_color = ""; else $v_ocupacion_color = "border-color: var(--color-rojo);";
?>

                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="condicion_laboral">Condicion Laboral</label>
                    <select name="condicion_laboral" id="condicion_laboral" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out;<?echo $v_ocupacion_color;?>" onchange="cambiaCondicion()" <?echo $v_disabled;?>>

<?php
        if ($varr_inversor['ocupacion_id'] == 0)
            echo '      <option value="0" style="color: var(--color-gris-oscuro)" selected>Seleccione</option>';

        $varr_ocupacion = $vobj_mae->get_tipos('OCUPACION');

        for ($i = 0; $i < count($varr_ocupacion); $i++){
            if ($varr_ocupacion[$i]['id'] == $varr_inversor['ocupacion_id']) 
                echo '  <option value="'.$varr_ocupacion[$i]['id'].'" selected>'.$varr_ocupacion[$i]['nombre'].'</option>';
            else echo ' <option value="'.$varr_ocupacion[$i]['id'].'">'.$varr_ocupacion[$i]['nombre'].'</option>';
        }
?>
                    </select>               
                </div>
<?php
    }
?>

            </div> <!-- DIV CONDICION LEGAL -->

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            LINEA DE DATOS DE COMISION Y CATEGORIA
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
            <div class="contenedor_formulario_column">
                
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="categoria">Categoria Inversor</label>
                    <input type="text" class="formulario_control" id="categoria" value="<?=$varr_inversor['categoria']?>" style="text-align: center;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="comision">Comision Factureate<abbr title="Es el % de la ganancia de cada operación que cobrará Factureate"><i class="fa-solid fa-circle-question"></i></abbr></label>
                    <input type="text" class="formulario_control" id="comision" style="text-align:right;" value="<?=$v_comision?>" readonly>
                </div>

            </div> <!-- COMISIONES Y CATEGORIA -->
            
            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            BOTONES -->
<?php
        if ($varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64){
?>
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="guardarDatos('directo')" id="btn_guardar">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Datos
                </button>
                
<?php
        }
?>
            </div>

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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        /*==== llamada a funcion que verifica si el inversor esta en estado REGISTRADO ====*/
        document.addEventListener("DOMContentLoaded", gestionaInversorNuevo);

        //==== funciones cuando el inversor esta en estado REGISTRADO ====
        function gestionaInversorNuevo(){
            var estado_id = document.getElementById("estado_id").value;
            var inversor_id = document.getElementById("inversor_id").value;
            var empresa_id = $('#emp_id').val();
            var user_id = $('#user_id').val();
            
            if (estado_id == 6){
                // estado registrado
                $('.modal-title').text('REGISTRO');

                var datos_personales = verifica_datos_personales();

                if (datos_personales == 1){
                    $('.modal-body').load('reg_datos_personales_inversor.php?inversor_id='+inversor_id+'&empresa_id='+empresa_id+'&user_id='+user_id,function(){
                    $('#PerfilInversor').modal({show:true});
                    });
                } else {
                    $('.modal-body').load('reg_datos_cuentas_inversor.php?inversor_id='+inversor_id+'&empresa_id='+empresa_id+'&user_id='+user_id,function(){
                    $('#PerfilInversor').modal({show:true});
                    });
                }
            }
        }

        function verifica_datos_personales(){
            var resultado;
            var nombre = document.getElementById("nombre").value;
            var apellido = document.getElementById("apellido").value;
            var tipo_doc_nom = document.getElementById("tipo_doc_nom").value;
            var nro_doc = document.getElementById("nro_doc").value;
            var archivo_documento = document.getElementById("archivo_documento").value;
            var email_persona = document.getElementById("email_persona").value;
            var telefono_persona = document.getElementById("telefono_persona").value;

            if (nombre != "" && apellido != "" && tipo_doc_nom > 0 && nro_doc != "" && archivo_documento != "" && email_persona != "" && telefono_persona != "")
                resultado = 0;
            else resultado = 1;

            return resultado;
        }

        function cambia_modal_registro(origen, inversor, empresa){
            //$('#contenedor_principal').modal('hide');
            $('#PerfilInversor').modal({show:false});

            if (origen == 'datos_personales'){
                $('.modal-title').text('REGISTRO');
                $('.modal-body').load('reg_datos_cuentas_inversor.php?inversor_id='+inversor+'&empresa_id='+empresa,function(){
                    $('#PerfilInversor').modal({show:true});
                });
            }

            if (origen == 'datos_cuenta'){
                alert('Enhorabuena!! terminaste el registro, tu información será analizada por nuestra área legal, luego de ello recibirás un correo donde te avisaremos que ya puedes invertir con nosotros');
                refresh_page();
            }
        }
        //==== END funciones cuando el inversor esta en estado REGISTRADO ====

        </script>
        
        <script>
        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES DE NEGOCIO
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        function guardarDatos(p_origen){
            var btn_guardar = document.getElementById("btn_guardar");
            btn_guardar.disabled = true;

            var usuario_id = document.getElementById("user_id").value;
            let telefono_persona = document.getElementById("telefono_persona").value
            let email_persona = document.getElementById("email_persona").value
            let estado_id = document.getElementById("estado_id").value
            let inversor_id = document.getElementById("inversor_id").value
            let tipo_doc_nom = document.getElementById("tipo_doc_nom").value
            let nro_doc = document.getElementById("nro_doc").value
            let condicion_laboral = document.getElementById("condicion_laboral").value
            let nombre = document.getElementById("nombre").value
            let apellido = document.getElementById("apellido").value
            let accion = 'guarda_datos'
            let kwc = document.getElementById("kwc").value

            var retorno = 1;
            var formaData = new FormData();

            formaData.append('usuario_id', usuario_id)
            formaData.append('telefono_persona', telefono_persona)
            formaData.append('email_persona', email_persona)
            formaData.append('estado_id', estado_id)
            formaData.append('inversor_id', inversor_id)
            formaData.append('tipo_doc_nom', tipo_doc_nom)
            formaData.append('nro_doc', nro_doc)
            formaData.append('condicion_laboral', condicion_laboral)
            formaData.append('nombre', nombre)
            formaData.append('apellido', apellido)
            formaData.append('accion', accion)

            if (estado_id == 6 && condicion_laboral > 0){
                // REGISTRADO
                var inputFileServicios = document.getElementById("f_servicios");
                var fileServicios = inputFileServicios.files[0];

                if (kwc == 0){
                    var inputFileKyc = document.getElementById("formulario_kyc");
                    var fileKyc = inputFileKyc.files[0];
                    formaData.append('formulario_kyc', fileKyc)
                }
                
                formaData.append('f_servicios', fileServicios)

                if (condicion_laboral == 113){
                    // ASALARIADO
                    var inputFileNomina = document.getElementById("ec_nomina");
                    var fileNomina = inputFileNomina.files[0];

                    formaData.append('ec_nomina', fileNomina)
                }

                if (condicion_laboral == 114){
                    // INDEPENDIENTE
                    var inputFileImpuestos = document.getElementById("declaracion_impuestos");
                    var fileImpuestos = inputFileImpuestos.files[0];
                    var inputFileBancos = document.getElementById("movimientos_banco");
                    var fileBancos = inputFileBancos.files[0];
                    let explica_actividad = document.getElementById("explica_actividad").value

                    formaData.append('declaracion_impuestos', fileImpuestos)
                    formaData.append('movimientos_banco', fileBancos)
                    formaData.append('explica_actividad', explica_actividad)
                }

                if (condicion_laboral == 115){
                    // EMPRESARIO
                    var inputFileEeff = document.getElementById("eeff");
                    var fileEeff = inputFileEeff.files[0];
                    var inputFileBancos = document.getElementById("movimientos_banco");
                    var fileBancos = inputFileBancos.files[0];
                    var inputFileRmercantil = document.getElementById("registro_mercantil");
                    var fileRmercantil = inputFileRmercantil.files[0];

                    formaData.append('eeff', fileEeff)
                    formaData.append('movimientos_banco', fileBancos)
                    formaData.append('registro_mercantil', fileRmercantil)
                }
            }

            $.ajax({
                    url: "perfil_inversor_proceso.php",
                    type: "POST",
                    data: formaData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data)
                    {
                        if (p_origen == 'directo') alert('Los datos fueron guardados');
                        btn_guardar.disabled = false;
                    }
                });
            
            return retorno;
        }

        function refresh_page(){
            location.href = "perfil_inversor.php";
        }

        function enviarInformacion(){
            var v_nro_doc = $('#nro_doc').val();
            var v_email = $('#email_persona').val();
            var v_telefono = $('#telefono_persona').val();
            var v_condicion = $('#condicion_laboral').val();
            var v_kwc = $('#kwc').val();
            var v_q_cuentas_banco = $('#q_cuentas_banco').val();

            var procede = 1;

            if (v_email == "" || v_telefono == "" || v_nro_doc == ""){
                procede = 0;
                alert('Debe completar la informacion de Nro de documento de identidad, email y telefono');
                $('#email_persona').focus();
            }

            if (procede == 1 && v_condicion == 0){
                procede = 0;
                alert('Debe seleccionar una condicion laboral');
                $('#condicion_laboral').focus();
            }

            if (procede == 1){
                if (v_condicion == 113){
                    var v_nomina = $('#ec_nomina').val();
                    var v_nomina_path = $('#ec_nomina_path').val();
                    var v_servicios = $('#f_servicios').val();
                    var v_servicios_path = $('#f_servicios_path').val();

                    if ((v_nomina == "" && v_nomina_path == "") || (v_servicios == "" && v_servicios_path == "")){
                        procede = 0;
                        alert('Debe adjuntar los comprobantes de nomina y comprobantes de servicios de su domicilio');
                        $('#ec_nomina').focus();
                    }
                }

                if (v_condicion == 114){
                    var v_impuestos = $('#declaracion_impuestos').val();
                    var v_servicios = $('#f_servicios').val();
                    var v_actividad = $('#explica_actividad').val();
                    var v_bancos = $('#movimientos_banco').val();
                    var v_impuestos_path = $('#declaracion_impuestos_path').val();
                    var v_servicios_path = $('#f_servicios_path').val();
                    var v_bancos_path = $('#movimientos_banco_path').val();

                    if ((v_impuestos == "" && v_impuestos_path == "") || (v_servicios == "" && v_servicios_path == "")){
                        procede = 0;
                        alert('Debe adjuntar la ultima declaracion de impuestos y comprobante de servicios de su domicilio');
                        $('#declaracion_impuestos').focus();
                    }

                    if (procede == 1 && v_actividad == ""){
                        procede = 0;
                        alert('Debe registrar una explicacion de su actividad economica');
                        $('#explica_actividad').focus();
                    }

                    if (procede == 1 && v_bancos == "" && v_bancos_path == ""){
                        procede = 0;
                        alert('Debe adjuntar los movimientos bancarios requeridos');
                        $('#movimientos_banco').focus();
                    }
                }

                if (v_condicion == 115){
                    var v_eeff = $('#eeff').val();
                    var v_servicios = $('#f_servicios').val();
                    var v_bancos = $('#movimientos_banco').val();
                    var v_mercantil = $('#registro_mercantil').val();

                    var v_eeff_path = $('#eeff_path').val();
                    var v_servicios_path = $('#f_servicios_path').val();
                    var v_bancos_path = $('#movimientos_banco_path').val();
                    var v_mercantil_path = $('#registro_mercantil_path').val();

                    if ((v_eeff == "" && v_eeff_path == "") || (v_servicios == "" && v_servicios_path == "")){
                        procede = 0;
                        alert('Debe adjuntar los estados financieros de su empresa y comprobante de servicios de su domicilio');
                        $('#eeff').focus();
                    }

                    if (procede == 1 && v_bancos == "" && v_bancos_path == ""){
                        procede = 0;
                        alert('Debe adjuntar los movimientos de la cuenta bancaria de su empresa');
                        $('#movimientos_banco').focus();
                    }

                    if (procede == 1 && v_mercantil == "" && v_mercantil_path == ""){
                        procede = 0;
                        alert('Debe adjuntar el registro mercantil vigente de su empresa');
                        $('#registro_mercantil').focus();
                    }
                }
            }

            if (procede == 1 && v_kwc == 0){
                var v_formulario_kyc = $('#formulario_kyc').val();

                if (v_formulario_kyc == ""){
                    procede = 0;
                    alert('Debe completar el formulario KWC (conozca a su cliente)');
                }
            }

            if (procede == 1 && v_q_cuentas_banco == 0){
                procede = 0;
                alert('Debe registrar por lo menos 1 cuenta de banco, donde recibira sus ganancias');
            }

            if (procede == 1){
                var result_save = guardarDatos('envio');

                let inversor_id = document.getElementById("inversor_id").value
                let nombre = document.getElementById("nombre").value
                let apellido = document.getElementById("apellido").value
                var v_accion = 'enviar';
                var formData = new FormData();

                var btn_enviar = document.getElementById("btn_enviar");

                formData.append('accion', v_accion)
                formData.append('inversor_id', inversor_id)
                formData.append('nombre', nombre)
                formData.append('apellido', apellido)

                $.ajax({
                    url:"perfil_inversor_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(data){
                        //alert(data);
                        btn_enviar.disabled = true;
                        alert('Su información fue enviada, nuestro departamento legal le enviará una notificación por correo luego de la revisión');
                        refresh_page();
                    }
                });
            }
        }

        function cuentasBanco(){
            location.href = 'perfil_inversor_cuentas_banco.php';
        }

        function perfilInvest(){
            location.href = 'perfil_inversor_invest.php';
        }

        function brokerInvest(){
            location.href = 'perfil_inversor_broker.php';
        }

        function contratoSeccion(){
            location.href = 'perfil_inversor_contrato.php';
        }
        
    </script>
    <!--###################################################-->
    
</BODY>
</HTML>