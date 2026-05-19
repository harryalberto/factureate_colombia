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
require("../lib-trans/c_cuentas.php");

$vobj_mae = new maestros;
$vobj_seg = new seguridad;
$vobj_mail = new mail_util;

// CREACION DE LA EMPRESA
$varr_empresa = array('identificacion' => $_POST['ruc'], 'nombre' => $_POST['nombre'], 'direccion' => $_POST['direccion'], 'sectorid' => $_POST['sector_id'],
					'tamanoid' => $_POST['tamano_id'], 'actividad' => $_POST['actividad'], 'nombre_repre' => $_POST['nombre_repre'], 'email_repre' => $_POST['email_repre'],
					'tipodoc_repre' => $_POST['tipodoc_repre'], 'nrodoc_repre' => $_POST['nrodoc_repre'], 'pathdoc_repre' => '', 'pathvigencia_repre' => '',
					'nombre_contacto' => $_POST['nombre_contacto'], 'email_contacto' => $_POST['email_contacto'], 'tipodoc_contacto' => $_POST['tipodoc_contacto'],
					'nrodoc_contacto' => $_POST['nrodoc_contacto'], 'telefono_contacto' => $_POST['telefono_contacto']);
$v_emisor_id = $vobj_mae->registro_empresa($varr_empresa);

// CREACION DE USUARIO ADMINISTRADOR
$v_pass = $vobj_seg->genera_pass();

$varr_usuario_emisor = array('empresaid' => $v_emisor_id, 'nombre' => $_POST['nombre_repre'], 'apellido' => '', 'email' => $_POST['email_repre'], 'telefono' => $_POST['telefono_contacto'],
                            'tipodoc' => $_POST['tipodoc_repre'], 'identificacion' => $_POST['nrodoc_repre'], 'password' => $v_pass, 'tipousuario' => 3, 'perfilid' => 4);

$v_rpta = $vobj_seg->crear_usuario($varr_usuario_emisor);

// ENVIO DE CORREO AL NUEVO USUARIO
//$varr_emisor = $vobj_mae->get_datos_emisor($_POST['emisor_id']);
$varr_acceso = $vobj_mae->get_parametro_detalle(53);

$varr_correo = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $_POST['email_repre'], 
                    'subject' => '[FACTUREATE] Has sido registrado como usuario de FACTUREATE !!', 
                    'body' => 'Hola '.$_POST['nombre_repre'].',<br><br>Hemos registrado su empresa '.$_POST['nombre'].', como EMISOR en nuestra plataforma, le recomendamos ingresar con las siguientes credenciales y presionar la opción de ENVIAR, con lo cual enviará su información para darle de alta y pueda financiarse, a través, de sus facturas:<br><br>Acceso:'.$varr_acceso['valorchar'].'<br>Usuario:'.$_POST['nrodoc_repre'].'<br>Pass:'.$v_pass.'<br><br>Cordialmente,<br>FACTUREATE');

$vobj_mail->enviar_correo($varr_correo);

echo $_POST['nombre'];
?>