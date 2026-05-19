<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../lib-seg/seguridad-acceso.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PANELANALI';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'PANEL';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    BODY
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>