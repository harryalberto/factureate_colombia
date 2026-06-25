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
$v_order = 'financiamiento.fpago';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_factura_load->get_financiamientos('COUNT', 0, 0, $_POST['filtros'],'');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'financiamiento.facturaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'emisor.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'cliente.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'financiamiento.fpago '.$_POST['orderType'];
//if ($_POST['orderCol'] == 5) $v_order = '';
//if ($_POST['orderCol'] == 6) $v_order = '';
if ($_POST['orderCol'] == 7) $v_order = 'financiamiento.monto_financiado '.$_POST['orderType'];
if ($_POST['orderCol'] == 8) $v_order = 'factura.total '.$_POST['orderType'];

$varr_financiamientos = $vobj_factura_load->get_financiamientos('SELECT', $_POST['registros'], $rowini, $_POST['filtros'],$v_order);
$totalFiltro = count($varr_financiamientos);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_financiamientos); $i++){
    $t_fpago = strtotime($varr_financiamientos[$i]['fpago']);
    $v_fpago = date('d-m-Y',$t_fpago);
    $t_fini_finan = strtotime($varr_financiamientos[$i]['fregistro_finan']);
    // calculo de dias
    $v_fecha_hoy = date('Y-m-d');
    $v_fini_finan_en = date('Y-m-d',$t_fini_finan);
    $v_fpago_en = date('Y-m-d',$t_fpago);

    $v_dt_hoy = new DateTime($v_fecha_hoy);
    $v_dt_fini = new DateTime($v_fini_finan_en);
    $v_dt_fpago = new DateTime($v_fpago_en);

    $diff_dias_finan = $v_dt_fini->diff($v_dt_fpago);

    if ($v_dt_hoy > $v_dt_fpago) $v_dias_xvencer = 'VENCIDO'; elseif ($v_dt_hoy == $v_dt_fpago) $v_dias_xvencer = 0; 
    else {
        $diff_dias_xvencer = $v_dt_hoy->diff($v_dt_fpago);
        $v_dias_xvencer = $diff_dias_xvencer->days;
    }

    // confirmacion de pago
    if ($varr_financiamientos[$i]['confirma_pago'] == 'SI'){
        $t_fconfirm = strtotime($varr_financiamientos[$i]['f_confirmacion']);
        $v_fconfirm = date('d-m-Y',$t_fconfirm);
    } else $v_fconfirm = '';

    $v_boton_accion = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_financiamientos[$i]['finan_id'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

    $output['data'] .= '<tr>
                            <td data-label="OPERACION ID">'.$varr_financiamientos[$i]['factura_id'].'</td>
                            <td data-label="EMISOR">'.$varr_financiamientos[$i]['emisor_nombre'].'</td>
                            <td data-label="PAGADOR">'.$varr_financiamientos[$i]['cliente_nombre'].'</td>  
                            <td data-label="FACTURA">'.$varr_financiamientos[$i]['factura_numero'].'</td>
                            <td data-label="F VTO">'.$v_fpago.'</td>         
                            <td data-label="DIAS FINAN">'.$diff_dias_finan->days.'</td>
                            <td data-label="MONEDA">'.$varr_financiamientos[$i]['moneda'].'</td> 
                            <td data-label="MONTO FINAN">'.number_format($varr_financiamientos[$i]['monto_financiado'],2,'.',',').'</td>
                            <td data-label="MONTO FACTURA">'.number_format($varr_financiamientos[$i]['monto_factura'],2,'.',',').'</td>
                            <td data-label="DIAS XVENCER">'.$v_dias_xvencer.'</td>
                            <td data-label="PAGO CONFIRMA">'.$varr_financiamientos[$i]['confirma_pago'].'</td>
                            <td data-label="F CONFIRMA">'.$v_fconfirm.'</td>
                            <td data-label="ACCION">'.$v_boton_accion.'</td>
                        </tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="13">Sin resultados</td>';
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