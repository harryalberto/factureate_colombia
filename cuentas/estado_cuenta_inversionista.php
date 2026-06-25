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
    
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cuentas = new cuentas;

$v_mes_hoy = date('n'); // mes del 1 al 12
$v_mes_filtro = $v_mes_hoy;
$v_mes2_hoy = date('m');    // mes del  01 al  12
$v_anho_hoy = date('Y');    // annho 2022
$v_anho_filtro = $v_anho_hoy;
$v_tipo_mov = 'FULL';
$v_anho_inicial = 2022;

$v_arr_meses = array();
$v_arr_meses[0] = array('mes'=>'01', 'mes_nom'=>'Enero');$v_arr_meses[1] = array('mes'=>'02', 'mes_nom'=>'Febrero');$v_arr_meses[2] = array('mes'=>'03', 'mes_nom'=>'Marzo');
$v_arr_meses[3] = array('mes'=>'04', 'mes_nom'=>'Abril');$v_arr_meses[4] = array('mes'=>'05', 'mes_nom'=>'Mayo');$v_arr_meses[5] = array('mes'=>'06', 'mes_nom'=>'Junio');
$v_arr_meses[6] = array('mes'=>'07', 'mes_nom'=>'Julio');$v_arr_meses[7] = array('mes'=>'08', 'mes_nom'=>'Agosto');$v_arr_meses[8] = array('mes'=>'09', 'mes_nom'=>'Septiembre');
$v_arr_meses[9] = array('mes'=>'10', 'mes_nom'=>'Octubre');$v_arr_meses[10] = array('mes'=>'11', 'mes_nom'=>'Noviembre');$v_arr_meses[11] = array('mes'=>'12', 'mes_nom'=>'Diciembre');

if (isset($_POST['periodo'])){
    $v_mes_filtro = $_POST['periodo'];
    $v_anho_filtro = $_POST['anho'];
    if (isset($_REQUEST['tipomov'])) $v_tipo_mov = 'BANCARIO';
}

if (substr($v_mes_filtro,0,1) == '0') $v_mes_filtro_num = substr($v_mes_filtro,1,1);
else $v_mes_filtro_num = $v_mes_filtro;

if ($v_mes_filtro == '01'){
    $v_mes_saldoini = '12';
    $v_anho_saldoini = $v_anho_filtro - 1;
} else{
    $v_mes_saldoini = $v_mes_filtro_num - 1;
    if ($v_mes_saldoini < 10) $v_mes_saldoini = '0'.$v_mes_saldoini;
    $v_anho_saldoini = $v_anho_filtro;
}

if ($v_mes_filtro == '12'){
    $v_mes_final = '01';
    $v_anho_final = $v_anho_filtro + 1;
} else{
    $v_mes_final = $v_mes_filtro_num + 1;
    if ($v_mes_final < 10) $v_mes_final = '0'.$v_mes_final;
    $v_anho_final = $v_anho_filtro;
}

$v_finicio = $v_anho_filtro.'-'.$v_mes_filtro.'-01';
$v_ffinal = $v_anho_final.'-'.$v_mes_final.'-01';
//$v_arr_saldos_ini = $obj_cuentas->get_saldos_ini($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$v_mes_saldoini,$v_anho_saldoini);
//======== SALDO INICIAL
if (isset($_GET['cuenta_id'])){ 
    $v_arr_saldos_ini = $obj_cuentas->get_saldo_inicial_cuenta($_GET['cuenta_id'],$v_mes_saldoini,$v_anho_saldoini); $v_cuenta_id = $_GET['cuenta_id'];
} else{ $v_arr_saldos_ini = $obj_cuentas->get_saldo_inicial_cuenta($_POST['cuenta_id'],$v_mes_saldoini,$v_anho_saldoini); $v_cuenta_id = $_POST['cuenta_id'];}

$v_moneda = $v_arr_saldos_ini['moneda'];
// recorrido de movimientos
$v_saldo_contable = 0;
$v_saldo_disponible = 0;
$v_saldo_comprometido = 0;
$v_saldo_invertido = 0;

$v_movimientos_cuenta = $obj_cuentas->get_movimientos_cuenta_fecha($v_cuenta_id,10000,0,$v_finicio,$v_tipo_mov,$v_ffinal);

