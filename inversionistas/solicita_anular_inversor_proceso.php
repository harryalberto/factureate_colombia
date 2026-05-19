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
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PROPUESTA';
    require("../lib/valida-acceso.php");
?>

</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mail = new mail_util;
$vobj_mae = new maestros;

if ($_POST['accion'] == 'solicitud'){
    $vobj_mae->registra_solicitud_anulacion_inversor($_POST['inversor_id'], $_POST['motivo']);
    // envio de correo al inversor
    if ($_SESSION['user']['empresaid'] > 0){
        $varr_empresa = $obj_mae->get_datos_inversor($_SESSION['user']['empresaid']);
        $varr_correo_emp = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_empresa['inversor_email'],
                                'subject' => '[FACTUREATE] Solicitud de anulacion de contrato', 
                                'body' => 'Hola, usted a registrado una solicitud de anulacion de contrato, nuestros analistas estan revisando su solicitud, 
                                            en breve estaran en contacto con usted.
                                        <br><br>Cordialmente,<br><br>Operaciones FACTUREATE');
        $obj_mail->enviar_correo($varr_correo_emp);
    }

    $arr_usuario = $obj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
    $varr_correo_usuario = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $arr_usuario['email'],
                                    'subject' => '[FACTUREATE] Solicitud de anulacion de contrato', 
                                    'body' => 'Hola, usted a registrado una solicitud de anulacion de contrato, nuestros analistas estan revisando su solicitud, 
                                            en breve estaran en contacto con usted.
                                    <br><br>Cordialmente,<br><br>Operaciones FACTUREATE');
    $obj_mail->enviar_correo($varr_correo_usuario);
    // envio de alerta al COO y analista op
    if ($_SESSION['user']['empresaid'] > 0) $v_nombre = $varr_empresa['inversor_nombre'].' '.$varr_empresa['inversor_apellido'];
    else $v_nombre = $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];

    $varr_correo = array('notificaid' => 23, 'datos_body' => 'Inversor que solicito anulacion de contrato es '.$v_nomnbre.'<br><br>FACTUREATE');
    $obj_mail->enviar_correo_xnotificacion($varr_correo);

    echo '<script>
            alert("Solicitud registrada");
        </script>';
} elseif ($_POST['accion'] == 'anular_solicitud') {
    $vobj_mae->anula_solicitud_anulacion($_POST['inversor_id']);
    // envio de correo al inversor
    if ($_SESSION['user']['empresaid'] > 0){
        $varr_empresa = $obj_mae->get_datos_inversor($_SESSION['user']['empresaid']);
        $varr_correo_emp = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_empresa['inversor_email'],
                                'subject' => '[FACTUREATE] Solicitud de anulacion de contrato cancelada', 
                                'body' => 'Hola, usted a cancelado su solicitud de anulacion de contrato, le agradecemos la renovacion de la confianza en FACTUREATE.
                                        <br><br>Cordialmente,<br><br>Operaciones FACTUREATE');
        $obj_mail->enviar_correo($varr_correo_emp);
    }

    $arr_usuario = $obj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
    $varr_correo_usuario = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $arr_usuario['email'],
                                    'subject' => '[FACTUREATE] Solicitud de anulacion de contrato cancelada', 
                                    'body' => 'Hola, usted a cancelado su solicitud de anulacion de contrato, le agradecemos la renovacion de la confianza en FACTUREATE.
                                    <br><br>Cordialmente,<br><br>Operaciones FACTUREATE');
    $obj_mail->enviar_correo($varr_correo_usuario);
}
/*--------------------------------------------------------*/
?>
</HTML>