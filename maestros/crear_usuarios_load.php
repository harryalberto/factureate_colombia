<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");

$vobj_seg_load = new seguridad;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_seg_load->get_usuarios('COUNT', 0, 0, $_POST['filtros'], $v_order);
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'usuarios.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'usuarios.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'usuarios.apellido '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'usuarios.tipodoc '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'usuarios.identificacion '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'usuarios.email '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'usuarios.telefono '.$_POST['orderType'];
if ($_POST['orderCol'] == 7) $v_order = 'tipousuario_nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 8) $v_order = 'perfil_nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 9) $v_order = 'usuarios.fiduciaria_id '.$_POST['orderType'];
if ($_POST['orderCol'] == 10) $v_order = 'usuarios.empresaid '.$_POST['orderType'];

$varr_usuarios = $vobj_seg_load->get_usuarios('SELECT', $rowini, $_POST['registros'], $_POST['filtros'], $v_order);
$totalFiltro = count($varr_usuarios);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_usuarios); $i++){
	$v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_usuarios[$i]['usuario_id'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

    if ($varr_usuarios[$i]['fiduciaria_id'] > 0) $v_fiducia = 'SI'; else $v_fiducia = 'NO';
    if ($varr_usuarios[$i]['empresa_id'] > 0) $v_empresa = 'SI'; else $v_empresa = 'NO';

	$output['data'] .= '<tr>';
	$output['data'] .= '	<td data-label="ID">'.$varr_usuarios[$i]['usuario_id'].'</td>           <td data-label="NOMBRE">'.$varr_usuarios[$i]['nombre'].'</td>
                    		<td data-label="APELLIDO">'.$varr_usuarios[$i]['apellido'].'</td>       <td data-label="TIPO DOC">'.$varr_usuarios[$i]['tipodoc_nombre'].'</td>
                    		<td data-label="NRO DOC">'.$varr_usuarios[$i]['identificacion'].'</td>  <td data-label="EMAIL">'.$varr_usuarios[$i]['email'].'</td>
                    		<td data-label="TELEFONO">'.$varr_usuarios[$i]['telefono'].'</td>       <td data-label="TIPO USUARIO">'.$varr_usuarios[$i]['tipousuario_nombre'].'</td>
                    		<td data-label="PERFIL">'.$varr_usuarios[$i]['perfil_nombre'].'</td>    <td data-label="FIDUCIA">'.$v_fiducia.'</td>
                    		<td data-label="EMPRESA">'.$v_empresa.'</td>                            <td data-label="DETALLE">'.$v_boton_detalle.'</td>
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