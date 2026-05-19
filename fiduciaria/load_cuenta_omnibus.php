<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");

$vobj_cuentas_load = new cuentas;

//==== MOSTRAR LAS CUENTAS SIN DETALLE
$varr_cuentas = $vobj_cuentas_load->get_cuenta_omnibus(0);

if ($_POST['cuenta'] == 0){
	$totalFiltro = count($varr_cuentas);

	// Mostrado resultados
	$output = [];
	$output['totalRegistros'] = $totalFiltro;
	$output['totalFiltro'] = $totalFiltro;
	$output['data'] = '';
	$output['paginacion'] = '';

	for ($i = 0; $i < count($varr_cuentas); $i++){
		$v_boton_ingresos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verIngresos('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill"></i> Ingresos</button>';
		$v_boton_salidas = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verSalidas('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-trend-up"></i> Salidas</button>';
		$v_boton_todos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verTodo('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-transfer"></i> Todo</button>';

		$v_s_contable = number_format($varr_cuentas[$i]['s_contable'],2,'.',',');
		$v_s_inversor = number_format($varr_cuentas[$i]['s_inversor'],2,'.',',');
		$v_s_disponible = number_format($varr_cuentas[$i]['s_disponible'],2,'.',',');
		$v_s_vendedor = number_format($varr_cuentas[$i]['s_vendedor'],2,'.',',');
		$v_s_transito = number_format($varr_cuentas[$i]['s_transito'],2,'.',',');

		$output['data'] .= '<tr>';
		$output['data'] .= '	<td data-label="ID">'.$varr_cuentas[$i]['cuenta_id'].'</td>          <td data-label="NRO CUENTA">'.$varr_cuentas[$i]['cuenta_numero'].'</td>
	                    		<td data-label="TIPO">'.$varr_cuentas[$i]['tipo_cuenta_nom'].'</td>  <td data-label="BANCO">'.$varr_cuentas[$i]['banco_nombre'].'</td>
	                    		<td data-label="MONEDA">'.$varr_cuentas[$i]['moneda_simbol'].'</td>  <td data-label="SALDO CONTABLE">'.$v_s_contable.'</td>
	                    		<td data-label="SALDO INVERSOR">'.$v_s_inversor.'</td>       		 <td data-label="SALDO DISPONIBLE">'.$v_s_disponible.'</td>
	                    		<td data-label="SALDO VENDEDOR">'.$v_s_vendedor.'</td> 				 <td data-label="SALDO TRANSITO">'.$v_s_transito.'</td>
	                    		<td data-label="MOVIMIENTOS ING">'.$v_boton_ingresos.'</td>			 <td data-label="MOVIMIENTOS SAL">'.$v_boton_salidas.'</td>
	                    		<td data-label="MOVIMIENTOS TODOS">'.$v_boton_todos.'</td>
	                    	</tr>';
	}

	if ($totalFiltro == 0){
		$output['data'] .= '<tr>';
	    $output['data'] .= '<td colspan="13">Sin movimientos</td>';
	    $output['data'] .= '</tr>';
	}
} else {
	//==== DETALLE CUANDO SE SELECCIONO UNA DE LAS CUENTAS
	// Mostrado resultados
	$output = [];
	$output['data'] = '';
	//==== RELACION DE CUENTAS HASTA LA CUENTA SELECCIONADA
	for ($i = 0; $i < count($varr_cuentas); $i++){
		if ($varr_cuentas[$i]['cuenta_id'] == $_POST['cuenta']){
			if ($_POST['accion'] == 'salidas'){
				$v_boton_ingresos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verIngresos('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill"></i> Ingresos</button>';
				$v_boton_salidas = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);"><i class="fa-solid fa-money-bill-trend-up"></i> Salidas</button>';
				$v_boton_todos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verTodo('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-transfer"></i> Todo</button>';
			} elseif ($_POST['accion'] == 'ingresos'){
				$v_boton_ingresos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);"><i class="fa-solid fa-money-bill"></i> Ingresos</button>';
				$v_boton_salidas = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verSalidas('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-trend-up"></i> Salidas</button>';
				$v_boton_todos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verTodo('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-transfer"></i> Todo</button>';
			} else {
				$v_boton_ingresos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verIngresos('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill"></i> Ingresos</button>';
				$v_boton_salidas = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verSalidas('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-trend-up"></i> Salidas</button>';
				$v_boton_todos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);"><i class="fa-solid fa-money-bill-transfer"></i> Todo</button>';
			}
		} else {
			$v_boton_ingresos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verIngresos('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill"></i> Ingresos</button>';
			$v_boton_salidas = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verSalidas('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-trend-up"></i> Salidas</button>';
			$v_boton_todos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verTodo('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-transfer"></i> Todo</button>';
		}

			$v_s_contable = number_format($varr_cuentas[$i]['s_contable'],2,'.',',');
			$v_s_inversor = number_format($varr_cuentas[$i]['s_inversor'],2,'.',',');
			$v_s_disponible = number_format($varr_cuentas[$i]['s_disponible'],2,'.',',');
			$v_s_vendedor = number_format($varr_cuentas[$i]['s_vendedor'],2,'.',',');
			$v_s_transito = number_format($varr_cuentas[$i]['s_transito'],2,'.',',');

			$output['data'] .= '<tr>';
			$output['data'] .= '	<td data-label="ID">'.$varr_cuentas[$i]['cuenta_id'].'</td>          <td data-label="NRO CUENTA">'.$varr_cuentas[$i]['cuenta_numero'].'</td>
		                    		<td data-label="TIPO">'.$varr_cuentas[$i]['tipo_cuenta_nom'].'</td>  <td data-label="BANCO">'.$varr_cuentas[$i]['banco_nombre'].'</td>
		                    		<td data-label="MONEDA">'.$varr_cuentas[$i]['moneda_simbol'].'</td>  <td data-label="SALDO CONTABLE">'.$v_s_contable.'</td>
		                    		<td data-label="SALDO INVERSOR">'.$v_s_inversor.'</td>       		 <td data-label="SALDO DISPONIBLE">'.$v_s_disponible.'</td>
		                    		<td data-label="SALDO VENDEDOR">'.$v_s_vendedor.'</td> 				 <td data-label="SALDO TRANSITO">'.$v_s_transito.'</td>
		                    		<td data-label="MOVIMIENTOS ING">'.$v_boton_ingresos.'</td>			 <td data-label="MOVIMIENTOS SAL">'.$v_boton_salidas.'</td>
		                    		<td data-label="MOVIMIENTOS TODOS">'.$v_boton_todos.'</td>
		                    	</tr>';

		    if ($varr_cuentas[$i]['cuenta_id'] == $_POST['cuenta']) break;
	}

	$v_indice_max_cuenta = $i; $v_indice_max_cuenta ++;
	
	//---- FILTROS DE FECHA
	date_default_timezone_set($_SESSION['user']['zona_horaria']);

    if (!isset($_POST['fini'])){
    	$v_fhoy = date('Y-m-d');
    	$v_fini = strtotime('-7 day', strtotime($v_fhoy)); $v_fini = date('Y-m-d', $v_fini);
    	$v_ffin = strtotime('+1 day', strtotime($v_fhoy)); $v_ffin = date('Y-m-d',$v_ffin);
    	$v_ffin_view = $v_fhoy;
    } else {
    	$v_fini = $_POST['fini'];
    	$v_ffin_view = $_POST['ffin'];
    	$v_ffin = strtotime('+1 day', strtotime($_POST['ffin'])); $v_ffin = date('Y-m-d',$v_ffin);
    }

	$output['header_detalle'] = ' <tr>
									<th colspan="6">
										<label style="margin-right:5px;">Movimientos desde el: </label><input type="date" class="formulario_control" id="fini" name="fini" value="'.$v_fini.'" style="margin-right:10px;">
										<label style="margin-right:5px;">hasta: </label><input type="date" class="formulario_control" id="ffin" name="ffin" value="'.$v_ffin_view.'">
										<button type="button" class="btn btn-primary" style="font-size:10px;background-color:var(--color-azulv2);border:none;" onclick="filtrarMovimientos()"><i class="fa-solid fa-filter"></i></button>
									</th>
								  </tr>';
	//---- HEADER DEL DETALLE
	$output['header_detalle'] .= '<tr>
			                        <th scope="col">ID MOV</th>            <th scope="col">TIPO MOVIMIENTO</th>
			                        <th scope="col">FECHA</th>             <th scope="col">MOTIVO</th>
			                        <th scope="col">ORIGEN / DESTINO</th>  <th scope="col">MONTO</th>
			                    </tr>';

    $rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
    $pagina = $_POST['pagina'];

    //==== CALCULO DEL DETALLE DE MOVIMIENTOS
	if ($_POST['accion'] == 'salidas') {
		$rowcount = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('COUNT', $_POST['cuenta'], 'SAL', 'BANCARIO', 0, 0, $v_fini, $v_ffin);
		$varr_movimientos = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('SELECT', $_POST['cuenta'], 'SAL', 'BANCARIO', $rowini, $_POST['registros'], $v_fini, $v_ffin);
	} elseif ($_POST['accion'] == 'ingresos'){
		$rowcount = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('COUNT', $_POST['cuenta'], 'ING', 'BANCARIO', 0, 0, $v_fini, $v_ffin);
		$varr_movimientos = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('SELECT', $_POST['cuenta'], 'ING', 'BANCARIO', $rowini, $_POST['registros'], $v_fini, $v_ffin);
	} else{
		$rowcount = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('COUNT', $_POST['cuenta'], '', 'BANCARIO', 0, 0, $v_fini, $v_ffin);
		$varr_movimientos = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('SELECT', $_POST['cuenta'], '', 'BANCARIO', $rowini, $_POST['registros'], $v_fini, $v_ffin);
	}

	$totalFiltro = count($varr_movimientos);
	$totalRegistros = $rowcount;

	$output['totalRegistros'] = $rowcount;
	$output['totalFiltro'] = $totalFiltro;
	$output['paginacion'] = '';

	//---- DETALLE DE LOS MOVIMIENTOS
	$output['detalle'] = '';
	
	for ($i = 0; $i < count($varr_movimientos); $i++){
		if ($varr_movimientos[$i]['ing_sal'] == 1) $v_tmovimiento = 'INGRESO';
		else $v_tmovimiento = 'SALIDA';

		$v_fmov = strtotime($varr_movimientos[$i]['f_movimiento']); $v_fmov = date('d-m-Y', $v_fmov);
		$v_monto = number_format($varr_movimientos[$i]['monto'], 2, '.',',');

		$output['detalle'] .= '	<tr>
									<td data-label="ID MOV">'.$varr_movimientos[$i]['movimiento_id'].'</td>	<td data-label="TIPO MOVIMIENTO">'.$v_tmovimiento.'</td>
		                    		<td data-label="FECHA">'.$v_fmov.'</td>  								<td data-label="MOTIVO">'.$varr_movimientos[$i]['tmovimiento'].'</td>
		                    		<td data-label="ORIGEN / DESTINO">'.$varr_movimientos[$i]['beneficiario_depositante'].'</td>  
		                    		<td data-label="MONTO">'.$v_monto.'</td>
		                    	</tr>';
	}

	if ($totalFiltro == 0){
		$output['detalle'] .= '<tr>';
	    $output['detalle'] .= '<td colspan="6">Sin resultados</td>';
	    $output['detalle'] .= '</tr>';
	}

	//==== PAGINACION
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

	//==== CUENTAS QUE LE SIGUEN A LA CUENTA SELECCIONADA
	$output['header_otras_cuentas'] = '';
	$output['detalle_otras_cuentas'] = '';

	if ($v_indice_max_cuenta < count($varr_cuentas)){
		$output['header_otras_cuentas'] .= '<tr>
						                        <th scope="col">ID</th>                <th scope="col">NRO CUENTA</th>
						                        <th scope="col">TIPO</th>              <th scope="col">BANCO</th>
						                        <th scope="col">MONEDA</th>            <th scope="col">SALDO CONTABLE</th>
						                        <th scope="col">SALDO INVERSOR</th>    <th scope="col">SALDO DISPONIBLE</th>
						                        <th scope="col">SALDO VENDEDOR</th>    <th scope="col">SALDO TRANSITO</th>
						                        <th scope="col">MOVIMIENTOS ING</th>   <th scope="col">MOVIMIENTOS SAL</th>
						                        <th scope="col">MOVIMIENTOS TODOS</th>
						                    </tr>';
	}

	for ($i = $v_indice_max_cuenta; $i < count($varr_cuentas); $i++){
		$v_boton_ingresos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verIngresos('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill"></i> Ingresos</button>';
		$v_boton_salidas = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verSalidas('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-trend-up"></i> Salidas</button>';
		$v_boton_todos = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);" onclick="verTodo('.$varr_cuentas[$i]['cuenta_id'].')"><i class="fa-solid fa-money-bill-transfer"></i> Todo</button>';

		$v_s_contable = number_format($varr_cuentas[$i]['s_contable'],2,'.',',');
		$v_s_inversor = number_format($varr_cuentas[$i]['s_inversor'],2,'.',',');
		$v_s_disponible = number_format($varr_cuentas[$i]['s_disponible'],2,'.',',');
		$v_s_vendedor = number_format($varr_cuentas[$i]['s_vendedor'],2,'.',',');
		$v_s_transito = number_format($varr_cuentas[$i]['s_transito'],2,'.',',');

		$output['detalle_otras_cuentas'] .= '
							<tr>
								<td data-label="ID">'.$varr_cuentas[$i]['cuenta_id'].'</td>          <td data-label="NRO CUENTA">'.$varr_cuentas[$i]['cuenta_numero'].'</td>
		                    	<td data-label="TIPO">'.$varr_cuentas[$i]['tipo_cuenta_nom'].'</td>  <td data-label="BANCO">'.$varr_cuentas[$i]['banco_nombre'].'</td>
		                    	<td data-label="MONEDA">'.$varr_cuentas[$i]['moneda_simbol'].'</td>  <td data-label="SALDO CONTABLE">'.$v_s_contable.'</td>
		                    	<td data-label="SALDO INVERSOR">'.$v_s_inversor.'</td>       		 <td data-label="SALDO DISPONIBLE">'.$v_s_disponible.'</td>
		                    	<td data-label="SALDO VENDEDOR">'.$v_s_vendedor.'</td> 				 <td data-label="SALDO TRANSITO">'.$v_s_transito.'</td>
		                    	<td data-label="MOVIMIENTOS ING">'.$v_boton_ingresos.'</td>			 <td data-label="MOVIMIENTOS SAL">'.$v_boton_salidas.'</td>
		                    	<td data-label="MOVIMIENTOS TODOS">'.$v_boton_todos.'</td>
		                    </tr>';
	}
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>