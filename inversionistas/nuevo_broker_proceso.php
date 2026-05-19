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
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mail = new mail_util;
$obj_seg_proc = new seguridad;
$obj_mae_proc = new maestros;

if ($_POST['accion'] == 'nuevo'){
	if ($_SESSION['user']['empresaid'] > 0) $v_inversor_id = $_SESSION['user']['empresaid'];
	else $v_inversor_id = $_SESSION['user']['usuarioid'];

	$varr_broker = array('nrodoc' => $_POST['nrodoc'], 'inversor_id' => $v_inversor_id);

	//==== verifica si ya existe el broker o si la persona ya esta registrada
	$varr_analiza = $obj_mae_proc->analiza_nuevo_broker($varr_broker);

	if ($varr_analiza['resultado'] > 0){
		$varr_broker['identificacion'] = $varr_analiza['broker_user'];

		//==== crear al inversor como empresa
		$varr_inversor_proc = $obj_mae_proc->get_datos_inversor($v_inversor_id);
		$varr_inversor = array(	'identificacion' => $varr_inversor_proc['identificacion'], 
								'nombre' => $varr_inversor_proc['inversor_nombre'].' '.$varr_inversor_proc['inversor_apellido'],
								'direccion' => $varr_inversor_proc['direccion'], 		'sectorid' => 0,		'tamanoid' => 0,
								'actividad' => '',										
								'nombre_representante' => $varr_inversor_proc['inversor_nombre'].' '.$varr_inversor_proc['inversor_apellido'],
								'email_repre' => $varr_inversor_proc['inversor_email'],	'tipodoc_repre' => $varr_inversor_proc['tipodoc'],
								'nrodoc_repre' => $varr_inversor_proc['identificacion'],				
								'nombre_contacto' => $varr_inversor_proc['inversor_nombre'].' '.$varr_inversor_proc['inversor_apellido'],
								'email_contacto' => $varr_inversor_proc['inversor_email'],	
								'tipodoc_contacto' => $varr_inversor_proc['tipodoc'],
								'nrodoc_contacto' => $varr_inversor_proc['identificacion'],			
								'telefono_contacto' => $varr_inversor_proc['inversor_telefono'],
								'tipo_empresa' => 47,									'inversor_id' => $v_inversor_id);

		//==== creacion de la empresa con el nombre del inversor y colocar el id del inversor en la empresa
		if ($_SESSION['user']['empresaid'] == 0) $obj_mae_proc->convierte_inversor_empresa($varr_inversor);

		$_SESSION['user']['empresaid'] = $v_inversor_id;

		//==== creacion del usuario broker
		$varr_broker['email'] = $_POST['email'];
		$varr_broker['nombre'] = $_POST['nombre'];
		$varr_broker['apellido'] = $_POST['apellido'];
		$varr_broker['tipodoc'] = $_POST['tipodoc'];
		$varr_broker['tipousuario'] = 4;
		$varr_broker['perfilid'] = 3;
		$varr_broker['empresaid'] = $v_inversor_id;

		//==== creacion del usuario del broker
		$varr_usuario_broker = $obj_seg_proc->crear_usuario($varr_broker);

		$v_sql = "update usuarios set estado_activo = 2 where id = ".$varr_usuario_broker['usuarioid'];

		$conn_user = new db_param; $conn_user->connect();
		$idqry = $conn_user->query($v_sql);
        if (!$idqry) echo pg_last_error($conn_user->Link_ID);
        $conn_user->next_record();
        $conn_user->close();

		//==== enviar notificacion a legal para que revise el nuevo broker
		$varr_mail_envio = array('notificaid' => 45, 'datos_body' => '<br>INVERSOR ID: '.$v_inversor_id.'<br>INVERSOR NOMBRE: '.$varr_inversor['nombre'].'
																	<br>BROKER ID: '.$varr_usuario_broker['usuarioid'].'
																	<br>BORKER NOMBRE: '.$varr_broker['nombre'].' '.$varr_broker['apellido'].'
																	<br>IDENTIFICACION: '.$varr_usuario_broker['identificacion'].'
																	<br><br>FACTUREATE');
    	$obj_mail->enviar_correo_xnotificacion($varr_mail_envio);
	} 

	echo $varr_analiza['resultado'];
} elseif ($_POST['accion'] == 'eliminar') {
	//==== eliminacion del broker

	$obj_mae_proc->eliminar_broker($_POST['id'], $_POST['documento'], $_POST['inversor_id']);

	//==== correo de notificacion al inversor

	$varr_broker = $obj_seg_proc->get_datos_usuario($_POST['id']);

	$arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                            'mail_destino' => $_SESSION['user']['email'],
                            'subject' => 'Anulacion de broker',
                            'body' => 'Hola, la anulacion del broker que realizo mediante la plataforma se ejecuto con exito, con lo cual '.$varr_broker['nombre'].' '.$varr_broker['apellido'].' ya no podra acceder a la plataforma
                            	<br><br>* tildes omitidas intencionalmente
                            	<br><br>Cordialmente,
                                <br><br>FACTUREATE');
    $obj_mail->enviar_correo($arr_mail_user);

    echo 1;
} elseif ($_POST['accion'] == 'aprueba_broker') {
	//==== busco informacion del inversor
	$varr_inversor_emp = $obj_mae_proc->get_datos_empresa($_POST['empresa_id']);

	if ($varr_inversor_emp['inversor_natural'] == 1){
		//==== el inversor es persona natural

		$varr_inversor_nat = $obj_mae_proc->get_inversor_detalle($_POST['empresa_id']);

		//==== defino la carpeta donde se guarda el informe

		$v_apellidos = ltrim(rtrim($varr_inversor_nat['apellido']));
    	$varr_apellidos = explode(" ", $v_apellidos);
    	$v_carpeta = '../archivos/INV_'.$varr_inversor_nat['nombre'].'_'.$varr_apellidos[0].'_'.$varr_apellidos[1].'_'.$varr_inversor_nat['identificacion'].'/brokers';
    	$v_nombre_inversor = $varr_inversor_nat['nombre'].' '.$varr_inversor_nat['apellido'];
	} else {
		//==== el inversor es una empresa juridica
		//==== defino la carpeta donde se guarda el informe
		
		$v_carpeta = '../archivos/empresa_'.$varr_inversor_emp['identificacion'].'/brokers';
		$v_nombre_inversor = $varr_inversor_emp['nombre'];
	}

	//==== registro el informe en la carpeta

	if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

	if (isset($_FILES['informe']) && $_FILES['informe']['name'] != ''){
	    $v_file_informe = $v_carpeta.'/informe_'.$_FILES['informe']['name'];
	    move_uploaded_file($_FILES['informe']['tmp_name'],  $v_file_informe);
	}

	//==== aprobacion del broker

	$obj_mae_proc->aprobar_broker($_POST['id'], $v_file_informe);

	//==== cambio de pass al broker

	$v_pass = $obj_seg_proc->cambia_pass_atm($_POST['id']);

	//==== envio de correo al broker

	$varr_link = $obj_mae_proc->get_parametro_detalle(53);

	$arr_mail_user = array(	'mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                            'mail_destino' => $_POST['email'],
                            'subject' => 'Cuenta de inversion en FACTUREATE',
                            'body' => 'Hola '.$_POST['nombre'].' '.$_POST['apellido'].', bienvenido a FACTUREATE, nuestro inversor '.$v_nombre_inversor.' lo ha registrado para que usted pueda invertir a su nombre, a continuacion le enviamos los accesos a nuestra plataforma:
                            	<br><br>Plataforma: <a href="'.$varr_link['valorchar'].'" target="_blank">Plataforma Factureate</a>
                            	<br>Usuario: '.$_POST['documento'].'
                            	<br>Password: '.$v_pass.'
                            	<br><br>* tildes omitidas intencionalmente
                            	<br><br>Cordialmente,
                                <br><br>FACTUREATE');
    $obj_mail->enviar_correo($arr_mail_user);

    //==== envio de correo al inversor

    $arr_mail_user = array(	'mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                            'mail_destino' => $varr_inversor_emp['email_repre'],
                            'subject' => 'El Broker que usted registro fue aprobado',
                            'body' => 'Hola '.$varr_inversor_emp['nombre'].', el broker '.$_POST['nombre'].' '.$_POST['apellido'].' que usted registro ha sido aprobado, desde este momento el Broker ya puede invertir a su nombre.
                            	<br><br>* tildes omitidas intencionalmente
                            	<br><br>Cordialmente,
                                <br><br>FACTUREATE');
    $obj_mail->enviar_correo($arr_mail_user);

	echo 1;
} elseif ($_POST['accion'] == 'rechazar_broker') {
	//==== busco informacion del inversor
	$varr_inversor_emp = $obj_mae_proc->get_datos_empresa($_POST['empresa_id']);

	if ($varr_inversor_emp['inversor_natural'] == 1){
		//==== el inversor es persona natural

		$varr_inversor_nat = $obj_mae_proc->get_inversor_detalle($_POST['empresa_id']);

		//==== defino la carpeta donde se guarda el informe

		$v_apellidos = ltrim(rtrim($varr_inversor_nat['apellido']));
    	$varr_apellidos = explode(" ", $v_apellidos);
    	$v_carpeta = '../archivos/INV_'.$varr_inversor_nat['nombre'].'_'.$varr_apellidos[0].'_'.$varr_apellidos[1].'_'.$varr_inversor_nat['identificacion'].'/brokers';
	} else {
		//==== el inversor es una empresa juridica
		//==== defino la carpeta donde se guarda el informe
		
		$v_carpeta = '../archivos/empresa_'.$varr_inversor_emp['identificacion'].'/brokers';
	}

	//==== registro el informe en la carpeta

	if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

	if (isset($_FILES['informe']) && $_FILES['informe']['name'] != ''){
	    $v_file_informe = $v_carpeta.'/informe_rechazo_'.$_FILES['informe']['name'];
	    move_uploaded_file($_FILES['informe']['tmp_name'],  $v_file_informe);
	}

	//==== aprobacion del broker

	$obj_mae_proc->rechazar_broker($_POST['id'], $v_file_informe);

	//==== envio de correo al inversor

    $arr_mail_user = array(	'mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                            'mail_destino' => $varr_inversor_emp['email_repre'],
                            'subject' => 'El Broker que usted registro fue rechazado',
                            'body' => 'Hola '.$varr_inversor_emp['nombre'].', el broker '.$_POST['nombre'].' '.$_POST['apellido'].' que usted registro ha sido rechazado luego del analisis realizado por nuestra area legal, si desea mas informacion del motivo del rechazo se puede comunicar con nosotros.
                            	<br><br>* tildes omitidas intencionalmente
                            	<br><br>Cordialmente,
                                <br><br>FACTUREATE');
    $obj_mail->enviar_correo($arr_mail_user);

	echo 1;
}
?>