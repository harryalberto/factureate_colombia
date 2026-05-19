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
$vobj_cuenta = new cuentas;

if ($_POST['accion'] == 'registrar'){
    $varr_datos = array('emisor_id' => $_POST['emisor_id'], 'moneda_id' => $_POST['moneda_id'], 'banco_id' => $_POST['banco_id'], 'nro_cuenta' => $_POST['nro_cuenta'], 'tcuenta_id' => $_POST['tcuenta_id']);

    $v_rpta = $vobj_cuenta->registra_cuenta_banco_emisor($varr_datos);
} elseif ($_POST['accion'] == 'update'){
    $varr_datos = array('emisor_id' => $_POST['emisor_id'], 'moneda_id' => $_POST['moneda_id'], 'banco_id' => $_POST['banco_id'], 
                        'nro_cuenta' => $_POST['nro_cuenta'], 'tcuenta_id' => $_POST['tcuenta_id'], 'id' => $_POST['id']);

    $v_rpta = $vobj_cuenta->update_cuenta_banco_emisor($varr_datos);
} elseif ($_POST['accion'] == 'eliminar') {
    $v_rpta = 1;
    $vobj_cuenta->delete_cuenta_banco_emisor($_POST['emisor_id'], $_POST['moneda_id'], $_POST['id']);
}

echo $v_rpta;
?>