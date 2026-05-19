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
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_inversiones = new inversiones;

$v_arr_inv = $obj_inversiones->get_detalle_inversion($_GET['pid'],$_GET['fid']);
$v_finversion_t = strtotime($v_arr_inv['fregistro']);
$v_finversion = date('d-m-Y',$v_finversion_t);

$v_fpago_t = strtotime($v_arr_inv['f_pago_factura']);
$v_fpago = date('d-m-Y',$v_fpago_t);
$v_fvencimiento = date('d-m-Y',strtotime($v_arr_inv['f_vencimiento']));

if ($v_fpago != $v_fvencimiento) $v_fpago = '<i class="fa-solid fa-circle-exclamation" style="color:var(--color-amarillo);font-size:18px;"></i> '.$v_fpago;

if (is_null($v_arr_inv['fregistro'])) $v_finversion = '';
if (is_null($v_arr_inv['f_pago'])) $v_fpago = '';

if ($v_arr_inv['e_subasta_id'] != 26){  // subasta no liquidada
    $v_estado = $v_arr_inv['e_subasta'];
    $v_estado_desc = 'La inversi&oacute;n aun no se ha realizado, estamos realizando el proceso !!';
} else{
    $v_estado = $v_arr_inv['e_finan'];

    switch ($v_arr_inv['e_finan_id']){
        case 28:    // financiamiento pagado
            $v_estado_desc = 'Ya se recibi&oacute; el pago del instrumento estamos procesando el deposito de su inversi&oacute;n';
            break;
        case 27:    // en proceso
            $v_estado_desc = 'A&uacute;n quedan d&iacute;as para que termine su inversi&oacute;n';
            break;
        case 30;    // en cobranza
            $v_estado_desc = 'Su inversi&oacute;n esta en proceso de cobranza por falta de pago del cliente';
            break;
        case 29;    // pagada tarde
            $v_estado_desc = 'Ya se recibi&oacute; el pago del instrumento con unos d&iacute;as de retraso, estamos procesando el deposito de su inversi&oacute;n';
            break;
    }
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Santo_Domingo");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
    //max-width:700px;
?>
    <!------ CUERPO VARIABLE ------>
    <div class="contenedor_formulario" style="height: 50%;">
        <div class="contenedor_formulario_column">
            <span class="icon-coin-dollar" style="font-size:30px;color:#1F9A8E;margin-right: 10px;"></span></li>
            <div class="formulario_grupo_row" style="width:100px;">
                <label>ID OPERACION</label>
                <label><?echo $_GET['fid']?></label>
            </div>
            <div class="formulario_grupo_row" style="width:200px;">
                <label>PAGADOR</label>
                <label><?echo $v_arr_inv['cliente'];?></label>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label>F PAGO</label>
                <label><?echo $v_fpago;?></label>
            </div>
        </div>

        <div style="overflow:hidden;background-color:#555555;height:1px;width: 100%;"></div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="factura">FACTURA</label>
                <input type="text" id="factura" class="formulario_control" value="<?=$v_arr_inv['factura_numero']?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="finversion">F INVERSION</label>
                <input type="text" id="finversion" class="formulario_control" value="<?=$v_finversion?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:60px;">
                <label for="dias">DIAS INV</label>
                <input type="text" id="dias" class="formulario_control" value="<?=number_format($v_arr_inv['dias_inversion'],0,'.',',')?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="estado">ESTADO</label>
                <input type="text" id="estado" class="formulario_control" value="<?=$v_estado?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="fvencimiento">F VTO</label>
                <input type="text" id="fvencimiento" class="formulario_control" value="<?=$v_fvencimiento?>" readonly>
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_row" style="width:200px;">
                <label for="inversion">INVERSION</label>
                <input type="text" id="inversion" class="formulario_control" style="text-align: right;" value="<?=number_format($v_arr_inv['monto_inversion'],2,'.',',')?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:200px;">
                <label for="ganancia">GANANCIA EST</label>
                <input type="text" id="ganancia" class="formulario_control" style="text-align: right;" value="<?=number_format($v_arr_inv['monto_ganancia'],2,'.',',')?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="moneda">MONEDA</label>
                <input type="text" id="moneda" class="formulario_control" value="<?=$v_arr_inv['moneda']?>" readonly>
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="contenedor_formulario_column">
                <label for="notas" style="margin-right:15px;">NOTAS</label>
                <textarea id="notas" class="formulario_control" rows="4" cols="60" readonly><?echo $v_estado_desc;?></textarea>
            </div>
        </div>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>