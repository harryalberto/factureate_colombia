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
    <script type="text/javascript">
        function enviar(){
            document.frm.tipoaccion.value = 'envia';
            document.frm.submit();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$v_retorno = $_GET['retorno'].'?ret='.$_GET['ret'].'&tipo='.$_GET['tipo'].'&id='.$_GET['id'];
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    //require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 20px;font-weight: bold;color:#064677;padding:10px;">
        <a href="<?=$v_retorno?>"><< Regresar</a>
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <div style="overflow:hidden;font-size: 10px;margin:0px auto;width:60%;">
        <ul style="overflow:hidden;list-style:none;">
            <embed src="<?=$_GET['path']?>" type="application/pdf" width="100%" height="600px" />    
        </ul>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>