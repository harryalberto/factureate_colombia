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
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'PANELANALI_FIN';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_subasta = new subasta;
$obj_maestro = new maestros;

$v_arr_xvencer = $obj_factura->facturas_indicadores('XVENCER');
$v_arr_vencidas = $obj_factura->facturas_indicadores('VENCIDAS');

$v_arr_sub_encomp = $obj_subasta->subastas_indicadores('ENCOMPENSACION');
$v_arr_sub_comp = $obj_subasta->subastas_indicadores('COMPENSADA');

$v_arr_opxvalidar = $obj_maestro->kpi_op_xvalidar();
$v_arr_riesgo_xvencer = $obj_maestro->kpi_op_riesgo_xvencer();
$v_arr_riesgo_vencido = $obj_maestro->kpi_op_riesgo_vencido();

$v_rojo = 'b30a1f';
$v_verde = '1F9A8E';
/*--------------------------------------------------------*/
//########## TRANSFERENCIAS POR REVISAR /   EMPRESAS POR EVALUAR    /   TRANSITO
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Santo_Domingo");
    $menu = 'panel/panel_analista_fin.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:left;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        <span class="icon-stats-dots" style="font-size: 16px;"></span> Panel de Control
    </div>
    <!-- INDICADORES -->
    <div class="frmtransaccion">
        <ul style="overflow:hidden;list-style:none;">
            <!--============== OPERACIONES EN COMPENSACION-->
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Operaciones en compensación">
                    Operaciones en compensación <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../subastas/subastas_comp.php"><span class="icon-bell" style="font-size: 14px;"></span> En compensación: <b style="font-size:16px;"><?php echo $v_arr_sub_encomp['count'];?></b></a>
                </p>
    <?php
        if ($v_arr_sub_encomp['count_alert'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../subastas/subastas_comp.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> En compensación alerta: <b style="font-size:16px;">'.$v_arr_sub_encomp['count_alert'].'</b></a>
                </p>';
        else {echo '
                <p>
                    <a href="../subastas/subastas_comp.php" style="color:#'.$v_verde.';"><span class="icon-bell" style="font-size: 14px;"></span> En compensación onTime: <b style="font-size:16px;">'.($v_arr_sub_encomp['count'] - $v_arr_sub_encomp['count_alert']).'</b></a>
                </p>';}
    ?>
            </li>
            <!--============== OPERACIONES COMPENSADAS-->
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Operaciones compensadas no liquidadas">
                    Operaciones compensadas <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../subastas/subastas_comp.php"><span class="icon-bell" style="font-size: 14px;"></span> Operaciones compensadas: <b style="font-size:16px;"><?php echo $v_arr_sub_comp['count'];?></b></a>
                </p>
    <?php
        if ($v_arr_sub_comp['count_alert'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../subastas/subastas_comp.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Oper compensadas alerta: <b style="font-size:16px;">'.$v_arr_sub_comp['count_alert'].'</b></a>
                </p>';
        else echo '
                <p>
                    <a href="../subastas/subastas_comp.php" style="color:#'.$v_verde.';"><span class="icon-bell" style="font-size: 14px;"></span> Oper compensadas onTime: <b style="font-size:16px;">'.($v_arr_sub_comp['count'] - $v_arr_sub_comp['count_alert']).'</b></a>
                </p>';
    ?>
            </li>
            <!--============== OPERACIONES X VENCER-->
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Operaciones proximas a vencer">
                    Operaciones que estan proximas a vencer dentro de <?php echo $v_arr_xvencer['parametro'].' días';?><span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../facturas/facturas_xvencer.php"><span class="icon-bell" style="font-size: 14px;"></span> Operaciones x Vencer: <b style="font-size:16px;"><?php echo $v_arr_xvencer['count'];?></b></a>
                </p>
            </li>
    <?php
            //============== OPERACIONES VENCIDAS
        if ($v_arr_vencidas['count'] > 0) echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Operaciones vencidas, es decir, su fecha de vencimiento ya paso">
                    Operaciones vencidas <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="background-color:#'.$v_rojo.';">
                    <a href="../facturas/facturas_vencidas.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Operaciones vencidas: <b style="font-size:16px;">'.$v_arr_vencidas['count'].'</b></a>
                </p>
            </li>';
    ?>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
    <?php
        //============== OBLIGADOS AL PAGO POR VALIDAR
        if ($v_arr_opxvalidar['count'] > 0) echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Empresas que estan pendientes de ser aprobadas para participar en operaciones">
                    Obligados al pago pendiente de validación <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="background-color:#'.$v_rojo.';">
                    <a href="../empresas/opxvalidar.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Pendientes de validar: <b style="font-size:16px;">'.$v_arr_opxvalidar['count'].'</b></a>
                </p>
            </li>';
        //============== EMPRESAS CON EVALUACION POR VENCER
        if ($v_arr_riesgo_xvencer['count'] > 0) echo '
        <li class="caja_indicador">
            <p style="text-align:center;font-size:14px;">
                <abbr title="Empresas cuya evaluacion de riesgo esta proximo a vencer">
                Empresas con evaluación de riesgo por vencer en los proximos '.$v_arr_riesgo_xvencer['maximo'].' días <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                </abbr></p>
            <p style="background-color:#'.$v_rojo.';">
                <a href="../empresas/opxvalidar.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Evaluación x vencer: <b style="font-size:16px;">'.$v_arr_riesgo_xvencer['count'].'</b></a>
            </p>
        </li>';
        //============== EMPRESAS CON EVALUACION VENCIDA
        if ($v_arr_riesgo_vencido['count'] > 0) echo '
        <li class="caja_indicador">
            <p style="text-align:center;font-size:14px;">
                <abbr title="Empresas cuya evaluacion de riesgo ya vencio por lo que no podran hacer operaciones">
                Empresas con evaluación de riesgo vencida <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                </abbr></p>
            <p style="background-color:#'.$v_rojo.';">
                <a href="../empresas/opxvalidar.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Evaluación vencida: <b style="font-size:16px;">'.$v_arr_riesgo_vencido['count'].'</b></a>
            </p>
        </li>';
    ?>
        </ul>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>