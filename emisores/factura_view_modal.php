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
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'REGFACT';
    require("../lib/valida-acceso.php");
?>
    
</HEAD>
<?php
//##################################################
//################ LOGICA NO VISIBLE 
$v_factura_id = $_GET['id'];
date_default_timezone_set($_SESSION['user']['zona_horaria']);

// LA FACTURA ESTA REVISADA ESTADO 21
$vobj_factura = new factura;
$vobj_maestros = new maestros;

$varr_factura = $vobj_factura->get_datos_factura($v_factura_id);
$varr_emisor = $vobj_maestros->get_datos_emisor($_SESSION['user']['empresaid']);
$varr_parametros = $vobj_maestros->get_parametros();

if ($varr_factura['estadofinanciamiento'] == 18){   // EN SUBASTA
    $colorregistro = 'var(--color-gris-oscuro);';
    $colorevaluacion = 'var(--color-gris-oscuro);';
    $colorsubasta = 'var(--color-verde);';
    $colorfinancia = 'var(--color-gris-oscuro);';
    $bold_registro = '';
    $bold_evaluacion = '';
    $bold_subasta = 'font-weight: bold;';
    $bold_financia = '';
    $v_aprox = '(Aprox)';
}else{  // EN FINANCIAMIENTO
    $colorregistro = 'var(--color-gris-oscuro);';
    $colorevaluacion = 'var(--color-gris-oscuro);';
    $colorsubasta = 'var(--color-gris-oscuro);';
    $colorfinancia = 'var(--color-verde);';
    $bold_registro = '';
    $bold_evaluacion = '';
    $bold_subasta = '';
    $bold_financia = 'font-weight: bold;';
    $v_aprox = '';
}

$readonly = '';
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm_factura_modal' method='post' id='frm_factura_modal' enctype="multipart/form-data">
        <input type="hidden" name="factura_id" value="<?=$v_factura_id?>">

    <!--========== LEYENDA DE ESTADO DE LA FACTURA ==========-->
    <div style="overflow:hidden;font-size: 10px;width:100%;">
        <ul style="overflow:hidden;list-style:none;margin:3px;padding-left:10px;padding-top:3px;">
<?php
            echo '
            <li style="float:left;display: block;font-size:12px;color:'.$colorregistro.'margin:2px;padding:3px;'.$bold_registro.'">
                <span class="icon-pencil"></span> Registro<span class="icon-arrow-right" style="margin-left:10px;"></span>
            </li>
            <li style="float:left;display: block;font-size:12px;color:'.$colorevaluacion.'margin:2px;padding:3px;'.$bold_evaluacion.'">
                <span class="icon-eye"></span> Evaluaci&oacute;n<span class="icon-arrow-right" style="margin-left:10px;"></span>
            </li>
            <li style="float:left;display: block;font-size:12px;color:'.$colorsubasta.'margin:2px;padding:3px;'.$bold_subasta.'">
                <span class="icon-hour-glass"></span> Subasta<span class="icon-arrow-right" style="margin-left:10px;"></span>
            </li>
            <li style="float:left;display: block;font-size:12px;color:'.$colorfinancia.'margin:2px;padding:3px;'.$bold_financia.'">
            <span class="icon-coin-dollar" style="margin-right:10px;"></span> Financiamiento
            </li>';
?>
        </ul>
    </div>
        
    <!--============== ZONA PRINCIPAL DEL FORMULARIO ===============-->
    <div id="principal" style="padding-left: 10px;height: 95%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="numeroemisor">ID EMISOR:</label>
                    <input type="text" name="numeroemisor" id="numeroemisor" class="formulario_control" value="<?=$varr_emisor['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="emisor">EMISOR:</label>
                    <input type="text" name="emisor" id="emisor" class="formulario_control" value="<?=$varr_emisor['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrofactura">FACTURA ID:</label>
                    <input type="text" name="factura_id" id="factura_id" class="formulario_control" value="<?=$v_factura_id?>" style="font-size: 14;font-weight: bold;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrofactura">NRO FACTURA:</label>
                    <input type="text" name="nrofactura" id="nrofactura" class="formulario_control" value="<?=$varr_factura['factura']?>" readonly>
                </div>
<?php
    $varr_moneda = $vobj_maestros->get_tipos('MONEDA');

    for ($i=0; $i<count($varr_moneda); $i++){
        if ($varr_factura['monedaid'] == $varr_moneda[$i]['id']) $v_moneda_simbolo = $varr_moneda[$i]['dato1'];
    }

    $vf_emision = date('d-m-Y',strtotime($varr_factura['femision']));
    $vf_vencimiento = date('d-m-Y',strtotime($varr_factura['fvencimiento']));
?>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="monedaid">MONEDA:</label>
                    <input type="text" name="monedaid" id="monedaid" class="formulario_control" value="<?=$v_moneda_simbolo?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="numerocliente">RNC:</label>
                    <input type="text" name="numerocliente" id="numerocliente" class="formulario_control" value="<?=$varr_factura['identificacion']?>" <?=$readonly?>>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="cliente">CLIENTE:</label>
                    <input type="text" name="cliente" id="cliente" class="formulario_control" value="<?=$varr_factura['cliente']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="femision">FECHA EMISION:</label>
                    <input type='text' name='femision' id='femision' value='<?=$vf_emision?>' class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="fvencimiento">FECHA VTO:</label>
                    <input type='text' name='fvencimiento' id='fvencimiento' value='<?=$vf_vencimiento?>' class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="pdf">PDF:</label>
                    <span style="margin-right:10px;font-size:20;"><a href="<?=varr_factura['facturapath']?>" target="_blank"><i class="fa-solid fa-file-pdf"></i></a></span>
                </div>
            </div>

