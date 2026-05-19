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
<?
    require("../lib/head.php");
    $acceso = 'PANELANALI';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_subasta = new subasta;
$obj_maestro = new maestros;

$v_arr_enviadas = $obj_factura->facturas_indicadores('ENVIADAS');
$v_arr_xvencer = $obj_factura->facturas_indicadores('XVENCER');
$v_arr_vencidas = $obj_factura->facturas_indicadores('VENCIDAS');

$v_arr_sub_nactivas = $obj_subasta->subastas_indicadores('NOACTIVAS');
$v_arr_sub_activas = $obj_subasta->subastas_indicadores('ACTIVAS');
$v_arr_sub_encomp = $obj_subasta->subastas_indicadores('COMPENSACION');
$v_arr_sub_comp = $obj_subasta->subastas_indicadores('COMPENSADA');

$v_arr_opxvalidar = $obj_maestro->kpi_op_xvalidar();
$v_arr_riesgo_xvencer = $obj_maestro->kpi_op_riesgo_xvencer();
$v_arr_riesgo_vencido = $obj_maestro->kpi_op_riesgo_vencido();

$v_bgc_enviadas = 'ffffff';
$v_color_enviadas = '000000';
$v_bgc_sub_nactiva = 'ffffff';
$v_color_sub_nactiva = '000000';
$v_bgc_sub_activa = 'ffffff';
$v_color_sub_activa = '000000';
$v_bgc_sub_encomp = 'ffffff';
$v_color_sub_encomp = '000000';
$v_bgc_sub_comp = 'ffffff';
$v_color_sub_comp = '000000';
$v_bgc_opxvalidar = 'ffffff';
$v_color_opxvalidar = '000000';
$v_bgc_riskxvencer = 'ffffff';
$v_color_riskxvencer = '000000';
$v_bgc_riskvencido = 'ffffff';
$v_color_riskvencido = '000000';

