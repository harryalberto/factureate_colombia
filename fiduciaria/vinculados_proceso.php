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

$vobj_mae_proc = new maestros;
$vobj_mail_proc = new mail_util;

$varr_zone = $vobj_mae_proc->get_parametro_detalle(21);
date_default_timezone_set($varr_zone['valorchar']);
$v_hoy = date('Y-m-d');

if ($_POST['accion'] == 'dd'){
	//==== PROCESAMIENTO DEL REGISTRO DE LA DEBIDA DILIGENCIA
	if ($_FILES['doc_dd']['name'] != '' && isset($_FILES['doc_dd'])){
		if (!is_dir('../archivos/INV_'.$_POST['nro_doc'])) mkdir('../archivos/INV_'.$_POST['nro_doc'], 0777, true);

		$carpeta = '../archivos/INV_'.$_POST['nro_doc'].'/vinculacion';

		if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

		$file_path = $carpeta.'/depuracion_'.$v_hoy.'_'.$_FILES['doc_dd']['name'];
		move_uploaded_file($_FILES['doc_dd']['tmp_name'],  $file_path);
	} else $file_path = '';

	$vobj_mae_proc->dd_inversor($_POST['inversor_id'], $file_path, $_POST['tpersona_id']);
}
?>