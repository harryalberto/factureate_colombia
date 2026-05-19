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
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mail = new mail_util;
$obj_seg = new seguridad;
$obj_mae = new maestros;
$vobj_inversiones = new inversiones;

$varr_parametros = $obj_mae->get_parametros();

if ($_POST['accion'] == 'anular'){
    $vobj_inversiones->anular_propuesta($_POST['propuesta_id']);
    //========= CORREO AL INVERSOR
    if ($_SESSION['user']['empresaid'] > 0){
        $varr_emisor_emp = $obj_mae->get_datos_empresa($_SESSION['user']['empresaid']);
        $varr_correo_emisor = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_emisor_emp['email_contacto'],
                                'subject' => '[FACTUREATE] Su propuesta fue anulada', 'body' => 'Hola, usted a realizado la anulacion de una propuesta en la plataforma, los datos a continuación:
                                <br><br>OPERACION ID: '.$_POST['factura_id'].'<br>MONTO: '.$_POST['monto'].'<br>MONEDA: '.$_POST['moneda'].'
                                <br><br>Puedes verificar mayor detalle en la plataforma<br><br>FACTUREATE');
        $obj_mail->enviar_correo($varr_correo_emisor);
    }

    $arr_usuario = $obj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
    $varr_correo_usuario = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $arr_usuario['email'],
                                'subject' => '[FACTUREATE] Su propuesta fue anulada', 'body' => 'Hola, usted a realizado la anulacion de una propuesta en la plataforma, los datos a continuación:
                                <br><br>OPERACION ID: '.$_POST['factura_id'].'<br>MONTO: '.$_POST['monto'].'<br>MONEDA: '.$_POST['moneda'].'
                                <br><br>Puedes verificar mayor detalle en la plataforma<br><br>FACTUREATE');
    $obj_mail->enviar_correo($varr_correo_usuario);
    //========= CORREO AL ANALISTA FACTUREATE
    $varr_correo = array('notificaid' => 22, 'datos_body' => 'Operacion ID = '.$_POST['factura_id'].'<br>Propuesta ID = '.$_POST['propuesta_id'].'<br>Pagador = '.$_POST['cliente_nombre'].'<br><br>FACTUREATE');
    $obj_mail->enviar_correo_xnotificacion($varr_correo);

    $v_mensaje = 'La Propuesta fue anulada';
}
/*--------------------------------------------------------*/
$v_retorno = 'propuestas_inversion.php';
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    //$menu = 'facturas_disponibles.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#b30a1f;padding:5px;">
        Proceso de Propuestas
    </div>
    <?
    echo '<script type="text/javascript">
                alert("'.$v_mensaje.'");
                location.href = "'.$v_retorno.'";
            </script>';
    ?>
    <!------ END CUERPO VARIABLE ------>
    </BODY>
</HTML>