if ($v_arr_enviadas['maximo'] >= $v_arr_enviadas['parametro']){
    $v_bgc_enviadas = 'a93032';
    $v_color_enviadas = 'ffffff';
}
if (($v_arr_sub_nactivas['maximo'] >= $v_arr_sub_nactivas['parametro']) && $v_arr_sub_nactivas['count'] > 0){
    $v_bgc_sub_nactiva = 'a93032';
    $v_color_sub_nactiva = 'ffffff';
}
if (($v_arr_sub_activas['maximo'] >= $v_arr_sub_activas['parametro']) && $v_arr_sub_activas['count'] > 0){
    $v_bgc_sub_activa = 'a93032';
    $v_color_sub_activa = 'ffffff';
}
if (($v_arr_sub_encomp['maximo'] >= $v_arr_sub_encomp['parametro']) && $v_arr_sub_encomp['count'] > 0){
    $v_bgc_sub_encomp = 'a93032';
    $v_color_sub_encomp = 'ffffff';
}
if (($v_arr_sub_comp['maximo'] >= $v_arr_sub_comp['parametro']) && $v_arr_sub_comp['count'] > 0){
    $v_bgc_sub_comp = 'a93032';
    $v_color_sub_comp = 'ffffff';
}
if ($v_arr_opxvalidar['count'] > 0){
    $v_bgc_opxvalidar = 'a93032';
    $v_color_opxvalidar = 'ffffff';
}
if ($v_arr_riesgo_xvencer['count'] > 0){
    $v_bgc_riskxvencer = 'a93032';
    $v_color_riskxvencer = 'ffffff';
}
if ($v_arr_riesgo_vencido['count'] > 0){
    $v_bgc_riskvencido = 'a93032';
    $v_color_riskvencido = 'ffffff';
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'panel/panel_ceo.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Panel de Control
    </div>
    <!-- INDICADORES -->
    <div class="frmtransaccion">
        <ul style="overflow:hidden;list-style:none;">
            <!-- facturas enviadas -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_enviadas?>;color:#<?=$v_color_enviadas?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Facturas enviadas por el vendedor pendientes de revisar para que pasen a subasta">
                        Facturas enviadas por revisar <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../facturas/facturas_xestado.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_enviadas['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Facturas</p>
            </li>
            <!-- facturas por vencer -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;color:#000000;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Facturas que estan proximas a vencer, es decir, que el obligado al pago la debe de pagar">
                        Facturas por vencer dentro de <?=$v_arr_xvencer['parametro']?> d&iacute;as <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../facturas/facturas_xvencer.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_xvencer['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Facturas</p>
            </li>
            <!-- facturas vencidas -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;color:#000000;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Facturas vencidas, es decir, que la fecha de vencimiento ya fue superada y aun no se paga la factura">
                        Facturas vencidas <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../facturas/facturas_vencidas.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_vencidas['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Facturas</p>
            </li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
            <!-- subastas activas -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_sub_activa?>;color:#<?=$v_color_sub_activa?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Subastas activas que estan en proceso de conseguir financiamiento">
                        Subastas Activas <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../subastas/subastas_activas.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_sub_activas['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Subastas</p>
            </li>
            <!-- subastas en compensacion -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_sub_encomp?>;color:#<?=$v_color_sub_encomp?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Subastas en compensacion, es decir, se esta verificando la disponibilidad de fondos de los inversionistas">
                        Subastas en compensacion <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../subastas/subastas_encomp.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_sub_encomp['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Subastas</p>
            </li>
            <!-- subastas compensadas -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_sub_comp?>;color:#<?=$v_color_sub_comp?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Subastas compensadas que aun no han sido procesadas por el analista, es decir, aun no se realiza el financiamiento">
                        Subastas compensadas <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../subastas/subastas_comp.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_sub_comp['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Subastas</p>
            </li>
        </ul>
        <ul>
            <!-- op por validar -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_opxvalidar?>;color:#<?=$v_color_opxvalidar?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Obligados al pago pendientes de ser validados">
                        Obligados al pago por validar <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../empresas/opxvalidar.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_opxvalidar['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Empresas</p>
            </li>
            <!-- rirsgos por vencer -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_riskxvencer?>;color:#<?=$v_color_riskxvencer?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Empresas con evaluacion de riesgo por vencer">
                        Empresas con riesgo por vencer dentro de los proximos <?echo $v_arr_riesgo_xvencer['maximo']?> dias <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../empresas/xxx.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_riesgo_xvencer['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Empresas</p>
            </li>
            <!-- rirsgos vencidos -->
            <li style="display:block;margin:5px;width:200px;float:left;border-color: #555555;border-style: solid;border-width: 1px 1px 1px 1px;line-height: 15px;padding:10px;background-color:#<?=$v_bgc_riskvencido?>;color:#<?=$v_color_riskvencido?>;">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Empresas con evaluacion de riesgo vencido">
                        Empresas con riesgo vencido <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p style="margin:10px 0px;text-align:center;font-size:18px;font-weight: bold;">
                    <? echo '<a href="../empresas/xxx.php" style="text-align:center;font-size:18px;font-weight: bold;">'.$v_arr_riesgo_vencido['count'].'</a>';?>
                </p>
                <p style="margin:10px 0px;text-align:center;font-size:14px;">Empresas</p>
            </li>
        </ul>

        <!--==== VIEW FINANCIERO ====-->
<?php
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

?>
        <ul style="overflow:hidden;list-style:none;">
            <!--============== OPERACIONES EN COMPENSACION-->
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Operaciones en compensación">
                    Operaciones en compensación <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../subastas/subastas_comp.php"><span class="icon-bell" style="font-size: 14px;"></span> En compensación: <b style="font-size:16px;"><?echo $v_arr_sub_encomp['count'];?></b></a>
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
                    <a href="../subastas/subastas_comp.php"><span class="icon-bell" style="font-size: 14px;"></span> Operaciones compensadas: <b style="font-size:16px;"><?echo $v_arr_sub_comp['count'];?></b></a>
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
                    <a href="../facturas/facturas_xvencer.php"><span class="icon-bell" style="font-size: 14px;"></span> Operaciones x Vencer: <b style="font-size:16px;"><?echo $v_arr_xvencer['count'];?></b></a>
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

        <!--==== LEGAL ====--->
<?php
    $varr_kpi = $obj_maestro->kpi_clo();

    $v_rojo = 'b30a1f';
    $v_verde = '1F9A8E';
?>
        <ul style="overflow:hidden;list-style:none;">
    <?php
        //============== ENVIO DE CONTRATOS DE ENDOSO
        echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Envio de los contratos de endoso a los EMISORES">
                    Contratos de endoso (envio) <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../subastas/subastas_comp.php"><span class="icon-bell" style="font-size: 14px;"></span> Pendientes Total: <b style="font-size:16px;">'.$varr_kpi['contratos_xenviar_endoso'].'</b></a>
                </p>';
    
        if ($varr_kpi['contratos_xenviar_endoso'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../subastas/subastas_comp.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Pendientes alerta: <b style="font-size:16px;">'.$varr_kpi['contratos_xenviar_endoso_alert'].'</b></a>
                </p>
                <p>
                    <a href="../subastas/subastas_comp.php" style="color:#'.$v_verde.';"><span class="icon-bell" style="font-size: 14px;"></span> Pendientes onTime: <b style="font-size:16px;">'.($varr_kpi['contratos_xenviar_endoso'] - $varr_kpi['contratos_xenviar_endoso_alert']).'</b></a>
                </p>';
        echo '
            </li>';
    
        //============== CONTRATOS DE ENDOSO SIN FIRMA
        echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Contratos de endoso que han sido enviados al EMISOR pero aun no han sido firmados">
                    Endoso sin firma <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../subastas/subastas_comp.php"><span class="icon-bell" style="font-size: 14px;"></span> Endosos sin firma: <b style="font-size:16px;">'.$varr_kpi['contratos_endoso_sinfirma'].'</b></a>
                </p>';
    
        if ($varr_kpi['contratos_endoso_sinfirma'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../subastas/subastas_comp.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Endoso alerta: <b style="font-size:16px;">'.$varr_kpi['contratos_endoso_sinfirma_alert'].'</b></a>
                </p>
                <p>
                    <a href="../subastas/subastas_comp.php" style="color:#'.$v_verde.';"><span class="icon-bell" style="font-size: 14px;"></span> Endoso onTime: <b style="font-size:16px;">'.($varr_kpi['contratos_endoso_sinfirma'] - $varr_kpi['contratos_endoso_sinfirma_alert']).'</b></a>
                </p>';
        
        echo '
            </li>';
        //============== EVALUACION DE EMPRESAS
        echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Evaluación legal de las empresas emisoras u obligadas al pago">
                    Evaluación de empresas <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../empresas/empresas.php"><span class="icon-bell" style="font-size: 14px;"></span> Sin evaluación legal: <b style="font-size:16px;">'.$varr_kpi['empresas_sineval'].'</b></a>
                </p>';

            if ($varr_kpi['empresas_sineval'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../empresas/empresas.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Sin evaluación alerta: <b style="font-size:16px;">'.$varr_kpi['empresas_sineval_alert'].'</b></a>
                </p>
                <p>
                    <a href="../subastas/subastas_comp.php" style="color:#'.$v_verde.';"><span class="icon-bell" style="font-size: 14px;"></span> Sin evaluación onTime: <b style="font-size:16px;">'.($varr_kpi['empresas_sineval'] - $varr_kpi['empresas_sineval_alert']).'</b></a>
                </p>';
            
            if ($varr_kpi['empresas_conflicto'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../empresas/empresas.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Empresas en conflicto: <b style="font-size:16px;">'.$varr_kpi['empresas_conflicto'].'</b></a>
                </p>';
        
        echo '    
            </li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">';
        //============== EVALUACION DE INVERSORES
        echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Evaluación de los inversores vinculados">
                    Evaluación de inversores <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>
                <p>
                    <a href="../inversionistas/inversores.php"><span class="icon-bell" style="font-size: 14px;"></span> Evaluación pendiente: <b style="font-size:16px;">'.$varr_kpi['inversor_sineval'].'</b></a>
                </p>';

            if ($varr_kpi['inversor_sineval_alert'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../inversionistas/inversores.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Pendiente alerta: <b style="font-size:16px;">'.$varr_kpi['inversor_sineval_alert'].'</b></a>
                </p>';
            else echo '
                <p>
                    <a href="../inversionistas/inversores.php"><span class="icon-bell" style="font-size: 14px;"></span> Pendiente alerta: <b style="font-size:16px;">'.$varr_kpi['inversor_sineval_alert'].'</b></a>
                </p>';
            
            echo '
                <p>
                    <a href="../inversionistas/inversores.php" style="color:#'.$v_verde.';"><span class="icon-bell" style="font-size: 14px;"></span> Pendiente onTime: <b style="font-size:16px;">'.($varr_kpi['inversor_sineval'] - $varr_kpi['inversor_sineval_alert']).'</b></a>
                </p>';

        echo '    
            </li>';
        //============== EVALUACION PLAFT
        echo '
            <li class="caja_indicador">
                <p style="text-align:center;font-size:14px;">
                    <abbr title="Evaluación de lavado de activos de los inversores vinculados">
                    Evaluación PLAFT de inversores <span class="icon-eye" style="color:#000000;font-size: 14px;"></span>
                    </abbr></p>';

            if ($varr_kpi['inversor_sinplaft'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../inversionistas/inversores.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> Pendiente PLAFT: <b style="font-size:16px;">'.$varr_kpi['inversor_sinplaft'].'</b></a>
                </p>';
            else echo '
                <p>
                    <a href="../inversionistas/inversores.php"><span class="icon-bell" style="font-size: 14px;"></span> Pendiente PLAFT: <b style="font-size:16px;">'.$varr_kpi['inversor_sinplaft'].'</b></a>
                </p>';

            if ($varr_kpi['inversor_plaft_alert'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../inversionistas/inversores.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> PLAFT x vencer: <b style="font-size:16px;">'.$varr_kpi['inversor_plaft_alert'].'</b></a>
                </p>';
            else echo '
                <p>
                    <a href="../inversionistas/inversores.php"><span class="icon-bell" style="font-size: 14px;"></span> PLAFT x vencer: <b style="font-size:16px;">'.$varr_kpi['inversor_plaft_alert'].'</b></a>
                </p>';

            if ($varr_kpi['inversor_plaft_vencido'] > 0) echo '
                <p style="background-color:#'.$v_rojo.'">
                    <a href="../inversionistas/inversores.php" style="color:#ffffff;"><span class="icon-bell" style="font-size: 14px;"></span> PLAFT vencido: <b style="font-size:16px;">'.$varr_kpi['inversor_plaft_vencido'].'</b></a>
                </p>';
            else echo '
                <p>
                    <a href="../inversionistas/inversores.php"><span class="icon-bell" style="font-size: 14px;"></span> PLAFT vencido: <b style="font-size:16px;">'.$varr_kpi['inversor_plaft_vencido'].'</b></a>
                </p>';

        echo '    
            </li>
        </ul>';
    ?>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>