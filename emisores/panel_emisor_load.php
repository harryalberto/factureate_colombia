<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");

$vobj_factura_load = new factura;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'factura.id';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_factura_load->get_facturas_activas_xemisor('COUNT', 0, 0, $_POST['filtros'],'', $_SESSION['user']['empresaid']);
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'factura.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'empresa.identificacion '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'factura.numero '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'factura.total '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'factura.fvencimiento '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'tfin.nombre '.$_POST['orderType'];

$varr_datos = $vobj_factura_load->get_facturas_activas_xemisor('SELECT', 0, 0, '',$v_order, $_SESSION['user']['empresaid']);
$totalFiltro = count($varr_datos);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i=0; $i<count($varr_datos); $i++){
    $v_fregistro_time = strtotime($varr_datos[$i]['fvencimiento']);
    $v_fregistro = date('d-m-Y',$v_fregistro_time);

    if ($varr_datos[$i]['estadoid'] == 21 && $varr_datos[$i]['estadofinanciamientoid'] != 19){    // REVISADA Y NO LIQUIDADA
        if ($varr_datos[$i]['estadofinanciamientoid'] == 15) $label_finan = 'FINAN';
        else $label_finan = 'SUBASTA';

        $estado_label = $varr_datos[$i]['estadofinanciamiento'];
        $ver_subasta = 1;
        $v_accion = '<button style="font-size:11px;" type="button" factura_id='.$varr_datos[$i]['facturaid'].' monto='.$varr_datos[$i]['total'].' titulo='.$label_finan.' class="btn btn-primary openBtn2"><i class="fa-solid fa-magnifying-glass"></i> '.$label_finan.'</button>';
    } else{ 
        $estado_label = $varr_datos[$i]['estado'];
        $ver_subasta = 0;
    }

    $v_funcion_detalle = "verDetalle(".$varr_datos[$i]['facturaid'].",".$varr_datos[$i]['estadoid'].",".$varr_datos[$i]['estadofinanciamientoid'].")";
    $v_funcion_finan = "solicitaFinan(".$varr_datos[$i]['facturaid'].")";

    $v_btn_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="'.$v_funcion_detalle.'"><i class="fa-solid fa-magnifying-glass"></i></button>';

    if ($varr_datos[$i]['estadoid'] == 11)
        $v_btn_financiamiento = '<abbr title="Solicitar financiamiento"><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;" onclick="'.$v_funcion_finan.'"><i class="fa-solid fa-coins"></i></button></abbr>';
    else $v_btn_financiamiento = '';

    if ($varr_datos[$i]['estadoid'] != 17){
        $output['data'] .= '
                        <tr>
                            <td data-label="OPERACION ID">'.$varr_datos[$i]['facturaid'].'</td>                 <td data-label="CLIENTE">'.$varr_datos[$i]['cliente'].'</td>
                            <td data-label="DOCUMENTO">'.$varr_datos[$i]['clientenro'].'</td>                   <td data-label="FACTURA">'.$varr_datos[$i]['facturanro'].'</td>
                            <td data-label="MONTO">'.number_format($varr_datos[$i]['total'],2,'.',',').'</td>   <td data-label="MONEDA">'.$varr_datos[$i]['moneda'].'</td>
                            <td data-label="F VTO">'.$v_fregistro.'</td>                                        <td data-label="TIPO">'.$varr_datos[$i]['tipofinanciamiento'].'</td>
                            <td data-label="ESTADO">'.$estado_label.'</td>
                            <td data-label="DETALLE">'.$v_btn_detalle.'</td>                                    <td data-label="ACCION">'.$v_btn_financiamiento.'</td>
                        </tr>';
    }
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