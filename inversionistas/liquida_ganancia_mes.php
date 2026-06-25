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
//================LOGICA
date_default_timezone_set($_SESSION['user']['zona_horaria']);

if (isset($_POST['mes'])){
    $v_mes = $_POST['mes'];
    $v_anno = $_POST['anno'];
} else {
    $v_mes = date('n');
    $v_anno = date('Y');
}

// FECHAS
if ($v_mes < 10) $v_mes_format = '0'.$v_mes;
else $v_mes_format = $v_mes;

$v_finicio = $v_anno.'-'.$v_mes_format.'-01';

if ($v_mes < 12){
    $v_mes_fin = $v_mes + 1;
    $v_anno_fin = $v_anno;

    if ($v_mes_fin < 10) $v_mesfin_format = '0'.$v_mes_fin;
    else $v_mesfin_format = $v_mes_fin;
} else {
    $v_mesfin_format = '01';
    $v_anno_fin++;
}

$v_ffin = $v_anno_fin.'-'.$v_mesfin_format.'-01';

// OBTENCIO DEL LISTADO DE GANANCIAS DEL MES
$vobj_inv = new inversiones;

//FILTROS
$v_filtros = '';

if ($_SESSION['user']['empresaid'] > 0){
    $v_filtros .= 'propuestas.empresaid = '.$_SESSION['user']['empresaid'];
} else {
    $v_filtros .= 'propuestas.usuarioid = '.$_SESSION['user']['usuarioid'];
}

$v_filtros .= " and financiamiento.fpago_efectivo >= '".$v_finicio."' and financiamiento.fpago_efectivo < '".$v_ffin."'";

// ORDEN
$v_order = 'financiamiento.fpago_efectivo';

$varr_liquidacion = $vobj_inv->get_liquidacion_mes('SELECT', $v_filtros, $v_order);

// ARREGLO DE MESES Y ANHOS
$varr_meses = array();

$varr_meses[0] = array('id' => 1,   'nombre' => 'ENERO');
$varr_meses[1] = array('id' => 2,   'nombre' => 'FEBRERO');
$varr_meses[2] = array('id' => 3,   'nombre' => 'MARZO');
$varr_meses[3] = array('id' => 4,   'nombre' => 'ABRIL');
$varr_meses[4] = array('id' => 5,   'nombre' => 'MAYO');
$varr_meses[5] = array('id' => 6,   'nombre' => 'JUNIO');
$varr_meses[6] = array('id' => 7,   'nombre' => 'JULIO');
$varr_meses[7] = array('id' => 8,   'nombre' => 'AGOSTO');
$varr_meses[8] = array('id' => 9,   'nombre' => 'SEPTIEMBRE');
$varr_meses[9] = array('id' => 10,  'nombre' => 'OCTUBRE');
$varr_meses[10] = array('id' => 11,  'nombre' => 'NOVIEMBRE');
$varr_meses[11] = array('id' => 12,  'nombre' => 'DICIEMBRE');

$v_anno_actual = date('Y');
$v_anno_inicio = 2026;

$varr_annos = array();
$j = 0;

for ($i = $v_anno_inicio; $i <= $v_anno_actual; $i++){
    $varr_annos[$j] = array('id' => $i);
    $j++;
}
//==============================================================
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'inversionistas/liquida_ganancia_mes.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>

    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:0px auto;">
        Liquidación mensual de ganancias
    </div>

    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="liquida_ganancia_mes.php">
            <input type="hidden" name="q_registros" id="q_registros" value="<?=count($varr_liquidacion)?>">
            <input type="hidden" name="f_inicio" id="f_inicio" value="<?=$v_finicio?>">
            <input type="hidden" name="f_fin" id="f_fin" value="<?=$v_ffin?>">

        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;margin-top:5px;">PERIODO DE LIQUIDACION:</li>
            <li>
                <select name="mes" id="mes" class="formulario_control">

<?php
    for ($i=0; $i<count($varr_meses); $i++){
        if ($varr_meses[$i]['id'] == $v_mes)
            echo '  <option value="'.$varr_meses[$i]['id'].'" selected>'.$varr_meses[$i]['nombre'].'</option>';
        else
            echo '  <option value="'.$varr_meses[$i]['id'].'">'.$varr_meses[$i]['nombre'].'</option>';
    }
?>

                </select>
            </li>
            <li>
                <select name="anno" id="anno" class="formulario_control">

<?php
    for ($i=0; $i<count($varr_annos); $i++){
        if ($varr_annos[$i]['id'] == $v_anno)
            echo '  <option value="'.$varr_annos[$i]['id'].'" selected>'.$varr_annos[$i]['id'].'</option>';
        else
            echo '  <option value="'.$varr_annos[$i]['id'].'">'.$varr_annos[$i]['id'].'</option>';
    }
