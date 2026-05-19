<?php
session_start();
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
    <script type="text/javascript">
        function retorna(empresa){
            window.opener.retrievenombreempresa(empresa);
            window.close();
        }
    </script>
</HEAD>
<?
$identificacion = $_GET['identificacion'];
$tipo = $_GET['tipo'];
$objmaestros = new maestros;
$empresa = $objmaestros->get_nombre_empresa_xdoc($identificacion);
?>
<BODY>
    <?
    echo '<script languaje="javascript">
            retorna("'.$empresa.'");
        </script>';
    ?>
</BODY>
</HTML>