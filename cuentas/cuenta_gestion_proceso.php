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
require("../lib-trans/c_cuentas.php");
require("../lib-trans/c_subasta.php");
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
$obj_mae = new maestros;
$obj_cuentas = new cuentas;
$obj_mail = new mail_util;
$objsubasta = new subasta;

date_default_timezone_set("America/Santo_Domingo");
$v_date_hoy = date('Y-m-d');

if ($_POST['accion'] == 'agregar_saldo'){
    $v_archivo = '../pdf/inversor_'.$_POST['inversor_id'].'/transferencias';
    if (!file_exists($v_archivo)) mkdir($v_archivo,0700);
    
    if (isset($_FILES['comprobante_saldo_carga']) && $_FILES['comprobante_saldo_carga']['name'] != ''){ //coloco el archivo en el servidor
        $v_path = $v_archivo.'/ING_'.$v_date_hoy.'_'.$_FILES['comprobante_saldo_carga']['name'];
        move_uploaded_file($_FILES['comprobante_saldo_carga']['tmp_name'],  $v_path);
    }
    
    $varr_nuevo_saldo = array('cuenta_id' => $_POST['cuenta_id'], 'saldo' => $_POST['monto_saldo_carga'], 'transito_id' => 0, 'constancia' => $v_path);
    $v_resultado = $obj_cuentas->agregar_saldo_manual($varr_nuevo_saldo);
    //=============== ENVIO CORREO AL INVERSOR QUE SU SALDO SE APLICO
    $varr_inversor = $obj_mae->get_datos_inversor($_POST['inversor_id']);
    $arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                'mail_destino' => $varr_inversor['inversor_email'],
                                'subject' => '[FACTUREATE] Se agrego saldo a tu cuenta',
                                'body' => 'Hola, se ha registrado en nuestra plataforma una transferencia que has realizado:<br><br>
                                            Monto: '.number_format($_POST['monto_saldo_carga'],2,'.',',').'<br>
                                            Moneda: '.$_POST['moneda'].'<br><br>
                                            <b>[IMPORTANTE]</b> Si tenias inversiones pendientes de saldo seguramente la plataforma ya lo proceso, de lo contrario veras tu saldo disponible incrementado.<br><br>
                                            FACTUREATE RD');
        
    $obj_mail->enviar_correo($arr_mail_user);

    if ($v_resultado == 2){   // SE COMPENSADO LA SUBASTA
        //========== ENVIO DEL CORREO AL INVERSOR GENERADO EN EL PROCEDIMIENTO NOTIFICACION 6
        $obj_mail->enviar_multicorreo_externo(6, $_POST['inversor_id'], $varr_inversor['inversor_email']);
        //========== ACTIVACION DEL PROCESO PARA LA LIQUIDACION DE LA SUBASTA
        $varr_parametros = $obj_mae->get_parametros();

        if ($varr_parametros['CONTRATO EMISOR AUTOM']['valornum'] == 1)     // EXISTE INTEGRACION TECNOLOGICA DE CONTRATO DIGITAL
            $objsubasta->envia_contrado_endoso_emisor($varr_param_contrato);
        else{        // ENVIA CORREO AL RESPONSABLE LEGAL PARA QUE PREPARE EL CONTRATO
            $obj_mail->enviar_multicorreo_interno(19);  // ENVIO DE CORREO A LOS RESPONSABLES LEGALES
        }
    }

    $v_mensaje = 'El saldo fue cargado correctamente';
    if ($_POST['retorno'] == 'cuentas') $v_regresar = 'cuentas_inversion.php';
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <div id="contenedor_carga">
        <div id="carga"></div>
    </div>
<?
    $menu = 'cuentas/cuentas_inversion.php';
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
    <script>
        window.onload = function(){
            var contenedor = document.getElementById('contenedor_carga');

            contenedor.style.visibility = 'hidden';
            contenedor.style.opacity = '0';
        }
    </script>
</BODY>
</HTML>