//====== SALDOS INICIALES
$v_relacion_print = '
    <div class="frmtransaccion">
        <ul>
            <li style="font-size:14px;font-weight: bold;padding: 1px;margin: 1px;">MONEDA:</li>
            <li style="font-size:14px;font-weight: bold;padding: 1px;margin: 1px;">'.$v_arr_saldos_ini['moneda'].'</li>
        </ul>
        <ul style="margin:0px;padding:0px;"><li style="margin:0px 3px;padding:0px;width:180px;">Saldo Contable Inicial:</li><li>'.number_format($v_arr_saldos_ini['saldo_contable'],2,'.',',').'</li></ul>
        <ul><li style="margin:0px 3px;padding:0px;width:180px;">Saldo Comprometido Inicial:</li><li>'.number_format($v_arr_saldos_ini['saldo_comprometido'],2,'.',',').'</li></ul>
        <ul><li style="margin:0px 3px;padding:0px;width:180px;">Saldo Disponible Inicial:</li><li>'.number_format($v_arr_saldos_ini['saldo_disponible'],2,'.',',').'</li></ul>
        <ul><li style="margin:0px 3px;padding:0px;width:180px;">Saldo Invertido Inicial:</li><li>'.number_format($v_arr_saldos_ini['saldo_invertido'],2,'.',',').'</li></ul>';
        
$v_moneda_id = $v_arr_saldos_ini['moneda_id'];
$v_saldo_contable = $v_arr_saldos_ini['saldo_contable'];
$v_saldo_disponible = $v_arr_saldos_ini['saldo_disponible'];
$v_saldo_comprometido = $v_arr_saldos_ini['saldo_comprometido'];
$v_saldo_invertido = $v_arr_saldos_ini['saldo_invertido'];

// pintado de cada movimiento
for ($j=0; $j<count($v_movimientos_cuenta); $j++){
    $v_fmovimiento_time = strtotime($v_movimientos_cuenta[$j]['f_movimiento']);
    $v_fmovimiento_print = date('d-m-Y',$v_fmovimiento_time);
    $v_detalle_movimiento = $v_movimientos_cuenta[$j]['movimiento'].' (OP-ID-'.$v_movimientos_cuenta[$j]['factura_id'].')(PROP-'.$v_movimientos_cuenta[$j]['propuesta_id'].')';
    
    if ($v_movimientos_cuenta[$j]['simbolo'] > 0){
        $v_simbolo = '<span class="icon-plus" style="color:#1F9A8E;font-weight: bold;"></span>';
        $v_saldo_contable = $v_saldo_contable + $v_movimientos_cuenta[$j]['monto'];
        $v_saldo_disponible = $v_saldo_disponible + $v_movimientos_cuenta[$j]['monto'];
        if ($v_movimientos_cuenta[$j]['tipo_movimiento'] == 54 || $v_movimientos_cuenta[$j]['tipo_movimiento'] == 88) $v_saldo_invertido = $v_saldo_invertido - $v_movimientos_cuenta[$j]['monto'];
    } else{
        $v_simbolo = '<span class="icon-minus" style="color:#b30a1f;font-weight: bold;"></span>';

        if ($v_movimientos_cuenta[$j]['tipo_movimiento'] == 25)  // asignacion a propuesta
            $v_saldo_comprometido = $v_saldo_comprometido + $v_movimientos_cuenta[$j]['monto'];
        elseif ($v_movimientos_cuenta[$j]['tipo_movimiento'] == 53){   //transferencia a vendedor
            $v_saldo_invertido = $v_saldo_invertido + $v_movimientos_cuenta[$j]['monto'];
            $v_saldo_contable = $v_saldo_contable - $v_movimientos_cuenta[$j]['monto'];
        } elseif ($v_movimientos_cuenta[$j]['tipo_movimiento'] == 95){   // LIQUIDACION DE SUBASTA DONDE SE TRANSFIERE AL VENDEDOR 
            $v_saldo_comprometido = $v_saldo_comprometido - $v_movimientos_cuenta[$j]['monto'];
            $v_saldo_invertido = $v_saldo_invertido + $v_movimientos_cuenta[$j]['monto'];
            $v_saldo_contable = $v_saldo_contable - $v_movimientos_cuenta[$j]['monto'];
        } else{
            $v_saldo_contable = $v_saldo_contable - $v_movimientos_cuenta[$j]['monto'];
        }

        if ($v_movimientos_cuenta[$j]['tipo_movimiento'] != 95) $v_saldo_disponible = $v_saldo_disponible - $v_movimientos_cuenta[$j]['monto'];
    }
        
    if ($j == 0){
        $v_relacion_print .= '
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">NRO MOV</th>
                    <th scope="col">FECHA</th>
                    <th scope="col">MONTO</th>
                    <th scope="col">DETALLE</th>
                    <th scope="col">ING/SAL</th>
                </tr></thead>
                <tbody>';
    }

    $v_relacion_print .= '
                    <tr>
                        <td data-label="NRO MOV">'.$v_movimientos_cuenta[$j]['movimiento_id'].'</td>
                        <td data-label="FECHA">'.$v_fmovimiento_print.'</td>
                        <td data-label="MONTO">'.number_format($v_movimientos_cuenta[$j]['monto'],2,'.',',').'</td>
                        <td data-label="DETALLE">'.$v_detalle_movimiento.'</td>
                        <td data-label="ING/SAL">'.$v_simbolo.'</td>
                    </tr>';
}   // final del for de movimientos

