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
<?
    require("../lib/head.php");
    $acceso = 'CTAEFE';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mail = new mail_util;
$obj_cuenta = new cuentas;
$obj_mae = new maestros;
$obj_seg = new seguridad;
$hoy = date('d-m-Y');
//-- verifica los saldos en transito verificados
if (empty($_POST['transito'])) $mensaje = 'No ha seleccionado ningun saldo en transito para confirmar !! ............. Verifique';
else{   // procesa los transito verificados
    $i = 0;

    foreach ($_POST['transito'] as $v_value){
        $arr_verificado[$i] = array('saldo_id'=>$v_value, 'inversionistaid'=>$_POST['inversionistaid'.$v_value]);
        $i++;
    }
    
    $obj_cuenta->procesa_transito_verificado($arr_verificado);
    $mensaje = 'Los saldos en transito fueron procesados .....';
    //----- correo a inversionistas
    for ($i=0; $i<count($arr_verificado); $i++){
        $arr_usuario = $obj_seg->get_datos_usuario($arr_verificado[$i]['inversionistaid']);
        $obj_mail->enviar_multicorreo_externo(6,$arr_verificado[$i]['inversionistaid'],$arr_usuario['email']);
    }
    //---- correo interno
    $obj_mail->enviar_multicorreo_interno(5);
}
//-----------------------------
$redireccion = '<script>
                    setTimeout(function(){location.href = "valida_cuenta.php";},1500);
                </script>';
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------

?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;font-size: 12px;margin:30px auto;width:40%;text-align:center;">
        <p>
        <?
            echo $mensaje;
            echo $redireccion;
        ?>
        </p>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>