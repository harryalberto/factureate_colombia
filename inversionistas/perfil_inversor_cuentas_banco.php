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
                <button style="font-size:12px;background-color:var(--color-amarillo);border:none;margin-right: 5px; color: #000;" type="button" class="btn btn-primary">
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

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA CUENTA DE BANCO 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
        if ($_SESSION['user']['perfilid'] == 2){    // administrador
        echo '
            <div style="display: inline-flex;width: 90%;margin-top:15px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold; font-size:10px;">Las cuentas bancarias registradas es donde depositaremos el dinero que usted decida retirar, es necesario que tenga registradas las cuentas bancarias en las monedas donde invertirá</p>
                <input type="hidden" name="q_cuentas_banco" id="q_cuentas_banco" value="'.count($varr_cuentas_banco).'">
            </div>';
?>
            <div style="overflow:hidden;margin:5px;padding:5px;width: 90%;" id="tabla_cuentas">
                <table class="tabla_resize">
                    <thead>
                        <tr>
                            <th scope="col" class="sort asc">ID CUENTA</th>     <th scope="col" class="sort asc">BANCO</th>
                            <th scope="col" class="sort asc">TIPO CUENTA</th>   <th scope="col" class="sort asc">MONEDA</th>
                            <th scope="col" class="sort asc">CUENTA</th>        <th scope="col" class="sort asc">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody id="content">
            
<?php
            for ($i=0; $i<count($varr_cuentas_banco); $i++){
                if ($varr_cuentas_banco[$i]['estado_id'] == 1) $v_estado = '<i class="fa-solid fa-circle-check" style="margin-left:5px;color:var(--color-verde);"></i>';
                else $v_estado = '<abbr title="Pendiente de verificar por Factureate"><i class="fa-solid fa-triangle-exclamation" style="margin-left:5px;color:var(--color-amarillo);"></i></abbr>';

                echo '   <tr>    
                            <td data-label="ID CUENTA">'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'</td>
                            <td data-label="BANCO">'.$varr_cuentas_banco[$i]['banco_nombre'].'</td>
                            <td data-label="TIPO CUENTA">'.$varr_cuentas_banco[$i]['tcuenta_nombre'].'</td>
                            <td data-label="MONEDA">'.$varr_cuentas_banco[$i]['moneda_nombre'].'</td>
                            <td data-label="CUENTA">'.$varr_cuentas_banco[$i]['cuenta'].'</td>
                            <td data-label="ESTADO">'.$varr_cuentas_banco[$i]['estado_nombre'].$v_estado.'</td>
                        </tr>';
            }
?>
                    </tbody>
                </table>
            </div>
<?php
            if ($varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64)
                echo '
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="agregarCuenta()">
                    <i class="fa-solid fa-circle-plus"></i> Agregar Cuenta
                </button>
            </div>';
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES VISUALES
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/

        function pintaCuentas(p_tabla){
            var tabla_cuentas = document.getElementById("tabla_cuentas");
            tabla_cuentas.innerHTML = p_tabla;
            $('#PerfilInversor').modal('hide');
            refresh_page();
        }

        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES DE NEGOCIO
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        
        function agregarCuenta(){
            var v_inversor_id = $('#inversor_id').val();
            var v_empresa_id = $('#emp_id').val();
            var v_nro_doc = $('#nro_doc').val();

            $('.modal-title').text('NUEVA CUENTA BANCARIA');
            $('.modal-body').load('nueva_cuenta_modal.php?inversor_id='+v_inversor_id+'&empresa_id='+v_empresa_id+'nro_doc='+v_nro_doc,function(){
                $('#PerfilInversor').modal({show:true});
            });
        }

        function eliminarCuenta(p_cuenta_banco_id, p_q_cuentas, p_i){
            if (p_q_cuentas == 1) alert('Lo sentimos debe mantener al menos una cuenta activa, no se puede elimnar la unica cuenta que tiene registrada');
            else{
                var v_moneda_eliminar = $('#moneda_id-'+p_i).val();
                var i, v_moneda;
                var encontrado = 0;

                for (let i=0; i<p_q_cuentas; i++){
                    if (i != p_i){
                        v_moneda = $('#moneda_id-'+i).val();

                        if (v_moneda == v_moneda_eliminar) encontrado = 1;
                    }
                }

                if (encontrado == 1){
                    $.ajax({
                        url:"perfil_inversor_proceso.php",
                        type:'post',
                        data:{
                            "cuenta_id":p_cuenta_banco_id,
                            "accion": 'eliminar_cuenta'
                        },
                        success:function(data,status){
                                    $('#formulario').fadeIn(1000).html(data);
                                    $('#formulario').modal('hide');
                                    refresh_page();
                        }
                    });
                } else alert('Lo sentimos debe mantener al menos una cuenta de cada moneda activa, no se puede elimnar la unica cuenta de esa moneda que tiene registrada');
            }
        }
        
        function refresh_page(){
            location.href = "perfil_inversor_cuentas_banco.php";
        }

        function perfilInvest(){
            location.href = "perfil_inversor_invest.php";
        }

        function datosPerson(){
            location.href = 'perfil_inversor.php';
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