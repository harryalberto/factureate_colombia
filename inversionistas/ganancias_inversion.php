<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_inv = new inversiones;

$periodo = 0;
$v_mes_hoy = date('n');
$v_mes2_hoy = date('m');
$v_anho_hoy = date('Y');
$v_resumen_print = '';
$v_total_inversion_mes = 0;
$v_total_ganancia_mes = 0;
$v_titulo_mes = '';

if (isset($_POST['periodo'])) $periodo = $_POST['periodo'];
if ($periodo == 0) $v_finicio = $v_anho_hoy.'-'.$v_mes2_hoy.'-01';
else{
    $v_diferencia = $v_mes_hoy - $periodo;

    if ($v_diferencia <= 0){
        $v_mes_nuevo = 12 + $v_diferencia;
        $v_anho_nuevo = $v_anho_hoy - 1;
    } else{
        if ($v_mes_hoy > 9) $v_mes_nuevo = $v_mes_hoy - $periodo;
        else $v_mes_nuevo = '0'.$v_diferencia;
        $v_anho_nuevo = $v_anho_hoy;
    }

    $v_finicio = $v_anho_nuevo.'-'.$v_mes_nuevo.'-01';
}

$arr_inversiones = $obj_inv->get_inversiones_liquidadas($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$v_finicio);
if (count($arr_inversiones) <= 0) $v_resumen_print = 'Lo sentimos aun no presenta ganancias de inversiones realizadas';
//============= ARMADO DEL LISTADO
    $v_mes = 0;
    $v_arr_resumen = array();
    $v_relacion_print = '';
    $v_mes_lold = '';
    $v_total_info = '';
    // recorrido de las ganancias
    for ($i=0; $i<count($arr_inversiones); $i++){
        $v_mes_t = strtotime($arr_inversiones[$i]['f_pago']);
        $v_fecha_print = date('d-m-Y',$v_mes_t);
        $v_finver_t = strtotime($arr_inversiones[$i]['f_inversion']);
        $v_finver_print = date('d-m-Y',$v_finver_t);
        $v_encontrado = -1;
        $v_cambia_mes = 0;
    
        if ($v_mes == 0 || $v_mes != date('n',$v_mes_t)){   // REGISTRO INICIAL O CAMBIO DE MES
            $v_mes_l = date('F Y',$v_mes_t);
            if ($v_mes == 0){ $v_cambia_mes = 1; $v_mes_lold = $v_mes_l;} else $v_cambia_mes = 2;
            $v_mes = date('n',$v_mes_t);
    
        }
        // busca la moneda
        for ($j=0; $j<count($v_arr_resumen); $j++){
            if ($v_arr_resumen[$j]['moneda_id'] == $arr_inversiones[$i]['moneda_id']) $v_encontrado = $j;
        }
    
        if ($v_encontrado >= 0){
            $v_arr_resumen[$v_encontrado]['total_inversion'] = $v_arr_resumen[$v_encontrado]['total_inversion'] + $arr_inversiones[$i]['monto_inversion'];
            $v_arr_resumen[$v_encontrado]['total_ganancia'] = $v_arr_resumen[$v_encontrado]['total_ganancia'] + $arr_inversiones[$i]['monto_ganancia'];
            $v_arr_resumen[$v_encontrado]['tia_acumulada'] = $v_arr_resumen[$v_encontrado]['tia_acumulada'] + $arr_inversiones[$i]['tia'];
            $v_arr_resumen[$v_encontrado]['cantidad_inversiones'] = $v_arr_resumen[$v_encontrado]['cantidad_inversiones'] + 1;
            $v_arr_resumen[$v_encontrado]['dias_acumulado'] = $v_arr_resumen[$v_encontrado]['dias_acumulado'] + $arr_inversiones[$i]['dias_inversion'];
        } else{ //moneda nueva
            $v_indice = count($v_arr_resumen);
            $v_arr_resumen[$v_indice]['moneda_id'] = $arr_inversiones[$i]['moneda_id'];
            $v_arr_resumen[$v_indice]['moneda_nom'] = $arr_inversiones[$i]['moneda_nom'];
            $v_arr_resumen[$v_indice]['total_inversion'] = $arr_inversiones[$i]['monto_inversion'];
            $v_arr_resumen[$v_indice]['total_ganancia'] = $arr_inversiones[$i]['monto_ganancia'];
            $v_arr_resumen[$v_indice]['tia_acumulada'] = $arr_inversiones[$i]['tia'];
            $v_arr_resumen[$v_indice]['cantidad_inversiones'] = 1;
            $v_arr_resumen[$v_indice]['dias_acumulado'] = $arr_inversiones[$i]['dias_inversion'];
        }
        // pintar listado por mes
        if ($v_cambia_mes > 0){
            if ($v_cambia_mes == 1){        // el inicio
                $v_titulo_mes = '
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:5px;float:left;padding:3px;color:#000000;font-weight: bold;font-size:14px;text-transform: uppercase;">'.$v_mes_l.' (';
                $v_relacion_print .= '
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">INSTRUMENTO</th>
                        <th scope="col">CLIENTE</th>
                        <th scope="col">FECHA INVER</th>
                        <th scope="col">FECHA GANANCIA</th>
                        <th scope="col">MONTO INVER</th>
                        <th scope="col">MONTO GANANCIA</th>
                        <th scope="col">MONEDA</th>
                        <th scope="col">% TASA ANUAL</th>
                        <th scope="col">DIAS INVER</th>
                    </tr>
                </thead>
                <tbody>';
            } else {
                //=== total del mes
                $v_titulo_mes .= 'TOTAL MONTO INVERTIDO = '.number_format($v_total_inversion_mes,2,'.',',').'  ||  TOTAL MONTO GANANCIA = '.number_format($v_total_ganancia_mes,2,'.',',').')</li>
        </ul>';

                $v_total_info .= $v_titulo_mes.$v_relacion_print.'</tbody></table></ul>';
                
                $v_mes_lold = $v_mes_l;
                $v_total_ganancia_mes = 0;
                $v_total_inversion_mes = 0;

                $v_titulo_mes = '
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:5px;float:left;padding:3px;color:#000000;font-weight: bold;font-size:14px;text-transform: uppercase;">'.$v_mes_l.' (';
                $v_relacion_print = '';
                $v_relacion_print .= '
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">INSTRUMENTO</th>
                            <th scope="col">CLIENTE</th>
                            <th scope="col">FECHA INVER</th>
                            <th scope="col">FECHA GANANCIA</th>
                            <th scope="col">MONTO INVER</th>
                            <th scope="col">MONTO GANANCIA</th>
                            <th scope="col">MONEDA</th>
                            <th scope="col">% TASA ANUAL</th>
                            <th scope="col">DIAS INVER</th>
                        </tr>
                    </thead>
                    <tbody>';
            }
        }
    
        $v_tia = number_format($arr_inversiones[$i]['tia']*100,2,'.',',');
        $v_tia_print = $v_tia.' %';
        $v_relacion_print .= '
            <tr>
                <td data-label="ID">'.$arr_inversiones[$i]['factura_id'].'</td>
                <td data-label="INSTRUMENTO">'.$arr_inversiones[$i]['factura_numero'].'</td>
                <td data-label="CLIENTE">'.$arr_inversiones[$i]['pagador'].'</td>
                <td data-label="FECHA INVER">'.$v_finver_print.'</td>
                <td data-label="FECHA GANANCIA">'.$v_fecha_print.'</td>
                <td data-label="MONTO INVER">'.number_format($arr_inversiones[$i]['monto_inversion'],2,'.',',').'</td>
                <td data-label="MONTO GANANCIA">'.number_format($arr_inversiones[$i]['monto_ganancia'],2,'.',',').'</td>
                <td data-label="MONEDA">'.$arr_inversiones[$i]['moneda_nom'].'</td>
                <td data-label="% TASA ANUAL">'.$v_tia_print.'</td>
                <td data-label="DIAS INVER">'.$arr_inversiones[$i]['dias_inversion'].'</td>
            </tr>';
        $v_total_inversion_mes = $v_total_inversion_mes + $arr_inversiones[$i]['monto_inversion'];
        $v_total_ganancia_mes = $v_total_ganancia_mes + $arr_inversiones[$i]['monto_ganancia'];
    }
    //=== total del mes
    $v_titulo_mes .= 'TOTAL MONTO INVERTIDO = '.number_format($v_total_inversion_mes,2,'.',',').'  ||  TOTAL MONTO GANANCIA = '.number_format($v_total_ganancia_mes,2,'.',',').')</li>
        </ul>';
    
    $v_relacion_print .= '</tbody></table></ul>';
    $v_total_info .= $v_titulo_mes.$v_relacion_print;
