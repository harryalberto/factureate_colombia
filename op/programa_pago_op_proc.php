<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

//====== logica
$vobj_factura_proc = new factura;
$vobj_mae_proc = new maestros;
$obj_mail = new mail_util;

$v_t_fpago = strtotime($_POST['f_prog']);
$v_fpago_es = date('d-m-Y',$v_t_fpago);

$vobj_factura_proc->confirma_pago($_POST['factura_id'], $_POST['f_prog']);
$v_dt_facuerdo = new DateTime($_POST['f_prog']);

$v_financiamiento = $vobj_factura_proc->get_financiamiento_xfactura($_POST['factura_id']);

$vobj_mae_proc->registra_notificacion_inversionistas($v_financiamiento['finan_id'],15, 'La nueva fecha de pago acordada con el Obligado al Pago es '.$v_fpago_es);
$obj_mail->enviar_notificacion_externo(15);

$output = [];
$output['resultado'] = 1;

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>