<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");
require("../lib-seg/seguridad-acceso.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/c_seguridad_trans.php");

$vobj_ws_seg = new seguridad_trans;

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$v_input = $_POST;

	$output = $vobj_ws_seg->ws_registra_inversor($v_input);
	header("HTTP/1.1 200 OK");
	echo json_encode($output);
	exit();
}

header("HTTP/1.1 400 Bad Request");
?>