<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/factura.php");

$vobj_mae_load = new maestros;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
//$v_order = 'factura.id ';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_mae_load->get_empresas_xestadoriesgo($_POST['e_pendientes'], $_POST['e_vencidos'], $_POST['e_xvencer'], $_POST['e_activo'], 0, 0,'count');
else $rowcount = $_POST['rowcount'];

/*if ($_POST['orderCol'] == 0) $v_order = 'factura.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'cliente.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'factura.femision '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'factura.fvencimiento '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'tipos.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 7) $v_order = 'factura.total '.$_POST['orderType'];
if ($_POST['orderCol'] == 9) $v_order = 'factura.fenvio '.$_POST['orderType'];*/

$varr_empresas = $vobj_mae_load->get_empresas_xestadoriesgo($_POST['e_pendientes'], $_POST['e_vencidos'], $_POST['e_xvencer'], $_POST['e_activo'],$_POST['registros'],$rowini,'select');
$totalFiltro = count($varr_empresas);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_empresas); $i++){
    $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_empresas[$i]['empresa_id'].')"><i class="fa-solid fa-magnifying-glass"></i></button>';

    if (!is_null($varr_empresas[$i]['f_registro'])){$t_fregistro = strtotime($varr_empresas[$i]['f_registro']);$f_registro = date('d-m-Y',$t_fregistro);}
    if (!is_null($varr_empresas[$i]['f_modifica'])){$t_fmodifica = strtotime($varr_empresas[$i]['f_modifica']);$f_modifica = date('d-m-Y',$t_fmodifica);}
    if (!is_null($varr_empresas[$i]['f_envio'])){$t_fenvio = strtotime($varr_empresas[$i]['f_envio']);$f_envio = date('d-m-Y',$t_fenvio);}
    if (!is_null($varr_empresas[$i]['f_aprobacion'])){$t_faprobacion = strtotime($varr_empresas[$i]['f_aprobacion']);$f_aprobacion = date('d-m-Y',$t_faprobacion);}

    // CASO DE EMISORES E INVERSORES
    if ($varr_empresas[$i]['tempresa_id'] == 46 || $varr_empresas[$i]['tempresa_id'] == 47) 
        $v_pendiente_riesgo = 'NO APLICA'; else $v_pendiente_riesgo = $varr_empresas[$i]['pendiente_riesgo'];
    
    $output['data'] .= '<tr>
                            <td data-label="ID">'.$varr_empresas[$i]['empresa_id'].'</td>  <td data-label="EMPRESA">'.$varr_empresas[$i]['empresa'].'</td>
                            <td data-label="NRO DOC">'.$varr_empresas[$i]['nrodoc'].'</td> <td data-label="REGISTRO">'.$f_registro.'</td>
                            <td data-label="F MOD">'.$f_modifica.'</td>                    <td data-label="F ENVIO">'.$f_envio.'</td>
                            <td data-label="F APROB">'.$f_aprobacion.'</td>                <td data-label="TIPO">'.$varr_empresas[$i]['tempresa'].'</td>
                            <td data-label="RIESGO PEND">'.$v_pendiente_riesgo.'</td>      <td data-label="ESTADO">'.$varr_empresas[$i]['estado'].'</td>
                            <td data-label="DIAS">'.$varr_empresas[$i]['dias_alert'].'</td><td data-label="DETALLE">'.$v_boton_detalle.'</td>
                        </tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="12">Sin resultados</td>';
    $output['data'] .= '</tr>';
}

if ($totalRegistros > 0) {
    $totalPaginas = ceil($totalRegistros / $_POST['registros']);
    
    $output['paginacion'] .= '<nav>';
    $output['paginacion'] .= '<ul class="pagination">';

    $numeroInicio = max(1, $pagina - 4);
    $numeroFin = min($totalPaginas, $numeroInicio + 9);

    for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
        $output['paginacion'] .= '<li class="page-item' . ($pagina == $i ? ' active' : '') . '">';
        $output['paginacion'] .= '<a class="page-link" href="#" onclick="nextPage(' . $i . ')">' . $i . '</a>';
        $output['paginacion'] .= '</li>';
    }

    $output['paginacion'] .= '</ul>';
    $output['paginacion'] .= '</nav>';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>