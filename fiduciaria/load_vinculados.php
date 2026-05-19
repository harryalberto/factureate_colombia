<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");

$vobj_mae_load = new maestros;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
//$v_order = 'inversionista.inversor_id';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_mae_load->get_inversores_v2('COUNT', 0, 0, $_POST['filtros'], $v_order);
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

$varr_vinculados = $vobj_mae_load->get_inversores_v2('SELECT', $rowini, $_POST['registros'], $_POST['filtros'], $v_order);
$totalFiltro = count($varr_vinculados);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_vinculados); $i++){
	$v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_vinculados[$i]['inversor_id'].','.$varr_vinculados[$i]['tipo_inversor'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

	$output['data'] .= '<tr>';
	$output['data'] .= '	<td data-label="ID">'.$varr_vinculados[$i]['inversor_id'].'</td>          <td data-label="NOMBRE">'.$varr_vinculados[$i]['nombre2'].'</td>
                    		<td data-label="APELLIDO">'.$varr_vinculados[$i]['apellido'].'</td>       <td data-label="TIPO DOC">'.$varr_vinculados[$i]['tipo_documento_nom'].'</td>
                    		<td data-label="NRO DOC">'.$varr_vinculados[$i]['identificacion'].'</td>  <td data-label="EMAIL">'.$varr_vinculados[$i]['email'].'</td>
                    		<td data-label="TELEFONO">'.$varr_vinculados[$i]['telefono'].'</td>       <td data-label="TIPO INV">'.$varr_vinculados[$i]['tipo_inversor_nombre'].'</td>
                    		<td data-label="ESTADO VINC">'.$varr_vinculados[$i]['estado_nombre'].'</td> <td data-label="ESTADO PLAFT">'.$varr_vinculados[$i]['estado_fiducia_nom'].'</td>
                    		<td data-label="DETALLE">'.$v_boton_detalle.'</td>
                    	</tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="11">Sin resultados</td>';
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