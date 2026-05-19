<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");

$vobj_cuentas_load = new cuentas;

//$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
//$v_order = 'inversionista.inversor_id';
//$pagina = $_POST['pagina'];

/*if ($_POST['rowcount'] == 0) $rowcount = $vobj_mae_load->get_inversores_v2('COUNT', 0, 0, $_POST['filtros'], $v_order);
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'inversionista.inversor_id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'inversionista.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'inversionista.apellido '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'tipodoc_nom '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'inversionista.identificacion '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'inversionista.email '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'inversionista.telefono '.$_POST['orderType'];
if ($_POST['orderCol'] == 7) $v_order = 'tipoinv_nom '.$_POST['orderType'];
if ($_POST['orderCol'] == 8) $v_order = 'inversionista.estado '.$_POST['orderType'];
if ($_POST['orderCol'] == 9) $v_order = 'inversionista.estado_fiducia '.$_POST['orderType'];
*/
$varr_movimientos = $vobj_cuentas_load->get_movimientos_depositos_inversor($_POST['filtros'], $_POST['order']);
//$totalFiltro = count($varr_vinculados);
//$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
//$output['totalRegistros'] = $rowcount;
//$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
//$output['paginacion'] = '';
$v_fecha = $varr_movimientos[0]['f_registro'];

for ($i = 0; $i < count($varr_movimientos); $i++){
    $v_fecha_cur = strtotime($varr_movimientos[$i]['f_registro']); $v_fecha_cur = date('d-m-Y', $v_fecha_cur);

    if ($i == 0 || $v_fecha != $varr_movimientos[$i]['f_registro']){
        if ($i > 0){
            $output['data'] .= '</tbody>
                            </table>
                            </div>';
        }

        $output['data'] .= '<p style="margin-left:10px;font-size:10px;font-weight: bold;text-transform: uppercase;">Movimientos del '.$v_fecha_cur.'</p>
                            <div style="overflow:hidden;margin:5px;padding:5px;">
                            <table class="tabla_resize">
                                <thead>
                                    <tr>
                                        <th scope="col">OPERACION</th>  <th scope="col">INVERSOR ID</th>
                                        <th scope="col">INVERSOR</th>   <th scope="col">MONEDA</th>     
                                        <th scope="col">MONTO</th>
                                    </tr>
                                </thead>
                                <tbody>';
    }

    $v_nombre = $varr_movimientos[$i]['nombre'].' '.$varr_movimientos[$i]['apellido'];
    $v_monto = number_format($varr_movimientos[$i]['monto'],2,'.',',');

	$output['data'] .= '<tr>	
                            <td data-label="OPERACION">'.$varr_movimientos[$i]['movimiento_id'].'</td>  <td data-label="INVERSOR ID">'.$varr_movimientos[$i]['inversor_id'].'</td>
                    		<td data-label="INVERSOR">'.$v_nombre.'</td>                                <td data-label="MONEDA">'.$varr_movimientos[$i]['moneda'].'</td>
                    		<td data-label="MONTO">'.$v_monto.'</td>
                    	</tr>';
}

if ($i > 0){
    $output['data'] .= '        </tbody>
                            </table>
                            </div>';
}

if (count($varr_movimientos) == 0){
    $output['data'] .= '<p>Sin resultados</p>';
	/*$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="11">Sin resultados</td>';
    $output['data'] .= '</tr>';*/
}

/*if ($totalRegistros > 0) {
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
}*/

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>