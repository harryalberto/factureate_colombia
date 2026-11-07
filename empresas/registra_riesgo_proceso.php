<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");

//++++ declaracion de objetos
$vobj_mae_proc = new maestros;

// arreglo de respuesta
$output = [];

if ($_POST['tipo_riesgo'] == 'EMISOR'){
	$v_cadena_datos_variables = $_POST['cadena_datos_variables'];
	$varr_datos_variables = explode('-', $v_cadena_datos_variables);        // arreglo con los datos
	$v_indice = 0;
	$varr_datos = array('empresa_id' => $_POST['empresa_id']);

	foreach ($varr_datos_variables as $dato){
		if ($v_indice == 0) $varr_datos['variable_id'] = $dato;
		if ($v_indice == 1) $varr_datos['param_id'] = $dato;
		if ($v_indice == 2) $varr_datos['param_valor'] = $dato;

		// registro la variable
		if ($v_indice == 3){
			$varr_datos['justi'] = $dato;
			$vobj_mae_proc->guarda_riesgos_variables_emisor($varr_datos);
			$v_indice = 0;
		} else $v_indice ++;
	}

	$vobj_mae_proc->calcula_riesgo_factura_comercial($_POST['empresa_id']);
	$output['respuesta'] = 'OK';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>