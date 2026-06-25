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
<?php
    require("../lib/head.php");
    $acceso = 'GMENU';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            document.frm.action = 'g-menu-update-proceso.php';
            document.frm.accion.value = accion;
            document.frm.submit();
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;

if ($_GET['mid'] > 0) $v_arr_menu = $obj_mae->get_menu_acceso_row($_GET['mid']);
else {
    $v_arr_menu = array('nombre_back' => '', 'nombre_visual' => '', 'codigo' => '', 'orden' => '', 'pagina' => '');
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <div class="frmtransaccion" style="color:#000000;">
        <form name='frm' method='post' id='frm' enctype="multipart/form-data">
            <input type="hidden" name="accion">
            <input type="hidden" name="mid" value="<?=$_GET['mid']?>">
            <input type="hidden" name="aid" value="<?=$_GET['aid']?>">

        <ul style="list-style:none;overflow:hidden;">
            <li style="width:80px;display: block;padding:5px;">Nombre:</li>
            <li style="width:200px;display: block;"><input type="text" name="nombre_back" style="width:150px;" value="<?=$v_arr_menu['nombre_back']?>"></li>
            <li style="width:80px;display: block;padding:5px;">Nombre Visual:</li>
            <li style="width:150px;display: block;"><input type="text" name="nombre_visual" style="width:150px;" value="<?=$v_arr_menu['nombre_visual']?>"></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:80px;display: block;padding:5px;">Codigo:</li>
            <li style="width:150px;display: block;"><input type="text" name="codigo" style="width:100px;" value="<?=$v_arr_menu['codigo']?>"></li>
            <li style="width:80px;display: block;padding:5px;">Orden (#):</li>
            <li style="width:80px;display: block;"><input type="text" name="orden" style="width:50px;" value="<?=$v_arr_menu['orden']?>"></li>
            <li style="width:80px;display: block;padding:5px;">Pagina URL:</li>
            <li style="width:200px;display: block;"><input type="text" name="pagina" style="width:200px;" value="<?=$v_arr_menu['pagina']?>"></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li class="botontransaccion" style="width:100px;display: block;"><a href=javascript:acciones("guardar")>Guardar</a></li>
        </ul>
        </form>
    </div>
    <!------ END CUERPO VARIABLE ------>
    
</BODY>
</HTML>