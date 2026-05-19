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
require("../lib-trans/c_subasta.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'SUBGESTION';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objsubasta = new subasta;
$obj_mae = new maestros;
$obj_mail = new mail_util;
$obj_seg = new seguridad;
$vobj_cuenta_proc = new cuentas;

if ($_POST['accion'] == 'liquidar'){
    $financiamientoid = $objsubasta->liquidar_subasta($_POST['subastaid']);
    
    $archivo = '../pdf/SUB'.$_POST['subastaid'];
    if (!file_exists($archivo)) mkdir($archivo,0700);
    
    if (isset($_FILES['transferenciafile']) && $_FILES['transferenciafile']['name'] != ''){ //coloco el archivo en el servidor
        $transferenciapath = '../pdf/'.'SUB'.$_POST['subastaid'].'/transferenciatit-'.$_FILES['transferenciafile']['name'];
        move_uploaded_file($_FILES['transferenciafile']['tmp_name'],  $transferenciapath);
    }
    if (isset($_FILES['fondosfile']) && $_FILES['fondosfile']['name'] != ''){ //coloco el archivo en el servidor
        $fondospath = '../pdf/'.'SUB'.$_POST['subastaid'].'/fondos-'.$_FILES['fondosfile']['name'];
        move_uploaded_file($_FILES['fondosfile']['tmp_name'],  $fondospath);
    }

    $arr_inversionistas = $objsubasta->get_inversionistas_xsubasta($_POST['subastaid']);
    //--- envio de correo a inversionistas
    for ($i=0; $i<count($arr_inversionistas); $i++){
        $arr_usuario = $obj_seg->get_datos_usuario($arr_inversionistas[$i]['inversionista_id']);
        $obj_mail->enviar_multicorreo_externo(11,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
    }

    $v_mensaje = 'La SUBASTA '.$_POST['subastaid'].' fue LIQUIDADA con Nro de liquidaci&oacute;n '.$financiamientoid;
    $v_regresar = 'subastas.php';
} elseif($_POST['accion'] == 'fondos'){     // REGISTRO MANUAL DE ENVIO DE FONDOS AL EMISOR
    $archivo = '../pdf/empresa_'.$_POST['emisor_identificacion'].'/OP_'.$_POST['factura_id'];
    if (!file_exists($archivo)) mkdir($archivo,0700);

    if (isset($_FILES['fondosfile']) && $_FILES['fondosfile']['name'] != ''){ //coloco el archivo en el servidor
        $fondospath = $archivo.'/adelanto-'.$_FILES['fondosfile']['name'];
        move_uploaded_file($_FILES['fondosfile']['tmp_name'],  $fondospath);
    }
    // registro de transferencia de fondos y liquidacion
    $v_arr_subasta = array('subasta_id'=>$_POST['subastaid'], 'path'=>$fondospath);
    $financiamientoid = $objsubasta->liquidar_subasta($v_arr_subasta);

    $arr_inversionistas = $objsubasta->get_inversionistas_xsubasta($_POST['subastaid']);
    //--- envio de correo a inversionistas
    for ($i=0; $i<count($arr_inversionistas); $i++){
        $arr_usuario = $obj_seg->get_datos_usuario($arr_inversionistas[$i]['inversionista_id']);
        $obj_mail->enviar_multicorreo_externo(11,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
    }
    //========= ENVIO DE CORREO AL EMISOR
    $varr_emisor_emp = $obj_mae->get_datos_empresa($_POST['emisor_id']);
    $varr_correo_emisor = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_emisor_emp['email_contacto'],
                                'subject' => '[FACTUREATE] Te hemos transferido un adelanto !!!!', 'body' => 'Hola '.$_POST['emisor'].', hemos realizado la transferencia por adelanto de tu factura, los datos a continuación:
                                <br><br>OPERACION ID: '.$_POST['factura_id'].'<br>MONTO ADELANTO: '.$_POST['monto_adelanto_f'].'<br>MONEDA: '.$_POST['moneda'].'
                                <br><br>Puedes verificar mayor detalle en la plataforma<br><br>FACTUREATE');
    $obj_mail->enviar_correo($varr_correo_emisor);
    //========= SI LOS FONDOS SON MAYORES AL PARAMETRO DE ALERTA ENVIAR CORREO AL DIRECTOR
    $varr_parametros = $obj_mae->get_parametros();

    if ($_POST['moneda_id'] == 21){     // DOLARES
        if ($varr_parametros['MIN TRANSF ALERTA DOL']['valornum'] <= $_POST['monto_adelanto']){     // EL MONTO A TRANSFERIR REQUIERE UNA ALERTA
            // CORREO A LOS DIRECTORES SOBRE LA TRANSFERENCIA QUE SUPERA LOS MAXIMOS
            $varr_correo_alerta = array('notificaid' => 21, 'datos_body' => '<br><br>OPERACION ID: '.$_POST['factura_id'].'<br>MOTIVO: TRANSFERENCIA DE ADELANTO AL EMISOR
                                                                            <br>EMISOR: '.$_POST['emisor'].'<br>MONTO: '.$_POST['monto_adelanto_f'].'
                                                                            <br>MONEDA: '.$_POST['moneda'].'<br><br>FACTUREATE');
            $obj_mail->enviar_correo_xnotificacion($varr_correo_alerta);
        }
    }

    $v_mensaje = 'Se liquido la SUBASTA de la operación '.$_POST['factura_id'];
    $v_regresar = 'subastas_comp.php';
} elseif($_POST['accion'] == 'envio_contrato'){     
// ENVIO DEL CONTRATO AL EMISOR
    $arr_datos = array('emisor_identificacion' => $_POST['emisor_identificacion'], 'emisor_nombre' => $_POST['emisor'], 'emisor_correo' => $_POST['emisor_correo'], 'cliente' => $_POST['cliente_nombre']);
    $objsubasta->envia_contrato($_POST['subastaid'], $_POST['link_envio'], $arr_datos);
    
    $v_valida_correo_cli = $obj_mae->valida_correo_op($_POST['cliente_id']);

    if ($v_valida_correo_cli == 0){
    // ENVIA NOTIFICACION A LEGAL Y OPERACIONES QUE FALTA EL CORREO DEL OP
        $varr_notificacion = array('notificaid' => 31, 'datos_body' => 'OP: '.$_POST['cliente'].'<br><br>FACTUREATE');
        $obj_mail->enviar_correo_xnotificacion($varr_notificacion);
    }

    // VALIDACION DE LA CUENTA BANCARIA DEL EMISOR
    $v_valida_cuentas = $vobj_cuenta_proc->valida_cuenta_banco_emisor($_POST['emisor_id'], $_POST['moneda_id']);

    if ($v_valida_cuentas == 2){
        $varr_correo_alerta = array('notificaid' => 40, 'datos_body' => '<br><br>EMISOR: '.$_POST['emisor'].'<br>
                                                                            <br>MONEDA: '.$_POST['moneda'].'<br><br>FACTUREATE');
        $obj_mail->enviar_correo_xnotificacion($varr_correo_alerta);
    } elseif ($v_valida_cuentas == 3){
        $varr_emisor_det = $obj_mae->get_datos_empresa($_POST['emisor_id']);
        $varr_mail_emisor = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_emisor_det['email_contacto'],
                                    'subject' => '[FACTUREATE] Pendiente registro de cuentas bancarias', 
                                    'body' => 'Hola '.$_POST['emisor'].', estas a punto de recibir el adelanto de una de tus facturas, para ello necesitamos que registres una cuenta bancaria en '.$_POST['moneda'].' para depositarte alli el adelanto, puedes registrar tus cuentas bancarias en la opción PERFIL del menu, si no registras una cuenta no podremos depositar el adelanto.<br><br>FACTUREATE');
        $obj_mail->enviar_correo($varr_mail_emisor);
    }

    $v_mensaje = 'Se registro el envio del contrato al vendedor';
    $v_regresar = 'subastas_comp.php';
} elseif($_POST['accion'] == 'contrato'){           
// RECEPCION DEL CONTRATO FIRMADO POR EL EMISOR
    $archivo = '../pdf/empresa_'.$_POST['emisor_identificacion'].'/contratos';
    if (!file_exists($archivo)) mkdir($archivo,0700);
    
    if (isset($_FILES['contrato']) && $_FILES['contrato']['name'] != ''){ //coloco el archivo en el servidor
        $contrato_path = $archivo.'/operacion-'.$_POST['factura_id'].'-'.$_FILES['contrato']['name'];
        move_uploaded_file($_FILES['contrato']['tmp_name'],  $contrato_path);
    }
    //############## registro del contrato firmado y GENERA LA ORDEN DE TRANSFERENCIA MANUAL SI NO SE ESTA INTEGRADO AL BANCO
    $objsubasta->recibe_contrato_firmado($_POST['subastaid'], $contrato_path);
    $varr_parametros = $obj_mae->get_parametros();

    //==== PREPARANDO CORREO A LA FIDUCIA
    $varr_fiducia = $obj_mae->get_parametro_detalle(60);

    if ($varr_fiducia['valornum'] == 1){
        $varr_inversores = $objsubasta->get_inversores_win_subasta($_POST['subastaid']);
        $v_inversores = '';

        for ($i=0; $i<count($varr_inversores); $i++){
            if ($v_inversores == '') $v_inversores .= $varr_inversores[$i]['inversor_id'].' - '.$varr_inversores[$i]['inversor_nombre'];
            else $v_inversores .= ' || '.$varr_inversores[$i]['inversor_id'].' - '.$varr_inversores[$i]['inversor_nombre'];
        }

        $v_monto_adelanto = number_format($_POST['monto_adelanto'],2,'.',',');
        $v_monto_factura = number_format($_POST['monto_factura_orig'],2,'.',',');

        $varr_mail_fiducia = array('notificaid' => 36, 'datos_body' => '<br><br>Operacion ID: '.$_POST['factura_id'].'<br>Factura Nro: '.$_POST['factura_numero'].'<br>F Vencimiento: '.$_POST['fvencimiento'].'<br>Vendedor: '.$_POST['emisor'].'<br>Pagador: '.$_POST['cliente_nombre'].'<br>Inversor: '.$v_inversores.'<br>Monto Inversion: '.$v_monto_adelanto.'<br>Monto Factura: '.$v_monto_factura.'<br>Moneda: '.$_POST['moneda'].'<br><br>El Monto Inversion es el monto que se le acaba de transferir como adelanto al vendedor de la factura.<br><br>Cordialmente,<br><br>FACTUREATE');
    }

    if($varr_parametros['INTEGRACION CON BANCO']['valornum'] == 0){      // NO HAY INTEGRACION CON EL BANCO, ES MANUAL LA INTERACCION
        //====== REGISTRO LA ORDEN DE TRANSFERENCIA MANUAL
        $obj_mae->registra_orden_transferencia($_POST['factura_id'], $_POST['monto_adelanto'], 100);    // OT PARA ADELANTO AL EMISOR
        //====== CORREO AL ANALISTA FINANCIERO PARA QUE REALICE LA TRANSFERENCIA AL EMISOR
        $varr_emisor = $obj_mae->get_datos_emisor_full($_POST['emisor_id']);
        $varr_datos_mail = array('notificaid' => 20, 'datos_body' => 'EMISOR: '.$varr_emisor['nombre'].'<br>BANCO: '.$varr_emisor['banco_nombre'].'
                                                                    <br>TIPO CUENTA: '.$varr_emisor['tcuenta_banco'].'<br>CUENTA NRO: '.$varr_emisor['nro_cuenta_banco'].'
                                                                    <br>MONEDA: '.$_POST['moneda'].'<br>MONTO A TRANSFERIR: '.number_format($_POST['monto_adelanto'],2,'.',',').'
                                                                    <br><br>FACTUREATE');
        $obj_mail->enviar_correo_xnotificacion($varr_datos_mail);

        //==== NOTIFICACION A LA FIDUCIARIA
        if ($varr_fiducia['valornum'] == 1) $obj_mail->enviar_correo_xnotificacion($varr_mail_fiducia);
    } else{     // ES AUTOMATICA LA ORDEN DE TRANSFERENCIA AL BANCO
        $objsubasta->ordena_transferencia_automatica($varr_datos);

        //==== NOTIFICACION A LA FIDUCIARIA
        if ($varr_fiducia['valornum'] == 1) $obj_mail->enviar_correo_xnotificacion($varr_mail_fiducia);
    }

    $v_mensaje = 'Se registro el contrato firmado por el vendedor';
    $v_regresar = 'subastas_comp.php';
} elseif($_POST['accion'] == 'endoso'){           // registro del endoso    @@ NO APLICA PARA RD
    $archivo = '../pdf/SUB'.$_POST['subastaid'];
    if (!file_exists($archivo)) mkdir($archivo,0700);
    
    if (isset($_FILES['endoso']) && $_FILES['endoso']['name'] != ''){ //coloco el archivo en el servidor
        $endoso_path = '../pdf/'.'SUB'.$_POST['subastaid'].'/endoso-'.$_FILES['endoso']['name'];
        move_uploaded_file($_FILES['endoso']['tmp_name'],  $endoso_path);
    }
    //############## registro del endoso
    $objsubasta->registra_endoso($_POST['subastaid'], $endoso_path);

    $v_mensaje = 'Se registro el endoso';
    $v_regresar = 'subastas_comp.php';
} elseif ($_POST['accion'] == 'terminar'){          
    //====== EL ANALISTA TERMINA MANUALMENTE LA SUBASTA
    if ($_POST['porc_minimo'] > $_POST['porc_propuestas']){
    // ANULAR LA SUBASTA PORQUE NO SE CONSIGUIO EL FINANCIAMIENTO MINIMO
        //..
    } else{
        $objsubasta->termina_subasta($_POST['subastaid']);
        $v_mensaje = 'La SUBASTA de la OPERACION '.$_POST['factura_id'].' fue TERMINADA';
        $v_regresar = 'subastas.php';

        if ($_POST['porc_propuestas'] >= 1){
            //====== NOTIFICACION A INVERSORES EN CASO DE QUE LA SUBASTA SE TERMINO E INICIARA LA COMPENSACION
            $arr_inversionistas = $objsubasta->get_inversionistas_xsubasta($_POST['subastaid']);
            
            for ($i=0; $i<count($arr_inversionistas); $i++){
                $arr_usuario = $obj_seg->get_datos_usuario($arr_inversionistas[$i]['inversionista_id']);
                $obj_mail->enviar_multicorreo_externo(6,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
                $obj_mail->enviar_multicorreo_externo(7,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
            }

            //====== NOTIFICACION INTERNA
            $varr_mail_legal = array('notificaid'=>5, 'datos_body'=>'OPERACION ID: '.$_POST['factura_id'].'<br>PAGADOR: '.$_POST['cliente_nombre'].'<br><br>APP FACTUREATE');
            $obj_mail->enviar_correo_xnotificacion($varr_mail_legal);   //-- INICIO DE COMPENSACION LEGAL        
        } else{
            //====== NO SE LLEGO AL 100% PERO SI AL MINIMO
            //====== NOTIFICACION PARA QUE EL EMISOR CONFIRME EL MONTO CONSEGUIDO
            $obj_mail->enviar_notificacion_externo(9);
        }
    }
} elseif ($_POST['accion'] == 'extender'){      // ==== EXTENSION DEL TIEMPO DE LA SUBASTA
    $objsubasta->extender_subasta($_POST['subastaid'], $_POST['dias_extension']);
    //=== NOTIFICACION AL EMISOR
    $arr_mail_user = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                'mail_destino' => $_POST['emisor_correo'],
                                'subject' => '[FACTUREATE ]EXTENSION DEL TIEMPO PARA SU FINANCIAMIENTO',
                                'body' => 'Se ha extendido en '.$_POST['dias_extension'].' día(s) en conseguirle financiamiento, esto para encontrar la solucion mas económica para su empresa:<br><br>
                                            Cliente: '.$_POST['cliente_nombre'].'<br>
                                            Factura: '.$_POST['factura_numero'].'<br>
                                            ID Operación: '.$_POST['factura_id'].'<br>
                                            Monto Adelanto: '.number_format($_POST['monto_subasta'],2,'.',',').' '.$_POST['moneda'].'<br><br>
                                            Todo seguimiento que desee hacer a la operación debe considerar el ID OPERACION en la plataforma.<br><br>
                                            FACTUREATE RD');
        
    $obj_mail->enviar_correo($arr_mail_user);
    
    $v_mensaje = 'Se extendio el plazo de la subasta';
    $v_regresar = 'subastas.php';
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'subastas/subastas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;font-size: 12px;margin:30px auto;width:40%;text-align:center;">
        <?echo '<p>'.$v_mensaje.'</p>';?>
        <br><br>
        <p style="width:200px;margin:0px auto;" class="botontransaccion"><a href="<?=$v_regresar?>">Volver</a></p>
    </div>
    <script>
        alert('<?=$v_mensaje?>');
        setTimeout(function(){location.href = "<?=$v_regresar?>";},500);
    </script>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>