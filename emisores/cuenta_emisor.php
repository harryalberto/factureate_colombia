<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'CUENTAEMISOR';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
//$arrestados = $objmaestro->get_estados('FACTURA');
//seleccion de los filtros y valores iniciales
$hoy = date('Y-m-d');
$finicio = strtotime('-180 day', strtotime($hoy));
$ffinicio = date('Y-m-d', $finicio);

if (isset($_POST['femision'])){
    $ffinicio = $_POST['femision'];
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/cuenta_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de financiamientos obtenidos
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="cuenta_emisor.php">
        <ul>
            <li>Fecha Inicio Financiamientos:</li>
            <li><input type='date' name='femision' value='<?=$ffinicio?>' min='2024-01-01' class="formulario_control"></li>
            <li>
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="filtrar()">
                    <i class="fa-solid fa-filter"></i> Filtrar</button>
            </li>
        </ul>
        <ul style="margin:1px;padding:1px;">
            <li>(*) Monto estimado hasta la fecha del pago efectivo por el obligado al pago</li>
            <li>(**) Monto depende del acuerdo con el pagador debido al incumplimiento en el pago</li>
        </ul>
        </form>
    </div>
    <?
    $v_nregistros = $objfactura->get_financiamiento_xemisor($_SESSION['user']['empresaid'],$ffinicio,'count');

    if ($v_nregistros <= 0){
        echo '<div class="listpag">
                No se encontraron registros de financiamiento ...
              </div>';
    } else{
        $v_arr_finan = $objfactura->get_financiamiento_xemisor($_SESSION['user']['empresaid'],$ffinicio,'select');
        echo '
        <div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">
            <ul style="list-style:none;overflow: hidden;">
                <table class="tabla_resize">
                    <thead>
                        <tr>
                            <th scope="col">OPERACION ID</th>
                            <th scope="col">FACTURA</th>
                            <th scope="col">NIT</th>
                            <th scope="col">CLIENTE</th>
                            <th scope="col">MONEDA</th>
                            <th scope="col">ADELANTO</th>
                            <th scope="col">REMANENTE</th>
                            <th scope="col">TOTAL RECIBIDO</th>
                            <th scope="col">TOTAL FACTURA</th>
                            <th scope="col">% DSCTO</th>
                            <th scope="col">FECHA FINAN</th>
                            <th scope="col">FECHA VCTO</th>
                            <th scope="col">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        for ($i=0; $i<count($v_arr_finan); $i++){
            $v_fecha_time = strtotime($v_arr_finan[$i]['finfregistro']);
            $v_fecha = date('d-m-Y',$v_fecha_time);
            $v_fecha_vcto = date('d-m-Y', strtotime($v_arr_finan[$i]['f_vencimiento']));

            $v_monto_total = $v_arr_finan[$i]['monto_financiado'] + $v_arr_finan[$i]['monto_remanente'];
            $v_estimado = '';
            $v_tasa_dcto = (($v_arr_finan[$i]['total_factura'] - $v_monto_total) / $v_arr_finan[$i]['total_factura']) * 100;

            if ($v_arr_finan[$i]['finan_estado_id'] == 27) $v_estimado = '<b>(*)</b>';
            elseif ($v_arr_finan[$i]['finan_estado_id'] == 30) $v_estimado = '<b>(**)</b>';

            echo '
                        <tr>
                            <td data-label="OPERACION ID">'.$v_arr_finan[$i]['facturaid'].'</td>
                            <td data-label="FACTURA">'.$v_arr_finan[$i]['facturanro'].'</td>
                            <td data-label="RNC">'.$v_arr_finan[$i]['empidentificacion'].'</td>
                            <td data-label="CLIENTE">'.$v_arr_finan[$i]['empnombre'].'</td>
                            <td data-label="MONEDA">'.$v_arr_finan[$i]['moneda_simbolo'].'</td>
                            <td data-label="ADELANTO">'.number_format($v_arr_finan[$i]['monto_financiado'],2,'.',',').'</td>
                            <td data-label="REMANENTE">'.$v_estimado.' '.number_format($v_arr_finan[$i]['monto_remanente'],2,'.',',').'</td>
                            <td data-label="TOTAL RECIBIDO">'.$v_estimado.' '.number_format($v_monto_total,2,'.',',').'</td>
                            <td data-label="TOTAL FACTURA">'.number_format($v_arr_finan[$i]['total_factura'],2,'.',',').'</td>
                            <td data-label="TOTAL RECIBIDO">'.$v_estimado.' '.number_format($v_tasa_dcto,2,'.',',').' %</td>
                            <td data-label="FECHA FINAN">'.$v_fecha.'</td>
                            <td data-label="FECHA VCTO">'.$v_fecha_vcto.'</td>
                            <td data-label="ESTADO">'.$v_arr_finan[$i]['finan_estado'].'</td>
                        </tr>';
        }
        echo '
                    </tbody>
                </table>
            </ul>
        </div>';
    }
    ?>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>