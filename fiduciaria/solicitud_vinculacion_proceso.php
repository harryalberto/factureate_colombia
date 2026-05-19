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

if ($_FILES['doc_depuracion']['name'] != '' && isset($_FILES['doc_depuracion'])){
	if (!is_dir('../archivos/INV_'.$_POST['nro_doc'])) mkdir('../archivos/INV_'.$_POST['nro_doc'], 0777, true);

	$carpeta = '../archivos/INV_'.$_POST['nro_doc'].'/vinculacion';

	if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

	$file_path = $carpeta.'/depuracion_'.$v_hoy.'_'.$_FILES['doc_depuracion']['name'];
	move_uploaded_file($_FILES['doc_depuracion']['tmp_name'],  $file_path);
} else $file_path = '';

$v_resultado = $vobj_mae_proc->aprobar_depuracion_inversor($_POST['accion'], $_POST['inversor_id'], $file_path, $_POST['tpersona_id']);

if ($v_resultado == 'CONTRATO MANUAL'){
	//---- NOTIFICACION A LEGAL
	$varr_notificacion = array('notificaid' => 33, 'datos_body' => 'Inversor: '.$_POST['nombre'].' '.$_POST['apellido'].'<br><br>Factureate App');
	$vobj_mail_proc->enviar_correo_xnotificacion($varr_notificacion);
} elseif ($v_resultado == 'RECHAZADO'){
	//---- NOTIFICACION AL CEO Y LEGAL
	$varr_notificacion = array('notificaid' => 34, 'datos_body' => 'Inversor: '.$_POST['nombre'].' '.$_POST['apellido'].'<br><br>Factureate App');
	$vobj_mail_proc->enviar_correo_xnotificacion($varr_notificacion);
} else {
	//---- CONTRATO AUTOMATICO
	$varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $_POST['email'], 
						'subject' => '[FACTUREATE] Contrato de vinculacion como Inversor en Factureate',
						'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', nos complace saludarte y agradecer tu interes en invertir con nosotros, el ultimo paso para que puedas hacer rendir tu dinero mediante nuestra plataforma es firmando biometricamente el contrato en el siguiente link:<br><br>Contrato: '.$v_resultado.' <br><br>Una vez firmado el contrato tendras acceso a la plataforma para poder iniciar a invertir.<br><br>Cordialmente,<br><br>FACTUREATE');

	$vobj_mail_proc->enviar_correo($varr_mail);
}
?>