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
<?php
    require("../lib/head.php");
    $acceso = 'FACTFACTU';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$obj_mae = new maestros;
$obj_mail = new mail_util;

$v_facturaid = $_POST['facturaid'];
$v_empresaid = $_POST['empresaid'];
$v_retorno = $_POST['retorno'];
$v_mensaje = $_POST['mensaje'];
$v_contacto = $_POST['contacto'];
$v_accion = $_POST['accion'];
$v_fpago = $_POST['fpago'];

$v_t_fpago = strtotime($v_fpago);
$v_fpago_es = date('d-m-Y',$v_fpago);

$v_dt_fvencimiento = new DateTime($_POST['fvencimiento']);
$v_dt_facuerdo = new DateTime($v_fpago);

if ($v_accion == 'confirma') $v_mensaje .= $v_mensaje.' - Fecha de pago = '.$v_fpago_es;
$v_comuid = $objfactura->registra_comunicacion($v_empresaid, $v_facturaid, $v_mensaje, $v_contacto);
if ($v_accion == 'confirma') $objfactura->confirma_pago($v_facturaid, $v_fpago);

if ($v_dt_fvencimiento < $v_dt_facuerdo){
    $v_arr_finan = $objfactura->get_financiamiento_xfactura($v_facturaid);
    $obj_mae->registra_notificacion_inversionistas($v_arr_finan['finan_id'],15, 'La nueva fecha de pago acordada con el Obligado al Pago es '.$v_fpago_es);
    $obj_mail->enviar_notificacion_externo(15);
}

if ($v_retorno == 'facturasxvencer') $v_pagina_retorno = 'facturas_xvencer.php';
else $v_pagina_retorno = '../panel/panel_analista.php';

$redireccion = '    <script>
                        location.href = "'.$v_pagina_retorno.'";
                    </script>';

if ($redireccion != '') echo $redireccion;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php
    date_default_timezone_set("America/Lima");
    
    $menu = 'facturas/facturas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>