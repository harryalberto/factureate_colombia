<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/c_cuentas.php");

$vobj_mae_proc = new maestros;
$vobj_seg_proc = new seguridad;
$vobj_mail_proc = new mail_util;
$vobj_cuentas_proc = new cuentas;

date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy = date('Y-m-d');

if ($_POST['accion'] == 'grabar'){
	if ($_POST['tipo_persona'] == 86) $v_empresa_id = -1;
	else $v_empresa_id = 0;

	// CREA USUARIO
	$varr_usuario = array('identificacion' => $_POST['nro_doc'], 'password' => '0000', 'email' => $_POST['email'], 'nombre' => $_POST['nombre'],
						'apellido' => $_POST['apellido'], 'tipodoc' => $_POST['tipodoc_id'], 'tipousuario' => 4, 'perfilid' => 2, 'empresaid' => $v_empresa_id);

	$varr_usuario_result = $vobj_seg_proc->crear_usuario($varr_usuario); //usuarioid, identificacion

	if ($_POST['tipo_persona'] == 86) $v_inversor_id = $v_empresa_id;
	else $v_inversor_id = $varr_usuario_result['usuarioid'];

	if ($varr_usuario_result['usuarioid'] != -200){
	// CREA INVERSOR
		// GUARDANDO DOCUMENTO DEL INVERSOR
		if ($_FILES['documento']['name'] != '' && isset($_FILES['documento'])){
			if (!is_dir('../archivos/INV_'.$_POST['nro_doc'])) mkdir('../archivos/INV_'.$_POST['nro_doc'], 0777, true);

			$carpeta = '../archivos/INV_'.$_POST['nro_doc'].'/vinculacion';

	        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

	        $file_path = $carpeta.'/doc_inv_'.$v_hoy.'_'.$_FILES['documento']['name'];
	        move_uploaded_file($_FILES['documento']['tmp_name'],  $file_path);
		} else $file_path = '';

		$varr_inversor = array('nombre' => $_POST['nombre'], 'apellido' => $_POST['apellido'], 'email' => $_POST['email'], 'telefono' => $_POST['telefono'],
							'tipo_doc' => $_POST['tipodoc_id'], 'nro_doc' => $_POST['nro_doc'], 'direccion' => $_POST['direccion'], 'tipo_persona' => $_POST['tipo_persona'],
							'documento' => $file_path, 'inversor_id' => $v_inversor_id, 'tipo_registro' => 111);
		$vobj_mae_proc->registra_inversor($varr_inversor);

		// NOTIFICACIONES
		// INVERSOR
		$varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $_POST['email'], 
						'subject' => '[FACTUREATE] Tu registro como inversor esta en proceso',
						'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', nos complace saludarte y agradecer tu interes en invertir con nosotros, tus datos fueron registrados y estan en proceso de validación por nuestra area legal, una vez realizado el analisis legal te enviaremos un correo con los datos para que puedas acceder a nuestra plataforma y experimentar el crecimiento de sus inversiones <br><br>Cordialmente,<br><br>FACTUREATE');

		$vobj_mail_proc->enviar_correo($varr_mail);
		// LEGAL
		$vobj_mail_proc->enviar_multicorreo_interno(29);
	}

	echo $varr_usuario_result['usuarioid'];
} elseif ($_POST['accion'] == 'upd') {
	$v_inversor_id = $_POST['inversor_id'];

	if ($_FILES['documento']['name'] != '' && isset($_FILES['documento'])){
		if (!is_dir('../archivos/INV_'.$_POST['nro_doc'])) mkdir('../archivos/INV_'.$_POST['nro_doc'], 0777, true);

		$carpeta = '../archivos/INV_'.$_POST['nro_doc'].'/vinculacion';

        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $file_path = $carpeta.'/doc_inv_'.$v_hoy.'_'.$_FILES['documento']['name'];
        move_uploaded_file($_FILES['documento']['tmp_name'],  $file_path);
	} else $file_path = '';

	$varr_inversor = array('nombre' => $_POST['nombre'], 'apellido' => $_POST['apellido'], 'email' => $_POST['email'], 'telefono' => $_POST['telefono'],
							'tipo_doc' => $_POST['tipodoc_id'], 'nro_doc' => $_POST['nro_doc'], 'direccion' => $_POST['direccion'], 'tipo_persona' => $_POST['tipo_persona'],
							'documento' => $file_path, 'inversor_id' => $v_inversor_id, 'tipo_registro' => 111);

	$v_rpta = $vobj_mae_proc->upd_inversor($varr_inversor);
	echo '1';
} elseif ($_POST['accion'] == 'aprobar') {
	// GUARDO DOCUMENTOS
	if ($_FILES['plaft']['name'] != '' && isset($_FILES['plaft']) && $_POST['fideicomiso'] == 0){
		$carpeta = '../archivos/INV_'.$_POST['nro_doc'].'/vinculacion';

        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $file_path = $carpeta.'/doc_inv_'.$v_hoy.'_'.$_FILES['plaft']['name'];
        move_uploaded_file($_FILES['plaft']['tmp_name'],  $file_path);
	} else $file_path = '';

	// APRUEBA INVERSOR
	//$vobj_mae_proc->aprobar_inversor($_POST['inversor_id'], $file_path);
	$varr_fideicomiso_proc = $vobj_mae_proc->get_parametro_detalle(60);
	$vobj_mae_proc->aprobar_inversor_factureate($_POST['inversor_id'], $file_path, $varr_fideicomiso_proc['valornum']);

	// APROBACION DE CUENTAS BANCARIAS
	if ($_POST['nro_cuentas'] > 0){
		for ($i = 0; $i < $_POST['nro_cuentas']; $i++){
			if ($_POST['cuentae'.$i] == 1){
				// APROBADO
			} else {
				// REGISTRADO -- VERIFICO QUE SE HAYA APROBADO
				if (isset($_POST['checka'.$i])){
					// SELECCIONADO PARA APROBACION
					$vobj_cuentas_proc->aprobar_cuenta_banco_inversor($_POST['inversor_id'], $_POST['checka'.$i]);
				}
			}
		}
	}

	// ENVIA CORREO A LA FIDUCIARIA Y AL INVERSOR
	if ($_POST['fideicomiso'] == 1){
		// REGISTRAR ARCHIVOS EN FIDUCIARIA

		// ENVIO DE CORREO A ODOO

		// CORREO A LA FIDUCIARIA
		$varr_mail_fiducia = array('notificaid' => 32, 'datos_body' => '<br>Nombre: '.$_POST['nombre'].' '.$_POST['apellido'].'<br>Documento: '.$_POST['nro_doc'].'<br><br>FACTUREATE');
		$vobj_mail_proc->enviar_correo_xnotificacion($varr_mail_fiducia);
	} else {
		// ACTUALIZA EL USUARIO DE ACCESO
		if ($_POST['tipo_persona'] == 85){
		// PERSONA NATURAL
			$v_pass = $vobj_seg_proc->cambia_pass_atm($_POST['inversor_id']);
		}

		// NOTIFICACIONES
		// INVERSOR
		$varr_link = $vobj_mae_proc->get_parametro_detalle(53);

		$varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $_POST['email'], 
							'subject' => '[FACTUREATE] Enhorabuena!! fuiste aprobado como inversor de Factureate',
							'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', nos complace saludarte y darte la buena noticia que 
								fuiste admitido como inversor de Factureate, solo falta un paso para puedas empezar a invertir en Factureate, 
								seguidamente especificamos el link del contrato de vinculacion para que lo puedas firmar e inmediatamente podras 
								iniciar como inversor.<br><br>LINK DEL CONTRATO: 
								<a href="'.$_POST['contrato'].'" target="_blank">ACCEDER AL CONTRATO</a>
								
								<br><br>Cordialmente,<br><br>FACTUREATE');

		$vobj_mail_proc->enviar_correo($varr_mail);
		// INTERNO
		$varr_mail_interno = array('notificaid' => 30, 'datos_body' => '<br>Nombre: '.$_POST['nombre'].' '.$_POST['apellido']);
		$vobj_mail_proc->enviar_correo_xnotificacion($varr_mail_interno);
	}

	echo '1';
} elseif ($_POST['accion'] == 'guarda_contrato') {
	// GUARDA EL ARCHIVO
	if ($_FILES['contrato_firmado']['name'] != '' && isset($_FILES['contrato_firmado'])){
		$carpeta = '../archivos/INV_'.$_POST['nro_doc'].'/vinculacion';

        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $file_path = $carpeta.'/doc_inv_'.$v_hoy.'_'.$_FILES['contrato_firmado']['name'];
        move_uploaded_file($_FILES['contrato_firmado']['tmp_name'],  $file_path);
	} else $file_path = '';

	// GUARDANDO EL CONTRATO
	$vobj_mae_proc->guardar_contrato_inversor($_POST['inversor_id'], $file_path);

	if ($_POST['tipo_persona'] == 85){
		// PERSONA NATURAL
		$v_pass = $vobj_seg_proc->cambia_pass_atm($_POST['inversor_id']);
	}

	$varr_link = $vobj_mae_proc->get_parametro_detalle(53);
	//NOTIFICACION AL INVERSOR
	$varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $_POST['email'], 
						'subject' => '[FACTUREATE] Enhorabuena!! ya puedes empezar a invertir con nosotros',
						'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', nos complace saludarte y darte la buena noticia que ya puedes ingresar a la plataforma y empezar a invertir, los accesos a la plataforma son los siguientes:<br><br>USUARIO: '.$_POST['nro_doc'].'<br>PASSWORD: '.$v_pass.'<br>LINK ACCESO: '.$varr_link['valorchar'].'<br><br>Muchos exitos en tus inversiones.<br><br>Cordialmente,<br><br>FACTUREATE');

	$vobj_mail_proc->enviar_correo($varr_mail);

	//NOTIFICACION FIDUCIA
	if($_POST['fideicomiso'] == 1){
		$varr_mail_fideicomiso = array('notificaid' => 30, 'datos_body' => '<br><br>Inversor ID: '.$_POST['inversor_id'].'<br>Inversor: '.$_POST['nombre'].' '.$_POST['apellido'].'<br><br>Factureate');
		$vobj_mail_proc->enviar_correo_xnotificacion($varr_mail_fideicomiso);
	}

	echo '1';
} elseif ($_POST['accion'] == 'envia_contrato'){
	$vobj_mae_proc->envia_contrato_vinculacion_inversor($_POST['inversor_id']);

	//==== ENVIO DE CONTRATO AL INVERSOR
	$varr_link = $vobj_mae_proc->get_parametro_detalle(53);

	$varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $_POST['email'], 
						'subject' => '[FACTUREATE] Enhorabuena!! fuiste aprobado como inversor de Factureate',
						'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', nos complace saludarte y darte la buena noticia que fuiste admitido como inversor de Factureate, solo falta un paso para puedas empezar a invertir en Factureate, seguidamente especificamos el link del contrato de vinculacion para que lo puedas firmar e inmediatamente podras iniciar como inversor.<br><br>LINK DEL CONTRATO: <a href="'.$_POST['contrato'].'" target="_blank">ACCEDER AL CONTRATO</a><br><br>Cordialmente,<br><br>FACTUREATE');

	$vobj_mail_proc->enviar_correo($varr_mail);
} elseif ($_POST['accion'] == 'graba_financiero'){
	// APROBACION DE CUENTAS BANCARIAS
	if ($_POST['nro_cuentas'] > 0){
		$v_para_aprobar = 0;
		for ($i = 0; $i < $_POST['nro_cuentas']; $i++){
			if ($_POST['cuentae'.$i] == 1){
				// APROBADO
			} else {
				// REGISTRADO -- VERIFICO QUE SE HAYA APROBADO
				if (isset($_POST['checka'.$i])){
					// SELECCIONADO PARA APROBACION
					$vobj_cuentas_proc->aprobar_cuenta_banco_inversor($_POST['inversor_id'], $_POST['checka'.$i]);
					$v_para_aprobar++;
				}
			}
		}

		if ($v_para_aprobar > 0) echo '1';
		else echo '-200';
	} else echo '-200';
}
?>