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

$v_arr_menu = $obj_mae->get_menu_acceso();
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'maestros/g-menu.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de accesos al sistema por menu
    </div>
    <div class="listpag">
        <div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">
                    <button style="font-size:10px;background-color:#e89b24;" type="button" mid="0" aid="0" class="btn btn-primary openBtn2">Nuevo menu</button>
                </li>
            </ul>
            <ul style="list-style:none;overflow: hidden;">
                <li style="float: left;display: block;padding: 5px;">
                    <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                        <tr style="background-color:#064677;color:#ffffff;">
                            <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE VISUAL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">CODIGO</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">ORDEN</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">PAGINA URL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"></td>
                        </tr>
    <?
    for ($i=0; $i<count($v_arr_menu); $i++){
        echo '          <tr>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_menu[$i]['id'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_menu[$i]['nombre_back'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_menu[$i]['nombre_visual'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_menu[$i]['codigo'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_menu[$i]['orden'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_menu[$i]['pagina'].'</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"><button style="font-size:10px;background-color:#e89b24;padding: 2 10px;" type="button" 
                                mid='.$v_arr_menu[$i]['id'].' aid='.$v_arr_menu[$i]['acceso_id'].' class="btn btn-primary openBtn2">Update</button></td>
                        </tr>';
    }
    ?>
                    </table>
                </li>
            </ul>
        </div>
    </div>
    <!------ END CUERPO VARIABLE ------>
    <!--============ Modal =============-->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
        <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Update del menu de acceso</h4>                  <!------------ titulo del modal -------------->
        </div>
        <div class="modal-body">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
    </div>
    <script>
        $('.openBtn2').on('click',function(){
            var mid = $(this).attr('mid');
            var aid = $(this).attr('aid');

            $('.modal-body').load('g-menu-update.php?mid='+mid+'&aid='+aid,function(){
                $('#myModal').modal({show:true});
            });
        });
    </script>
    <!---=============== end modal ==============--->
</BODY>
</HTML>