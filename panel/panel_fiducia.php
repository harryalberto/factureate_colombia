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
    $acceso = 'PCLO';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*#################################################
#################### LOGICA */
$vobj_mae = new maestros;

$varr_kpi = $vobj_mae->kpi_fiducia();

//============= PENDIENTES DE DEPURACION
if ($varr_kpi['qdepuracion_pendiente'] > 0)
    $v_qdepuracion = $varr_kpi['qdepuracion_pendiente'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qdepuracion = '---';

if ($varr_kpi['qdepuracion_pendiente_alert'] > 0)
    $v_qdepuracion_alert = $varr_kpi['qdepuracion_pendiente_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qdepuracion_alert = '---';

//============ PENDIENTES DD
if ($varr_kpi['qdd_pendiente'] > 0)
    $v_qdd = $varr_kpi['qdd_pendiente'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qdd = '---';

if ($varr_kpi['qdd_pendiente_alert'] > 0)
    $v_qdd_alert = $varr_kpi['qdd_pendiente_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qdd_alert = '---';

?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'panel/panel_fiducia.php';
    //@@@@@@@@@@@@@@@@@@ PARTE SUPERIOR 
    require("../lib/superior.php");
    //@@@@@@@@@@@@@@@@@@ PARTE IZQUIERDA 
    require("../lib/menu-n1.php");
?>
    <!--#############################################
    ################ CUERPO PRINCIPAL -->

    <!--@@@@@@@@@@@@@@@@@@@ TO DO LIST -->
    <div style="overflow:hidden; float: left;width: 500px;height: calc(100% - 84px);">
        <div style="overflow:hidden;text-align:left;font-size: 18px;font-weight: bold;color:var(--color-azulv2);padding:10px;">
            <span class="icon-stats-dots" style="font-size: 16px;"></span> Panel de Control
        </div>

        <!--=========== TABLA DE INDICADORES -->
        <div style="padding-left: 10px;">
            <table class="tabla_resize_trans">
                <thead>
                    <tr>
                    <th scope="col">AREA</th>
                    <th scope="col">ALERTA</th>
                    <th scope="col">ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-label="AREA">VINCULACIONES</td>
                        <td data-label="ALERTA">Solicitudes de vinculación con mas de <?echo $varr_kpi['dias_depuracion'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_qdepuracion_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">VINCULACIONES</td>
                        <td data-label="ALERTA">Solicitudes de vinculación con menos de <?echo $varr_kpi['dias_depuracion'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_qdepuracion;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">VINCULACIONES</td>
                        <td data-label="ALERTA">Vinculaciones depuradas sin informe de debida diligencia con mas de <?echo $varr_kpi['dias_dd']?> dias</td>
                        <td data-label="ESTADO"><?echo $v_qdd_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">VINCULACIONES</td>
                        <td data-label="ALERTA">Vinculaciones depuradas sin informe de debida diligencia con menos de <?echo $varr_kpi['dias_dd']?> dias</td>
                        <td data-label="ESTADO"><?echo $v_qdd;?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!--@@@@@@@@@@@@@@@@@@@ GRFICOS -->
    <div style="overflow:hidden;float: right;width: calc(100% - 500px)">
        <!-- GRAFICO DE MONTOS DE ADELANTOS -->
        <div style="width: 400px;float: left;">
          <canvas id="kpi_adelantos"></canvas>
        </div>

        <!-- GRAFICO DE INGRESOS -->
        <div style="width: 400px;float: left;">
          <canvas id="kpi_ingresos"></canvas>
        </div>

        <!-- GRAFICO DE INVERSIONES -->
        <div style="width: 400px;float: left;">
          <canvas id="kpi_inversiones"></canvas>
        </div>

        <!-- GRAFICO DE LIQUIDACIONES -->
        <div style="width: 400px;float: left;">
          <canvas id="kpi_liquidaciones"></canvas>
        </div>
    </div>

    <!--====================== LIBRERIAS PARA EL GRAFICO -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!--====================== GRAFICOS -->
<?php
    $v_paleta_colores = array(0=>'rgb(255,64,105)',1=>'rgb(255,144,32)',2=>'rgb(212,161,43)',3=>'rgb(34,206,206)',4=>'rgb(5,155,255)',5=>'rgb(218,247,166)',6=>'rgb(255,195,0)', 
                                7=>'rgb(255,159,51)', 8=>'rgb(255,196,51)', 9=>'rgb(243,255,51)', 10=>'rgb(178,255,51)');

    //@@@@ GRAFICO DE ADELANTOS
    $varr_adelantos = $vobj_mae->kpi_graph_adelantos_mes();
    $varr_moneda_nac = $vobj_mae->get_parametro_detalle(49);
    $varr_monedas = $vobj_mae->get_tipos('MONEDA');

    for ($j = 0; $j < count($varr_monedas); $j ++){
        if ($varr_monedas[$j]['id'] == $varr_moneda_nac['valornum']) $v_moneda_nac = $varr_monedas[$j]['nombre'];
    }

    $v_labels_fecha = '';
    $v_fecha_old = '';
    $v_moneda_ext = '';
    $v_adelantos_nac = '';
    $v_adelantos_ext = '';

    for ($i = 0; $i < count($varr_adelantos); $i++){
        $v_fecha = date('d-m-Y', strtotime($varr_adelantos[$i]['f_registro']));

        if ($v_fecha_old == '' || $v_fecha_old != $v_fecha){
            //@@@@ CAMBIO DE FECHA
            $v_labels_fecha .= "'".$v_fecha."'";
            $v_fecha_go = 1;
            $v_fecha_old = $v_fecha;

            if (($varr_adelantos[$i]['moneda_id'] != $varr_moneda_nac['valornum']) && $v_moneda_ext == ''){
                for ($j = 0; $j < count($varr_monedas); $j ++){
                    if ($varr_monedas[$j]['id'] == $varr_adelantos[$i]['moneda_id']) $v_moneda_ext = $varr_monedas[$j]['nombre'];
                }
            }

            if ($varr_adelantos[$i]['moneda_id'] == $varr_moneda_nac['valornum']){
                //$v_adelantos_nac .= number_format($varr_adelantos[$i]['monto'],0);
                $v_adelantos_nac .= round($varr_adelantos[$i]['monto'],0);

                if (($i + 1) < count($varr_adelantos)){
                    if ($varr_adelantos[$i]['f_registro'] != $varr_adelantos[$i + 1]['f_registro']) $v_adelantos_ext .= "0";
                } else $v_adelantos_ext .= "0";
            } else {
                $v_adelantos_ext .= round($varr_adelantos[$i]['monto'],0);

                if (($i + 1) < count($varr_adelantos)){
                    if ($varr_adelantos[$i]['f_registro'] != $varr_adelantos[$i + 1]['f_registro']) $v_adelantos_nac .= "0";
                } else $v_adelantos_nac .= "0";
            }
        } else{
            $v_fecha_go = 0;
            $v_adelantos_ext .= round($varr_adelantos[$i]['monto'],0);

            if (($varr_adelantos[$i]['moneda_id'] != $varr_moneda_nac['valornum']) && $v_moneda_ext == ''){
                for ($j = 0; $j < count($varr_monedas); $j ++){
                    if ($varr_monedas[$j]['id'] == $varr_adelantos[$i]['moneda_id']) $v_moneda_ext = $varr_monedas[$j]['nombre'];
                }
            }
        }

        if ((($i + 1) < count($varr_adelantos)) && $v_fecha_go == 1){
            $v_labels_fecha .= ","; $v_adelantos_nac .= ",";

            if ($varr_adelantos[$i]['f_registro'] != $varr_adelantos[$i + 1]['f_registro']) $v_adelantos_ext .= ",";
        } else {
            if (($i + 1) < count($varr_adelantos)) $v_adelantos_ext .= ",";
        }
    }
    
    if ($v_labels_fecha == ''){
        $v_moneda_ext = '----';
        $v_labels_fecha = "'No hay datos'";
        $v_adelantos_nac = '0';
        $v_adelantos_ext = '0';
    } elseif ($v_moneda_ext == '') $v_moneda_ext = '----';
    
?>
    <script type="text/javascript">
        const g_adelantos = document.getElementById('kpi_adelantos');

        new Chart(g_adelantos, {
            type: 'bar',
            data: {
                labels: [<?echo $v_labels_fecha;?>],
                datasets: [
                    {
                        label: '<?echo $v_moneda_nac;?>',
                        data: [<?echo $v_adelantos_nac;?>],
                        borderColor: '<?echo $v_paleta_colores[0];?>',
                        backgroundColor: '<?echo $v_paleta_colores[0];?>',
                        borderWidth: 2,
                        borderRadius: 3,
                        borderSkipped: false,
                    },
                    {
                        label: '<?echo $v_moneda_ext;?>',
                        data: [<?echo $v_adelantos_ext;?>],
                        borderColor: '<?echo $v_paleta_colores[1];?>',
                        backgroundColor: '<?echo $v_paleta_colores[1];?>',
                        borderWidth: 2,
                        borderRadius: 5,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Adelantos realizados en el mes actual'
                    }
                }
            },
        });
        
    </script>

    <!--@@@@ GRAFICO DE INGRESOS -->
<?php
    $varr_ingresos = $vobj_mae->kpi_graph_ingresos_mes();

    $v_labels_fecha_ing = '';
    $v_fecha_old = '';
    $v_moneda_ext = '';
    $v_ingresos_nac = '';
    $v_ingresos_ext = '';

    for ($i = 0; $i < count($varr_ingresos); $i++){
        $v_fecha = date('d-m-Y', strtotime($varr_ingresos[$i]['f_registro']));

        if ($v_fecha_old == '' || $v_fecha_old != $v_fecha){
            //@@@@ CAMBIO DE FECHA
            $v_labels_fecha_ing .= "'".$v_fecha."'";
            $v_fecha_go = 1;
            $v_fecha_old = $v_fecha;

            if (($varr_ingresos[$i]['moneda_id'] != $varr_moneda_nac['valornum']) && $v_moneda_ext == ''){
                for ($j = 0; $j < count($varr_monedas); $j ++){
                    if ($varr_monedas[$j]['id'] == $varr_ingresos[$i]['moneda_id']) $v_moneda_ext = $varr_monedas[$j]['nombre'];
                }
            }

            if ($varr_ingresos[$i]['moneda_id'] == $varr_moneda_nac['valornum']){
                $v_ingresos_nac .= round($varr_ingresos[$i]['monto'],0);

                if (($i + 1) < count($varr_ingresos)){
                    if ($varr_ingresos[$i]['f_registro'] != $varr_ingresos[$i + 1]['f_registro']) $v_ingresos_ext .= "0";
                } else $v_ingresos_ext .= "0";
            } else {
                $v_ingresos_ext .= round($varr_ingresos[$i]['monto'],0);

                if (($i + 1) < count($varr_ingresos)){
                    if ($varr_ingresos[$i]['f_registro'] != $varr_ingresos[$i + 1]['f_registro']) $v_ingresos_nac .= "0";
                } else $v_ingresos_nac .= "0";
            }
        } else{
            $v_fecha_go = 0;
            $v_ingresos_ext .= round($varr_ingresos[$i]['monto'],0);

            if (($varr_ingresos[$i]['moneda_id'] != $varr_moneda_nac['valornum']) && $v_moneda_ext == ''){
                for ($j = 0; $j < count($varr_monedas); $j ++){
                    if ($varr_monedas[$j]['id'] == $varr_ingresos[$i]['moneda_id']) $v_moneda_ext = $varr_monedas[$j]['nombre'];
                }
            }
        }

        if ((($i + 1) < count($varr_ingresos)) && $v_fecha_go == 1){
            $v_labels_fecha_ing .= ","; $v_ingresos_nac .= ",";

            if ($varr_ingresos[$i]['f_registro'] != $varr_ingresos[$i + 1]['f_registro']) $v_ingresos_ext .= ",";
        } else {
            if (($i + 1) < count($varr_ingresos)) $v_ingresos_ext .= ",";
        }
    }
    
    if ($v_labels_fecha_ing == ''){
        $v_moneda_ext = '----';
        $v_labels_fecha_ing = "'No hay datos'";
        $v_ingresos_nac = '0';
        $v_ingresos_ext = '0';
    } elseif ($v_moneda_ext == '') $v_moneda_ext = "----";
?>

    <script type="text/javascript">
        const g_ingresos = document.getElementById('kpi_ingresos');

        new Chart(g_ingresos, {
            type: 'bar',
            data: {
                labels: [<?echo $v_labels_fecha_ing;?>],
                datasets: [
                    {
                        label: '<?echo $v_moneda_nac;?>',
                        data: [<?echo $v_ingresos_nac;?>],
                        borderColor: '<?echo $v_paleta_colores[2];?>',
                        backgroundColor: '<?echo $v_paleta_colores[2];?>',
                        borderWidth: 2,
                        borderRadius: 3,
                        borderSkipped: false,
                    },
                    {
                        label: '<?echo $v_moneda_ext;?>',
                        data: [<?echo $v_ingresos_ext;?>],
                        borderColor: '<?echo $v_paleta_colores[3];?>',
                        backgroundColor: '<?echo $v_paleta_colores[3];?>',
                        borderWidth: 2,
                        borderRadius: 5,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Ingresos recibidos en el mes actual'
                    }
                }
            },
        });
        
    </script>

    <!--@@@@ GRAFICO DE INVERSIONES -->
<?php
    $varr_inversiones = $vobj_mae->kpi_graph_inversiones_mes();

    $v_labels_fecha = '';
    $v_fecha_old = '';
    $v_ingresos_nac = '';
    
    for ($i = 0; $i < count($varr_inversiones); $i++){
        $v_fecha = date('d-m-Y', strtotime($varr_inversiones[$i]['f_registro']));

        if ($v_fecha_old == '' || $v_fecha_old != $v_fecha){
            //@@@@ CAMBIO DE FECHA
            $v_labels_fecha .= "'".$v_fecha."'";
            $v_fecha_go = 1;
            $v_fecha_old = $v_fecha;

            $v_ingresos_nac .= round($varr_inversiones[$i]['qinversiones'],0);
        }

        if ((($i + 1) < count($varr_inversiones)) && $v_fecha_go == 1){
            $v_labels_fecha .= ","; $v_ingresos_nac .= ",";
        }
    }
    
    if ($v_labels_fecha == ''){
        $v_labels_fecha = "'No hay datos'";
        $v_ingresos_nac = '0';
    }
?>

    <script type="text/javascript">
        const g_inversiones = document.getElementById('kpi_inversiones');

        new Chart(g_inversiones, {
            type: 'bar',
            data: {
                labels: [<?echo $v_labels_fecha;?>],
                datasets: [
                    {
                        label: '# Compras',
                        data: [<?echo $v_ingresos_nac;?>],
                        borderColor: '<?echo $v_paleta_colores[4];?>',
                        backgroundColor: '<?echo $v_paleta_colores[4];?>',
                        borderWidth: 2,
                        borderRadius: 3,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de compras realizadas en el mes actual'
                    }
                }
            },
        });
        
    </script>

    <!--@@@@ GRAFICO DE LIQUIDACIONES -->
<?php
    $varr_liquidaciones = $vobj_mae->kpi_graph_liquidaciones_mes();

    $v_labels_fecha = '';
    $v_fecha_old = '';
    $v_ingresos_nac = '';
    
    for ($i = 0; $i < count($varr_liquidaciones); $i++){
        $v_fecha = date('d-m-Y', strtotime($varr_liquidaciones[$i]['f_liquidacion']));

        if ($v_fecha_old == '' || $v_fecha_old != $v_fecha){
            //@@@@ CAMBIO DE FECHA
            $v_labels_fecha .= "'".$v_fecha."'";
            $v_fecha_go = 1;
            $v_fecha_old = $v_fecha;

            $v_ingresos_nac .= round($varr_liquidaciones[$i]['qliquidaciones'],0);
        }

        if ((($i + 1) < count($varr_liquidaciones)) && $v_fecha_go == 1){
            $v_labels_fecha .= ","; $v_ingresos_nac .= ",";
        }
    }
    
    if ($v_labels_fecha == ''){
        $v_labels_fecha = "'No hay datos'";
        $v_ingresos_nac = '0';
    }
?>

    <script type="text/javascript">
        const g_liquidaciones = document.getElementById('kpi_liquidaciones');

        new Chart(g_liquidaciones, {
            type: 'bar',
            data: {
                labels: [<?echo $v_labels_fecha;?>],
                datasets: [
                    {
                        label: '# Liquidaciones',
                        data: [<?echo $v_ingresos_nac;?>],
                        borderColor: '<?echo $v_paleta_colores[5];?>',
                        backgroundColor: '<?echo $v_paleta_colores[5];?>',
                        borderWidth: 2,
                        borderRadius: 3,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de liquidaciones realizadas en el mes actual'
                    }
                }
            },
        });
        
    </script>

</BODY>
</HTML>