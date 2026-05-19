<?php
session_start();
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");

$fnumero = $_POST['fnumero'];
$objmaestros = new maestros;
$resultado = $objmaestros->valida_factura_existencia($fnumero);

echo $resultado;
?>