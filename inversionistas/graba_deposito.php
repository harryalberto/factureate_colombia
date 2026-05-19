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
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

date_default_timezone_set("America/Lima");
$v_hoy = date('Y-m-d');
$v_time_hoy = date('H:i:s');
$obj_mail = new mail_util;
$obj_cuenta = new cuentas;
$obj_mae = new maestros;
$obj_seg = new seguridad;

if ($_SESSION['user']['empresaid'] > 0){
    $arr_empresa = $obj_mae->get_datos_emisor($_SESSION['user']['empresaid']);
    $identificacion = $arr_empresa['identificacion'];
    $nombre = $arr_empresa['nombre'];
} else{
    $arr_usuario = $obj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
    $identificacion = $arr_usuario['identificacion'];
    $nombre = $arr_usuario['nombre'];
}

if (isset($_FILES['comprobante']) && $_FILES['comprobante']['name'] != ''){
    $path = '../archivos/inversionista_'.$identificacion.'/COMP_'.$_POST['cuenta_id'].'_'.$v_hoy.'_'.$_FILES['comprobante']['name'];
    $archivo = '../archivos/inversionista_'.$identificacion;
    
    //if (!file_exists($archivo)) mkdir($archivo,0700);
    if (!is_dir($archivo)) mkdir($archivo, 0777, true);
    move_uploaded_file($_FILES['comprobante']['tmp_name'],  $path);
    $path_db = 'inversionista_'.$identificacion.'/COMP_'.$_POST['cuenta_id'].'_'.$v_hoy.'_'.$_FILES['comprobante']['name'];
}

$obj_cuenta->registra_saldo_transito($_POST['cuenta_id'], $_POST['monto_depositado'], $path_db); // previo a ser procesado
//============== envio de notificaciones ===================
$arr_email = array('perfilid' => 9, 'nombre_salida' => 'FACTUREATE Cuentas Inversionista',
                        'subject' => 'Se registro saldo por parte de un inversionista',
                        'body' => 'El inversionista '.$nombre.' con DOC '.$identificacion.' a registrado saldo en transito.<br><br>FACTUREATE');
$obj_mail->enviar_correo_xperfil($arr_email);
//----- correo al inversionista
$arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                        'mail_destino' => $arr_usuario['email'],
                        'subject' => 'Comprobante de saldo registrado',
                        'body' => 'Hola '.$nombre.', usted ha registrado un saldo por '.$_POST['monto_saldo'].' '.$_POST['moneda'].', el cual ya esta en proceso de verificacion<br><br>
                                        FACTUREATE');
$obj_mail->enviar_correo($arr_mail_user);
/*--------------------------------------------------------*/
?>