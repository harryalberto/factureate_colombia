<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");

$vobj_mae_load = new maestros;
$vobj_factura_load = new factura;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
//$v_order = 'financiamiento.fpago';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_factura_load->get_financiamientos('COUNT', 0, 0, $_POST['filtros'],'');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'emisor.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'financiamiento.monedaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'factura.total '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'financiamiento.fpago '.$_POST['orderType'];
//if ($_POST['orderCol'] == 5) $v_order = '';
//if ($_POST['orderCol'] == 6) $v_order = '';

$varr_facturas = $vobj_factura_load->get_financiamientos('SELECT', $_POST['registros'], $rowini, $_POST['filtros'],$v_order);
$totalFiltro = count($varr_facturas);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_facturas); $i++){
    $t_fpago = strtotime($varr_facturas[$i]['fpago']);
    $v_fpago = date('d-m-Y',$t_fpago);

    // calculo de dias
    $v_fecha_hoy = date('Y-m-d');
    $v_fpago_en = date('Y-m-d',$t_fpago);

    if (is_null($varr_facturas[$i]['f_confirmacion']) || $varr_facturas[$i]['f_confirmacion'] == '') {
        $v_fpago_efectivo = $varr_facturas[$i]['fpago'];
        $v_fconfirmacion = '--';
    } else {
        $v_fpago_efectivo = $varr_facturas[$i]['f_confirmacion'];
        $v_fconfirmacion = date('d-m-Y', strtotime($varr_facturas[$i]['f_confirmacion']));
    }

    $v_dt_hoy = new DateTime($v_fecha_hoy);
    $v_dt_fpago_confir = new DateTime($v_fpago_efectivo);

    if ($v_dt_hoy > $v_dt_fpago_confir) $v_dias_xvencer = 'VENCIDO'; elseif ($v_dt_hoy == $v_dt_fpago_confir) $v_dias_xvencer = 0;
    else {
        $diff_dias_xvencer = $v_dt_hoy->diff($v_dt_fpago_confir);
        $v_dias_xvencer = $diff_dias_xvencer->days;
    }
    
    $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_facturas[$i]['factura_id'].')"><i class="fa-solid fa-magnifying-glass"></i></button>';
    $v_boton_fecha = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;" onclick="progPago('.$varr_facturas[$i]['factura_id'].')"><i class="fa-solid fa-calendar-day"></i></button>';
    $v_boton_pagar = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;" onclick="pagar('.$varr_facturas[$i]['factura_id'].')"><i class="fa-solid fa-money-check-dollar"></i></button>';
    $v_monto_total = number_format($varr_facturas[$i]['monto_factura'], 2, '.', ',');

    $output['data'] .= '<tr>
                            <td data-label="[]"><input type="checkbox" name="slc[]" value="'.$varr_facturas[$i]['factura_id'].'" onchange="seleccion(this)"></td>
                            <td data-label="FACTURA">'.$varr_facturas[$i]['factura_numero'].'</td>
                            <td data-label="PROVEEDOR">'.$varr_facturas[$i]['emisor_nombre'].'</td>
                            <td data-label="MONEDA">'.$varr_facturas[$i]['moneda'].'</td>
                            <td data-label="MONTO">'.$v_monto_total.'</td>
                            <td data-label="F VTO">'.$v_fpago.'</td>         
                            <td data-label="DIAS XVENCER">'.$v_dias_xvencer.'</td>
                            <td data-label="F PAGO PROG">'.$v_fconfirmacion.'</td>
                            <td data-label="F PAGO">'.$v_boton_fecha.'</td>
                            <td data-label="DETALLE">'.$v_boton_detalle.'</td>
                            <td data-label="PAGAR">'.$v_boton_pagar.'</td>
                            <input type="hidden" name="monto'.$varr_facturas[$i]['factura_id'].'" id="monto'.$varr_facturas[$i]['factura_id'].'" value="'.$varr_facturas[$i]['monto_factura'].'">
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