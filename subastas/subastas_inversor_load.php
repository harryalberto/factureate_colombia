<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/factura.php");

$vobj_subastas_load = new subasta;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'subasta.id ';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_subastas_load->get_subastas_inversor('COUNT', 0, 0, $_POST['filtros'], '', $_POST['inversor_id']);
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'subasta.facturaid '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'factura.total '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'tipos.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'factura.fvencimiento '.$_POST['orderType'];

$varr_subastas_load = $vobj_subastas_load->get_subastas_inversor('SELECT', $_POST['registros'], $rowini, $_POST['filtros'], $v_order, $_POST['inversor_id']);
$totalFiltro = count($varr_subastas_load);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_subastas_load); $i++){
    if ($varr_subastas_load[$i]['qpropuestas'] > 0){
        //==== el inversor ya tiene una propuesta sobre esta subasta
        $v_propuesta_id = $vobj_subastas_load->get_propuesta_xinversionista($varr_subastas_load[$i]['subasta_id'],$_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);
        
        $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-amarillo);border:none;" onclick="verDetalle('.$varr_subastas_load[$i]['factura_id'].','.$varr_subastas_load[$i]['subasta_id'].','.$v_propuesta_id.')"><i class="fa-solid fa-bag-shopping"></i> Propuesta</button>';
    } else {
        //==== el inversor no tiene propuestas en la subasta
        $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_subastas_load[$i]['factura_id'].','.$varr_subastas_load[$i]['subasta_id'].',0)"><i class="fa-solid fa-cart-arrow-down"></i> Comprar</button>';
    }

    //==== dias por vencer
    $hoy = date('Y-m-d');
    $dhoy = new DateTime($hoy);
    $dvenc = new DateTime($varr_subastas_load[$i]['f_vencimiento']);
    $dif = $dhoy->diff($dvenc);
    $dias = $dif->days;
    $v_fvencimiento_t = strtotime($varr_subastas_load[$i]['f_vencimiento']);
    $v_fvencimiento_l = date('d-m-Y',$v_fvencimiento_t);
    
    $output['data'] .= '<tr>
                            <td data-label="ID">'.$varr_subastas_load[$i]['factura_id'].'</td>      <td data-label="PAGADOR">'.$varr_subastas_load[$i]['cliente'].'</td>
                            <td data-label="MONTO FACTURA">'.number_format($varr_subastas_load[$i]['total'],2,'.',',').'</td>   
                            <td data-label="FINANCIAMIENTO">'.number_format($varr_subastas_load[$i]['monto_fin'],2,'.',',').'</td>
                            <td data-label="MONEDA">'.$varr_subastas_load[$i]['moneda'].'</td>      <td data-label="DIAS X COBRAR">'.$dias.'</td>
                            <td data-label="F VENCIMIENTO">'.$v_fvencimiento_l.'</td>               
                            <td data-label="RIESGO" style="background-color:#'.$varr_subastas_load[$i]['color'].';color:#'.$varr_subastas_load[$i]['color_fuente'].';">['.$varr_subastas_load[$i]['calificacion'].'] '.$varr_subastas_load[$i]['riesgo'].'</td>
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