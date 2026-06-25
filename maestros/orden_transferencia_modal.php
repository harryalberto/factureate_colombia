<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'FINANCIAMIENTO';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(p_accion){
            var validacion = 0;
            
            if (p_accion == 'transferir'){
                var nro_operacion = document.frm_modal.nro_operacion.value;
                var monto_transferencia = Number(document.frm_modal.monto_transferencia.value);
                var comprobante = document.frm_modal.comprobante.value;
                
                if (nro_operacion == '' || monto_transferencia <= 0) alert('Debe completar los datos de la transferencia');
                else{
                    if (comprobante == '') alert ('Debe ingresar el comprobante de la transferencia');
                    else validacion = 1;
                }
            }

            if (validacion > 0){
                document.frm_modal.action = 'orden_transferencia_proceso.php';
                document.frm_modal.accion.value = p_accion;
                document.frm_modal.submit();
            }
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;

$varr_ot = $obj_mae->get_orden_transferencia($_GET['ot_id']);
    
$t_forden = strtotime($varr_ot['fecha_orden']);
$v_forden = date('d-m-Y',$t_forden);
$v_fhoy_en = date('Y-m-d');
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Santo_Domingo");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <div class="frmtransaccion" style="font-size:12px;">
        <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
            <input type="hidden" name="ot_id" value="<?=$_GET['ot_id']?>">
            <input type="hidden" name="moneda_id" value="<?=$varr_ot['moneda_id']?>">
            <input type="hidden" name="f_hoy" value="<?=$v_fhoy_en?>">
            <input type="hidden" name="motivo_id" value="<?=$varr_ot['motivo_id']?>">
            <input type="hidden" name="identificacion" value="<?=$varr_ot['identificacion']?>">
            <input type="hidden" name="operacion_id" value="<?=$varr_ot['operacion_id']?>">
            <input type="hidden" name="destinatario_id" value="<?=$varr_ot['destinatario_id']?>">
            <input type="hidden" name="email_contacto" value="<?=$varr_ot['email_contacto']?>">
            <input type="hidden" name="destinatario" value="<?=$varr_ot['destino_nombre']?>">
            <input type="hidden" name="subasta_id" value="<?=$varr_ot['subasta_id']?>">
            <input type="hidden" name="accion" value="">
        <!-- datos del financiamiento -->
        <ul>
            <li style="margin-left:32px;font-weight: bold;width:80px;">ID ORDEN</li>
            <li style="font-weight: bold;width:210px;">DESTINATARIO</li>
            <li style="font-weight: bold;width:200px;">MOTIVO</li>
        </ul>
        <ul>
            <li><span class="icon-coin-dollar" style="font-size:25px;color:#1F9A8E;"></span></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?php echo $_GET['ot_id'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:50px;"><?php echo $varr_ot['destino_nombre'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?php echo $varr_ot['motivo'];?></li>
        </ul>
        
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">INFORMACION DE LA TRANSFERENCIA:</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:80px;">F ORDEN:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:210px;">BANCO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:110px;">CUENTA:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:135px;">TIPO CUENTA:</li>
        </ul>
        <ul>
            <li><input type="text" name="fecha_orden_label" value="<?=$v_forden?>" class="frminput_text_off" style="text-align:center;" size="10" readonly></li>
            <li><input type="text" name="banco" size="30" value="<?=$varr_ot['banco']?>" class="frminput_text_off" style="text-align:left;" readonly></li>
            <li><input type="text" name="cuenta_banco" size="15" value="<?=$varr_ot['cuenta_banco']?>" class="frminput_text_off" style="text-align:center;" readonly></li>
            <li><input type="text" name="tipo_cuenta" size="15" value="<?=$varr_ot['tcuenta']?>" class="frminput_text_off" style="text-align:center;" readonly></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:145px;">TIPO DESTINO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:110px;">MONTO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:130px;">MONEDA:</li>
        </ul>
        <ul>
            <li><input type="text" name="tipo_destino" value="<?=$varr_ot['destino_tipo']?>" class="frminput_text_off" style="text-align:center;" readonly></li>
            <li><input type="text" name="monto_label" size="15" value="<?=number_format($varr_ot['monto'],2,'.',',')?>" class="frminput_text_off" style="text-align:right;" readonly></li>
            <li><input type="text" name="moneda" size="15" value="<?=$varr_ot['moneda']?>" class="frminput_text_off" style="text-align:center;" readonly></li>
        </ul>
        
        <!-- ========= EJECUCION DE TRANSFERENCIA -->
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">EJECUCION DE TRANSFERENCIA:</li>
        </ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:145px;">NRO OPERACION:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:145px;">MONEDA:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">MONTO:</li>
        </ul>
        <ul>
            <li><input type="text" name="nro_operacion" placeholder="# operacion banco" class="frminput_text"></li>
            <li><input type="text" name="moneda" value="<?=$varr_ot['moneda']?>" class="frminput_text_off" readonly></li>
            <?php $v_monto = round($varr_ot['monto'],2);?>
            <li><input type="number" name="monto_transferencia" value="<?=$v_monto?>" class="frminput_text" style="text-align:right;" size="15"></li>
        </ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:120px;">COMPROBANTE:</li>
            <li><input type="file" name="comprobante" id="comprobante" class="frminput_text"></li>
        </ul>

    
    <!--//=============== BOTONES-->

<?php
    $accion = "'transferir'";
    echo '
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="acciones('.$accion.')"><i class="fa-solid fa-money-bill-transfer"></i> Transferir</button>
        </ul>';
        //<li class="botontransaccionazul"><a href=javascript:acciones("transferir")><span class="icon-shrink2"></span> Transferir</a></li>
    ?>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    
</BODY>
</HTML>