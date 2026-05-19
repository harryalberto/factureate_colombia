<?php
session_start();
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");

$identificacion = $_POST['identificacion'];
$objmaestros = new maestros;
$empresa = $objmaestros->get_nombre_empresa_xdoc($identificacion);
echo $empresa;
?>
