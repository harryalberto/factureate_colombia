<?php
session_start();
require("conn/conn_db.inc");
require("conn/conn_db_param.inc");
require("conn/conn_db_trans.inc");
require("conn/conn_db_param_trans.inc");
require("lib-trans/maestros.php");
require("lib-seg/seguridad-acceso.php");
require("libmail/class.phpmailer.php");
require("lib/mail_util.php");

$vobj_mae = new maestros;
$vobj_seg = new seguridad;
$vobj_mail = new mail_util;

$varr_usuario = $vobj_seg->cambiar_password_usuario($_POST['documento'], $_POST['password'], $_POST['new_password']);

/*if ($varr_usuario['sec'] == 1){
	$v_pass = $vobj_seg->cambia_pass_atm($varr_usuario['id']);

	$varr_datos_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'PLATAFORMA FACTUREATE', 'mail_destino' => $varr_usuario['email'], 
							'subject' => '[PLATAFORMA FACTUREATE] Recuperacion de password', 
							'body' => 'Hola '.$varr_usuario['nombre'].',<br><br>Hemos generado un nuevo password para su cuenta, la informacion para su acceso a sido enviada al correo electronico que tiene registrado con nosotros.<br><br>Informacion para recuperar su password:<br><br>Acceso de logueo: <a href="'.$varr_param['valorchar'].'">Acceso Plataforma FACTUREATE</a><br>Password: '.$v_pass.'<br><br>Area de Seguridad<br>Factureate');
	$vobj_mail->enviar_correo($varr_datos_mail);
}*/

echo $varr_usuario['sec'];
?>