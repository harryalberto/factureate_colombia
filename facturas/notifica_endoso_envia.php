<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

$vobj_factura_noti = new factura;
$vobj_mae_noti = new maestros;
$vobj_mail_noti = new mail_util;

date_default_timezone_set($_SESSION['user']['zona_horaria']);

$output = 0;

if ($_POST['accion'] == 'envia'){
	$varr_noti = $vobj_factura_noti->get_detalle_noti_endoso($_POST['factura_id']);
	$varr_factura = $vobj_factura_noti->get_datos_factura($_POST['factura_id']);
	$varr_parametros = $vobj_mae_noti->get_parametros();

	//=== envio mail al OP
	//$v_mail_op = $vobj_mae_noti->valida_correo_op($varr_factura['clienteid']);
    $v_mail_op = $_POST['email'];

	if ($v_mail_op == '' || $v_mail_op == 0) $output = -1;
	else {
		if ($varr_factura['monedaid'] == 20){
            $v_cuenta_factu = $varr_parametros['CUENTA NACIONAL']['valorchar'];
            $varr_tcuenta_factu = $vobj_mae_noti->get_tipo_detalle($varr_parametros['CUENTA NACIONAL']['valornum']);
            $v_tcuenta_factu = $varr_tcuenta_factu['nombre'];
            $varr_banco_factu = $vobj_mae_noti->get_banco_detalle($varr_parametros['BANCO CTA NACIONAL']['valornum']);
            $v_banco_factu = $varr_banco_factu['nombre'];
        } else {
            $v_cuenta_factu = $varr_parametros['CUENTA DOL']['valorchar'];
            $varr_tcuenta_factu = $vobj_mae_noti->get_tipo_detalle($varr_parametros['CUENTA DOL']['valornum']);
            $v_tcuenta_factu = $varr_tcuenta_factu['nombre'];
            $varr_banco_factu = $vobj_mae_noti->get_banco_detalle($varr_parametros['BANCO CTA DOL']['valornum']);
            $v_banco_factu = $varr_banco_factu['nombre'];
        }

        $varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                    'mail_destino' => $v_mail_op,
                                    'subject' => 'ENDOSO DE UNA FACTURA DE SU PROVEEDOR '.$varr_factura['emisor'],
                                    'body' => 'Sres. '.$varr_factura['cliente'].', los saludamos cordialmente para comunicarle que su proveedor '.$varr_factura['emisor'].', ha endosado la factura Nro
                                                '.$varr_factura['factura'].' a favor nuestro, somos '.$varr_parametros['RAZON SOCIAL FACTUREATE']['valorchar'].', empresa dedicada a financiar
                                                PYMEs mediante sus facturas, lo cual representa que al vencimiento de la factura en mencion debe realizar el pago respectivo en nuestra cuenta
                                                bancaria:<br>
                                                - Nro Cuenta: '.$v_cuenta_factu.'<br>
                                                - Tipo Cuenta: '.$v_tcuenta_factu.'<br>
                                                - Banco: '.$v_banco_factu.'<br>
                                                - Moneda: '.$varr_factura['moneda'].'<br><br>
                                                Cualquier duda o coordinacion la pueden realizar con nuestro '.$varr_parametros['CARGO PARA OP']['valorchar'].'
                                                '.$varr_parametros['NOMBRE PARA OP']['valorchar'].' al correo '.$varr_parametros['EMAIL PARA OP']['valorchar'].' o al telefono
                                                '.$varr_parametros['TELF PARA OP']['valorchar'].'<br><br>
                                                Cordialmente,<br>
                                                FACTUREATE');

        $vobj_mail_noti->enviar_correo($varr_mail);
        $vobj_mae_noti->envia_noti_endoso($varr_noti['id'], $_POST['factura_id'],  $varr_noti['estado_id'], 122,'');
        $output = 1;
	}
}

echo $output;
?>