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
                <button style="font-size:12px;background-color:var(--color-amarillo);border:none;margin-right: 5px;color: #000;" type="button" class="btn btn-primary">
                    <i class="fa-solid fa-arrows-down-to-people"></i> Brokers de Inversión
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="contratoSeccion()">
                    <i class="fa-solid fa-scale-balanced"></i> Contrato
                </button>
            </div>

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA CUENTA DE BROKERS 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->

            <div style="display: inline-flex;width: 90%;margin-top:15px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold; font-size:10px;">Los brokers son las personas a las que le está encargando realizar inversiones a su nombre, todas las inversiones realizadas por sus borker es equivalente a que usted mismo haya echo las inversiones</p>
                <input type="hidden" name="q_cuentas_banco" id="q_cuentas_banco" value="'.count($varr_cuentas_banco).'">
            </div>

<?php
    if ($_SESSION['user']['perfilid'] == 2){    // administrador
?>

            <div style="overflow:hidden;margin:5px;padding:5px;width: 90%;" id="tabla_brokers">
                <table class="tabla_resize">
                    <thead>
                        <tr>
                            <th scope="col" class="sort asc">ID</th>            <th scope="col" class="sort asc">NOMBRE</th>
                            <th scope="col" class="sort asc">TIPO DOC</th>      <th scope="col" class="sort asc">DOCUMENTO</th>
                            <th scope="col" class="sort asc">ESTADO</th>        <th scope="col" class="sort asc">ACCION</th>
                        </tr>
                    </thead>
                    <tbody id="content">
            
<?php
        $varr_brokers = $vobj_mae->get_brokers($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);

        for ($i=0; $i<count($varr_brokers); $i++){
            if ($varr_brokers[$i]['estado_id'] == 1) $v_estado = '<i class="fa-solid fa-circle-check" style="margin-left:5px;color:var(--color-verde);"></i>';
            else $v_estado = '<abbr title="Pendiente de verificar por Factureate"><i class="fa-solid fa-triangle-exclamation" style="margin-left:5px;color:var(--color-amarillo);"></i></abbr>';

            $v_documento = "'".$varr_brokers[$i]['identificacion']."'";
            $v_botom = '<button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="eliminarBroker('.$varr_brokers[$i]['id'].','.$v_documento.','.$_SESSION['user']['empresaid'].')">
                            <i class="fa-solid fa-trash"></i>
                        </button>';

            echo '      <tr>    
                            <td data-label="ID">'.$varr_brokers[$i]['id'].'</td>
                            <td data-label="NOMBRE">'.$varr_brokers[$i]['nombre'].'</td>
                            <td data-label="TIPO DOC">'.$varr_brokers[$i]['tipodoc'].'</td>
                            <td data-label="DOCUMENTO">'.$varr_brokers[$i]['identificacion'].'</td>
                            <td data-label="ESTADO">'.$varr_brokers[$i]['estado'].' '.$v_estado.'</td>
                            <td data-label="ACCION">'.$v_botom.'</td>
                        </tr>';
        }
?>
                    </tbody>
                </table>
            </div>  <!-- tabla de listado de brokers -->
<?php
        if ($varr_inversor['estado_id'] == 64)
            echo '
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="agregarBroker()">
                    <i class="fa-solid fa-handshake-angle"></i> Agregar Broker
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
    <script>
        
        function agregarBroker(){
            var v_usuario_id = $('#user_id').val();
            var v_empresa_id = $('#emp_id').val();
            var v_inversor_id;

            if (v_empresa_id > 0) v_inversor_id = v_empresa_id;
            else v_inversor_id = v_usuario_id;

            $('.modal-title').text('NUEVO BROKER');
            $('.modal-body').load('nuevo_broker_modal.php?inversor_id='+v_inversor_id+'&empresa_id='+v_empresa_id,function(){
                $('#PerfilInversor').modal({show:true});
            });
        }

        function eliminarBroker(p_id,p_documento,p_inversor_id){
            if (confirm("¿Está seguro de eliminar el broker?")) {
                var formData = new FormData();

                formData.append('accion', 'eliminar')
                formData.append('id', p_id)
                formData.append('documento', p_documento)
                formData.append('inversor_id', p_inversor_id)
                
                $.ajax({
                    url:"nuevo_broker_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(data){
                        alert('El broker fue eliminado');                        
                        refresh_page();
                    }
                });
            }
        }

        function refresh_page(){
            location.href = "perfil_inversor_broker.php";
        }

        function grabarInformacion(){
            alert('guardado');

        }

        function cuentasBanco(){
            location.href = 'perfil_inversor_cuentas_banco.php';
        }

        function perfilInvest(){
            location.href = 'perfil_inversor_invest.php';
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