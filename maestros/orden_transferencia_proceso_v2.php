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

$obj_mae_proc = new maestros;
$obj_mail = new mail_util;
$obj_subasta = new subasta;
$obj_seg = new seguridad;

date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy = date('d-m-Y');
$hora24 = date("H:i:s");

if ($_POST['accion'] == 'transferencia_inver'){
    //+++ transferencia al inversor manual
    //+++ guardar el archivo de comprobante
    $v_carpeta = '../archivos_operaciones/RETIROS_INVERSOR';
    if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['name'] != ''){
        $v_file_path = $v_carpeta.'/'.$v_hoy.'_'.$_FILES['comprobante']['name'];
        move_uploaded_file($_FILES['comprobante']['tmp_name'],  $v_file_path);
    }

    $varr_transferencia = array(    'motivo' => 92,                 'moneda_id' => $_POST['moneda_id'],                 'monto' => $_POST['monto'],
                                    'destinatario_id' => 0,         'usuario_id' => $_SESSION['user']['usuarioid'],     'operacion_id' => 0,
                                    'ot_id' => $_POST['ot_id'],     'atm' => 0);

    //+++ ejecutar la transferencia manual
    $v_transferencia = $obj_mae_proc->registra_transferencia_bancaria($varr_transferencia);

    if ($v_transferencia == 1){
        //+++ envio de correo de confirmacion al inversor
        $varr_inversor = $obj_mae_proc->get_datos_inversor($_POST['inversor_id']);

        $varr_correo = array(   'mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_inversor['inversor_email'],
                                'subject' => '[FACTUREATE] Hemos transferido su retiro de capital !!!!',
                                'body' => 'Hola '.$varr_inversor['inversor_nombre'].' '.$varr_inversor['inversor_apellido'].', el retiro de capital que realizo ha sido transferido, los
                                            siguientes son los datos de la transferencia:
                                <br><br>OPERACION BANCARIA: '.$_POST['operacion_banco'].'
                                <br>MONTO: '.number_format($_POST['monto'],2,'.',',').'
                                <br>MONEDA: '.$_POST['moneda'].'
                                <br>FECHA OPERACION: '.$v_hoy.' '.$hora24.'
                                <br>BANCO: '.$_POST['banco'].'
                                <br>TIPO CUENTA: '.$_POST['tcuenta'].'
                                <br>CUENTA: '.$_POST['cuenta'].'
                                <br><br>* Tildes omitidas intencionalmente');
        $obj_mail->enviar_correo($varr_correo);
    }

    echo $v_transferencia;
}

?>
