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
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");

/*==========================================================
========== LOGICA
==========================================================*/
$obj_cuenta = new cuentas;
$obj_mail = new mail_util;

date_default_timezone_set($_SESSION['user']['zona_horaria']);
$hoy = date('d-m-Y');

$varr_inversor = $obj_cuenta->get_datos_inversor($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);
$v_path_db = '../archivos_inversor/INV_'.$varr_inversor['identificacion'].'/transferencias/COMP_'.$_POST['cuenta_id'].'_'.$hoy.'_'.$_FILES['comprobante']['name'];
$obj_cuenta->registra_saldo_transito($_POST['cuenta_id'], $_POST['monto'], $v_path_db, $_POST['banco_id']);
//------------ REGISTRO EL COMPROBANTE
$v_carpeta = '../archivos_inversor/INV_'.$varr_inversor['identificacion'].'/transferencias';

$v_carpeta_inv = '../archivos_inversor/INV_'.$varr_inversor['identificacion'];
if (!file_exists($v_carpeta_inv)) mkdir($v_carpeta_inv,0700);

if (!file_exists($v_carpeta)) mkdir($v_carpeta,0700);
move_uploaded_file($_FILES['comprobante']['tmp_name'],  $v_path_db);
//----- correo interno
$arr_email = array('perfilid' => 9, 'nombre_salida' => 'FACTUREATE Cuentas Inversionista',
                        'subject' => 'Se registro saldo por parte de un inversionista',
                        'body' => 'El inversionista '.$varr_inversor['nombre_inversor'].' con DOC '.$varr_inversor['identificacion'].' a registrado saldo en transito.<br><br>FACTUREATE');
$obj_mail->enviar_correo_xperfil($arr_email);
//----- correo al inversionista
$arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                        'mail_destino' => $varr_inversor['email'],
                        'subject' => 'Comprobante de saldo registrado',
                        'body' => 'Hola '.$varr_inversor['nombre_inversor'].', usted ha registrado un saldo por '.number_format($_POST['monto'],2,'.',',').' '.$_POST['moneda_nombre'].', el cual ya esta en proceso de verificacion<br><br>
                                        FACTUREATE');
$obj_mail->enviar_correo($arr_mail_user);

echo 1;

//==========================================================
?>