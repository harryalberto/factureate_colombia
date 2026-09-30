<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");

$vobj_cta_proc = new cuentas;

$varr_saldos_proc = $vobj_cta_proc->get_saldos($_POST['inversor_id'],0);
$v_disponible = 0;

for ($i = 0; $i < count($varr_saldos_proc); $i++){
    if ($varr_saldos_proc[$i]['moneda_id'] == $_POST['moneda_id']){
        $v_disponible = $varr_saldos_proc[$i]['saldo_disponible'];
        break;
    }
}

echo $v_disponible;
?>