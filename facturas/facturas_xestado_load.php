<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/factura.php");

$vobj_factura_load = new factura;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'factura.id ';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_factura_load->get_facturas('COUNT', 0, 0, $_POST['filtros'],'');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'factura.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'cliente.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'factura.femision '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'factura.fvencimiento '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'tipos.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 7) $v_order = 'factura.total '.$_POST['orderType'];
if ($_POST['orderCol'] == 9) $v_order = 'factura.fenvio '.$_POST['orderType'];

$varr_facturas = $vobj_factura_load->get_facturas('SELECT', $rowini, $_POST['registros'], $_POST['filtros'], $v_order);
$totalFiltro = count($varr_facturas);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';
//$output['select'] = $varr_facturas;
for ($i = 0; $i < count($varr_facturas); $i++){
    $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_facturas[$i]['facturaid'].')"><i class="fa-solid fa-magnifying-glass"></i></button>';

    $v_fecha_hoy = date('Y-m-d');
    $t_fenvio = strtotime($varr_facturas[$i]['fenvio']);
    $fenvio = date('d-m-Y',$t_fenvio);
    $v_fenvio_en = date('Y-m-d',$t_fenvio);
    $v_dt_hoy = new DateTime($v_fecha_hoy);
    $v_dt_fe = new DateTime($v_fenvio_en);
    $diff_fe = $v_dt_fe->diff($v_dt_hoy);

    $t_femision = strtotime($varr_facturas[$i]['femision']);
    $femision = date('d-m-Y',$t_femision);

    $t_fvencimiento = strtotime($varr_facturas[$i]['fvencimiento']);
    $fvencimiento = date('d-m-Y',$t_fvencimiento);

    //$v_montofin = number_format($varr_subastas[$i]['montofin'],2,'.',',');
    //$v_fvencimiento = date('d-m-Y',strtotime($varr_subastas[$i]['fvencimiento']));
    //$v_fsubasta = date('d-m-Y',strtotime($varr_subastas[$i]['fsubasta_creacion']));
    
    $output['data'] .= '<tr>
                            <td data-label="OPERACION ID">'.$varr_facturas[$i]['facturaid'].'</td>  <td data-label="EMISOR">'.$varr_facturas[$i]['emisor'].'</td>
                            <td data-label="PAGADOR">'.$varr_facturas[$i]['clientenombre'].'</td>   <td data-label="FACTURA">'.$varr_facturas[$i]['facturanro'].'</td>
                            <td data-label="F EMISION">'.$femision.'</td>                           <td data-label="F VTO">'.$fvencimiento.'</td>
                            <td data-label="MONEDA">'.$varr_facturas[$i]['moneda'].'</td>           <td data-label="MONTO FACTURA">'.number_format($varr_facturas[$i]['total'],2,'.',',').'</td>
                            <td data-label="ESTADO">'.$varr_facturas[$i]['estado'].'</td>           <td data-label="F ENVIO">'.$fenvio.'</td>
                            <td data-label="DIAS">'.$diff_fe->days.'</td>                           <td data-label="ACCION">'.$v_boton_detalle.'</td>
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