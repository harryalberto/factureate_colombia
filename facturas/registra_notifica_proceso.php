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

$vobj_mae_proc = new maestros;

if (isset($_POST['factura_id'])){
	// tratamiento del archivo adjubnto
	if (isset($_FILES['notificacion']) && $_FILES['notificacion']['name'] != ''){
		$carpeta = $_SERVER['DOCUMENT_ROOT'].'/archivos_operaciones/OP_'.$_POST['factura_id'];
		if (!file_exists($carpeta)) mkdir($carpeta,0700);

		$noti_path = $carpeta.'/noti_alguacil-'.$_FILES['notificacion']['name'];
        $noti_path_db = '../archivos_operaciones/OP_'.$_POST['factura_id'].'/noti_alguacil-'.$_FILES['notificacion']['name'];
        move_uploaded_file($_FILES['notificacion']['tmp_name'],  $noti_path);
	}

	// registro del archivo y actualizacion de la notificacion
	$vobj_mae_proc->registra_noti_fisica($_POST['factura_id'], $noti_path_db);
	echo 1;
}
?>