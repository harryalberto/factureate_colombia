<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

$vobj_mae_ajax = new maestros;

$output = [];

if ($_POST['tipo'] == 'buro'){
	$v_puntaje_conv = $vobj_mae_ajax->convertir_ScoreBuro($_POST['puntaje_buro'], $_POST['buro']);	// valor redondeado en 2 decimales
	$v_puntaje_conv_format = number_format($v_puntaje_conv,2,'.',',');
	$varr_nivel_riesgo = $vobj_mae_ajax->getRiesgoid_xpuntaje($v_puntaje_conv);

	$output['puntaje_conv'] = $v_puntaje_conv;
	$output['puntaje_conv_view'] = $v_puntaje_conv_format;
	$output['riesgo_id'] = $varr_nivel_riesgo['riesgo_id'];
	$output['calificacion'] = $varr_nivel_riesgo['calificacion'];
	$output['color'] = $varr_nivel_riesgo['color'];
	$output['color_fuente'] = $varr_nivel_riesgo['color_fuente'];
} elseif ($_POST['tipo'] == 'cumplimiento_pago') {
	$output['calificacion'] = $vobj_mae_ajax->calcula_calificacion_cumple_pago_op($_POST['op_id'], $_POST['historia_pago'], $_POST['dias_promedio']);
} elseif ($_POST['tipo'] == 'calcula_tamanho'){
	$varr_param_tamanho = $vobj_mae_ajax->get_risk_parametros(0,'TAMANHO OP');

	for ($i = 0; $i < count($varr_param_tamanho); $i++){
		if ($varr_param_tamanho[$i]['valor_num'] == $_POST['tamanho_id']){
			$output['rango'] = '[$ '.number_format($varr_param_tamanho[$i]['valor_minimo'],2,'.',',').' - $ '.number_format($varr_param_tamanho[$i]['valor_maximo'],2,'.',',').']';
			$output['calificacion_tamanho'] = $varr_param_tamanho[$i]['calificacion'];
			break;
		}
	}
} elseif ($_POST['tipo'] == 'calcula_operando'){
	$varr_param_operando = $vobj_mae_ajax->get_risk_parametros(0,'ANTIGUEDAD OP');

	for ($i = 0; $i < count($varr_param_operando); $i++){
		if ($varr_param_operando[$i]['valor_minimo'] <= $_POST['anhos'] && $varr_param_operando[$i]['valor_maximo'] >= $_POST['anhos']){
			$output['factor'] = $varr_param_operando[$i]['calificacion'];
			break;
		}
	}
} elseif ($_POST['tipo'] == 'calcula_riesgo_op'){
	$v_factor_total = 0;
	$v_calificacion_f = 0;

	//++++ verifico variable de riesgo concentracion si esta activo o no
	$varr_concentracion_param = $vobj_mae_ajax->get_risk_parametros(30,'');

	// variables de la dimension op
	$varr_variables = $vobj_mae_ajax->get_risk_variables(0,0,1);

	// calculo el factor total
	for ($i = 0; $i < count($varr_variables); $i++){
		if ($varr_variables[$i]['variable_padre'] == 0){
			if ($varr_variables[$i]['variable_id'] == 1){
				if ($_POST['historia_pago'] == 'SI') $v_factor_total = $v_factor_total + $varr_variables[$i]['impacto'];
			} elseif ($varr_variables[$i]['variable_id'] == 5){
				if ($_POST['emp_riesgoid'] > 0 && $_POST['puntaje_buro'] > 0) $v_factor_total = $v_factor_total + $varr_variables[$i]['impacto'];
			} else $v_factor_total = $v_factor_total + $varr_variables[$i]['impacto'];
		}
	}

	// calculo el puntaje de riesgos
	for ($i = 0; $i < count($varr_variables); $i++){
		if ($varr_variables[$i]['variable_padre'] == 0){
			if ($varr_variables[$i]['variable_id'] == 1) $v_calificacion_f = $v_calificacion_f + ($_POST['calificacion_pago'] * ($varr_variables[$i]['impacto'] / $v_factor_total));
			if ($varr_variables[$i]['variable_id'] == 2) $v_calificacion_f = $v_calificacion_f + ($_POST['calificacion_perfil'] * ($varr_variables[$i]['impacto'] / $v_factor_total));
			if ($varr_variables[$i]['variable_id'] == 5) $v_calificacion_f = $v_calificacion_f + ($_POST['puntaje_buro_conv'] * ($varr_variables[$i]['impacto'] / $v_factor_total));
			if ($varr_variables[$i]['variable_id'] == 6) $v_calificacion_f = $v_calificacion_f + ($_POST['puntaje_macro'] * ($varr_variables[$i]['impacto'] / $v_factor_total));
		}
	}

	if ($varr_concentracion_param['valor_num'] == 1){
		// considerar la variable de concentracion
		$v_factor_concentracion = $vobj_mae_ajax->get_variable_concentracion($_POST['empresa_id']);
		$v_calificacion_f = $v_calificacion_f * $v_factor_concentracion;
	}

	//obtengo los datos del nivel de riesgo de factureate
	$varr_info_riesgos = $vobj_mae_ajax->get_calificacion_riesgo_xpuntaje($v_calificacion_f);

	// asigno los valores a devolver
	$output['puntaje_f'] = number_format($v_calificacion_f,2,'.',',');
	$output['calificacion_f'] = $varr_info_riesgos['calificacion'];
	$output['nom_calificacion_f'] = $varr_info_riesgos['nombre'];

	// identificar el logo del nivel de riesgo
	if ($varr_info_riesgos['nivel'] >= 6) $output['logo_calificacion'] = '<i class="fa-solid fa-square-check" style="color:#'.$varr_info_riesgos['color'].'; font-size:20px;"></i>';
	elseif ($varr_info_riesgos['nivel'] == 4 || $varr_info_riesgos['nivel'] == 5) $output['logo_calificacion'] = '<i class="fa-solid fa-square-check" style="color:#'.$varr_info_riesgos['color'].';font-size:20px;"></i>';
	else $output['logo_calificacion'] = '<i class="fa-solid fa-triangle-exclamation" style="color:#'.$varr_info_riesgos['color'].';font-size:20px;"></i>';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>