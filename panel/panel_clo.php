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

$varr_kpi_clo = $vobj_mae->kpi_clo();

//============= INDICADOR DE TIEMPO INVERSOR
$varr_max_dias_aprob_inversor = $vobj_mae->get_parametro_detalle(58);

if ($varr_kpi_clo['qinversor_sineval_alert'] > 0)
    $v_qinversor_alert = $varr_kpi_clo['qinversor_sineval_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_qinversor_alert = '---';

if ($varr_kpi_clo['qinversor_sineval_max'] > 0)
    $v_qinversor_max = $varr_kpi_clo['qinversor_sineval_max'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_qinversor_max = '---';

//============ INDECIADOR PLAFT
if ($varr_kpi_clo['qinversor_plaft_alert'] > 0)
    $v_plaft_alert = $varr_kpi_clo['qinversor_plaft_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_plaft_alert = '---';

if ($varr_kpi_clo['qinversor_plaft_vencido'] > 0)
    $v_plaft_vencido = $varr_kpi_clo['qinversor_plaft_vencido'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_plaft_vencido = '---';

//============= PENDIENTE DE ENVIO DE CONTRATO DE ENDOSO
$varr_max_dias_xenviar_contrato = $vobj_mae->get_parametro_detalle(38);

if ($varr_kpi_clo['qcontratos_xenviar_alert'] > 0)
    $v_contrato_xenviar_max = $varr_kpi_clo['qcontratos_xenviar_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_contrato_xenviar_max = '---';

$v_xenviar = $varr_kpi_clo['qcontratos_xenviar'] - $varr_kpi_clo['qcontratos_xenviar_alert'];

if ($v_xenviar > 0)
    $v_contrato_xenviar_alert = $v_xenviar.' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_contrato_xenviar_alert = '---';

//============== PENDIENTE DE FIRMA DE CONTRATO
$varr_max_dias_firmar_contrato = $vobj_mae->get_parametro_detalle(39);

if ($varr_kpi_clo['qcontratos_sinfirma_alert'] > 0)
    $v_contrato_sinfirma_max = $varr_kpi_clo['qcontratos_sinfirma_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_contrato_sinfirma_max = '---';

$v_xfirmar = $varr_kpi_clo['qcontratos_sinfirma'] - $varr_kpi_clo['qcontratos_sinfirma_alert'];

if ($v_xfirmar > 0)
    $v_contrato_sinfirma_alert = $v_xfirmar.' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_contrato_sinfirma_alert = '---';

//================= PENDIENTES DE ENDOSO
if ($varr_kpi_clo['qoperaciones_sin_endoso'] > 0)
    $v_sin_endoso = $varr_kpi_clo['qoperaciones_sin_endoso'].' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_sin_endoso = '---';

//============= EMPRESAS SIN EVALUACION
$varr_max_dias_sineval = $vobj_mae->get_parametro_detalle(40);

if ($varr_kpi_clo['qempresas_sineval_alert'] > 0)
    $v_empresa_sineval_max = $varr_kpi_clo['qempresas_sineval_alert'].' <i class="fa-solid fa-circle" style="color:var(--color-rojo);font-size:18px;"></i>';
else $v_empresa_sineval_max = '---';

$v_sineval = $varr_kpi_clo['qempresas_sineval'] - $varr_kpi_clo['qempresas_sineval_alert'];

if ($v_sineval > 0)
    $v_empresa_sineval_alert = $v_sineval.' <i class="fa-solid fa-circle" style="color:var(--color-amarillo);font-size:18px;"></i>';
else $v_empresa_sineval_alert = '---';
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'panel/panel_clo.php';
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
                        <td data-label="AREA">INVERSORES</td>
                        <td data-label="ALERTA">Pendiente de aprobar con menos de <?echo $varr_max_dias_aprob_inversor['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_qinversor_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">INVERSORES</td>
                        <td data-label="ALERTA">Pendiente de aprobar con mas de <?echo $varr_max_dias_aprob_inversor['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_qinversor_max;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">INVERSORES</td>
                        <td data-label="ALERTA">Analisis PLAFT por vencer</td>
                        <td data-label="ESTADO"><?echo $v_plaft_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">INVERSORES</td>
                        <td data-label="ALERTA">Analisis PLAFT vencidos</td>
                        <td data-label="ESTADO"><?echo $v_plaft_vencido;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">OPERACIONES</td>
                        <td data-label="ALERTA">Pendiente de envio de contrato con menos de <?echo $varr_max_dias_xenviar_contrato['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_contrato_xenviar_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">OPERACIONES</td>
                        <td data-label="ALERTA">Pendiente de envio de contrato con mas de <?echo $varr_max_dias_xenviar_contrato['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_contrato_xenviar_max;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">OPERACIONES</td>
                        <td data-label="ALERTA">Contratos en espera de firma con menos de <?echo $varr_max_dias_firmar_contrato['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_contrato_sinfirma_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">OPERACIONES</td>
                        <td data-label="ALERTA">Contratos en espera de firma con mas de <?echo $varr_max_dias_firmar_contrato['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_contrato_sinfirma_max;?></td>
                    </tr>
                    <!--APLICA SOLO PARA COLOMBIA
                        <tr>
                        <td data-label="AREA">OPERACIONES</td>
                        <td data-label="ALERTA">Pendientes de Endoso</td>
                        <td data-label="ESTADO"><?//echo $v_sin_endoso;?></td>
                    </tr>-->
                    <tr>
                        <td data-label="AREA">EMPRESAS</td>
                        <td data-label="ALERTA">Empresas pendientes de evaluacion legal con menos de <?echo $varr_max_dias_sineval['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_empresa_sineval_alert;?></td>
                    </tr>
                    <tr>
                        <td data-label="AREA">EMPRESAS</td>
                        <td data-label="ALERTA">Empresas pendientes de evaluacion legal con mas de <?echo $varr_max_dias_sineval['valornum'];?> dias</td>
                        <td data-label="ESTADO"><?echo $v_empresa_sineval_alert;?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!--@@@@@@@@@@@@@@@@@@@ GRFICOS -->
    <div style="overflow:hidden;float: right;width: calc(100% - 500px)">
        <!-- GRAFICO DE LA DISTRIBUCION DE EMISORES POR TAMANHO DE EMPRESA -->
        <div style="width: 300px;float: left;">
          <canvas id="kpi_empresas_tamanho"></canvas>
        </div>

        <!-- GRAFICO DE TIPO INVERSOR -->
        <div style="width: 300px;float: left;">
          <canvas id="kpi_tipo_inversor"></canvas>
        </div>

        <!-- GRAFICO DE PLAFT -->
        <div style="width: 500px;float: left;">
          <canvas id="kpi_plaft"></canvas>
        </div>
    </div>

    <!--====================== LIBRERIAS PARA EL GRAFICO -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!--====================== INFORMACION PARA EL GRAFICO DE DISTRIBUCION DE EMPRESAS -->
<?php
    $v_paleta_colores = array(0=>'rgb(255,64,105)',1=>'rgb(255,144,32)',2=>'rgb(212,161,43)',3=>'rgb(34,206,206)',4=>'rgb(5,155,255)',5=>'rgb(218,247,166)',6=>'rgb(255,195,0)', 
                                7=>'rgb(255,159,51)', 8=>'rgb(255,196,51)', 9=>'rgb(243,255,51)', 10=>'rgb(178,255,51)');

    $varr_distribucion_empresas = $vobj_mae->kpi_graph_distribucion_empresa_tamano();
    $v_labels_empdist = '';
    $v_q_empdist = '';
    $v_color_empdist = '';
    $j = 0;

    for ($i = 0; $i < count($varr_distribucion_empresas); $i++){
        $v_labels_empdist .= "'".$varr_distribucion_empresas[$i]['tamanho_nombre']."'";
        $v_q_empdist .= $varr_distribucion_empresas[$i]['cantidad'];
        $v_color_empdist .= "'".$v_paleta_colores[$j]."'";

        if ($j == 10) $j = 0; else $j++;

        if (($i + 1) < count($varr_distribucion_empresas)){
            $v_labels_empdist .= ",";
            $v_q_empdist .= ",";
            $v_color_empdist .= ",";
        }
    }
?>
    <script>
      const ctx = document.getElementById('kpi_empresas_tamanho');

      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: [<?echo $v_labels_empdist;?>],
          datasets: [{
            label: '# empresas',
            data: [<?echo $v_q_empdist;?>],
            backgroundColor: [<?echo $v_color_empdist;?>],
          }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribucion empresas por tamaño'
                }
            }
        },
      });
    </script>

    <!--====================== INFORMACION PARA EL GRAFICO DE TIPO INVERSOR -->
<?php
    $varr_tipoinver = $vobj_mae->kpi_graph_tipo_inversor();
    $v_labels_tipoinver = '';
    $v_q_tipoinver = '';
    $v_bcolor_tipoinver = '';
    $v_bgcolor_tipoinver = '';
    $j = 0;

    for ($i = 0; $i < count($varr_tipoinver); $i++){
        $v_labels_tipoinver .= "'".$varr_tipoinver[$i]['tipo_nombre']."'";
        $v_q_tipoinver .= $varr_tipoinver[$i]['cantidad'];
        $v_bcolor_tipoinver .= "'rgb(191,192,190)'";
        $v_bgcolor_tipoinver .= "'".$v_paleta_colores[$j]."'";

        if ($i + 1 < count($varr_tipoinver)){
            $v_labels_tipoinver .= ",";
            $v_q_tipoinver .= ",";
            $v_bcolor_tipoinver .= ",";
            $v_bgcolor_tipoinver .= ",";
        }
    }
?>
    <script>
      const ctx2 = document.getElementById('kpi_tipo_inversor');

      new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: [<?echo $v_labels_tipoinver;?>],
          datasets: [{
            label: '# inversores',
            data: [<?echo $v_q_tipoinver;?>],
            borderColor: [<?echo $v_bcolor_tipoinver;?>],
            backgroundColor: [<?echo $v_bgcolor_tipoinver;?>],
          }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Tipos de Inversores'
                }
            }
        },
      });
    </script>

    <!--====================== INFORMACION PARA EL GRAFICO PLAFT -->
<?php
    $varr_plaft = $vobj_mae->kpi_graph_plaft();
    $v_labels_plaft = "'PEP INVERSOR','PEP ACCIONISTA','OFAC INVERSOR','OFAC ACCIONISTA','ONU INVERSOR','ONU ACCIONISTA','CAUTELA INVERSOR','CAUTELA ACCIONISTA'";

    // ACTIVOS
    $v_q_activos = $varr_plaft['pep_activo_inver'].",".$varr_plaft['pep_activo_acc'].",".$varr_plaft['ofac_activo_inver'].",".$varr_plaft['ofac_activo_acc'].",".$varr_plaft['onu_activo_inver'].",".$varr_plaft['onu_activo_acc'].",".$varr_plaft['cautela_activo_inver'].",".$varr_plaft['cautela_activo_acc'];
    //$v_q_activos = "10,2,3,4,7,9,11,6";
    $v_bgcolor_activos = "'rgb(70,105,200)'";
    
    //INACTIVOS
    $v_q_inactivos = $varr_plaft['pep_inactivo_inver'].",".$varr_plaft['pep_inactivo_acc'].",".$varr_plaft['ofac_inactivo_inver'].",".$varr_plaft['ofac_inactivo_acc'].",".$varr_plaft['onu_inactivo_inver'].",".$varr_plaft['onu_inactivo_acc'].",".$varr_plaft['cautela_inactivo_inver'].",".$varr_plaft['cautela_inactivo_acc'];
    //$v_q_inactivos = "3,5,9,11,3,5,9,10";
    $v_bgcolor_inactivos = "'rgb(152,152,153)'";
?>
    <script>
      const ctx3 = document.getElementById('kpi_plaft');

      new Chart(ctx3, {
        type: 'bar',
        data: {
          labels: [<?echo $v_labels_plaft;?>],
          datasets: [{
            label: 'activos:',
            data: [<?echo $v_q_activos;?>],
            backgroundColor: [<?echo $v_bgcolor_activos;?>],
          },
          {
            label: 'no activos:',
            data: [<?echo $v_q_inactivos;?>],
            backgroundColor: [<?echo $v_bgcolor_inactivos;?>],
          },]
        },
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'Estado PLAFT de la cartera'
                },
            },
            responsive: true,
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
</BODY>
</HTML>