?>

                </select>
            </li>
            <li>
                <button type="button" class="btn btn-primary" onclick="filtrar()" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
                <i class="fa-solid fa-filter"></i> Filtrar</button>
            </li>
            <li>
                <button type="button" class="btn btn-primary" onclick="exportar()" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF</button>
            </li>
        </ul>
        </form>
    </div>

    <div class="frmtransaccion">
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col">ID OP</th>
                        <th scope="col">INSTRUMENTO</th>
                        <th scope="col">CLIENTE</th>
                        <th scope="col">FECHA INVER</th>
                        <th scope="col">FECHA GANANCIA</th>
                        <th scope="col">MONEDA</th>
                        <th scope="col">%TASA ANUAL</th>
                        <th scope="col">DIAS INVER</th>
                        <th scope="col">MONTO INVER</th>
                        <th scope="col">MONTO GANANCIA</th>
                        <th scope="col">COMISION FACTUREATE</th>
                        <th scope="col"><abbr title="% sobre la ganancia obtenida por el inversionista">%TASA COMISION</abbr></th>
                    </tr>
                </thead>
                <tbody>

<?php
    if (count($varr_liquidacion) <= 0)
        echo '      <tr><td colspan="12">No se encontraron registros en el periodo seleccionado</td></tr>';
    else {
        $v_monto_inversion_total = 0;
        $v_monto_ganancia_total = 0;
        $v_monto_comision_total = 0;

        for ($i=0; $i<count($varr_liquidacion); $i++){
            $v_finversion = date('d-m-Y', strtotime($varr_liquidacion[$i]['f_inversion']));
            $v_fganancia = date('d-m-Y', strtotime($varr_liquidacion[$i]['f_pago']));
            $v_tia = $varr_liquidacion[$i]['tia'] * 100;
            $v_tasa_comision = $varr_liquidacion[$i]['tasa_comision'] * 100;

            // TOTALES
            $v_monto_inversion_total = $v_monto_inversion_total + $varr_liquidacion[$i]['monto_inversion'];
            $v_monto_ganancia_total = $v_monto_ganancia_total + $varr_liquidacion[$i]['monto_ganancia'];
            $v_monto_comision_total = $v_monto_comision_total + $varr_liquidacion[$i]['monto_comision'];

            echo '  <tr>
                        <td data-label="ID OP">'.$varr_liquidacion[$i]['factura_id'].'</td>
                        <td data-label="INSTRUMENTO">'.$varr_liquidacion[$i]['factura_nro'].'</td>
                        <td data-label="CLIENTE">'.$varr_liquidacion[$i]['cliente_nombre'].'</td>
                        <td data-label="FECHA INVER">'.$v_finversion.'</td>
                        <td data-label="FECHA GANANCIA">'.$v_fganancia.'</td>
                        <td data-label="MONEDA">'.$varr_liquidacion[$i]['moneda'].'</td>
                        <td data-label="%TASA ANUAL">'.number_format($v_tia, 2, '.', ',').' %</td>
                        <td data-label="DIAS INVER">'.$varr_liquidacion[$i]['dias_inversion'].'</td>
                        <td data-label="MONTO INVER" style="text-align:right;">'.number_format($varr_liquidacion[$i]['monto_inversion'], 2, '.', ',').'</td>
                        <td data-label="MONTO GANANCIA" style="text-align:right;">'.number_format($varr_liquidacion[$i]['monto_ganancia'], 2, '.', ',').'</td>
                        <td data-label="COMISION FACTUREATE" style="text-align:right;">'.number_format($varr_liquidacion[$i]['monto_comision'], 2, '.', ',').'</td>
                        <td data-label="%TASA COMISION">'.number_format($v_tasa_comision, 2, '.', ',').' %</td>
                    </tr>';
        }

        $v_ganancia_neta = $v_monto_ganancia_total - $v_monto_comision_total;

        echo '      <tr>
                        <td colspan="8" style="text-align:right;font-weight:bold;">TOTALES:</td>
                        <td data-label="MONTO INVER" style="text-align:right;font-weight:bold;">'.number_format($v_monto_inversion_total,2,'.',',').'</td>
                        <td data-label="MONTO GANANCIA" style="text-align:right;font-weight:bold;">'.number_format($v_monto_ganancia_total,2,'.',',').'</td>
                        <td data-label="COMISION FACTUREATE" style="text-align:right;font-weight:bold;">'.number_format($v_monto_comision_total,2,'.',',').'</td>
                        <td data-label="%TASA COMISION"></td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align:right;font-weight:bold;">GANANCIA NETA:</td>
                        <td style="text-align:right;font-weight:bold;">'.number_format($v_ganancia_neta,2,'.',',').'</td>
                        <td colspan="3"></td>
                    </tr>';
    }
?>

                </tbody>
            </table>
        </ul>
    </div>

    <!--================================================================
    FUNCIONES JS -->

    <script>
        function filtrar(){
            document.frm.submit();
        }

        function exportar(){
            var mes = document.getElementById('mes').value;
            var anno = document.getElementById('anno').value;
            var registros = document.getElementById('q_registros').value;
            var f_inicio = document.getElementById('f_inicio').value;
            var f_fin = document.getElementById('f_fin').value;

            if (registros > 0){
                let formData = new FormData()

                formData.append('accion', 'exportar');
                formData.append('mes', mes);
                formData.append('anno', anno);
                formData.append('f_inicio', f_inicio);
                formData.append('f_fin', f_fin);

                $.ajax({
                    url:"exportar_liquidacion.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    if (rpta == 1){
                        alert('El reporte fue enviado a su correo');
                    }
                });
            } else alert('No existen registros para exportar');
        }
    </script>
</BODY>
</HTML>