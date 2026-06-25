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
$vobj_mae = new maestros;

$varr_emisor = $vobj_mae->get_datos_emisor_full($_POST['emisor_id']);

if ($_POST['accion'] == 'registrar'){
    $v_file_path_db = '/pdf/EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'].'/cuentas/'.$_FILES['certificado']['name'];

    $varr_datos = array('emisor_id' => $_POST['emisor_id'], 'moneda_id' => $_POST['moneda_id'], 'banco_id' => $_POST['banco_id'], 'nro_cuenta' => $_POST['nro_cuenta'],
                        'tcuenta_id' => $_POST['tcuenta_id'], 'certificado' => $v_file_path_db);

    $v_rpta = $vobj_cuenta->registra_cuenta_banco_emisor($varr_datos);

    // UPLOAD DEL CERTIFICADO BANCARIO
    $v_carpeta = $_SERVER['DOCUMENT_ROOT'].'/pdf/EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'].'/cuentas';
    $v_file_path = $v_carpeta.'/'.$_FILES['certificado']['name'];

    if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

    move_uploaded_file($_FILES['certificado']['tmp_name'],  $v_file_path);
} elseif ($_POST['accion'] == 'update'){
    if (isset($_FILES['certificado']) && $_FILES['certificado']['name'] != ''){
        $v_file_path_db = '/pdf/EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'].'/cuentas/'.$_FILES['certificado']['name'];

        $v_carpeta = $_SERVER['DOCUMENT_ROOT'].'/pdf/EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'].'/cuentas';
        $v_file_path = $v_carpeta.'/'.$_FILES['certificado']['name'];

        if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

        move_uploaded_file($_FILES['certificado']['tmp_name'],  $v_file_path);
    } else $v_file_path_db = $_POST['certificado_old'];

    $varr_datos = array('emisor_id' => $_POST['emisor_id'], 'moneda_id' => $_POST['moneda_id'], 'banco_id' => $_POST['banco_id'], 
                        'nro_cuenta' => $_POST['nro_cuenta'], 'tcuenta_id' => $_POST['tcuenta_id'], 'id' => $_POST['id'], 'certificado' => $v_file_path_db);

    $v_rpta = $vobj_cuenta->update_cuenta_banco_emisor($varr_datos);
} elseif ($_POST['accion'] == 'eliminar') {
    $v_rpta = 1;
    $vobj_cuenta->delete_cuenta_banco_emisor($_POST['emisor_id'], $_POST['moneda_id'], $_POST['id']);
}

echo $v_rpta;
?>