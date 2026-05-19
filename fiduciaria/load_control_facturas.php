<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");

$vobj_factura_load = new factura;

date_default_timezone_set($_SESSION['user']['zona_horaria']);

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_factura_load->get_financiamientos('COUNT', 0, 0, $_POST['filtros'], '');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'financiamiento.facturaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'emisor.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'cliente.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'financiamiento.fpago '.$_POST['orderType'];
//if ($_POST['orderCol'] == 5) $v_order = 'financiamiento.fpago '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'financiamiento.monedaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 7) $v_order = 'financiamiento.monto_financiado '.$_POST['orderType'];
if ($_POST['orderCol'] == 8) $v_order = 'factura.total '.$_POST['orderType'];
//if ($_POST['orderCol'] == 9) $v_order = 'inversionista.estado_fiducia '.$_POST['orderType'];
if ($_POST['orderCol'] == 10) $v_order = 'financiamiento.notifica_op '.$_POST['orderType'];

$varr_financiamientos = $vobj_factura_load->get_financiamientos('SELECT', $_POST['registros'], $rowini, $_POST['filtros'], $v_order);
$totalFiltro = count($varr_financiamientos);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_financiamientos); $i++){
	$v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_financiamientos[$i]['factura_id'].','.$varr_financiamientos[$i]['finan_id'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

    $v_fpago = strtotime($varr_financiamientos[$i]['fpago']); $v_fpago = date('d-m-Y',$v_fpago);
    
    $v_fini_finan = strtotime($varr_financiamientos[$i]['fregistro_finan']); $v_fini_finan = date('Y-m-d',$v_fini_finan); $v_fini_finan = new DateTime($v_fini_finan);
    $v_fpago_finan = strtotime($varr_financiamientos[$i]['fpago']); $v_fpago_finan = date('Y-m-d',$v_fpago_finan); $v_fpago_finan = new DateTime($v_fpago_finan);
    $diff_dias_finan = $v_fini_finan->diff($v_fpago_finan);

    $v_monto_finan = number_format($varr_financiamientos[$i]['monto_financiado'],2,'.',',');
    $v_monto_factura = number_format($varr_financiamientos[$i]['monto_factura'],2,'.',',');

    $v_fecha_hoy = date('Y-m-d'); $v_fecha_hoy = new DateTime($v_fecha_hoy);
    $diff_dias_xvencer = $v_fecha_hoy->diff($v_fpago_finan);
    //---- VALIDANDO VENCIMIENTO
    $v_fecha_hoy_comp = date('Y-m-d');
    $v_fpago_finan_comp = strtotime($varr_financiamientos[$i]['fpago']); $v_fpago_finan_comp = date('Y-m-d',$v_fpago_finan_comp);

    if ($v_fecha_hoy_comp > $v_fpago_finan_comp) $v_vencimiento = $diff_dias_xvencer->days * -1;
    else $v_vencimiento = $diff_dias_xvencer->days;
    
    if ($varr_financiamientos[$i]['notifica_op'] == 0) $v_notificacion = '<i class="fa-solid fa-volume-xmark" style="font-size:16px;color:var(--color-rojo);"></i>';
    else $v_notificacion = '<i class="fa-solid fa-circle-check" style="font-size:16px;color:var(--color-verde);"></i>';

	$output['data'] .= '<tr>
	                        <td data-label="OPERACION ID">'.$varr_financiamientos[$i]['factura_id'].' </td><td data-label="EMISOR">'.$varr_financiamientos[$i]['emisor_nombre'].'</td>
                    		<td data-label="PAGADOR">'.$varr_financiamientos[$i]['cliente_nombre'].'  </td><td data-label="FACTURA">'.$varr_financiamientos[$i]['factura_numero'].'</td>
                    		<td data-label="F VTO">'.$v_fpago.'</td>                                  <td data-label="DIAS FINAN">'.$diff_dias_finan->days.'</td>
                    		<td data-label="MONEDA">'.$varr_financiamientos[$i]['moneda'].'</td>      <td data-label="MONTO FINAN">'.$v_monto_finan.'</td>
                    		<td data-label="MONTO FACTURA">'.$v_monto_factura.'</td>                  <td data-label="DIAS XVENCER">'.$v_vencimiento.'</td>
                    		<td data-label="NOTIFICACION">'.$v_notificacion.'</td>                    <td data-label="DETALLE">'.$v_boton_detalle.'</td>
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