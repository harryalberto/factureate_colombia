<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'REGFACT';
    require("../lib/valida-acceso.php");
?>
    
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$emp = new maestros;
// compruebo que la llamada del post es valida
if (isset($_POST['facturaid'])){
    $objfactura->anular_factura($_POST['facturaid']);  
    // esta accion puede generar una notificacion
    $emp->gestiona_notificacion($_SESSION['user']['usuarioid']); 
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <?
    echo '<div style="overflow:hidden;text-align:center;font-size: 16px;font-weight: bold;width:50%;padding:30px;">
                Su factura ha sido ANULADA
            </div>
            <div style="overflow:hidden;width:50%;padding:30px;">
                <ul>
                    <li class="botontransaccion" style="width:200px;margin:auto;"><a href="../facturas/facturas.php">Ver mis Facturas</a></li>
                </ul>
            </div>';
    ?>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>