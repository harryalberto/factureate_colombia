<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_inversiones.php");

$vobj_inv_load = new inversiones;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$pagina = $_POST['pagina'];

$v_ffin_load = strtotime('+1 day', strtotime($_POST['ffin'])); $v_ffin_load = date('Y-m-d',$v_ffin_load);
$v_filtros = "financiamiento.estado = 37 and financiamiento.fpago_efectivo >= '".$_POST['fini']."' and financiamiento.fpago_efectivo < '".$v_ffin_load."'";

$rowcount = $vobj_inv_load->get_inversiones('COUNT', $rowini, $_POST['registros'], $v_filtros, '');

if ($_POST['orderCol'] == 0) $v_order = 'financiamiento.facturaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'emisor.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'pagador.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'financiamiento.fregistro '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'financiamiento.fpago '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'financiamiento.fpago_efectivo '.$_POST['orderType'];
if ($_POST['orderCol'] == 9) $v_order = 'tasa_compuesta '.$_POST['orderType'];
if ($_POST['orderCol'] == 11) $v_order = 'financiamiento.monedaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 12) $v_order = 'factura.total '.$_POST['orderType'];
if ($_POST['orderCol'] == 13) $v_order = 'financiamiento.monto_financiado '.$_POST['orderType'];
if ($_POST['orderCol'] == 14) $v_order = 'financiamiento.ganancia_e '.$_POST['orderType'];
if ($_POST['orderCol'] == 15) $v_order = 'financiamiento.comision_factureate_e '.$_POST['orderType'];
if ($_POST['orderCol'] == 16) $v_order = 'financiamiento.monto_remanente_e '.$_POST['orderType'];

$varr_inv = $vobj_inv_load->get_inversiones('SELECT', $rowini, $_POST['registros'], $v_filtros, $v_order);
$totalFiltro = count($varr_inv);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_inv); $i++){
    $v_monto_factura = number_format($varr_inv[$i]['monto_factura'], 2, '.', ',');
    $v_monto_finan = number_format($varr_inv[$i]['monto_financiado'], 2, '.', ',');
    $v_ganancia_inversor = number_format($varr_inv[$i]['ganancia'], 2, '.', ',');
    $v_comision_factureate = number_format($varr_inv[$i]['comision_factureate'], 2, '.', ',');
    $v_remanente = number_format($varr_inv[$i]['monto_remanente'], 2, '.', ',');
    $v_tasa = $varr_inv[$i]['tasa_compuesta'] * 100; $v_tasa = number_format($v_tasa, 2, '.', ','); $v_tasa = $v_tasa.'%';

    $v_ffinan = strtotime($varr_inv[$i]['f_registro']); $v_ffinan = date('d-m-y', $v_ffinan);
    $v_fvencimiento = strtotime($varr_inv[$i]['f_vencimiento']); $v_fvencimiento = date('d-m-Y', $v_fvencimiento);
    $v_fpago = strtotime($varr_inv[$i]['fpago_efectivo']); $v_fpago = date('d-m-Y', $v_fpago);

    $v_ffinan_dt = strtotime($varr_inv[$i]['f_registro']); $v_ffinan_dt = date('Y-m-d', $v_ffinan_dt); $v_ffinan_dt = new DateTime($v_ffinan_dt);
    $v_fpago_dt = strtotime($varr_inv[$i]['fpago_efectivo']); $v_fpago_dt = date('Y-m-d', $v_fpago_dt); $v_fpago_dt = new DateTime($v_fpago_dt);
    $v_fvenc_dt = strtotime($varr_inv[$i]['f_vencimiento']); $v_fvenc_dt = date('Y-m-d', $v_fvenc_dt); $v_fvenc_dt = new DateTime($v_fvenc_dt);

    $v_dias_finan = $v_fpago_dt->diff($v_ffinan_dt); $v_dias_finan = $v_dias_finan->days;
    $v_dias_retraso = $v_fpago_dt->diff($v_fvenc_dt); $v_dias_retraso = $v_dias_retraso->days;
    
    $output['data'] .= '<tr>	
                            <td data-label="ID OPERACION">'.$varr_inv[$i]['factura_id'].'</td>              <td data-label="FACTURA">'.$varr_inv[$i]['factura_numero'].'</td>
                    		<td data-label="VENDEDOR">'.$varr_inv[$i]['emisor_nombre'].'</td>               <td data-label="PAGADOR">'.$varr_inv[$i]['cliente_nombre'].'</td>  
                            <td data-label="F FINAN">'.$v_ffinan.'</td>                                     <td data-label="F VENCIMIENTO">'.$v_fvencimiento.'</td>
                    		<td data-label="F PAGO">'.$v_fpago.'</td>                                       <td data-label="DIAS FINAN">'.$v_dias_finan.'</td>
                            <td data-label="DIAS RETRASO">'.$v_dias_retraso.'</td>                          <td data-label="TASA %">'.$v_tasa.'</td>
                            <td data-label="MONEDA">'.$varr_inv[$i]['moneda_simbol'].'</td>                 <td data-label="MONTO FACTURA">'.$v_monto_factura.'</td>
                            <td data-label="MONTO FINAN">'.$v_monto_finan.'</td>                            <td data-label="GANANCIA INVERSOR">'.$v_ganancia_inversor.'</td>
                            <td data-label="COMISION FACTUREATE">'.$v_comision_factureate.'</td>            <td data-label="REMANENTE">'.$v_remanente.'</td>
                    	</tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="16">Sin resultados</td>';
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