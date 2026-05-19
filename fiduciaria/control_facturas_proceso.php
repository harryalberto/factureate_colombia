<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");

date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy = date('Y-m-d');

$vobj_factura_proc = new factura;

if ($_POST['accion'] == 'notifica_op'){
	if ($_FILES['notifica_op']['name'] != '' && isset($_FILES['notifica_op'])){
		if (!is_dir('../archivos_operaciones/Operacion_'.$_POST['factura_id'])) mkdir('../archivos_operaciones/Operacion_'.$_POST['factura_id'], 0777, true);

		$carpeta = '../archivos_operaciones/Operacion_'.$_POST['factura_id'];
		$file_path = $carpeta.'/notificaOP_'.$v_hoy.'_'.$_FILES['notifica_op']['name'];
		move_uploaded_file($_FILES['notifica_op']['tmp_name'],  $file_path);
	} else $file_path = '';

	$vobj_factura_proc->registra_notificacion_op($_POST['finan_id'],$file_path);
}

?>