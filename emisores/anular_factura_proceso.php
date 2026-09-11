<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");

$objfactura = new factura;
$emp = new maestros;

// compruebo que la llamada del post es valida
if (isset($_POST['facturaid'])){
    $rpta = $objfactura->anula_factura_v2($_POST['facturaid']);
    echo $rpta;
} else echo -1;
?>
