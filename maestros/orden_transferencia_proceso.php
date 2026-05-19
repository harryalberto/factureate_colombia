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
    $acceso = 'FINAN';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$obj_mail = new mail_util;
$obj_subasta = new subasta;
$obj_seg = new seguridad;

if ($_POST['accion'] == 'transferir'){
    if ($_POST['motivo_id'] == 91){     // ENTREGA DE REMANENTE AL EMISOR
        // GUARDO EL COMPROBANTE EN EL SERVER
        $v_file = '../pdf/empresa_'.$_POST['identificacion'].'/OP_'.$_POST['operacion_id'];
        if (!file_exists($v_file)) mkdir($v_file,0700);

        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['name'] != ''){ //coloco el archivo en el servidor
            $v_path = $v_file.'/remanente_'.$_FILES['comprobante']['name'];
            move_uploaded_file($_FILES['comprobante']['tmp_name'],  $v_path);
        }
        // PROCESO LA TRANSFERENCIA
        $varr_datos = array('motivo_id'=>$_POST['motivo_id'], 'moneda_id'=>$_POST['moneda_id'], 'monto'=>$_POST['monto_transferencia'], 'destinatario_id'=>$_POST['destinatario_id'],
                            'operacion_id'=>$_POST['operacion_id'], 'ot_id'=>$_POST['ot_id']);
        $obj_mae->transferir_fondos_exterior($varr_datos);
        // CORREO AL EMISOR
        $varr_correo_emisor = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $_POST['email_contacto'],
                                'subject' => '[FACTUREATE] Te hemos transferido un remanente !!!!', 
                                'body' => 'Hola '.$_POST['destinatario'].', debido a que tu cliente ya pago, hemos realizado la transferencia por motivo del remanente de la factura que vendiste, 
                                            los datos a continuacion:
                                <br><br>OPERACION ID: '.$_POST['operacion_id'].'<br>MONTO REMANENTE: '.number_format($_POST['monto_transferencia'],2,'.',',').'<br>MONEDA: '.$_POST['moneda'].'
                                <br><br>Puedes verificar mayor detalle en la plataforma<br><br>FACTUREATE');
        $obj_mail->enviar_correo($varr_correo_emisor);

        $v_alerta = 'alert("La transferencia fue registrada");';
    } elseif ($_POST['motivo_id'] == 100){  
    // ADELANTO AL EMISOR
        // GUARDO COMPROBANTE EN EL SERVER
        $archivo = '../pdf/empresa_'.$_POST['identificacion'].'/OP_'.$_POST['operacion_id'];
        if (!file_exists($archivo)) mkdir($archivo,0700);

        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['name'] != ''){ //coloco el archivo en el servidor
            $fondospath = $archivo.'/adelanto-'.$_FILES['comprobante']['name'];
            move_uploaded_file($_FILES['comprobante']['tmp_name'],  $fondospath);
        }
        // registro de transferencia de fondos y liquidacion
        $v_arr_subasta = array('subasta_id'=>$_POST['subasta_id'], 'path'=>$fondospath);
        $financiamientoid = $obj_subasta->liquidar_subasta($v_arr_subasta);
        $obj_mae->termina_orden_transferencia($_POST['ot_id']);

        $arr_inversionistas = $obj_subasta->get_inversionistas_xsubasta($_POST['subasta_id']);
        //--- envio de correo a inversionistas
        for ($i=0; $i<count($arr_inversionistas); $i++){
            $arr_usuario = $obj_seg->get_datos_usuario($arr_inversionistas[$i]['inversionista_id']);
            $obj_mail->enviar_multicorreo_externo(11,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
        }
        //========= ENVIO DE CORREO AL EMISOR
        $varr_emisor_emp = $obj_mae->get_datos_empresa($_POST['destinatario_id']);
        $varr_correo_emisor = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_emisor_emp['email_contacto'],
                                'subject' => '[FACTUREATE] Te hemos transferido un adelanto !!!!', 'body' => 'Hola '.$_POST['destinatario'].', hemos realizado la transferencia por adelanto de tu factura, los datos a continuacion:
                                <br><br>OPERACION ID: '.$_POST['operacion_id'].'<br>MONTO ADELANTO: '.number_format($_POST['monto_transferencia'],2,'.',',').'<br>MONEDA: '.$_POST['moneda'].'
                                <br><br>Puedes verificar mayor detalle en la plataforma<br><br>FACTUREATE');
        $obj_mail->enviar_correo($varr_correo_emisor);
        //========= SI LOS FONDOS SON MAYORES AL PARAMETRO DE ALERTA ENVIAR CORREO AL DIRECTOR
        $varr_parametros = $obj_mae->get_parametros();

        if ($_POST['moneda_id'] == 21){     // DOLARES
            if ($varr_parametros['MIN TRANSF ALERTA DOL']['valornum'] <= $_POST['monto_transferencia']){     // EL MONTO A TRANSFERIR REQUIERE UNA ALERTA
                // CORREO A LOS DIRECTORES SOBRE LA TRANSFERENCIA QUE SUPERA LOS MAXIMOS
                $varr_correo_alerta = array('notificaid' => 21, 'datos_body' => '<br><br>OPERACION ID: '.$_POST['operacion_id'].'<br>MOTIVO: TRANSFERENCIA DE ADELANTO AL EMISOR
                                                                                <br>EMISOR: '.$_POST['destinatario'].'<br>MONTO: '.$_POST['monto_transferencia'].'
                                                                                <br>MONEDA: '.$_POST['moneda'].'<br><br>FACTUREATE');
                $obj_mail->enviar_correo_xnotificacion($varr_correo_alerta);
            }
        }

        $v_alerta = 'alert("La transferencia fue registrada");';
    }
}

$v_pagina_retorno = 'ordenes_transferencia.php';
$redireccion = '    <script>'.
                        $v_alerta.'
                        location.href = "'.$v_pagina_retorno.'";
                    </script>';

if ($redireccion != '') echo $redireccion;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?
    
    $menu = 'facturas/finan_xestado.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>