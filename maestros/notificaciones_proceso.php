<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");

$vobj_mae_proc = new maestros;

$v_i = 0;
$varr_seleccionados = array();

if (!empty($_POST['perfil_noti'])){
	foreach($_POST['perfil_noti'] as $seleccionado){
	    $varr_seleccionados[$v_i] = $seleccionado;
	    $v_i++;
	}
}

$varr_perfil_permiso_proc = $vobj_mae_proc->get_perfiles_factureate();
$varr_insert = array();
$varr_delete = array();
$v_i = 0;
$v_j = 0;

for ($i = 0; $i < count($varr_perfil_permiso_proc); $i++){
	$v_seleccionado = 0;

	if (in_array($varr_perfil_permiso_proc[$i]['id'], $varr_seleccionados)) $v_seleccionado = 1;

	if ($v_seleccionado == 1){
		if ($_POST['chkperfil-'.$varr_perfil_permiso_proc[$i]['id']] == 0){
			$varr_insert[$v_i] = $varr_perfil_permiso_proc[$i]['id'];
			$v_i++;
		}
	} else{
		if ($_POST['chkperfil-'.$varr_perfil_permiso_proc[$i]['id']] == 1){
			$varr_delete[$v_j] = $varr_perfil_permiso_proc[$i]['id'];
			$v_j++;
		}
	}
}

$vobj_mae_proc->procesa_noti_perfil($_POST['notificacion'], $varr_insert, $varr_delete);
echo '1';
?>