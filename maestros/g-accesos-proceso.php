<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'GACCESOS';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$v_arr_eliminados = array();
$v_arr_nuevos = array();
$v_count_eliminados = 0;
$v_count_nuevos = 0;
// tratamiento de los asignados que se quitaron
$v_arr_accesos_asig = $obj_mae->get_accesos($_POST['perfil_id'],'ASIGNADO');

for ($i=0; $i<count($v_arr_accesos_asig); $i++){
    if ($_POST['chk'.$v_arr_accesos_asig[$i]['menu_id']] == 0){
        $v_arr_eliminados[$v_count_eliminados]['menu_id'] = $v_arr_accesos_asig[$i]['menu_id'];
        $v_count_eliminados++;
    }
}

if ($v_count_eliminados > 0) $obj_mae->update_accesos('DELETE',$v_arr_eliminados,$_POST['perfil_id']);
// tratamiento de los nuevos accesos
if (!empty($_POST['no_asignado'])){
    foreach($_POST['no_asignado'] as $seleccionado){
        $v_arr_nuevos[$v_count_nuevos]['menu_id'] = $seleccionado;
        $v_arr_nuevos[$v_count_nuevos]['acceso_id'] = $_POST['nasignado'.$seleccionado];
        $v_count_nuevos++;
    }
}

if ($v_count_nuevos > 0) $obj_mae->update_accesos('INSERT',$v_arr_nuevos,$_POST['perfil_id']);
////////////////////////
$v_pagina_retorno = 'g-accesos.php?perfil_id='.$_POST['perfil_id'];

$redireccion = '    <script>
                        location.href = "'.$v_pagina_retorno.'";
                    </script>';

if ($redireccion != '') echo $redireccion;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?
    date_default_timezone_set("America/Lima");
    
    $menu = 'maestros/g-accesos.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>