<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/maestros.php");
require("../lib-seg/seguridad-acceso.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/c_seguridad_trans.php");
?>

<HTML>
<HEAD>

<?php
    require("../lib/head.php");
    $acceso = 'XXXX';
    //require("../lib/valida-acceso.php");
?>

</HEAD>

<?php
$vobj_ws_seg = new seguridad_trans;

$v_input = array('nombre' => 'HUMBERTO', 'apellido' => 'BENITES SANCHEZ', 'nro_doc' => '2020111', 'tipo_persona' => 85, 'email' => 'hacespedes@yahoo.com', 'tipo_doc' => 1,
				'a_paterno' => 'BENITES', 'a_materno' => 'SANCHEZ', 'telefono' => '987979', 'direccion' => 'SANTO DOMINGO REP DOM');

$output = $vobj_ws_seg->ws_registra_inversor($v_input);
echo $output;
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

</BODY>
</HTML>