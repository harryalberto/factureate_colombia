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
<?php
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!--########### ZONA SCRIPTS JS ##############-->

<!--##########################################-->
</HEAD>
<?php
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
<?php
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
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="perfilInvest()">
                    <i class="fa-solid fa-money-bill-trend-up"></i> Perfil Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-right: 5px;" type="button" class="btn btn-primary" onclick="brokerInvest()">
                    <i class="fa-solid fa-arrows-down-to-people"></i> Brokers de Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-amarillo);border:none;color: #000;" type="button" class="btn btn-primary">
                    <i class="fa-solid fa-scale-balanced"></i> Contrato
                </button>
            </div>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA LEGAL 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $varr_inversor['estado_id'] == 64){    //INVERSOR ADMINISTRADOR
?>
            <div style="display: inline-flex;width: 100%;margin-top:20px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold;"><i class="fa-solid fa-scale-balanced"></i> Contrato</p>
            </div>
            <div class="contenedor_formulario_column" style="margin-bottom:20px;">
                <div style="width:100%;display: inline-flex;">
                    <label for="contrato" style="margin-right:30px;">
                        Contrato de Vinculación: <a href="<?=$varr_inversor['contrato']?>" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a>
                    </label>
<?php
        if ($varr_inversor['estado_anulacion_id'] == 0)
        echo '      <button style="font-size:12px;background-color:var(--color-rojo);border:none;" type="button" class="btn btn-primary" onclick="anularContrato()">
                        <i class="fa-solid fa-bell-concierge"></i> Solicitar Anulación
                    </button>';
        elseif ($varr_inversor['estado_anulacion_id'] == 55 || $varr_inversor['estado_anulacion_id'] == 56) {
        echo '      <label style="margin-right:30px;color:var(--color-rojo);">Anulación: '.$varr_inversor['estado_anulacion_nombre'].'</label>
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="anularSolicitudAnul()">
                        <i class="fa-solid fa-hand"></i> Anular solicitud
                    </button>';
    }
?>

                </div>
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
        function anularContrato(){
            if (confirm("Esta seguro de anular el contrato con Factureate? Una vez aceptada la solicitud de anulacion de contrato por nuestros analistas no podra realizar ninguna operacion en la plataforma") == true){
                var v_inversor_id = $('#inversor_id').val();

                $('.modal-title').text('SOLICITUD DE ANULACION DE CONTRATO');
                $('.modal-body').load('solicitud_anulacion_inversor_modal.php?inversor_id='+v_inversor_id,function(){
                    $('#PerfilInversor').modal({show:true});
                });
            }
        }

        function anularSolicitudAnul(){
            var v_inversor_id = $('#inversor_id').val();

            $.ajax({
                url:"solicita_anular_inversor_proceso.php",
                type:'post',
                data:{
                    "inversor_id":v_inversor_id,
                    "accion": 'anular_solicitud'
                },
                success:function(data,status){
                    $('#formulario').fadeIn(1000).html(data);
                    $('#formulario').modal('hide');
                    refresh_page();
                }
            });            
        }

        function refresh_page(){
            location.href = "perfil_inversor_contrato.php";
        }

        function grabarInformacion(){
            alert('guardado');

        }

        function datosPerson(){
            location.href = 'perfil_inversor.php';
        }

        function cuentasBanco(){
            location.href = 'perfil_inversor_cuentas_banco.php';
        }

        function perfilInvest(){
            location.href = "perfil_inversor_invest.php";
        }

        function brokerInvest(){
            location.href = 'perfil_inversor_broker.php';
        }

        
    </script>
    <!--###################################################-->
    
</BODY>
</HTML>