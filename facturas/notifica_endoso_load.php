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
$vobj_mae_load = new maestros;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'endoso_notifica.factura_id';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_factura_load->get_noti_endosos('COUNT', 0, 0, $_POST['filtros'],'');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'endoso_notifica.factura_id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'endoso_notifica.fecha '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'financiamiento.fregistro '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'estados.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'tipos.nombre '.$_POST['orderType'];

$varr_notifica = $vobj_factura_load->get_noti_endosos('SELECT', $_POST['registros'], $rowini, $_POST['filtros'],$v_order);
$totalFiltro = count($varr_notifica);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

$varr_param_load = $vobj_mae_load->get_parametros();

for ($i = 0; $i < count($varr_notifica); $i++){
	$v_fnotifica = date('d-m-Y', strtotime($varr_notifica[$i]['fecha']));

	if ($varr_param_load['NOTI OP AUTOM']['valornum'] == 1){
        $v_email_op = "'".$varr_notifica[$i]['email_op']."'";
		$v_boton_accion = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="enviar_notifica('.$varr_notifica[$i]['factura_id'].','.$v_email_op.')" title="Envia notificacion">
						<i class="fa-solid fa-envelopes-bulk"></i></button>';
    } else{
		$v_boton_accion = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="registra_notifica('.$varr_notifica[$i]['factura_id'].')" title="Registrar notificacion">
						<i class="fa-solid fa-pen-to-square"></i></button>';

        // cuando la noti fisica ya fue enviada muestra el link
        if ($varr_notifica[$i]['estado_id'] == 72) $v_boton_accion .= '<a href="'.$varr_notifica[$i]['path_noti'].'" target="_blank" style="margin-left:5px;"><i class="fa-solid fa-link"></i></a>';
    }

	if ($varr_notifica[$i]['noti_old'] > 0)
		$v_boton_old = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verNotiOld('.$varr_notifica[$i]['factura_id'].')">'.$varr_notifica[$i]['noti_old'].'</button>';
	else $v_boton_old = '';

	$output['data'] .= '<tr>
                            <td data-label="OPERACION ID">'.$varr_notifica[$i]['factura_id'].'</td>
                            <td data-label="OBLIGADO AL PAGO">'.$varr_notifica[$i]['op_nombre'].'</td>
                            <td data-label="FECHA">'.$v_fnotifica.'</td>
                            <td data-label="HORA">'.$varr_notifica[$i]['hora'].'</td>
                            <td data-label="ESTADO">'.$varr_notifica[$i]['estado_nom'].'</td>
                            <td data-label="TIPO">'.$varr_notifica[$i]['tipo_nom'].'</td>
                            <td data-label="EMAIL OP">'.$varr_notifica[$i]['email_op'].'</td>
                            <td data-label="ACCION">'.$v_boton_accion.'</td>
                            <td data-label="OTRAS">'.$v_boton_old.'</td>
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