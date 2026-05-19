<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");   //seguridad
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/c_seguridad_trans.php");
require("../lib-trans/maestros.php");
require("../lib-seg/seguridad-acceso.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

$obj_st = new seguridad_trans;
$obj_mae = new maestros;
$obj_seg = new seguridad;
$obj_mail = new mail_util;

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  $input = $_POST;

  //--- registro de la informacion del usuario
  $v_resultado = $obj_mae->registra_mensaje_web($input);
  $output = array('resultado' => 1, 'mensaje' => $v_resultado);

  //--- envio de correo al equipo Factureate
  $varr_mail = array('notificaid' => 44, 'datos_body' => 'Nombre: '.$input['nombre'].'<br><br>Factureate');
  $obj_mail->enviar_correo_xnotificacion($varr_mail);
  
  header("HTTP/1.1 200 OK");
  echo json_encode($output);
  exit();
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");
?>