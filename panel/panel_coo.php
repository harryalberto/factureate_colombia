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
    $acceso = 'PANELCOO';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*######################################################
##################### LOGICA */
$vobj_mae = new maestros;

$varr_kpi_coo = $vobj_mae->kpi_coo();
$varr_max_dias_aprob_factura = $vobj_mae->get_parametro_detalle(11);
$varr_max_dias_subasta_activa = $vobj_mae->get_parametro_detalle(14);
$varr_max_dias_subasta_xliquidar = $vobj_mae->get_parametro_detalle(16);
$varr_max_dias_xvencer_finan = $vobj_mae->get_parametro_detalle(12);

//=============== INDICADOR DE FACTURAS SIN APROBAR
if ($varr_kpi_coo['qfacturas_ensolicitud_alert'] > 0)
    $v_qfacturas_ensolicitud_alert = $varr_kpi_coo['qfacturas_ensolicitud_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qfacturas_ensolicitud_alert = '---';

$v_qfacturas_ensolicitud_max = $varr_kpi_coo['qfacturas_ensolicitud'] - $varr_kpi_coo['qfacturas_ensolicitud_alert'];

if ($v_qfacturas_ensolicitud_max > 0)
    $v_qfacturas_ensolicitud_max_lb = $v_qfacturas_ensolicitud_max.' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qfacturas_ensolicitud_max_lb = '---';

//=============== INDICADOR DE SUBASTAS ACTIVAS
if ($varr_kpi_coo['qsubastas_activas_alert'] > 0)
    $v_qsubastas_activas_alert = $varr_kpi_coo['qsubastas_activas_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qsubastas_activas_alert = '---';

$v_qsubastas_activas_max = $varr_kpi_coo['qsubastas_activas'] - $varr_kpi_coo['qsubastas_activas_alert'];

if ($v_qsubastas_activas_max > 0)
    $v_qsubastas_activas_max_lb = $v_qsubastas_activas_max.' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qsubastas_activas_max_lb = '---';

//=============== INDICADOR DE SUBASTAS SIN PROPUESTAS
if ($varr_kpi_coo['qsubastas_sin_propuestas'] > 0)
    $v_qsubastas_sin_propuestas = $varr_kpi_coo['qsubastas_sin_propuestas'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qsubastas_sin_propuestas = '---';

//=============== INDICADOR DE SUBASTAS SIN MINIMO
if ($varr_kpi_coo['qsubastas_sin_minimo'] > 0)
    $v_qsubastas_sin_minimo = $varr_kpi_coo['qsubastas_sin_minimo'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qsubastas_sin_minimo = '---';

//=============== INDICADOR DE SUBASTAS X LIQUIDAR
if ($varr_kpi_coo['qsubastas_encomp_alert'] > 0)
    $v_qsubastas_xliquidar_alert = $varr_kpi_coo['qsubastas_encomp_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qsubastas_xliquidar_alert = '---';

$v_qsubastas_xliquidar_max = $varr_kpi_coo['qsubastas_encomp'] - $varr_kpi_coo['qsubastas_encomp_alert'];

if ($v_qsubastas_xliquidar_max > 0)
    $v_qsubastas_xliquidar_max_lb = $v_qsubastas_xliquidar_max.' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qsubastas_xliquidar_max_lb = '---';

//================ FINANCIAMIENTO X VENCER
if ($varr_kpi_coo['qfinan_xvencer'] > 0)
    $v_qfinan_xvencer = $varr_kpi_coo['qfinan_xvencer'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qfinan_xvencer = '---';

//================ FINANCIAMIENTOS VENCIDOS
if ($varr_kpi_coo['qfinan_vencido'] > 0)
    $v_qfinan_vencido = $varr_kpi_coo['qfinan_vencido'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qfinan_vencido = '---';

//================ FINANCIAMIENTO VENCE HOY
if ($varr_kpi_coo['qfinan_vencehoy'] > 0)
    $v_qfinan_vencehoy = $varr_kpi_coo['qfinan_vencehoy'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qfinan_vencehoy = '---';

//================ EMPRESAS SIN VALIDACION
if ($varr_kpi_coo['qempresas_validacionop'] > 0)
    $v_qempresas_sinvalidar = $varr_kpi_coo['qempresas_validacionop'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qempresas_sinvalidar = '---';

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'panel/panel_coo.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ CUERPO PRINCIPAL -->

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
                        <td data-label="AREA">FACTURAS</td>
                        <td data-label="ALERTA">Pendiente de aprobar con menos de <?php echo $varr_max_dias_aprob_factura['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qfacturas_ensolicitud_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">FACTURAS</td>
                        <td data-label="ALERTA">Pendiente de aprobar con mas de <?php echo $varr_max_dias_aprob_factura['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qfacturas_ensolicitud_max_lb;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">SUBASTAS</td>
                        <td data-label="ALERTA">Activas con menos de <?php echo $varr_max_dias_subasta_activa['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qsubastas_activas_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">SUBASTAS</td>
                        <td data-label="ALERTA">Activas con mas de <?php echo $varr_max_dias_subasta_activa['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qsubastas_activas_max_lb;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">SUBASTAS</td>
                        <td data-label="ALERTA">Sin propuestas de compra</td>
                        <td data-label="ESTADO"><?php echo $v_qsubastas_sin_propuestas;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">SUBASTAS</td>
                        <td data-label="ALERTA">Sin conseguir el minimo a financiar</td>
                        <td data-label="ESTADO"><?php echo $v_qsubastas_sin_minimo;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">SUBASTAS</td>
                        <td data-label="ALERTA">Pendientes de liquidar con menos de <?php echo $varr_max_dias_subasta_xliquidar['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qsubastas_xliquidar_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">SUBASTAS</td>
                        <td data-label="ALERTA">Pendientes de liquidar con mas de <?php echo $varr_max_dias_subasta_xliquidar['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qsubastas_xliquidar_max_lb;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">FINANCIAMIENTO</td>
                        <td data-label="ALERTA">Financiamientos por vencer en maximo <?php echo $varr_max_dias_xvencer_finan['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?php echo $v_qfinan_xvencer;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">FINANCIAMIENTO</td>
                        <td data-label="ALERTA">Financiamientos vencidos</td>
                        <td data-label="ESTADO"><?php echo $v_qfinan_vencido;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">FINANCIAMIENTO</td>
                        <td data-label="ALERTA">Financiamientos que vencen el dia de hoy</td>
                        <td data-label="ESTADO"><?php echo $v_qfinan_vencehoy;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">EMPRESAS</td>
                        <td data-label="ALERTA">Pendientes de validacion operativa</td>
                        <td data-label="ESTADO"><?php echo $v_qempresas_sinvalidar;?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!--@@@@@@@@@@@@@@@@@@@ GRFICOS -->
    <div style="overflow:hidden;float: right;width: calc(100% - 500px);padding-left: 15px;">
        <!-- FACTURAS REGISTRADAS ULTIMOS 18 MESES -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_qfacturas_registradas"></canvas>
        </div>

        <!-- FACTURAS QUE PASARON A SUBASTA ULTIMOS 12 MESES -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_qfacturas_subasta"></canvas>
        </div>

        <!-- DISTRIBUCION DE COMO TERMINARON LAS SUBASTAS ULTIMOS 12 MESES -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_estado_subastas"></canvas>
        </div>

        <!-- TIEMPO PROMEDIO DE ATENCION DE FACTURAS ULTIMOS 12 MESES -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_tiempo_atencion_facturas"></canvas>
        </div>

        <!-- TIEMPO PROMEDIO DE SUBASTAS -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_tiempo_atencion_subastas"></canvas>
        </div>

        <!-- CANTIDAD DE FINANCIAMIENTOS QUE SE RETRZARARON Y TERMINARON O PASARON A COBRANZA -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_financiamientos_estado"></canvas>
        </div>

        <!-- DIFERENCIA DE TIEMPO DE PAGO DE FINANCIAMIENTOS -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_financiamientos_pagos"></canvas>
        </div>
    </div>

    <!--====================== LIBRERIAS PARA EL GRAFICO -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!--====================== INFORMACION PARA kpi_qfacturas_registradas -->
<?php
    $v_paleta_colores = array(0=>'rgb(255,64,105)',1=>'rgb(255,144,32)',2=>'rgb(212,161,43)',3=>'rgb(34,206,206)',4=>'rgb(5,155,255)',5=>'rgb(218,247,166)',6=>'rgb(255,195,0)', 
                                7=>'rgb(255,159,51)', 8=>'rgb(255,196,51)', 9=>'rgb(243,255,51)', 10=>'rgb(178,255,51)');

    $varr_facturas_tiempo = $vobj_mae->kpi_graph_facturas_reg_tiempo();
    $v_labels_graph1 = '';
    $v_data_graph1 = '';
    $v_bg_graph1 = '';
    $v_border_graph1 = '';

    for ($i = 0; $i < count($varr_facturas_tiempo); $i++){
        $v_labels_graph1 .= "'".$varr_facturas_tiempo[$i]['mes']."'";
        $v_data_graph1 .= $varr_facturas_tiempo[$i]['cantidad'];
        $v_bg_graph1 .= "'rgba(54, 162, 235, 0.2)'";
        $v_border_graph1 .= "'rgb(54, 162, 235)'";

        if (($i + 1) < count($varr_facturas_tiempo)){
            $v_labels_graph1 .= ",";
            $v_data_graph1 .= ",";
            $v_bg_graph1 .= ",";
            $v_border_graph1 .= ",";
        }
    }
?>
    <script>
      const ctx1 = document.getElementById('kpi_qfacturas_registradas');

      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: [<?php echo $v_labels_graph1;?>],
          datasets: [{
            label: '# facturas',
            data: [<?php echo $v_data_graph1;?>],
            backgroundColor: [<?php echo $v_bg_graph1;?>],
            borderColor: [<?php echo $v_border_graph1;?>],
            borderWidth: 1
          }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Facturas registradas ultimos 18 meses'
                }
            }
        },
      });
    </script>

    <!--====================== INFORMACION PARA kpi_qfacturas_subasta -->
<?php
    $varr_subastas_tiempo = $vobj_mae->kpi_graph_facturas_subasta_tiempo();
    $v_labels_graph2 = '';
    $v_dataset_graph2 = '';
    $v_bg_graph2 = '';
    $v_border_graph2 = '';

    for ($i = 0; $i < count($varr_subastas_tiempo); $i++){
        $v_labels_graph2 .= "'".$varr_subastas_tiempo[$i]['mes']."'";
        $v_dataset_graph2 .= $varr_subastas_tiempo[$i]['registrados'];
        $v_dataset2_graph2 .= $varr_subastas_tiempo[$i]['subastados'];
        $v_bg_graph2 .= "'rgb(255,99,132)'";
        $v_bg2_graph2 .= "'rgb(5,155,255)'";
        
        if (($i + 1) < count($varr_facturas_tiempo)){
            $v_labels_graph2 .= ",";
            $v_dataset_graph2 .= ",";
            $v_dataset2_graph2 .= ",";
            $v_bg_graph2 .= ",";
            $v_bg2_graph2 .= ",";
        }
    }
?>
    <script>
      const ctx2 = document.getElementById('kpi_qfacturas_subasta');

      new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: [<?php echo $v_labels_graph2;?>],
          datasets: [{
            label: '# facturas',
            data: [<?php echo $v_dataset_graph2;?>],
            backgroundColor: [<?php echo $v_bg_graph2;?>],
            stack: 'Stack 0',
          },
          {
            label: '# subastas',
            data: [<?php echo $v_dataset2_graph2;?>],
            backgroundColor: [<?php echo $v_bg2_graph2;?>],
            stack: 'Stack 0',
          },]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Comparativo de 12 meses de facturas registradas y negociadas'
                },
            },
            interaction: {
                intersect: false,
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true
                }
            }
        }
      });
    </script>
    <!--@@@@@@@@@@@@@ END CUERPO PRINCIPAL -->
</BODY>
</HTML>