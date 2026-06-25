<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/c_cuentas.php");

$vobj_cuentas_load = new cuentas;

$varr_ctas_emisor = $vobj_cuentas_load->get_cuentas_banco_emisor($_SESSION['user']['empresaid']);
$totalDatos = count($varr_ctas_emisor);

//objeto con resultados
$output = [];
$output['data'] = '';
$output['totalDatos']= $totalDatos;

for ($i=0; $i<count($varr_ctas_emisor); $i++){
    $output['data'] .= '<tr>
                            <td data-label="BANCO">'.$varr_ctas_emisor[$i]['banco_nombre'].'</td>
                            <td data-label="TIPO CUENTA">'.$varr_ctas_emisor[$i]['tcuenta_nombre'].'</td>
                            <td data-label="MONEDA">'.$varr_ctas_emisor[$i]['moneda_nombre'].'</td>
                            <td data-label="NRO CUENTA">'.$varr_ctas_emisor[$i]['nro_cuenta'].'</td>
                            <td data-label="NRO CUENTA">'.$varr_ctas_emisor[$i]['estado_nombre'].'</td>
                            <td data-label="ELIMINAR"><button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="eliminarCuenta('.$varr_ctas_emisor[$i]['moneda_id'].','.$varr_ctas_emisor[$i]['id'].')"><i class="fa-solid fa-trash"></i> Eliminar</button></td>
                            <td data-label="MODIFICAR"><button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="modificarCuenta('.$varr_ctas_emisor[$i]['moneda_id'].','.$varr_ctas_emisor[$i]['id'].')"><i class="fa-solid fa-pen-to-square"></i> Modificar</button></td>
                        </tr>';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>