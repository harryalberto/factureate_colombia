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
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PROPUESTA';
    require("../lib/valida-acceso.php");
?>

</HEAD>
<?php
/*##########################################################
################## LOGICA
############################################################*/
$vobj_subasta = new subasta;
$vobj_mail = new mail_util;

if ($_POST['accion'] == 'confirmar'){
    $vobj_subasta->confirmacion_emisor($_POST['confirma_id']);
    // ==== NOTIFICACION A LOS INVERSORES 6
    $vobj_mail->enviar_notificacion_externo(6);
    // ==== NOTIFICACION A LEGAL 5
    $vobj_mail->enviar_multicorreo_interno(5);
} else{
    $vobj_subasta->rechaza_finan_emisor($_POST['confirma_id']);
}
?>
</HTML>