<?php
    $v_adelanto = number_format($varr_factura['total'] * $varr_parametros['% FINANCIA']['valornum'],2,'.',',');

    $v_porc_adelanto = $vobj_maestros->get_porc_adelanto_emisor($_SESSION['user']['empresaid'],$varr_factura['clienteid']);
    $v_comi_fact_emi = $vobj_maestros->get_comision_fact_emisor($_SESSION['user']['empresaid'],$varr_factura['clienteid']);

    $v_dt_femision = new DateTime($varr_factura['femision']);
    $v_dt_fvencimiento = new DateTime($varr_factura['fvencimiento']);
    $v_diff = $v_dt_femision->diff($v_dt_fvencimiento);
    $v_dias = $v_diff->days;

    if ($varr_factura['monedaid'] == 20) $v_tarifa_registro = $varr_parametros['TARIFA REGISTRO INSTRUMENTO']['valornum'];
    else $v_tarifa_registro = $varr_parametros['TARIFA REG INST DOL']['valornum'];

    $v_adelanto_upd = $varr_factura['total'] * $v_porc_adelanto;
    $v_comi_fact_upd = $v_tarifa_registro + ($v_comi_fact_emi * $v_adelanto_upd);
    $v_ganancia_upd = $varr_parametros['TED PROMEDIO INVERSOR']['valornum'] * $v_dias * $v_adelanto_upd;
    
    $v_remanente_math = $varr_factura['total'] - $v_adelanto_upd - $v_comi_fact_upd - $v_ganancia_upd;
    $v_remanente = number_format($v_remanente_math, 2, '.', ',');

    $varr_tipofin = $vobj_maestros->get_tipos('TFINANCIAMIENTO');

    for ($i=0; $i<count($varr_tipofin); $i++){
        if ($varr_factura['tipofinanciamiento'] == $varr_tipofin[$i]['id']) $v_tipo_finan = $varr_tipofin[$i]['nombre'];
    }
?>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="total">IMPORTE FACTURA:</label>
                    <input type="text" name="total" class="formulario_control" style="text-align:right;" value="<?=number_format($varr_factura['total'],2,'.',',')?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="adelanto">ADELANTO: <?echo $v_aprox;?></label>
                    <input type="text" name="adelanto" class="formulario_control" style="text-align:right;" value="<?=$v_adelanto?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="remanente">REMANENTE: (Aprox)</label>
                    <input type="text" name="remanente" class="formulario_control" style="text-align:right;" value="<?=$v_remanente?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="tipofinanciamiento">TIPO FINAN:</label>
                    <input type="text" name="tipofinanciamiento" class="formulario_control" value="<?=$v_tipo_finan?>" readonly>
                </div>

<?php
    if ($varr_factura['condescuentomaximo'] == 1){
?>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="maximo">MAXIMO DCTO (%):</label>
                    <input type="text" name="maximo" class="formulario_control" style="text-align:right;" value="<?=$varr_factura['descuentomaximo']?>" readonly>
                </div>
<?php
    }
?>
            </div>
            
        </div>  <!-- CONTENEDOR  FORMULARIO -->

        <!-- ========== PROPUESTAS RECIBIDAS =============== -->
<?php
    $vobj_subasta = new subasta;

    $varr_finan = $vobj_factura->get_datos_financiamiento($v_factura_id);
    $varr_propuestas = $vobj_subasta->get_subasta_posiciones($varr_finan['subasta_id']);
?>

        <div style="overflow:hidden;font-size: 10px;width:100%;">
            <ul style="overflow:hidden;list-style:none;margin:3px;padding-left:10px;padding-top:3px;">
                <li style="float:left;display: block;font-size:14px;color: var(--color-azul-oscuro);margin:2px;padding:3px;font-weight: bold;">
                    <i class="fa-solid fa-list"></i> PROPUESTAS RECIBIDAS
                </li>
            </ul>

            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">MONTO</th>
                        <th scope="col">PORCENTAJE</th>
                        <th scope="col">% INTERES ANUAL</th>
                    </tr>
                </thead>
                <tbody>
<?php
    for ($i=0; $i<count($varr_propuestas); $i++){
        $porcentaje = number_format(100 * $varr_propuestas[$i]['posicion_porc'],0,'.',',');
        $tia = number_format(100 * $varr_propuestas[$i]['tia'],2,'.',',');
        $monto = number_format($varr_propuestas[$i]['posicion'],2,'.',',');

        echo '  <tr>
                    <td data-label="ID">'.$varr_propuestas[$i]['propuestaid'].'</td>
                    <td data-label="MONTO">'.$monto.'</td>
                    <td data-label="PORCENTAJE">'.$porcentaje.' %</td>
                    <td data-label="% INTERES ANUAL">'.$tia.' %</td>
                </tr>';
    }
?>
                </tbody>
            </table>
        </div>

    </div>  <!-- CONTENEDOR PRINCIPAL -->
    
    </form>
    <!------ END CUERPO VARIABLE ------>
    
</BODY>
</HTML>