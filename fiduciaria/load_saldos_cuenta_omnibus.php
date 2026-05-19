<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");

$vobj_cuentas_load = new cuentas;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$pagina = $_POST['pagina'];

$v_filtros = "cuenta_inversionista.monedaid = ".$_POST['moneda'];

$rowcount = $vobj_cuentas_load->get_saldos_cuenta_omnibus('COUNT', 0, 0, $v_filtros, '');
$varr_saldo_omnibus = $vobj_cuentas_load->get_cuenta_omnibus_xmoneda($_POST['moneda']);

if ($_POST['orderCol'] == 0) $v_order = 'cuenta_inversionista.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'inversionista.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'inversionista.apellido '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'cuenta_inversionista.saldo_contable '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'cuenta_inversionista.saldo_comprometido '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'cuenta_inversionista.saldo_disponible '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'cuenta_inversionista.saldo_invertido '.$_POST['orderType'];

$varr_cuentas = $vobj_cuentas_load->get_saldos_cuenta_omnibus('SELECT', $rowini, $_POST['registros'], $v_filtros, $v_order);
$totalFiltro = count($varr_cuentas);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

//==== SALDOS DE LA CUENTA OMNIBUS DE LA MONEDA
$output['saldo_contable'] = number_format($varr_saldo_omnibus[0]['s_contable'],2,'.',',');
$output['saldo_inversor'] = number_format($varr_saldo_omnibus[0]['s_inversor'],2,'.',',');
$output['saldo_disponible'] = number_format($varr_saldo_omnibus[0]['s_disponible'],2,'.',',');
$output['saldo_vendedor'] = number_format($varr_saldo_omnibus[0]['s_vendedor'],2,'.',',');
$output['saldo_transito'] = number_format($varr_saldo_omnibus[0]['s_transito'],2,'.',',');

for ($i = 0; $i < count($varr_cuentas); $i++){
    $v_scontable = number_format($varr_cuentas[$i]['s_contable'], 2, '.', ',');
    $v_scomprometido = number_format($varr_cuentas[$i]['s_comprometido'], 2, '.', ',');
    $v_sdisponible = number_format($varr_cuentas[$i]['s_disponible'], 2, '.', ',');
    $v_sinvertido = number_format($varr_cuentas[$i]['s_invertido'], 2, '.', ',');

    if ($varr_cuentas[$i]['inversionista_id'] == 0) $v_nombre = 'FACTUREATE';
    else $v_nombre = $varr_cuentas[$i]['nombre'];

	$output['data'] .= '<tr>	
                            <td data-label="ID CUENTA">'.$varr_cuentas[$i]['cuenta_id'].'</td><td data-label="NOMBRE">'.$v_nombre.'</td>
                    		<td data-label="APELLIDO">'.$varr_cuentas[$i]['apellido'].'</td>  <td data-label="SALDO CONTABLE">'.$v_scontable.'</td>
                    		<td data-label="SALDO COMPROMETIDO">'.$v_scomprometido.'</td>     <td data-label="SALDO DISPONIBLE">'.$v_sdisponible.'</td>
                    		<td data-label="SALDO INVERTIDO">'.$v_sinvertido.'</td>
                    	</tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="7">Sin resultados</td>';
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