<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura_valida = new factura;

date_default_timezone_set($_SESSION['user']['zona_horaria']);

if ($_POST['tipo_valida'] == 'NUMERO'){
	$v_resultado = $objfactura_valida->valida_param_factura('NUMERO', $_POST['nro_factura'], 0, 0, 0, 0, 0);
} elseif ($_POST['tipo_valida'] == 'MONTO') {
	$v_resultado = $objfactura_valida->valida_param_factura('MONTO', '', 0, 0, 0, $_POST['moneda_id'], $_POST['monto']);
} elseif ($_POST['tipo_valida'] == 'TIEMPO') {
	$v_f_emision = new DateTime($_POST['f_emision']);
	$v_f_vencimiento = new DateTime($_POST['f_vencimiento']);
	$v_dias = $v_f_emision->diff($v_f_vencimiento)->days;
	$v_signo = $v_f_emision->diff($v_f_vencimiento)->invert;

	if ($v_signo == 0) $v_resultado = $objfactura_valida->valida_param_factura('TIEMPO', '', 0, 0, $v_dias, 0, 0);
	else $v_resultado = -1;
}

echo $v_resultado;
?>