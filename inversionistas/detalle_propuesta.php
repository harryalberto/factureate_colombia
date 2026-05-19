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
    <script type="text/javascript">
        function acciones(accion){
            document.frm_modal.accion.value = accion;
            
            if (accion == 'anular'){
                var confirma = confirm("Esta seguro de anular la propuesta?");

                if (confirma == true){
                    var estado_propuesta = Number(document.frm_modal.estado_propuesta_id.value);

                    if (estado_propuesta == 35) alert("Lo sentimos, la propuesta esta compensada y no se puede anular, comuniquese con un asesor de Factureate");
                    else{
                        document.frm_modal.action = 'detalle_propuesta_proceso.php';
                        document.frm_modal.submit();
                    }
                }
            }
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_inversiones = new inversiones;

$v_arr_inv = $obj_inversiones->get_propuesta($_GET['pid']);
$v_fpropuesta_t = strtotime($v_arr_inv['f_creacion']); $v_fpropuesta = date('d-m-Y',$v_fpropuesta_t);
$v_fvencimiento_t = strtotime($v_arr_inv['fecha_vencimiento']); $v_fvencimiento = date('d-m-Y',$v_fvencimiento_t);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Santo_Domingo");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
    
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="propuesta_id" value="<?=$_GET['pid']?>">
        <input type="hidden" name="factura_id" value="<?=$_GET['fid']?>">
        <input type="hidden" name="estado_propuesta_id" value="<?=$v_arr_inv['e_propuesta_id']?>">
        <input type="hidden" name="cliente_nombre" value="<?=$v_arr_inv['cliente_nombre']?>">
        <input type="hidden" name="monto" value="<?=$v_arr_inv['monto']?>">
        <input type="hidden" name="moneda" value="<?=$v_arr_inv['moneda']?>">
        <input type="hidden" name="accion">
    </form>
    <div class="frmtransaccion" style="font-size:12px;">
        <ul>
            <li style="margin-left:32px;font-weight: bold;width:100px;">ID OPERACION</li>
            <li style="font-weight: bold;width:210px;">PAGADOR</li>
            <li style="font-weight: bold;width:200px;">F PAGO ESTIMADO</li>
        </ul>
        <ul>
            <li><span class="icon-coin-dollar" style="font-size:25px;color:#1F9A8E;"></span></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?echo $_GET['fid'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:70px;"><?echo $v_arr_inv['cliente_nombre'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:30px;"><?echo $v_fvencimiento;?></li>
        </ul>

        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">INFORMACION DE LA PROPUESTA:</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:110px;">F PROPUESTA:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">DIAS INVERSION:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">TASA GANANCIA (TIA):</li>
        </ul>
<?php
    date_default_timezone_set("America/Santo_Domingo");
    $v_fecha_hoy = date('Y-m-d H:i:s');
    $v_dt_hoy = new DateTime($v_fecha_hoy);
    $v_dt_vencimiento = new DateTime($v_arr_inv['fecha_vencimiento']);
    $v_diferencia = $v_dt_hoy->diff($v_dt_vencimiento);
    $v_tasa_tia = $v_arr_inv['propuesta_tia']*100;
?>
        <ul>
            <li style="margin-left:5px;width:130px;"><?echo $v_fpropuesta;?></li>
            <li style="margin-left:5px;width:130px;"><?echo $v_diferencia->days;?></li>
            <li style="margin-left:5px;width:80px;"><?echo $v_tasa_tia.' %';?></li>
        </ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:110px;">MONEDA:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">MONTO PROPUESTO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">GANANCIA ESTIMADA:</li>
        </ul>
        <ul>
            <li style="margin-left:5px;width:130px;"><?echo $v_arr_inv['moneda'];?></li>
            <li style="width:150px;"><?echo number_format($v_arr_inv['monto'],2,'.',',');?></li>
            <li style="width:100px;"><?echo number_format($v_arr_inv['ganancia'],2,'.',',');?></li>
        </ul>
        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
        <hr>
        <button type="button" class="btn btn-primary" onclick="acciones('anular')" style="font-size:12px;background-color:var(--color-rojo);border:none;">
            <span class="icon-cross" style="font-size:16px;"></span> Anular</button>
        
    <?
    //<ul style="margin-top:10px;">
    //echo '
      //      <li class="botontransaccionrojo"><a href=javascript:acciones("anular")><span class="icon-cross"></span> Anular</a></li>';
      //</ul>
    ?>
        
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>