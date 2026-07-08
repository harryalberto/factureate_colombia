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
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'GESTEMP';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*#######################################################################
------ LOGICA NO VISIBLE 
#######################################################################*/
$obj_mae = new maestros;
$obj_mail = new mail_util;
$obj_seg = new seguridad;

$v_nro_registros = $_POST['nro_registros'];
$v_j = 0;
$varr_accionistas = array();

for ($i=0; $i<$v_nro_registros; $i++){
    if ($_POST['nombre'.$i] != '' && $_POST['area'.$i] != 0 && $_POST['nro_doc'.$i] != ''){
        echo '<script>alert('.$_POST['nombre'.$i].');</script>';
        $varr_accionistas[$v_j] = array('nombre'=>$_POST['nombre'.$i], 'tipodoc'=>$_POST['area'.$i], 'nro_doc'=>$_POST['nro_doc'.$i], 'empresa_id'=>$_POST['empresa2_id']);
        $v_j ++;
    }
}

if ($v_j > 0){
    $obj_mae->guarda_accionistas($varr_accionistas);
}

echo '  <script>
            alert("Los accionistas fueron registrados");
        </script>';
//$mensaje = 'Los accionistas furon agregados';
//#######################################################################
?>

</HTML>