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

$vobj_seg_proc = new seguridad;
$vobj_mail_proc = new mail_util;
$vobj_mae_proc = new maestros;

$output = [];

if ($_POST['usuario_id'] == 0){
	//==== NUEVO USUARIO
	$v_password = $vobj_seg_proc->genera_pass();
	$varr_usuario = array(	'identificacion' => $_POST['identificacion'], 	'password' => $v_password, 					'email' => $_POST['email'],
							'nombre' => $_POST['nombre'],					'apellido' => $_POST['apellido'],			'tipodoc' => $_POST['tipodoc'],
							'tipousuario' => $_POST['tipousuario'],			'perfilid' => $_POST['perfil'],				'empresaid' => $_POST['empresa'],
							'telefono' => $_POST['telefono'], 				'fiduciaria_id' => $_POST['fiduciaria'],	'url' => '');

	$varr_resultado = $vobj_seg_proc->crear_usuario($varr_usuario);
	$varr_acceso = $vobj_mae_proc->get_parametro_detalle(53);

	//==== NOTIFICACION AL USUARIO
	$varr_mail = array(	'mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $_POST['email'], 
						'subject' => '[FACTUREATE] Se ha creado su acceso a la plataforma Factureate',
						'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', nos complace saludarte y comentarte que se ha creado tu acceso a la plataforma Factureate, los datos de tu acceso son los siguientes, no olvides cambiar el password:<br><br>Plataforma: '.$varr_acceso['valorchar'].'<br>Usuario: '.$_POST['identificacion'].'<br>Password: '.$v_password.'<br><br>Cordialmente,<br><br>FACTUREATE');

	$vobj_mail_proc->enviar_correo($varr_mail);

	$output['data'] = 'ok';
} else {
	//==== MODIFICACION DE USUARIO
	$varr_datos = array('usuario_id' => $_POST['usuario_id'],'perfil_id' => $_POST['perfil']);
	$vobj_seg_proc->actualiza_datos_usuario($varr_datos);
	$output['data'] = 'ok';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>