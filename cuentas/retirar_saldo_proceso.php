<?php
session_start();
header('Content-Type: application/json');
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/c_cuentas.php");

//++++++ objetos
$vobj_mae_proc = new maestros;
//++++++ logica de negocio

//+++ verificacion si hay integracion con el banco
$varr_integracion = $vobj_mae_proc->get_parametro_detalle(91);

if ($varr_integracion['valornum'] == 0){
	//+++ no hay integracion con el banco
	//+++ generar orden de transferencia
	$varr_ot = array(	'destinatario_id' => $_POST['inversor_id'],		'cuenta' => $_POST['nro_cuenta'],			'destinatario' => $_POST['inversor'],
						'moneda_id' => $_POST['moneda_id'],				'monto' => $_POST['monto'],					'cuenta_id' => $_POST['cuenta_id'],
						'cuenta_banco_id' => $_POST['cuenta_banco_id'],	'destino_tipo_id' => 74,					'estado_id' => 46,
						'motivo_id' => 92);

	$vobj_mae_proc->registra_orden_transferencia_v2($varr_ot);
	echo 1;
} else {
	//+++ integracion con el banco
	echo 2;
}

?>