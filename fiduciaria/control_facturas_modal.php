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
</HEAD>
<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@ LOGICA
$vobj_factura_modal = new factura;

date_default_timezone_set($_SESSION['user']['zona_horaria']);

$varr_finan = $vobj_factura_modal->get_financiamiento_detalle($_GET['finan_id']);

$v_f_finan = strtotime($varr_finan['fregistro_finan']); $v_f_finan = date('d-m-Y',$v_f_finan);
$v_f_pago = strtotime($varr_finan['fpago']); $v_f_pago = date('d-m-Y',$v_f_pago);

$v_fpago_finan = strtotime($varr_finan['fpago']); $v_fpago_finan = date('Y-m-d',$v_fpago_finan); $v_fpago_finan = new DateTime($v_fpago_finan);
$v_fecha_hoy = date('Y-m-d'); $v_fecha_hoy = new DateTime($v_fecha_hoy);
$diff_dias_xvencer = $v_fecha_hoy->diff($v_fpago_finan);

$v_fecha_hoy_comp = date('Y-m-d');
$v_fpago_finan_comp = strtotime($varr_finan['fpago']); $v_fpago_finan_comp = date('Y-m-d',$v_fpago_finan_comp);

if ($v_fecha_hoy_comp > $v_fpago_finan_comp) $v_dias_xvencer = $diff_dias_xvencer->days * -1;
else $v_dias_xvencer = $diff_dias_xvencer->days;

$v_monto_financiado = number_format($varr_finan['monto_financiado'],2,'.',',');
$v_monto_factura = number_format($varr_finan['monto_factura'],2,'.',',');
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion">
        <input type="hidden" name="finan_id" id="finan_id" value="<?=$_GET['finan_id']?>">
        
    <div id="principal" style="padding-left: 10px;height: 55%;"> <!--display:block;height: 75%;-->
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="factura_id">OP ID:</label>
                    <input type="text" name="factura_id" id="factura_id" class="formulario_control" value="<?=$_GET['factura_id']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="emisor">EMISOR:</label>
                    <input type="text" name="emisor" id="emisor" class="formulario_control" value="<?=$varr_finan['emisor_nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="cliente">PAGADOR:</label>
                    <input type="text" name="cliente" id="cliente" class="formulario_control" value="<?=$varr_finan['cliente_nombre']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="factura_nro">FACTURA NRO:</label>
                    <input type="text" name="factura_nro" id="factura_nro" class="formulario_control" value="<?=$varr_finan['factura_numero']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="f_finan">F FINAN:</label>
                    <input type="text" name="f_finan" id="f_finan" class="formulario_control" value="<?=$v_f_finan?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="f_pago">F VTO:</label>
                    <input type="text" name="f_pago" id="f_pago" class="formulario_control" value="<?=$v_f_pago?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="dias_xvencer">DIAS VTO:</label>
                    <input type="text" name="dias_xvencer" id="dias_xvencer" class="formulario_control" value="<?=$v_dias_xvencer?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="moneda">MONEDA:</label>
                    <input type="text" name="moneda" id="moneda" class="formulario_control" value="<?=$varr_finan['simbolo']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="monto_finan">MONTO FINAN:</label>
                    <input type="text" name="monto_finan" id="monto_finan" class="formulario_control" style="text-align: right;" value="<?=$v_monto_financiado?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="monto_factura">MONTO FACTURA:</label>
                    <input type="text" name="monto_factura" id="monto_factura" class="formulario_control" style="text-align: right;" value="<?=$v_monto_factura?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
<?php
    if ($varr_finan['notifica_op'] == 0){
        echo '  <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="notifica_op">NOTIFICA OP:</label>
                    <input type="file" name="notifica_op" id="notifica_op" class="formulario_control">
                </div>';
    } else {
        echo '  <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="notifica_op">NOTIFICA OP:</label>
                    <label><a href="'.$varr_finan['notifica_comprobante'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                </div>';
    }
?>
            </div>

            <div style="overflow:hidden;background-color:var(--color-gris-oscuro);height:1px;width:100%; float:left;margin-top:20px;margin-bottom: 20px;"></div>
        </div>  <!-- CONTENEDOR FORMULARIO -->

        <!--========================================
            ================== BOTONERA ========= -->
        <nav style="margin-top: 20px;">
<?php
    if ($varr_finan['notifica_op'] == 0){
        echo '
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_noti" class="btn btn-primary" onclick="registraNotificacion()">
                    <i class="fa-solid fa-notes-medical"></i> Registra Notificacion OP
            </button>';
    }
?>
        </nav>
    </div>  <!-- CONTENEDOR PRINCIPAL -->

    </form>    
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">
        function registraNotificacion(){
            var v_doc_notifica = $('#notifica_op').val();
            var btn_notifica = document.getElementById("boton_noti");
            var accion = document.getElementById("accion");

            if (v_doc_notifica == '') alert('Debe adjuntar el comprobando de notificacion al OP');
            else {
                accion.value = 'notifica_op';
                var formData = new FormData(document.getElementById("frm_modal"));
                btn_notifica.disabled = true;

                $.ajax({
                    url:"control_facturas_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html",
                    success:function(data,status){
                        $('#InversorDetalle').fadeIn(1000).html(data);
                        $('#InversorDetalle').modal('hide');
                        refresh_page();
                    }
                });
            }
        }

    </script>
</BODY>
</HTML>