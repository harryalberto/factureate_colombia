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
    $acceso = 'FACTFACTU';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_mae = new maestros;
$obj_mail = new mail_util;

if ($_POST['accion'] == 'acuerdo'){
    $v_t_fpago = strtotime($_POST['fpago']);
    $v_fpago_es = date('d-m-Y',$v_t_fpago);
    $obj_factura->registra_comunicacion($_POST['empresaid'], $_POST['facturaid'], '[Acuerdo de pago] Fecha de pago: '.$v_fpago_es, '');
    $obj_factura->confirma_pago($_POST['facturaid'], $_POST['fpago']);
    $v_dt_fvencimiento = new DateTime($_POST['fvencimiento']);
    $v_dt_facuerdo = new DateTime($_POST['fpago']);
    
    if ($v_dt_fvencimiento < $v_dt_facuerdo){
        $obj_mae->registra_notificacion_inversionistas($_POST['finan_id'],15, 'La nueva fecha de pago acordada con el Obligado al Pago es '.$v_fpago_es);
        $obj_mail->enviar_notificacion_externo(15);
    }

    $v_alerta = 'alert("El acuerdo de pago fue registrado");';
} elseif ($_POST['accion'] == 'cobranza'){
    $v_t_fpago = strtotime($_POST['fpago']);
} elseif ($_POST['accion'] == 'liquidar'){      // SOLO VA  A PASAR CUANDO SE DESEA LIQUIDAR Y EL MONTO NO CUMPLE LOS REQUERIMIENTOS
    if ($_SESSION['user']['perfilid'] == 14 || $_SESSION['user']['perfilid'] == 15){    // CFO o CEO
        $obj_factura->liquida_financiamiento($_POST['facturaid'], $_POST['moneda_id'], $_POST['monto_pago'], $_POST['nro_operacion'], 72);
        $v_alerta = 'alert("La operacion fue liquidada con exito !!");';
    } else{     //SOLO DEBE TENER ACCESO EL ANALISTA FINANCIERO
        $obj_factura->preliquida_financiamiento($_POST['facturaid']);
        $v_alerta = 'alert("La operacion fue PRE-Liquidada con exito !!");';
    }
} elseif ($_POST['accion'] == 'pago'){
    $obj_factura->registra_pago($_POST['facturaid'],$_POST['nro_operacion'],$_POST['moneda_id'],$_POST['monto_pago'],$_POST['fecha_pago'],$_POST['hora_pago']);
    $v_total_pagado = $_POST['monto_pago'] + $_POST['monto_pagado'];
    $v_minimo_xpagar = $_POST['monto_factura'] * (1 - $_POST['diferencia']);

    //==== NOTIFICACION A LA FIDUCIARIA DEL PAGO RECIBIDO
    $varr_fiducia = $obj_mae->get_parametro_detalle(60);

    if ($varr_fiducia['valornum'] == 1){
        $varr_fiducia_mail = array('notificaid' => 37, 'datos_body' => '<br><br>Obligado al Pago (depositante): '.$_POST['cliente_nombre'].'<br>Operacion: '.$_POST['facturaid'].'<br>Moneda: '.$_POST['moneda'].'<br>Monto: '.number_format($_POST['monto_pago'],2,'.',',').'<br><br>FACTUREATE');
        $obj_mail->enviar_correo_xnotificacion($varr_fiducia_mail);
    }

    if ($v_total_pagado >= $v_minimo_xpagar){ 
        $obj_factura->liquida_financiamiento($_POST['facturaid'], $_POST['moneda_id'], $_POST['monto_pago'], $_POST['nro_operacion'], 72);

        if ($varr_fiducia['valornum'] == 1){
            $varr_financiamiento = $obj_factura->get_financiamiento_detalle($_POST['finan_id']);

            $varr_fiducia_mail_liq = array('notificaid' => 38, 'datos_body' => '<br><br>Operacion: '.$_POST['facturaid'].'<br>Factura Nro: '.$_POST['factura_nro'].'<br>Vendedor: '.$_POST['emisor_nombre'].'<br>Pagador: '.$_POST['cliente_nombre'].'<br>Moneda: '.$_POST['moneda'].'<br>Monto Factura: '.number_format($_POST['monto_factura'],2,'.',',').'<br>Monto Adelanto (inversion): '.number_format($varr_financiamiento['monto_financiado'],2,'.',',').'<br>Intereses Inversor: '.number_format($varr_financiamiento['ganancia'],2,'.',',').'<br>Comision Factureate: '.number_format($varr_financiamiento['comision_factureate'],2,'.',',').'<br>Remanente: '.number_format($varr_financiamiento['monto_remanente_e'],2,'.',',').'<br><br>FACTUREATE');
            $obj_mail->enviar_correo_xnotificacion($varr_fiducia_mail_liq);
        }
    }

    $v_alerta = 'alert("El pago fue registrado con exito !!");';
}

$v_pagina_retorno = 'finan_xestado.php';
$redireccion = '    <script>'.
                        $v_alerta.'
                    </script>';
//location.href = "'.$v_pagina_retorno.'";
if ($redireccion != '') echo $redireccion;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    //date_default_timezone_set("America/Lima");
    //$menu = 'facturas/finan_xestado.php';
    //------ PARTE SUPERIOR ------
    //require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    //require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>