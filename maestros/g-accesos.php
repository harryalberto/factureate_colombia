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
    <script type="text/javascript">
        function acciones(accion){
            document.frm.action = 'g-accesos-proceso.php';
            document.frm.submit();
        }
        function slcchange(){
            document.frm.action = 'g-accesos.php';
            document.frm.submit();
        }
        function chkchange(menuid){
            var chk = eval("document.frm.chk"+menuid);
            if (chk.value == 1) chk.value = 0;
            else chk.value = 1;
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$v_arr_perfiles = $obj_mae->get_perfiles();

if (isset($_POST['perfil_id'])) $v_perfil_id = $_POST['perfil_id'];
elseif (isset($_GET['perfil_id'])) $v_perfil_id = $_GET['perfil_id'];
else $v_perfil_id = $v_arr_perfiles[0]['perfil_id'];

$v_arr_accesos_asig = $obj_mae->get_accesos($v_perfil_id,'ASIGNADO');
$v_arr_accesos_nasig = $obj_mae->get_accesos($v_perfil_id,'NASIGNADO');
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
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Gesti&oacute;n de accesos
    </div>
    <div class="listpag">
        <div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">Perfil:</li>
                <li style="float: left;display: block;padding: 5px;">
                <select id="perfil_id" name="perfil_id" onchange="javascript:slcchange()">
                <?
                for ($i=0; $i<count($v_arr_perfiles); $i++){
                    if ($v_arr_perfiles[$i]['perfil_id'] == $v_perfil_id) echo '<option value="'.$v_perfil_id.'" selected>'.$v_arr_perfiles[$i]['nombre'].' | '.$v_arr_perfiles[$i]['descripcion'].' | '.$v_arr_perfiles[$i]['tipo'].'</option>';
                    else echo '<option value="'.$v_arr_perfiles[$i]['perfil_id'].'">'.$v_arr_perfiles[$i]['nombre'].' | '.$v_arr_perfiles[$i]['descripcion'].' | '.$v_arr_perfiles[$i]['tipo'].'</option>';
                }
                ?>
                </select>
                </li>
            </ul>
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">Accesos asignados</li>
            </ul>
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">
                    <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                        <tr style="background-color:#064677;color:#ffffff;">
                            <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE VISUAL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">PAGINA URL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"></td>
                        </tr>
                        <?
    for ($i=0; $i<count($v_arr_accesos_asig); $i++){
        echo '          <tr>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_asig[$i]['menu_id'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_asig[$i]['nombre'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_asig[$i]['nombre_visual'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_asig[$i]['pagina'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"><input type="checkbox" name="asignado[]" value="'.$v_arr_accesos_asig[$i]['menu_id'].'" checked onchange="javascript:chkchange('.$v_arr_accesos_asig[$i]['menu_id'].')"></td>
                            <input type="hidden" name="chk'.$v_arr_accesos_asig[$i]['menu_id'].'" value="1">
                        </tr>';
    }
    ?>
                    </table>
                </li>
            </ul>
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">Accesos disponibles</li>
            </ul>
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">
                    <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                        <tr style="background-color:#064677;color:#ffffff;">
                            <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE VISUAL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">PAGINA URL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"></td>
                        </tr>
                        <?
    for ($i=0; $i<count($v_arr_accesos_nasig); $i++){
        echo '          <tr>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_nasig[$i]['menu_id'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_nasig[$i]['nombre'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_nasig[$i]['nombre_visual'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_accesos_nasig[$i]['pagina'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"><input type="checkbox" name="no_asignado[]" value="'.$v_arr_accesos_nasig[$i]['menu_id'].'"></td>
                            <input type="hidden" name="nasignado'.$v_arr_accesos_nasig[$i]['menu_id'].'" value="'.$v_arr_accesos_nasig[$i]['acceso_id'].'">
                        </tr>';
    }
    ?>
                    </table>
                </li>
            </ul>
            <ul style="list-style:none;overflow: hidden;">
            <li class="botontransaccion" style="width:100px;display: block;"><a href=javascript:acciones("guardar")>Guardar</a></li>
            </ul>
        </div>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>