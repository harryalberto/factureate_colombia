<?php
session_start();
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
    <script type="text/javascript">
        function retorna(resultado){
            window.opener.retrievevalidafactura(resultado);
            window.close();
        }
    </script>
</HEAD>
<?
$fnumero = $_GET['fnumero'];
$objmaestros = new maestros;
$resultado = $objmaestros->valida_factura_existencia($fnumero);
?>
<BODY>
    <?
    echo '<script languaje="javascript">
            retorna("'.$resultado.'");
        </script>';
    ?>
</BODY>
</HTML>