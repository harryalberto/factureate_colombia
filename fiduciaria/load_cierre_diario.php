<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");

$vobj_cuentas_load = new cuentas;

//$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
//$pagina = $_POST['pagina'];

$v_fdia_anterior = strtotime('-1 day', strtotime($_POST['dia_cierre'])); $v_fdia_anterior = date('Y-m-d',$v_fdia_anterior);
$v_fdia_siguiente = strtotime('+1 day', strtotime($_POST['dia_cierre'])); $v_fdia_siguiente = date('Y-m-d',$v_fdia_siguiente);

$varr_cuenta_omnibus = $vobj_cuentas_load->get_cuenta_omnibus_xmoneda($_POST['moneda']);
$varr_movimientos = $vobj_cuentas_load->get_movimientos_cuenta_omnibus('SELECT', $varr_cuenta_omnibus[0]['cuenta_id'], 'TODO', 'BANCARIO', 0, 0, $_POST['dia_cierre'], $v_fdia_siguiente);

$totalFiltro = count($varr_movimientos);
$totalRegistros = $totalFiltro;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $totalFiltro;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';
$output['numero_cuenta'] = $varr_cuenta_omnibus[0]['cuenta_numero'];
$output['banco'] = $varr_cuenta_omnibus[0]['banco_nombre'];

//==== SALDOS INICIALES
$v_aux = strtotime($v_fdia_anterior);
$output['dia'] = date('j',$v_aux);
$output['mes'] = date('n',$v_aux);
$varr_cierre_inicial = $vobj_cuentas_load->get_cierre_diario($v_fdia_anterior, $varr_cuenta_omnibus[0]['cuenta_id']);

$output['saldo_inicial'] = '<ul>
                                <li style="display:inline;margin-right:15px;">Saldo Contable:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_inicial['saldo_contable'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:25px;">Saldo Inversor:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_inicial['saldo_inversor'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:10px;">Saldo Disponible:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_inicial['saldo_disponible'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:10px;">Saldo Vendedor:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_inicial['saldo_vendedor'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:25px;">Saldo Transito:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_inicial['saldo_transito'],2,'.',',s').'</li>
                            </ul>';

//==== CONTENIDO DE TABLA
for ($i = 0; $i < count($varr_movimientos); $i++){
    $v_monto = number_format($varr_movimientos[$i]['monto'], 2, '.', ',');
    if ($varr_movimientos[$i]['ing_sal'] == 1) $v_tipo = 'INGRESO'; else $v_tipo = 'SALIDA';
    
    $output['data'] .= '<tr>	
                            <td data-label="ID MOVIMIENTO">'.$varr_movimientos[$i]['movimiento_id'].'</td>  <td data-label="MOTIVO">'.$varr_movimientos[$i]['tmovimiento'].'</td>
                    		<td data-label="ORIGEN / DESTINO">'.$varr_movimientos[$i]['beneficiario_depositante'].'</td>               
                            <td data-label="TIPO MOV">'.$v_tipo.'</td>                                      <td data-label="MONTO">'.$v_monto.'</td>
                    	</tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="5">Sin resultados</td>';
    $output['data'] .= '</tr>';
}

//==== SALDOS FINALES
$varr_cierre_final = $vobj_cuentas_load->get_cierre_diario($_POST['dia_cierre'], $varr_cuenta_omnibus[0]['cuenta_id']);

$output['saldo_final'] = '  <ul>
                                <li style="display:inline;margin-right:15px;">Saldo Contable:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_final['saldo_contable'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:25px;">Saldo Inversor:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_final['saldo_inversor'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:10px;">Saldo Disponible:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_final['saldo_disponible'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:10px;">Saldo Vendedor:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_final['saldo_vendedor'],2,'.',',').'</li>
                            </ul>
                            <ul>
                                <li style="display:inline;margin-right:25px;">Saldo Transito:</li>
                                <li style="display:inline;">'.number_format($varr_cierre_final['saldo_transito'],2,'.',',s').'</li>
                            </ul>';

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>