//============= armo el print del resumen
for ($j=0; $j<count($v_arr_resumen); $j++){
    $v_tia_prom = number_format(($v_arr_resumen[$j]['tia_acumulada'] / $v_arr_resumen[$j]['cantidad_inversiones'])*100,2,'.',',');
    $v_tia_prom_print = $v_tia_prom.'%';
    $v_dias_prom = number_format($v_arr_resumen[$j]['dias_acumulado'] / $v_arr_resumen[$j]['cantidad_inversiones'],0,'.',',');

    $v_resumen_print .= '
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:5px;float:left;padding:3px;color:#b30a1f;font-weight: bold;font-size:16px;">MONEDA: '.$v_arr_resumen[$j]['moneda_nom'].'</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li class="box_subtitulo_verde"><p>Total Inversi&oacute;n</p><p><i class="fa-solid fa-hand-holding-dollar" style="font-size:18px;"></i> '.number_format($v_arr_resumen[$j]['total_inversion'],2,'.',',').'</p></li>
            <li class="box_subtitulo_verde"><p>Total Ganancia</p><p><i class="fa-solid fa-sack-dollar" style="font-size:18px;"></i> '.number_format($v_arr_resumen[$j]['total_ganancia'],2,'.',',').'</p></li>
            <li class="box_subtitulo_verde"><p>Tasa Promedio</p><p><i class="fa-solid fa-money-bill-trend-up" style="font-size:18px;"></i> '.$v_tia_prom_print.'</p></li>
            <li class="box_subtitulo_amarillo"><p>Nro Inversiones</p><p><i class="fa-solid fa-arrow-up-wide-short" style="font-size:18px;"></i> '.number_format($v_arr_resumen[$j]['cantidad_inversiones'],0,'.',',').'</p></li>
            <li class="box_subtitulo_amarillo"><p>Prom d&iacute;as Inver</p><p><i class="fa-solid fa-gauge" style="font-size:18px;"></i> '.$v_dias_prom.'</p></li>
        </ul>';
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'inversionistas/ganancias_inversion.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Ganancias de inversiones realizadas
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="ganancias_inversion.php">
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;margin-top:5px;">PERIODO DE ANALISIS:</li>
            <li>
                <select name="periodo" class="frminput_text">
                <?php
                    $v_opcion_print = '';

                    if ($periodo == 0) $v_opcion_print .= '<option value="0" selected>Mes actual</option>'; else $v_opcion_print .= '<option value="0">Mes actual</option>';
                    if ($periodo == 1) $v_opcion_print .= '<option value="1" selected>Desde 1 mes atras</option>'; else $v_opcion_print .= '<option value="1">Desde 1 mes atras</option>';
                    if ($periodo == 2) $v_opcion_print .= '<option value="2" selected>Desde 2 meses atras</option>'; else $v_opcion_print .= '<option value="2">Desde 2 meses atras</option>';
                    if ($periodo == 3) $v_opcion_print .= '<option value="3" selected>Desde 3 meses atras</option>'; else $v_opcion_print .= '<option value="3">Desde 3 meses atras</option>';
                    if ($periodo == 4) $v_opcion_print .= '<option value="4" selected>Desde 4 meses atras</option>'; else $v_opcion_print .= '<option value="4">Desde 4 meses atras</option>';
                    if ($periodo == 6) $v_opcion_print .= '<option value="6" selected>Desde 6 meses atras</option>'; else $v_opcion_print .= '<option value="6">Desde 6 meses atras</option>';
                    if ($periodo == 12) $v_opcion_print .= '<option value="12" selected>Desde 12 meses atras</option>'; else $v_opcion_print .= '<option value="12">Desde 12 meses atras</option>';
                    echo $v_opcion_print;
                ?>
                </select>
            </li>
            <li><button type="button" class="btn btn-primary" onclick="filtrar()" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
            <span class="icon-filter" style="font-size:16px;"></span> Filtrar</button></li>
        </ul>
        </form>
    </div>
    <!-- resumen -->
    <div class="frmtransaccion">
        <?php echo $v_resumen_print;?>
    </div>
    <!-- listado -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
    <?php echo $v_total_info;?>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>