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
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = '';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*==========================================================
========== LOGICA
==========================================================*/
$obj_cuenta = new cuentas;
$obj_mail = new mail_util;
$vobj_mae = new maestros;

$varr_st = $obj_cuenta->get_st($_POST['st_id']);
$varr_inversor = $obj_cuenta->get_datos_inversor($varr_st['inversionista_id'], $varr_st['empresa_id']);

//==== procesa el saldo en transito

$varr_st_proceso[0] = array('saldo_id'=>$_POST['st_id'], 'operacion'=>$_POST['nro_op'], 'remitente'=>$_POST['remitente']);
$obj_cuenta->procesa_transito_verificado($varr_st_proceso);

$v_monto = number_format($varr_st['monto'],2,'.',',');
//----- correo al inversionista
$v_fecha = date("d/m/Y", strtotime($varr_st['f_creacion']));

$arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                        'mail_destino' => $varr_inversor['email'],
                        'subject' => 'Deposito verificado',
                        'body' => 'Hola '.$varr_inversor['nombre_inversor'].', el deposito realizado el '.$v_fecha.' por '.$varr_st['moneda_simbolo'].' '.$v_monto.' ha sido verificado y esta
                                    disponible en su cuenta<br><br>
                                    FACTUREATE');
$obj_mail->enviar_correo($arr_mail_user);

//---- NOTIFICACION A LA FIDUCIARIA
$varr_fiducia = $vobj_mae->get_parametro_detalle(60);

if ($varr_fiducia['valornum'] == 1){
    $varr_mail_fiducia = array('notificaid' => 35, 'datos_body' => '<br><br>Inversor: '.$varr_st['nombre_inversor'].'<br>Moneda: '.$varr_st['moneda'].'<br>Monto: '.$v_monto.'<br><br>FACTUREATE');
    $obj_mail->enviar_correo_xnotificacion($varr_mail_fiducia);
}

echo '<script>
        alert("Deposito verificado");
    </script>';

//==========================================================
?>
</HTML>