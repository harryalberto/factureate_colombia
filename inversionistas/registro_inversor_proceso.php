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

$vobj_seg_proc = new seguridad;
//$vobj_inversiones_proc = new inversiones;
$vobj_mae_proc = new maestros;
$vobj_cuenta_proc = new cuentas;
$vobj_mail = new mail_util;

$resultado = 1;

if ($_POST['que_guardo'] == 'datos_personales'){
    $varr_datos = array('inversionista_id'=>$_POST['inversor_id'],  'telefono_persona' => $_POST['telefono'],   'email_persona' => $_POST['email'],
                        'estado_inversor_id' => 6,                  'tipo_documento' => $_POST['tipodoc'],      'nro_documento' => $_POST['nrodoc'],
                        'condicion_laboral' => $_POST['cond_laboral'], 'inversor_id' => $_POST['user_id']);
    
    //@@@@ CARPETA DE VINCULACION
    $v_apellidos = ltrim(rtrim($_POST['apellido']));
    $varr_apellidos = explode(" ", $v_apellidos);

    $v_nombre_inversor = $_POST['nombre'].'_'.$varr_apellidos[0].'_'.$varr_apellidos[1];
    $v_nombre_inversor_final = str_replace(" ", "_", $v_nombre_inversor);

    $v_carpeta = $_SERVER['DOCUMENT_ROOT'].'/archivos/INV_'.$v_nombre_inversor_final.'_'.$_POST['nrodoc'].'/vinculacion';

    if (isset($_FILES['file_identidad']) && $_FILES['file_identidad']['name'] != ''){
        $v_file_identidad = $v_carpeta.'/docidentidad_'.$_FILES['file_identidad']['name'];

        move_uploaded_file($_FILES['file_identidad']['tmp_name'],  $v_file_identidad);
        $varr_datos['docidentidad_path'] = $v_file_identidad;
    } else $resultado = 2;

    $vobj_mae_proc->upd_datos_inversor($varr_datos);
    $vobj_seg_proc->upd_datos_usuario($varr_datos);
    echo $resultado;
} elseif ($_POST['que_guardo'] == 'datos_cuenta'){
    //---- datos del inversor
    $varr_inversor_proc = $vobj_mae_proc->get_datos_inversor($_POST['inversor_id']);

    //---- guardo la cuenta
    $v_apellidos = ltrim(rtrim($varr_inversor_proc['inversor_apellido']));
    $varr_apellidos = explode(" ", $v_apellidos);

    $v_nombre_inversor = $varr_inversor_proc['inversor_nombre'].'_'.$varr_apellidos[0].'_'.$varr_apellidos[1];
    $v_nombre_inversor_final = str_replace(" ", "_", $v_nombre_inversor);

    $v_carpeta = $_SERVER['DOCUMENT_ROOT'].'/archivos/INV_'.$v_nombre_inversor_final.'_'.$varr_inversor_proc['identificacion'].'/cuentas';
    if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

    $v_file_certificado = $v_carpeta.'/certificado_'.$_POST['cuenta'].'_'.$_FILES['file_certificado']['name'];
    $v_file_certificado_db = '/archivos/INV_'.$v_nombre_inversor_final.'_'.$varr_inversor_proc['identificacion'].'/cuentas/certificado_'.$_POST['cuenta'].'_'.$_FILES['file_certificado']['name'];

    $varr_datos_cuenta = array('inversor_id' => $_POST['inversor_id'], 'banco_id' => $_POST['banco'], 'tcuenta_id' => $_POST['t_cuenta'], 'cuenta' => $_POST['cuenta'],
                        'moneda_id' => $_POST['moneda'], 'empresa_id' => $_POST['empresa_id'], 'certificado_path' => $v_file_certificado_db);
    
    $v_rpta_cuenta = $vobj_cuenta_proc->crear_cuenta($varr_datos_cuenta);  // retorna el numero de cuenta o 0 si ya existe

    if ($v_rpta_cuenta > 0){
        //---- guardo el certificado bancario
        if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
        move_uploaded_file($_FILES['file_certificado']['tmp_name'],  $v_file_certificado);
    }

    //---- cambia el estado del inversor para que sea analizado
    $vobj_mae_proc->envia_inversor($_POST['inversor_id']);

    //---- NOTIFICACION AL AREA LEGAL
    $varr_mail_envio = array('notificaid' => 29, 'datos_body' => 'INVERSOR ID: '.$_POST['inversor_id'].'<br>INVERSOR NOMBRE: '.$varr_inversor_proc['inversor_nombre'].' '.$varr_inversor_proc['inversor_apellido'].'<br>FACTUREATE');
    $vobj_mail->enviar_correo_xnotificacion($varr_mail_envio);
    
    echo $v_rpta_cuenta;
}

//echo json_encode($output, JSON_UNESCAPED_UNICODE);
/*--------------------------------------------------------*/
?>