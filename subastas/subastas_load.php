<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");

$vobj_mae_load = new maestros;
$vobj_subasta_load = new subasta;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'subasta.id ';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_subasta_load->get_subastas_v2('COUNT', 0, 0, $_POST['filtros'],'');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'subasta.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'subasta.estado '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'factura.fvencimiento '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'subasta.monto_financia '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'tipos.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 7) $v_order = 'subasta.fcreacion '.$_POST['orderType'];

$varr_subastas = $vobj_subasta_load->get_subastas_v2('SELECT', $rowini, $_POST['registros'], $_POST['filtros'], $v_order);
$totalFiltro = count($varr_subastas);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_subastas); $i++){
    $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_subastas[$i]['subastaid'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

    $v_montofin = number_format($varr_subastas[$i]['montofin'],2,'.',',');
    $v_fvencimiento = date('d-m-Y',strtotime($varr_subastas[$i]['fvencimiento']));
    $v_fsubasta = date('d-m-Y',strtotime($varr_subastas[$i]['fsubasta_creacion']));
    
    $output['data'] .= '<tr>
                            <td data-label="OPERACION ID">'.$varr_subastas[$i]['facturaid'].'</td>      <td data-label="PAGADOR">'.$varr_subastas[$i]['cliente'].'</td>
                            <td data-label="FACTURA">'.$varr_subastas[$i]['facnumero'].'</td>           <td data-label="ESTADO">'.$varr_subastas[$i]['subasta_estado'].'</td>
                            <td data-label="VENCIMIENTO">'.$v_fvencimiento.'</td>                       <td data-label="MONTO FINANCIAR">'.$v_montofin.'</td>
                            <td data-label="MONEDA">'.$varr_subastas[$i]['moneda'].'</td>               <td data-label="F SUBASTA">'.$v_fsubasta.'</td>
                            <td data-label="ACCION">'.$v_boton_detalle.'</td>
                        </tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="9">Sin resultados</td>';
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