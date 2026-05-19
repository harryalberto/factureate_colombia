<?php
session_start();
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");

$objmaestros = new maestros;

$v_resultado = $objmaestros->valida_parametros_factura($_POST['emisor_id'], $_POST['cliente_doc']);

echo $v_resultado;
?>
