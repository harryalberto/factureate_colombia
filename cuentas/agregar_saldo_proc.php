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
<?php
    require("../lib/head.php");
    $acceso = 'CTAEFE';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mail = new mail_util;
$obj_cuenta = new cuentas;
$obj_mae = new maestros;
$obj_seg = new seguridad;
$hoy = date('d-m-Y');

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
    $path = '../archivos/inversionista_'.$identificacion.'/COMP_'.$_POST['cuenta_id'].'_'.$hoy.'_'.$_FILES['comprobante']['name'];
    $archivo = '../archivos/inversionista_'.$identificacion;

    if (!file_exists($archivo)) mkdir($archivo,0700);
    move_uploaded_file($_FILES['comprobante']['tmp_name'],  $path);
    $path_db = 'inversionista_'.$identificacion.'/COMP_'.$_POST['cuenta_id'].'_'.$hoy.'_'.$_FILES['comprobante']['name'];
}

$obj_cuenta->registra_saldo_transito($_POST['cuenta_id'], $_POST['monto_saldo'], $path_db); // previo a ser procesado
//$obj_cuenta->procesa_saldo_transito($_POST['cuenta_id']);
//----- correo interno
$arr_email = array('perfilid' => 9, 'nombre_salida' => 'FACTUREATE Cuentas Inversionista',
                        'subject' => 'Se registro saldo por parte de un inversionista',
                        'body' => 'El inversionista '.$nombre.' con DOC '.$identificacion.' a registrado saldo en transito.<br><br>FACTUREATE PERU');
$obj_mail->enviar_correo_xperfil($arr_email);
//----- correo al inversionista
$arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                        'mail_destino' => $arr_usuario['email'],
                        'subject' => 'Comprobante de saldo registrado',
                        'body' => 'Hola '.$nombre.', usted ha registrado un saldo por '.$_POST['monto_saldo'].' '.$_POST['moneda'].', el cual ya esta en proceso de verificacion<br><br>
                                        FACTUREATE PERU');
$obj_mail->enviar_correo($arr_mail_user);
//-----------------------------
$mensaje = 'El saldo fue registrado, ser&aacute; verificado el comprobante para que vea el saldo en DISPONIBLE  ...';
$redireccion = '<script>
                    setTimeout(function(){location.href = "estado_cuenta.php";},1500);
                </script>';
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------

?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;font-size: 12px;margin:30px auto;width:40%;text-align:center;">
        <p>
        <?php
            echo $mensaje;
            echo $redireccion;
        ?>
        </p>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>