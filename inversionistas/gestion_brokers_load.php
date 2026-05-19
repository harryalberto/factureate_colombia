<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");

$vobj_mae_load = new maestros;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'usuarios.id';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_mae_load->get_brokers_list('COUNT', 0, 0, $_POST['filtros'],'');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'usuarios.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'usuarios.identificacion '.$_POST['orderType'];

$varr_brokers = $vobj_mae_load->get_brokers_list('SELECT', $_POST['registros'], $rowini, $_POST['filtros'], $v_order);
$totalFiltro = count($varr_brokers);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_brokers); $i++){
    $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_brokers[$i]['id'].')"><i class="fa-solid fa-magnifying-glass"></i></button>';
    
    $output['data'] .= '<tr>
                            <td data-label="ID">'.$varr_brokers[$i]['id'].'</td>                     <td data-label="NOMBRE">'.$varr_brokers[$i]['nombre'].'</td>
                            <td data-label="DOCUMENTO">'.$varr_brokers[$i]['identificacion'].'</td>  <td data-label="EMAIL">'.$varr_brokers[$i]['email'].'</td>
                            <td data-label="ESTADO">'.$varr_brokers[$i]['estado'].'</td>             <td data-label="DETALLE">'.$v_boton_detalle.'</td>
                        </tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="6">Sin resultados</td>';
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