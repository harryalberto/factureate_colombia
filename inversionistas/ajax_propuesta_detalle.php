<?
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/c_inversiones.php");

$obj_ajax_inversion = new inversiones;
$arr_propuesta = $obj_ajax_inversion->get_propuesta($_POST['propuesta_id']);
echo json_encode($arr_propuesta);
exit;
?>