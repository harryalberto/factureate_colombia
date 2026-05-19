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

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@ LOGICA
$vobj_seg = new seguridad;
$vobj_mae = new maestros;
$vobj_mail = new mail_util;

$v_pass = $vobj_seg->genera_pass();

$varr_usuario_emisor = array('empresaid' => $_POST['emisor_id'], 'nombre' => $_POST['nombre'], 'apellido' => $_POST['apellido'], 'email' => $_POST['email'], 'telefono' => $_POST['telefono'],
                            'tipodoc' => $_POST['tipodoc_id'], 'identificacion' => $_POST['nro_doc'], 'password' => $v_pass, 'tipousuario' => 3, 'perfilid' => 5);

$v_rpta = $vobj_seg->crear_usuario($varr_usuario_emisor);

// ENVIO DE CORREO AL NUEVO USUARIO
$varr_emisor = $vobj_mae->get_datos_emisor($_POST['emisor_id']);
$varr_acceso = $vobj_mae->get_parametro_detalle(53);

$varr_correo = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_usuario_emisor['email'], 
                    'subject' => '[FACTUREATE] Has sido registrado como usuario de FACTUREATE !!', 
                    'body' => 'Hola '.$varr_usuario_emisor['nombre'].' '.$varr_usuario_emisor['apellido'].',<br><br>La empresa '.$varr_emisor['nombre'].', lo ha registrado como usuario de FACTUREATE, con lo cual usted podra registrar facturas que la empresa en mencion desee obtener un adelanto mediante nuestra plataforma, el acceso lo podra realizar utilizando la siguiente informacion:<br><br>Acceso:'.$varr_acceso['valorchar'].'<br>Usuario:'.$_POST['nro_doc'].'<br>Pass:'.$v_pass.'<br><br>Cordialmente,<br>FACTUREATE');

$vobj_mail->enviar_correo($varr_correo);

echo $v_pass;
?>