$v_relacion_print .= '
                </tbody>            
            </table>
        </ul>';

if (count($v_movimientos_cuenta) <= 0){
    $v_relacion_print .= '
        <ul style="overflow:hidden;list-style:none;">
            <p style="font-weight: bold; font-size:14px;">No tiene movimientos en el periodo seleccionado</p>
        </ul>';
}

$v_relacion_print .= '
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:130px;">Saldo Contable Final:</li><li>'.number_format($v_saldo_contable,2,'.',',').'</li></ul>
        <ul><li style="margin:0px 3px;padding:0px;width:130px;">Saldo Comprometido Final:</li><li>'.number_format($v_saldo_comprometido,2,'.',',').'</li></ul>
        <ul><li style="margin:0px 3px;padding:0px;width:130px;">Saldo Disponible Final:</li><li>'.number_format($v_saldo_disponible,2,'.',',').'</li></ul>
        <ul><li style="margin:0px 3px;padding:0px;width:130px;">Saldo Invertido Final:</li><li>'.number_format($v_saldo_invertido,2,'.',',').'</li></ul>
    </div>';

// armado de los filtros
$v_filtros_print = '<li>Periodo</li>
                    <li>
                        <select name="periodo">';
for ($z=0; $z<count($v_arr_meses); $z++){
    if ($v_arr_meses[$z]['mes'] == $v_mes_filtro) $v_selected = 'selected'; else $v_selected = '';
    $v_filtros_print .= '   <option value="'.$v_arr_meses[$z]['mes'].'" '.$v_selected.'>'.$v_arr_meses[$z]['mes_nom'].'</option>';
}

$v_filtros_print .= '
                        </select>
                    </li>
                    <li>
                        <select name="anho">';
$v_anho_indice = $v_anho_inicial;
while ($v_anho_indice <= $v_anho_hoy){
    if ($v_anho_indice == $v_anho_filtro) $v_selected = 'selected'; else $v_selected = '';
    $v_filtros_print .= '   <option value="'.$v_anho_indice.'" '.$v_selected.'>'.$v_anho_indice.'</option>';
    $v_anho_indice++;
}

$v_filtros_print .= '   </select>
                    </li>
                    <li><input type="checkbox" name="tipomov"> Solo movimientos bancarios</li>';
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'cuentas/estado_cuenta.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Estado de cuenta
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="estado_cuenta_inversionista.php">
            <input type="hidden" name="cuenta_id" value="<?=$v_cuenta_id?>">
            <input type="hidden" name="moneda" value="<?=$v_moneda?>">
        <ul>
            <?php echo $v_filtros_print;?>
            <button type="button" class="btn btn-primary" onclick="filtrar()" style="background-color:var(--color-azulv2);"><span class="icon-filter"></span> Filtrar</button>
            <button type="button" class="btn btn-primary" onclick="retornar()" style="background-color:var(--color-amarillo);"><i class="fa-solid fa-backward"></i> Retornar</button>
        </ul>
        </form>
    </div>
    <!-- listado -->
    <div class="frmtransaccion">
        <?php echo $v_relacion_print;?>
    </div>
    <!------ END CUERPO VARIABLE ------>

    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }

        function retornar(){
            location.href = 'estado_cuenta.php';
        }
    </script>
</BODY>
</HTML>