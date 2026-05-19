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

$v_filtros = "financiamiento.estado = 27";

$rowcount = $vobj_inv_load->get_inversiones('COUNT', $rowini, $_POST['registros'], $v_filtros, '');

if ($_POST['orderCol'] == 0) $v_order = 'financiamiento.facturaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'pagador.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'financiamiento.fpago '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'tmoneda.dato1 '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'factura.total '.$_POST['orderType'];

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
    $v_monto = number_format($varr_inv[$i]['monto_factura'], 2, '.', ',');
    $v_fvencimiento = strtotime($varr_inv[$i]['f_vencimiento']); $v_fvencimiento = date('d-m-Y', $v_fvencimiento);
    
    $output['data'] .= '<tr>	
                            <td data-label="ID OPERACION">'.$varr_inv[$i]['factura_id'].'</td><td data-label="FACTURA">'.$varr_inv[$i]['factura_numero'].'</td>
                    		<td data-label="PAGADOR">'.$varr_inv[$i]['cliente_nombre'].'</td>  <td data-label="F VENCIMIENTO">'.$v_fvencimiento.'</td>
                    		<td data-label="MONEDA">'.$varr_inv[$i]['moneda_simbol'].'</td>     <td data-label="MONTO">'.$v_monto.'</td>
                    	</tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="7">Sin resultados</td>';
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