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
    $acceso = 'REGFACT';
    require("../lib/valida-acceso.php");
?>

</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$tipo = $_GET['tipo'];
date_default_timezone_set($_SESSION['user']['zona_horaria']);

$objmaestros = new maestros;
$objfactura = new factura;

$arremisor = $objmaestros->get_datos_emisor($_SESSION['user']['empresaid']);
$arrparametros = $objmaestros->get_parametros();
$hoy = date('Y-m-d');

if ($tipo == 'new'){
    $fnula = strtotime($hoy); $ffnula = date('Y-m-d', $fnula);

    $v_femision_esp = date('d-m-Y',strtotime(date('Y-m-d')));
    $v_femision_eng = date('Y/m/d');

    $v_fvencimiento_esp = date('d-m-Y',strtotime(date('Y-m-d')));

    $femision = $ffnula; $fvencimiento = $ffnula;

    $subtotal = 0;
    $subtotal_view = '';

    $anticipos = 0;
    $anticipos_view = '';

    $descuentos = 0;
    $descuentos_view = '';

    $impuestoventa = 0;
    $impuestoventa_view = '';

    $otroscargos = 0;
    $otroscargos_view = '';

    $otrostributos = 0;
    $otrostributos_view = '';

    $nrofactura = ''; $numerocliente = ''; $cliente = ''; $nombrepdf = ''; $xmlpath = ''; $pdfpath = ''; $readonly = ''; $v_simbolo = ''; $v_condesc_maximo = '';
    $monedaid = 0;  $valorventa = 0;  $total = 0; $tfinid = 0; $v_desc_maximo = 0;
    $total_label = 0;
    $nombrexml = '[SIN XML]';
    $enviar = 'off'; $anular = 'off';
    $estadoid = 11; $estadofinanciamientoid = 14;
    $v_desc_maximo_readonly = 'readonly';
    $v_desc_maximo_bgc = '#aaaaaa;';
    $v_comi_fact_emi = $arrparametros['COMISION']['valornum'];
    $v_porc_adelanto = $arrparametros['% FINANCIA']['valornum'];
    $facturaid = 0;
} elseif ($tipo == 'upd' || $tipo == 'view'){
    $facturaid = $_GET['id'];
    $arrfactura = $objfactura->get_datos_factura($facturaid);
    $nrofactura = $arrfactura['factura'];
    
    $femision = $arrfactura['femision'];
    $v_femision_esp = date('d-m-Y',strtotime($femision));
    $v_femision_eng = $femision;
    
    $fvencimiento = $arrfactura['fvencimiento'];
    $v_fvencimiento_esp = date('d-m-Y', strtotime($fvencimiento));
    $v_fvencimiento_eng = $fvencimiento;

    $numerocliente = $arrfactura['identificacion'];
    $cliente = $arrfactura['cliente'];
    $monedaid = $arrfactura['monedaid'];
    
    $subtotal = $arrfactura['subtotal'];
    $subtotal_view = number_format($subtotal, 2,'.',',');

    $anticipos = $arrfactura['anticipos'];
    $anticipos_view = number_format($anticipos, 2, '.', ',');

    $descuentos = $arrfactura['descuentos'];
    $descuentos_view = number_format($descuentos, 2, '.', ',');

    $valorventa = $arrfactura['valorventa'];

    $impuestoventa = $arrfactura['impuestoventa'];
    $impuestoventa_view = number_format($impuestoventa, 2, '.', ',');
    
    $otroscargos = $arrfactura['otroscargos'];
    $otroscargos_view = number_format($otroscargos, 2, '.', ',');

    $otrostributos = $arrfactura['otrostributos'];
    $otrostributos_view = number_format($otrostributos, 2, '.', ',');

    $total = $arrfactura['total'];
    $total_label = number_format($total, 2,'.', ',');
    $nombrexml = '[ARCHIVO XML]';
    $tfinid = $arrfactura['tipofinanciamiento'];
    $nombrepdf = '<i class="fa-solid fa-file-pdf"></i>';
    $xmlpath = $arrfactura['xmlpath'];
    $pdfpath = $arrfactura['facturapath'];
    $clienteid = $arrfactura['clienteid'];
    $anular = 'on';
    $estadoid = $arrfactura['estado'];
    $estadofinanciamientoid = $arrfactura['estadofinanciamiento'];

    if ($arrfactura['condescuentomaximo'] == 1){
        $v_condesc_maximo = 'checked';
        $v_desc_maximo = $arrfactura['descuentomaximo'];
        $v_desc_maximo_readonly = '';
        $v_desc_maximo_bgc = '#ffffff;';
    } else {
        $v_condesc_maximo = '';
        $v_desc_maximo = 0;
        $v_desc_maximo_readonly = 'readonly';
        $v_desc_maximo_bgc = 'var(--color-gris-claro);';
    }

    //======== CALCULO DE ESTIMADOS PARA EL EMISOR
    $v_dt_femision = new DateTime($femision);
    $v_dt_fvencimiento = new DateTime($fvencimiento);
    $v_diff = $v_dt_femision->diff($v_dt_fvencimiento);
    $v_dias = $v_diff->days;

    $v_comi_fact_emi = $objmaestros->get_comision_fact_emisor($_SESSION['user']['empresaid'],$clienteid);
    $v_porc_adelanto = $objmaestros->get_porc_adelanto_emisor($_SESSION['user']['empresaid'],$clienteid);

    $v_inicial = number_format($total * $arrparametros['% FINANCIA']['valornum'],2,'.',',');
    $v_inicial_math = $total * $arrparametros['% FINANCIA']['valornum'];

    if ($monedaid == 20) $v_tarifa_registro = $arrparametros['TARIFA REGISTRO INSTRUMENTO']['valornum'];
    else $v_tarifa_registro = $arrparametros['TARIFA REG INST DOL']['valornum'];

    $v_adelanto_upd = $total * $v_porc_adelanto;
    $v_comi_fact_upd = $v_tarifa_registro + ($v_comi_fact_emi * $v_adelanto_upd);
    $v_ganancia_upd = $arrparametros['TED PROMEDIO INVERSOR']['valornum'] * $v_dias * $v_adelanto_upd;
    
    $v_remanente_math = $total - $v_adelanto_upd - $v_comi_fact_upd - $v_ganancia_upd;
    $v_remanente = number_format($v_remanente_math, 2, '.', ',');
    
    $v_financiado = number_format($v_inicial_math + $v_remanente_math,2,'.',',');
    $v_tasa_dcto_math = (($total - ($v_inicial_math + $v_remanente_math)) / $total) * 100;
    $v_tasa_dcto = number_format($v_tasa_dcto_math,2,'.',',');

    if ($tfinid != 0 && $pdfpath != '') $enviar = 'on';
    else $enviar = 'off';

    if ($tipo == 'upd'){ 
        $readonly = '';
        $disabled = '';
    } else {
        $readonly = 'readonly';
        $disabled = 'disabled';
        $enviar = 'off';
        $anular = 'off';
    }
}
// colores de la leyenda
if ($estadoid == 11 || $estadoid == 17){
    $colorregistro = 'var(--color-verde);';
    $colorevaluacion = 'var(--color-gris-oscuro);';
    $colorsubasta = 'var(--color-gris-oscuro);';
    $colorfinancia = 'var(--color-gris-oscuro);';
    $bold_registro = 'font-weight: bold;';
    $bold_evaluacion = '';
    $bold_subasta = '';
    $bold_financia = '';
} elseif ($estadoid == 12){
    $colorregistro = 'var(--color-gris-oscuro);';
    $colorevaluacion = 'var(--color-verde);';
    $colorsubasta = 'var(--color-gris-oscuro);';
    $colorfinancia = 'var(--color-gris-oscuro);';
    $bold_registro = '';
    $bold_evaluacion = 'font-weight: bold;';
    $bold_subasta = '';
    $bold_financia = '';
} elseif ($estadoid == 13){
    if ($estadofinanciamientoid == 18){
        $colorregistro = 'var(--color-gris-oscuro);';
        $colorevaluacion = 'var(--color-gris-oscuro);';
        $colorsubasta = 'var(--color-verde);';
        $colorfinancia = 'var(--color-gris-oscuro);';
        $bold_registro = '';
        $bold_evaluacion = '';
        $bold_subasta = 'font-weight: bold;';
        $bold_financia = '';
    }else{
        $colorregistro = 'var(--color-gris-oscuro);';
        $colorevaluacion = 'var(--color-gris-oscuro);';
        $colorsubasta = 'var(--color-gris-oscuro);';
        $colorfinancia = 'var(--color-verde);';
        $bold_registro = '';
        $bold_evaluacion = '';
        $bold_subasta = '';
        $bold_financia = 'font-weight: bold;';
    }
}

//==== PARAMETROS DE MINIMOS
$varr_monedas = $objmaestros->get_tipos('MONEDA');
$v_moneda_nac_id = $arrparametros['MONEDA NACIONAL']['valornum'];

for ($i=0; $i<count($varr_monedas); $i++){
    if ($varr_monedas[$i]['id'] == $v_moneda_nac_id){
        $v_monto_minimo_nac = $arrparametros['MONTO MIN FACT DOP']['valornum'];
        $v_moneda_nac = $varr_monedas[$i]['dato1'].' '.number_format($v_monto_minimo_nac,2,'.',',');
    } else {
        $v_monto_minimo_ext = $arrparametros['MONTO MIN FACT DOL']['valornum'];
        $v_moneda_ext = $varr_monedas[$i]['dato1'].' '.number_format($v_monto_minimo_ext,2,'.',',');
    }
}

$v_dias_min = $arrparametros['DIAS MIN ACEPTACION']['valornum'];
$v_dias_min = $v_dias_min.' días';
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm_factura_modal' method='post' id='frm_factura_modal' enctype="multipart/form-data">
        <input type="hidden" name="emisor_id" id="emisor_id" value="<?=$_SESSION['user']['empresaid']?>">
        <input type="hidden" name="porcimpuestoventa" value="<?=$arrparametros['IMPUESTOVENTA']['valornum']?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
        <input type="hidden" name="tipoaccion" value="<?=$tipo?>">
        <input type="hidden" name="facturaid" value="<?=$facturaid?>">
        <input type="hidden" name="porc_dcto" value="<?=$arrparametros['DESCUENTO PROMEDIO']['valornum']?>">
        <input type="hidden" name="tipo_factura" value="59">

        <!-- DATOS PARA EL CALCULO DE COMISIONES Y REMANENTE -->
        <input type="hidden" name="tarifa_registro_nac" id="tarifa_registro_nac" value="<?=$arrparametros['TARIFA REGISTRO INSTRUMENTO']['valornum']?>">
        <input type="hidden" name="tarifa_registro_dol" id="tarifa_registro_dol" value="<?=$arrparametros['TARIFA REG INST DOL']['valornum']?>">
        <input type="hidden" name="tea_param" id="tea_param" value="<?=$arrparametros['TEA PROMEDIO INVERSOR']['valornum']?>">
        <input type="hidden" name="ted_param" id="ted_param" value="<?=$arrparametros['TED PROMEDIO INVERSOR']['valornum']?>">
        <input type="hidden" name="porc_comision" id="porc_comision" value="<?=$v_comi_fact_emi?>">
        <input type="hidden" name="porc_financia" id="porc_financia" value="<?=$v_porc_adelanto?>">

        <input type="hidden" name="numeroclienteold" value="<?=$numerocliente?>">
        <input type="hidden" name="clienteid" value="<?=$clienteid?>">
        <input type="hidden" name="f_hoy" id="f_hoy" value="<?=$hoy?>">

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

        <ul style="overflow:hidden;list-style:none; padding-left:5px; background: var(--color-oro); margin-left: 12px; font-size: 12px; color: #fff;">
            <li style="float:left; display: block; padding-right: 10px;">Monto mínimo <?echo $v_moneda_nac;?></li>
            <li style="float:left; display: block; padding-right: 10px;">Monto mínimo <?echo $v_moneda_ext;?></li>
            <li style="float:left; display: block;">Días mínimo vencimiento <?echo $v_dias_min;?></li>
        </ul>
    </div>

    <!--============== ZONA PRINCIPAL DEL FORMULARIO ===============-->
    <div style="padding-left: 20px;overflow: hidden;">
        <ul style="list-style:none; overflow:hidden; margin: 0px; padding: 0px; font-size: 10px;">
            <li style="color:#b30a1f;font-weight: bold; display: inline-block;">[*] Requisito para regisro de factura</li>
            <li style="color:#b30a1f;font-weight: bold; display: inline-block;">[**] Requisito para envio de factura</li>
        </ul>

    </div>

    <div id="principal" style="padding-left: 10px;height: 95%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="numeroemisor">ID EMISOR:</label>
                    <input type="text" name="numeroemisor" id="numeroemisor" class="formulario_control" value="<?=$arremisor['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="emisor">EMISOR:</label>
                    <input type="text" name="emisor" id="emisor" class="formulario_control" value="<?=$arremisor['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrofactura">FACTURA ID:</label>
                    <input type="text" name="factura_id" id="factura_id" class="formulario_control" value="<?=$facturaid?>" style="font-size: 14;font-weight: bold;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrofactura">NRO FACTURA:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" name="nrofactura" id="nrofactura" class="formulario_control" value="<?=$nrofactura?>" <?=$readonly?>>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="monedaid">MONEDA:<b style="color:#b30a1f;">[*]</b></label>
                    <select class="formulario_control" name="monedaid" id="monedaid" onchange="javascript:simbolo_moneda()" <?=$disabled?>>
<?php 
    $arrmoneda = $objmaestros->get_tipos('MONEDA');
    
    if ($monedaid == 0){
        echo '          <option value="0" selected>Elija Moneda</option>';
        $v_simbolo = '';
    }
                    
    for ($i=0; $i<count($arrmoneda); $i++){
        if ($monedaid == $arrmoneda[$i]['id']){
            echo '      <option value="'.$arrmoneda[$i]['id'].'" selected>'.$arrmoneda[$i]['nombre'].'</option>';
            $v_simbolo = $arrmoneda[$i]['dato1'];
        } else
            echo '      <option value="'.$arrmoneda[$i]['id'].'">'.$arrmoneda[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="numerocliente">NIT:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" name="numerocliente" id="numerocliente" class="formulario_control" value="<?=$numerocliente?>" <?=$readonly?>>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="cliente">CLIENTE:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" name="cliente" id="cliente" class="formulario_control" value="<?=$cliente?>" readonly>
                </div>
                
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="femision">FECHA EMISION:<b style="color:#b30a1f;">[*]</b></label>
                    <div style="display: flex;">
                        <input type="text" id="femision_view" name="femision_view" value="<?= $v_femision_esp ?>" class="formulario_control" style="background:#fff; width:80px;" readonly>
                        <input type='date' name='femision' id='femision' value='<?=$femision?>' class="formulario_control" onchange="javascript:cambia_fecha('EMISION')" <?=$readonly?> style="width: 30px;font-size:18px;color:transparent;cursor:pointer;">
                    </div>
                </div>

                <div class="formulario_grupo_row" style="width: 140px;">
                    <label for="fvencimiento_view">FECHA VTO:<b style="color:#b30a1f;">[*]</b></label>
                    <div style="display: flex;">
                        <input type="text" id="fvencimiento_view" name="fvencimiento_view" value="<?= $v_fvencimiento_esp ?>" class="formulario_control" style="background:#fff; width:80px;" readonly>
                        <input type='date' name='fvencimiento' id='fvencimiento' value='<?=$fvencimiento?>' class="formulario_control" onchange="javascript:cambia_fecha('VENCIMIENTO')" <?=$readonly?> style="width: 30px;font-size:18px;color:transparent;cursor:pointer;">
                    </div>
                </div>
            </div>

<?php
    $subtotalp = number_format($subtotal,2,'.',',');
    $valorventap = number_format($valorventa,2,'.',',');
?>
            
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="subtotal_view">SUBTOTAL VENTA:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" id="subtotal_view" name="subtotal_view" placeholder="0.00" class="formulario_control" style="text-align: right;" value="<?= $subtotal_view ?>" <?=$readonly?> onchange="valida_monto_factura()">
                    <input type="hidden" id="subtotal" name="subtotal" value="<?= $subtotal ?>">
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="anticipos_view">ANTICIPOS:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" id="anticipos_view" name="anticipos_view" placeholder="0.00" class="formulario_control" style="text-align: right;" value="<?= $anticipos_view ?>" <?=$readonly?>>
                    <input type="hidden" id="anticipos" name="anticipos" value="<?= $anticipos ?>">
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="descuentos_view">DESCUENTOS:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" id="descuentos_view" name="descuentos_view" placeholder="0.00" class="formulario_control" style="text-align: right;" value="<?= $descuentos_view ?>" <?=$readonly?>>
                    <input type="hidden" id="descuentos" name="descuentos" value="<?= $descuentos ?>">
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="valorventa_l">VALOR VENTA:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" name="valorventa_l" class="formulario_control" style="text-align:right;" value="<?=$valorventap?>" readonly>
                </div>
            </div>

                    <input type="hidden" name="valorventa" value="<?=$valorventa?>">
                    <input type="hidden" name="impuestoventa" value="<?=$impuestoventa?>">
                    <input type="hidden" name="total" id="total" value="<?=$total?>">

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="impuestoventa_view">IVA:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" id="impuestoventa_view" name="impuestoventa_view" placeholder="0.00" class="formulario_control" style="text-align: right;" value="<?= $impuestoventa_view ?>" <?=$readonly?>>
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="otroscargos_view">OTROS CARGOS:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" id="otroscargos_view" name="otroscargos_view" placeholder="0.00" class="formulario_control" style="text-align: right;" value="<?= $otroscargos_view ?>" <?=$readonly?>>
                    <input type="hidden" id="otroscargos" name="otroscargos" value="<?= $otroscargos ?>">
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="otrostributos_view">OTROS TRIBUTOS:<b style="color:#b30a1f;">[*]</b></label>
                    <input type="text" id="otrostributos_view" name="otrostributos_view" placeholder="0.00" class="formulario_control" style="text-align: right;" value="<?= $otrostributos_view ?>" <?=$readonly?>>
                    <input type="hidden" id="otrostributos" name="otrostributos" value="<?= $otrostributos ?>">
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="total_l">IMPORTE TOTAL:</label>
                    <input type="text" name="total_l" class="formulario_control" style="text-align:right;" value="<?=$total_label?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tipofinanciamiento">TIPO FINANCIAMIENTO:<b style="color:#b30a1f;">[*][*]</b></label>
                    <select name="tipofinanciamiento" id="tipofinanciamiento" class="formulario_control" <?=$disabled?>>
<?php
    $arrtipofin = $objmaestros->get_tipos('TFINANCIAMIENTO');

    if ($tfinid == 0) 
        echo '          <option value="0" selected>Elegir</option>';

    for ($i=0; $i<count($arrtipofin); $i++){
        if ($tfinid == $arrtipofin[$i]['id'])
            echo '      <option value="'.$arrtipofin[$i]['id'].'" selected>'.$arrtipofin[$i]['nombre'].'</option>';
        else
            echo '      <option value="'.$arrtipofin[$i]['id'].'">'.$arrtipofin[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="conmaxdescuento" style="color:#fff;">CHK</label>
                    <input id="conmaxdescuento" type="checkbox" class="formulario_control" name="conmaxdescuento" value="1" <?echo $v_condesc_maximo;?> onchange="javascript:checkdescuento('conmaxdescuento','maxdescuento')" <?=$disabled?>>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="maxdescuento"><abbr title="Es el % maximo que esta dispuesto a aceptar que le descuenten, este valor es calculado siempre que su cliente pague en la fecha de vencimiento">MAXIMO DCTO(%):</abbr></label>
                    <input id="maxdescuento" type="number" name="maxdescuento" class="formulario_control" value="<?=$v_desc_maximo?>" min="2" max="20" <?echo $v_desc_maximo_readonly;?> style="text-align:right;background-color:<?echo $v_desc_maximo_bgc;?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="facturafile">IMG/PDF FACTURA:<b style="color:#b30a1f;">[**]</b></label>
<?php
    if ($pdfpath != '') 
        echo '      <span style="margin-right:10px;font-size:20;"><a href="'.$pdfpath.'" target="_blank">'.$nombrepdf.'</a></span>';
            
    if ($tipo != 'view') 
        echo '      <input name="facturafile" id="facturafile" type="file" class="formulario_control" style="background-color:#fff;">';
    echo '      </div>';

    if ($xmlpath != ''){
?>
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="xmlpath">XML de la factura:</label>
                    <?echo $xmlpath;?>
                    <input type="hidden" name="xmlpath" value="<?=$nombrexml?>">
                </div>
<?php
    }
?>
            </div>

<?php
        if ($estadoid == 17){   // rechazado
            if ($arrfactura['rechazopath'] != '') $rechazopath = '<a href="'.$arrfactura['rechazopath'].'"><i class="fa-solid fa-file-pdf"></i></a>';
            else $rechazopath = '<i class="fa-solid fa-file-pdf"></i>';
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="rechazo">Motivo de Rechazo:<?echo $rechazopath;?></label>
                    <textarea name="rechazo" cols="100" rows="5" class="formulario_control" readonly>'.$arrfactura['motivorechazo'].'</textarea>
                </div>
            </div>
<?php
        }

        $v_dcto_promedio = $arrparametros['DESCUENTO PROMEDIO']['valornum'] * 100;
?>

            <div class="contenedor_formulario_column">
                <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px; color: var(--color-rojo);">CALCULO APROXIMADO DE SU FINANCIAMIENTO:</p>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="adelanto" style="color: var(--color-rojo);">Adelanto Aprox:<abbr title="Es el monto que recibiría al inicio, esta es solo una simulación aproximada, el monto definitivo se conocerá luego de la evaluación de su factura"><i class="fa-solid fa-eye"></i></abbr></label>
                    <input type="text" name="adelanto" id="adelanto" class="formulario_control" value="<?=$v_simbolo.' '.$v_inicial?>" style="text-align:right;" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="plus" style="color: #fff;">Plus</label>
                    <i class="fa-solid fa-plus"></i>
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="remanente" style="color: var(--color-rojo);">Remanente Aprox:<abbr title="Es el monto que recibirá cuando su cliente pague, esta es solo una simulación aproximada, el monto definitivo depende de las condiciones del inversor y luego que su cliente pague"><i class="fa-solid fa-eye"></i></abbr></label>
                    <input type="text" name="remanente" id="remanente" class="formulario_control" value="<?=$v_simbolo.' '.$v_remanente?>" style="text-align:right;" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="plus" style="color: #fff;">Plus</label>
                    <i class="fa-solid fa-equals"></i>
                </div>

                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="totalrecibido" style="color: var(--color-rojo);">Total aproximado:<abbr title="Es el monto total que recibirá, que es la suma de los dos montos anteriores (Monto Inicial + Remanente)"><i class="fa-solid fa-eye"></i></abbr></label>
                    <input type="text" name="totalrecibido" id="totalrecibido" class="formulario_control" value="<?=$v_simbolo.' '.$v_financiado?>" style="text-align:right;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="tasa_descuento" style="color: var(--color-rojo);">Tasa de Dcto(%):<abbr title="Es la tasa de descuento que obtendra = monto total que recibira / monto de la factura"><i class="fa-solid fa-eye"></i></abbr></label>
                    <input type="text" name="tasa_descuento" id="tasa_descuento" class="formulario_control" value="<?=$v_tasa_dcto?>" style="text-align:right;" readonly>
                </div>
            </div>

            <!--================== BOTONERA ====================-->
            <div class="contenedor_formulario_column" style="padding-top: 5px;">
            
<?php
    if ($tipo != 'view'){
        echo '      <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-left:5px;" onclick="grabarFactura()" id="btn_grabar">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Factura</button>';

        if ($enviar != 'on')
            echo '  
                    <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-left:5px;" onclick="grabarEnviarFactura()" id="btn_enviar">
                    <i class="fa-solid fa-money-check-dollar"></i> Solicitar Financiamiento</button>';
    }

    if ($enviar == 'on') 
        echo '      <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-left:5px;" onclick="enviar()" id="btn_enviar">
                    <i class="fa-solid fa-money-check-dollar"></i> Solicitar Financiamiento</button>';
            
    if ($anular == 'on') 
        echo '      <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;margin-left:5px;" onclick="anular()" id="btn_anular">
                    <i class="fa-solid fa-delete-left"></i> Anular Factura</button>';
            
    echo '          <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;margin-left:10px;" onclick="volver()" id="btn_volver">
                    <i class="fa-solid fa-rotate-left"></i> Volver</button>';
?>
                
            </div>
            
        </div>  <!-- CONTENEDOR  FORMULARIO -->
    </div>  <!-- CONTENEDOR PRINCIPAL -->
    
    </form>
    <!------ END CUERPO VARIABLE ------>

    <!-- div para el spinner de loadgin -->
    <div id="loadingModal" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-title">Procesando </div>
            <div class="loading-subtitle">............................</div>
        </div>
    </div>
    <!-- fin del spinner loading -->

    <!--====================== AVISO AL EMISOR =====================-->
<?php
    if ($arrparametros['RECUERDA SOLICITUD']['valornum'] > 0 && $estadoid == 11 && $tipo == 'upd'){
        echo '  <script>
                    alert("Recuerda que debes de presionar el boton SOLICITAR FINANCIAMIENTO para recibir adelanto de tu factura");
                </script>';
    }
?>
    <script>
        
        function volver(){
            location.href = 'panel_emisor.php';
        }

        function grabarEnviarFactura(){
            var btn_grabar = document.getElementById('btn_grabar');
            var btn_enviar = document.getElementById('btn_enviar');

            var factura_id = document.frm_factura_modal.facturaid.value;

            if (factura_id > 0) enviar();
            else {
                var v_valida_grabar = validaGrabar();

                if (v_valida_grabar == 1){
                    var v_ok = 1;
                    var v_tipo_finan = $('#tipofinanciamiento').val();
                    var v_factura_file = $('#facturafile').val();

                    if (v_tipo_finan == 0){
                        alert('Debe ingresar un tipo de financiamiento');
                        v_ok = 0;
                    }

                    if (v_factura_file == '' && v_ok == 1){
                        alert('Debe ingresar el PDF de la factuar');
                        v_ok = 0;
                    }

                    if (v_ok == 1){
                        btn_grabar.disabled = true;
                        btn_enviar.disabled = true;
                        document.frm_factura_modal.tipoaccion.value = 'grabaenvia';
                        /*document.frm_factura_modal.submit();*/

                        var formData = new FormData(document.getElementById("frm_factura_modal"));

                        //==== llamada al spinner
                        mostrarLoading();

                        $.ajax({
                            url:"registro_factura_fisica_proc.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html"
                        })
                        .done(function(rpta){
                            //==== ocultar el spinner
                            ocultarLoading();

                            if (rpta == 1){
                                alert('Su factura ha sido enviada para la evaluación de nuestros analistas, inmediatamente supere la evaluación le enviaremos un correo de confirmación');
                                refresh_page();
                            } else {
                                if (rpta == -1){
                                    alert('Ocurrio un error al guardar la Factura');
                                    refresh_page();
                                } else{
                                    if (rpta == -2){
                                        alert('Ocurrio un error al enviar la Factura, su factura fue rechazada');
                                        refresh_page();
                                    } else{
                                        if (rpta == -3){
                                            alert('Datos incorrectos');
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            }
        }

        function validaGrabar(){
            var nrofactura,femision,fvencimiento;
            var numerocliente,cliente,monedaid;
            var subtotal,tipofinanciamiento;
            var fhoy = new Date();
            
            nrofactura = document.frm_factura_modal.nrofactura.value;
            femision = document.frm_factura_modal.femision.value;
            fvencimiento = document.frm_factura_modal.fvencimiento.value;
            numerocliente = document.frm_factura_modal.numerocliente.value;
            cliente = document.frm_factura_modal.cliente.value;
            monedaid = document.frm_factura_modal.monedaid.value;
            subtotal = Number(document.frm_factura_modal.subtotal.value);
            tipofinanciamiento = document.frm_factura_modal.tipofinanciamiento.value;

            nrofactura = nrofactura.trim();
            numerocliente = numerocliente.trim();
            cliente = cliente.trim();       
            var fantes = femision.split('-');
            var fdespues = fvencimiento.split('-');
            var femision2 = new Date(fantes[0],fantes[1]-1,fantes[2]);
            var fvencimiento2 = new Date(fdespues[0],fdespues[1]-1,fdespues[2]);
            var restaemision = fhoy.getTime() - femision2.getTime();
            var restavencimiento = fvencimiento2.getTime() - fhoy.getTime();
            var cuatromeses = 1000 * 60 * 60 * 24 * 120;
            var unmes = 1000 * 60 * 60 * 24 * 30;

            if (nrofactura.length == 0 || nrofactura == 0){
                alert('Debe ingresar un numero de factura');
                return 0;
            } else{
                if (restaemision > cuatromeses){
                    alert('Ingrese una fecha de emision valida');
                    return 0;
                } else{
                    if (restavencimiento < unmes){
                        alert('Ingrese una fecha de vencimiento valida');
                        return 0;
                    } else{
                        if (numerocliente.length == 0 || numerocliente == 0){
                            alert('Debe ingresar la identificacion de su cliente');
                            return 0;
                        } else{
                            if (cliente.length == 0){
                                alert('El nombre de su cliente no puede estar vacio');
                                return 0;
                            }else{
                                if (monedaid == 0){
                                    alert('Debe seleccionar una moneda');
                                    return 0;
                                }else{
                                    if (subtotal <= 0){
                                        alert('El subtotal no puede ser menor o igual a cero');
                                        return 0;
                                    }else{
                                        return 1;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        function grabarFactura(){
            var btn_grabar = document.getElementById('btn_grabar');
            var btn_enviar = document.getElementById('btn_enviar');

            var v_valida_grabar = validaGrabar();

            if (v_valida_grabar == 1){
                btn_grabar.disabled = 'true';
                btn_enviar.disabled = 'true';
                /*document.frm_factura_modal.submit();*/
                var formData = new FormData(document.getElementById("frm_factura_modal"));

                //==== Llamada al spinner
                mostrarLoading();

                $.ajax({
                    url:"registro_factura_fisica_proc.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    //==== Ocultar el spinner
                    ocultarLoading();

                    if (rpta > 0){
                        if (rpta == 1){
                            alert('Su factura ha sido enviada para la evaluación de nuestros analistas, inmediatamente supere la evaluación le enviaremos un correo de confirmación');
                            refresh_page();
                        } else{
                            alert('Se registro la factura Nro '+rpta+', recuerde que puede solicitar financiamiento presionando el boton abajo');
                            btn_enviar.disabled = false;
                            
                            document.frm_factura_modal.factura_id.value = rpta;
                            document.frm_factura_modal.facturaid.value = rpta;
                            document.frm_factura_modal.tipoaccion.value = 'upd';
                            getData();
                        }
                    } else {
                        if (rpta == -1){
                            alert('Ocurrio un error al guardar la Factura');
                            refresh_page();
                        } else{
                            if (rpta == -2){
                                alert('Ocurrio un error al enviar la Factura, su factura fue rechazada');
                                refresh_page();
                            } else{
                                if (rpta == -3){
                                    alert('Datos incorrectos');
                                }
                            }
                        }
                    }
                });
            }
        }

        //============
        function checkdescuento(idcheck,iddescuento){

            var elementodescuento = document.getElementById(iddescuento);
            var elementocheck = document.getElementById(idcheck).checked;
            
            if (elementocheck){
                elementodescuento.style.backgroundColor = "#ffffff";
                elementodescuento.readOnly = false;
                alert("Usted ha activado la restricción de descuento máximo, si su cliente demora en pagar este limite será modificado dependiendo de la cantidad de dias de retrazo en el pago por su cliente");
            } else{
                elementodescuento.style.backgroundColor = "#aaaaaa";
                elementodescuento.readOnly = true;
            }
        }
        function validacliente(){
            var identificacion = document.frm_factura_modal.numerocliente.value;
            window.open('../maestros/validanombreempresa.php?identificacion='+identificacion+'&tipo=regfact','maestros','menubar=0,resizable=0,location=1,status=0,scrollbars=1,width=100,height=100');
            /* deja el resultado en retrievenombreempresa */
        }
        function validafactura(){
            var fnumero = document.frm_factura_modal.nrofactura.value;
            window.open('../maestros/validafactura.php?fnumero='+fnumero,'maestros','menubar=0,resizable=0,location=1,status=0,scrollbars=1,width=100,height=100');
            /* deja el resultado en retrievevalidafactura */
        }
        function retrievevalidafactura(resultado){
            if (resultado > 0){
                alert('El numero de factura que esta ingresando ya existe, verifique por favor el numero de la factura');
                document.frm_factura_modal.nrofactura.value = '';
            }
        }
        function retrievenombreempresa(nombre){
            document.frm_factura_modal.cliente.value = nombre;
            var elementocliente = document.getElementById('cliente');

            if (nombre != ''){
                elementocliente.readOnly = true;
            } else{
                elementocliente.readOnly = false;
            }
        }
        function calculatotales(dato){
                        
            var subtotal = Number(document.frm_factura_modal.subtotal.value);
            var anticipos = Number(document.frm_factura_modal.anticipos.value);
            var descuentos = Number(document.frm_factura_modal.descuentos.value);
            var valorventa;
            var porcimpuestoventa = Number(document.frm_factura_modal.porcimpuestoventa.value);
            var impuestoventa;
            var otroscargos = Number(document.frm_factura_modal.otroscargos.value);
            var otrostributos = Number(document.frm_factura_modal.otrostributos.value);
            var total, monto_inicial, monto_remanente, monto_financiado, tasa_dcto, monto_inicial_r, monto_remanente_r, monto_financiado_r;
            var porc_financia = Number(document.frm_factura_modal.porc_financia.value);
            var porc_dcto = Number(document.frm_factura_modal.porc_dcto.value);
            var porc_comision = Number(document.frm_factura_modal.porc_comision.value);
            var v_ted = Number(document.frm_factura_modal.ted_param.value);
            var v_valorventa_f, v_valorventa_l, v_igv_f, v_igv_l, v_total_f, v_total_l, v_adelanto_l, v_remanente_l, v_totalrecibido_l;

            var obj_subtotal_view = document.getElementById('subtotal_view');
            var obj_femision = document.getElementById('femision');
            var obj_femision_view = document.getElementById('femision_view');
            var obj_fvencimiento = document.getElementById('fvencimiento');
            
            var v_femision = new Date(document.getElementById("femision").value);
            var v_fvencimiento = new Date(document.getElementById("fvencimiento").value);
            var v_dias;
            
            if (v_fvencimiento > v_femision){
                var diff = v_fvencimiento.getTime() - v_femision.getTime();
                v_dias = Math.round(diff / (1000*60*60*24));
            } else{
                alert ('Verifique las fechas de emsion y vencimiento por favor, la fecha de emision no puede ser igual o mayor a la fecha de vencimiento !!');
                v_dias = 0;

                var v_fecha_vencimiento = obj_fvencimiento.value;
                let [anio, mes, dia] = v_fecha_vencimiento.split("-");
                let v_fecha_valida = new Date(anio, mes - 1, dia);
                v_fecha_valida.setDate(v_fecha_valida.getDate() - 1);
                let fecha_resultado = v_fecha_valida.toISOString().split("T")[0];

                obj_femision.value = fecha_resultado;
                [anio, mes, dia] = fecha_resultado.split("-");
                obj_femision_view.value = dia+'-'+mes+'-'+anio;
            }
            
            if (subtotal < 0){
                alert('No puede ingresar valores negativos');
                document.frm_factura_modal.subtotal.value = 0;
                obj_subtotal_view.value = '';
            } else{
                valorventa = Number(subtotal - anticipos - descuentos);
                v_valorventa_f = Number(valorventa.toFixed(2));
                v_valorventa_l = v_valorventa_f.toLocaleString('en-US');
                
                if (dato == 1) impuestoventa = Number(document.frm_factura_modal.impuestoventa.value);
                else impuestoventa = Number(porcimpuestoventa) * Number(subtotal - descuentos);
                
                v_igv_f = Number(impuestoventa.toFixed(2));
                v_igv_l = v_igv_f.toLocaleString('en-US');

                total = Number(valorventa + impuestoventa + otroscargos + otrostributos);
                v_total_f = Number(total.toFixed(2));
                v_total_l = v_total_f.toLocaleString('en-US');

                monto_inicial = Number(total) * porc_financia;
                tasa_dcto = 0.03;
                monto_remanente = Number(total) - Number(monto_inicial) - (Number(monto_inicial) * Number(v_dias) * Number(v_ted)) - (Number(total) * porc_comision);
                monto_financiado = Number(monto_inicial) + Number(monto_remanente);
                monto_inicial_r = Number(monto_inicial.toFixed(2));
                v_adelanto_l = monto_inicial_r.toLocaleString('en-US');

                monto_remanente_r = Number(monto_remanente.toFixed(2));
                v_remanente_l = monto_remanente_r.toLocaleString('en-US');

                monto_financiado_r = Number(monto_financiado.toFixed(2));
                v_totalrecibido_l = monto_financiado_r.toLocaleString('en-US');

                document.frm_factura_modal.valorventa_l.value = v_valorventa_l;
                document.frm_factura_modal.valorventa.value = valorventa;

                document.frm_factura_modal.impuestoventa_view.value = v_igv_l;
                document.frm_factura_modal.impuestoventa.value = impuestoventa;

                document.frm_factura_modal.total_l.value = v_total_l;
                document.frm_factura_modal.total.value = total;

                calcula_financiamiento();
            }
        }
        function enviar(){
            var btn_enviar = document.getElementById('btn_enviar');
            btn_enviar.disabled = true;
            document.frm_factura_modal.tipoaccion.value = 'envia';
            /*document.frm_factura_modal.submit();*/

            var formData = new FormData(document.getElementById("frm_factura_modal"));

            //Llamada al spinner
            mostrarLoading();

            $.ajax({
                url:"registro_factura_fisica_proc.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                //==== ocultar spinner
                ocultarLoading();
                
                if (rpta == 1){
                    alert('Su factura ha sido enviada para la evaluación de nuestros analistas, inmediatamente supere la evaluación le enviaremos un correo de confirmación');
                    refresh_page();
                } else {
                    if (rpta == -1){
                        alert('Ocurrio un error al guardar la Factura');
                        refresh_page();
                    } else{
                        if (rpta == -2){
                            alert('Ocurrio un error al enviar la Factura, su factura fue rechazada');
                            refresh_page();
                        } else{
                            if (rpta == -3) alert('Datos incorrectos');
                        }
                    }
                }
            });
        }
        function anular(){
            var opcion = confirm("Esta seguro de anular la factura?")

            if (opcion == true){
                document.frm_factura_modal.action = 'anular_factura.php';
                document.frm_factura_modal.submit();
            }
        }

        function simbolo_moneda(){
            var moneda_id = document.getElementById("monedaid").value;
            var monto = document.getElementById("subtotal").value;

            if (monto > 0){
                //==== VALIDA MONTO
                let formData = new FormData()

                var total = document.getElementById("total").value;

                formData.append('monto', total);
                formData.append('moneda_id', moneda_id);
                formData.append('tipo_valida', 'MONTO');

                $.ajax({
                    url:"valida_factura_proc.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    if (rpta == -1){
                        alert('El Importe Total de la factura no cubre el minimo requerido.');
                    }
                });
            }
        }

        function validar(accion){
            var nrofactura,femision,fvencimiento;
            var numerocliente,cliente,monedaid;
            var subtotal,tipofinanciamiento;
            var fhoy = new Date();

            nrofactura = document.frm_factura_modal.nrofactura.value;
            femision = document.frm_factura_modal.femision.value;
            fvencimiento = document.frm_factura_modal.fvencimiento.value;
            numerocliente = document.frm_factura_modal.numerocliente.value;
            cliente = document.frm_factura_modal.cliente.value;
            monedaid = document.frm_factura_modal.monedaid.value;
            subtotal = Number(document.frm_factura_modal.subtotal.value);
            tipofinanciamiento = document.frm_factura_modal.tipofinanciamiento.value;

            nrofactura = nrofactura.trim();
            numerocliente = numerocliente.trim();
            cliente = cliente.trim();       
            var fantes = femision.split('-');
            var fdespues = fvencimiento.split('-');
            var femision2 = new Date(fantes[0],fantes[1]-1,fantes[2]);
            var fvencimiento2 = new Date(fdespues[0],fdespues[1]-1,fdespues[2]);
            var restaemision = fhoy.getTime() - femision2.getTime();
            var restavencimiento = fvencimiento2.getTime() - fhoy.getTime();
            var cuatromeses = 1000 * 60 * 60 * 24 * 120;
            var unmes = 1000 * 60 * 60 * 24 * 30;
            
            if (nrofactura.length == 0 || nrofactura == 0){
                alert('Debe ingresar un numero de factura');
            } else{
                if (restaemision > cuatromeses){
                    alert('Ingrese una fecha de emision valida');
                } else{
                    if (restavencimiento < unmes){
                        alert('Ingrese una fecha de vencimiento valida');
                    } else{
                        if (numerocliente.length == 0 || numerocliente == 0){
                            alert('Debe ingresar la identificacion de su cliente');
                        } else{
                            if (cliente.length == 0){
                                alert('El nombre de su cliente no puede estar vacio');
                            }else{
                                if (monedaid == 0){
                                    alert('Debe seleccionar una moneda');
                                }else{
                                    if (subtotal <= 0){
                                        alert('El subtotal no puede ser menor o igual a cero');
                                    }else{
                                        document.frm_factura_modal.submit();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //======
        $('#nrofactura').change(function(){
                var v_nrofactura = document.getElementById('nrofactura').value;
                var vobj_nrofactura = document.getElementById('nrofactura');
                var v_ruta = "fnumero="+v_nrofactura;

                $.ajax({
                    url: '../maestros/validafactura_v2.php',
                    type: 'POST',
                    data: v_ruta,
                })
                .done(function(res){
                    if (res > 0){ 
                        alert('El numero de factura que esta ingresando ya existe, verifique por favor el numero de la factura');
                        vobj_nrofactura.value = '';
                    }
                });
            });

            $('#numerocliente').change(function(){
                var v_idcliente = document.getElementById('numerocliente').value;
                var vobj_cliente = document.getElementById('cliente');
                var v_ruta = "identificacion="+v_idcliente;
                var v_emisor_id = $('#emisor_id').val();

                $.ajax({
                    url: '../maestros/validanombreempresa_v2.php',
                    type: 'POST',
                    data: v_ruta,
                })
                .done(function(res2){
                    vobj_cliente.value = res2;

                    recalcula_parametros(v_emisor_id, v_idcliente);
                    calcula_financiamiento();

                    if (res2 != '') vobj_cliente.readOnly = true;
                    else vobj_cliente.readOnly = false;
                });
            });

            function recalcula_parametros(emisor_id, cliente_doc){
                var vobj_porc_comision = document.getElementById('porc_comision');
                var vobj_porc_financia = document.getElementById('porc_financia');

                $.ajax({
                    url: 'registro_factura_valida_parametros.php',
                    type: 'POST',
                    data: {
                        "emisor_id": emisor_id,
                        "cliente_doc": cliente_doc
                    }
                })
                .done(function(res){
                    /*res=porc_comision+porc_financia*/
                    var resultado = res;
                    var v_pos = Number(resultado.indexOf("+"));
                    var v_porc_comision = Number(resultado.substring(0,v_pos));
                    var v_pos2 = Number(v_pos + 1);
                    var v_porc_financia = Number(resultado.substring(v_pos2));
                    
                    vobj_porc_comision.value = v_porc_comision;
                    vobj_porc_financia.value = v_porc_financia;

                    if (v_porc_financia <= 0) alert('El cliente ingresado tiene una calificacion de riesgo que no sera aceptada cuando solicite financiamiento, puede registrar la factura pero cuando solicite financiamiento puede que sea rechazado')
                });
            }

            function calcula_financiamiento(){
                var v_moneda_id = $('#monedaid').val();
                var v_cliente_doc = $('#numerocliente').val();
                var v_subtotal = $('#subtotal').val();
                var v_femision = new Date($('#femision').val());
                var v_fvencimiento = new Date($('#fvencimiento').val());
                var v_procesa = 1;

                if (v_moneda_id == 0) v_procesa = 0;
                if (v_cliente_doc == '') v_procesa = 0;
                if (v_subtotal == 0) v_procesa = 0;
                if (v_fvencimiento <= v_femision) v_procesa = 0;

                if (v_procesa > 0){
                    var v_porc_financia = $('#porc_financia').val();
                    var v_porc_comision = $('#porc_comision').val();
                    var v_factura_total = $('#total').val();
                    var v_tarifa_registro_nac = $('#tarifa_registro_nac').val();
                    var v_tarifa_registro_dol = $('#tarifa_registro_dol').val();
                    var v_ted = $('#ted_param').val();

                    var vobj_adelanto = document.getElementById('adelanto');
                    var vobj_remanente = document.getElementById('remanente');
                    var vobj_totalrecibido = document.getElementById('totalrecibido');
                    var vobj_tasa_descuento = document.getElementById('tasa_descuento');

                    var v_tarifa_registro = 0;
                    var v_adelanto, v_comision_fact, v_ganancia, v_remanente, v_totalrecibido;

                    if (v_moneda_id == 20) v_tarifa_registro = v_tarifa_registro_nac;
                    if (v_moneda_id == 21) v_tarifa_registro = v_tarifa_registro_dol;
                    if (v_tarifa_registro == 0) v_tarifa_registro = v_tarifa_registro_dol;

                    var diff = v_fvencimiento.getTime() - v_femision.getTime();
                    var v_dias = Math.round(diff / (1000*60*60*24));

                    v_adelanto = Number(v_porc_financia) * Number(v_factura_total);
                    v_comision_fact = Number(v_tarifa_registro) + (Number(v_porc_comision) * v_adelanto);
                    v_ganancia = Number(v_adelanto) * Number(v_dias) * Number(v_ted);
                    v_remanente = Number(v_factura_total) - v_adelanto - v_ganancia - v_comision_fact;

                    v_totalrecibido = v_adelanto + v_remanente;
                    
                    var v_adelanto_decimal = Number(v_adelanto.toFixed(2));
                    var v_adelanto_view = v_adelanto_decimal.toLocaleString('en-US');

                    var v_remanente_decimal = Number(v_remanente.toFixed(2));
                    var v_remanente_view = v_remanente_decimal.toLocaleString('en-US');

                    var v_total_decimal = Number(v_totalrecibido.toFixed(2));
                    var v_total_view = v_total_decimal.toLocaleString('en-US');

                    var v_tasa_dcto = (1 - (v_totalrecibido / Number(v_factura_total))) * 100;
                    var v_tasadcto_decimal = Number(v_tasa_dcto.toFixed(2));
                    var v_tasadcto_view = v_tasadcto_decimal.toLocaleString('en-US');

                    vobj_adelanto.value = v_adelanto_view;
                    vobj_remanente.value = v_remanente_view;
                    vobj_totalrecibido.value = v_total_view;
                    vobj_tasa_descuento.value = v_tasadcto_view;
                    
                }
            }

            function cambia_fecha(tipo_fecha){
                if (tipo_fecha == 'EMISION'){
                    var v_fecha = $('#femision').val();
                    let [anio, mes, dia] = v_fecha.split("-");
                    let v_fecha_esp = `${dia}-${mes}-${anio}`;

                    let obj_femision_view = document.getElementById('femision_view');
                    obj_femision_view.value = v_fecha_esp;
                }

                if (tipo_fecha == 'VENCIMIENTO'){
                    var v_fecha = $('#fvencimiento').val(); // fecha en ENG
                    let [anio, mes, dia] = v_fecha.split("-");
                    let v_fecha_esp = `${dia}-${mes}-${anio}`;

                    let obj_fvencimiento_view = document.getElementById('fvencimiento_view');
                    obj_fvencimiento_view.value = v_fecha_esp;
                }

                calculatotales(0);

                //====VALIDACION DE FACTURA
                let f_hoy = $('#f_hoy').val();

                if (tipo_fecha == 'EMISION'){
                    var f_vencimiento = $('#fvencimiento').val();
                    var f_emision = v_fecha;

                    if (f_vencimiento != f_hoy){
                        //==== VALIDO LA CANTIDAD DE DIAS
                        let formData = new FormData()

                        formData.append('f_emision', f_emision);
                        formData.append('f_vencimiento', f_vencimiento);
                        formData.append('tipo_valida', 'TIEMPO');

                        $.ajax({
                            url:"valida_factura_proc.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html"
                        })
                        .done(function(rpta){
                            if (rpta == -1){
                                alert('El tiempo que queda para el vencimiento de la factura no cumple el mínimo requerido.');
                            }
                        });
                    }
                } else {
                    var f_vencimiento = v_fecha;
                    var f_emision = $('#femision').val();

                    var formData = new FormData();

                    formData.append('f_emision', f_emision);
                    formData.append('f_vencimiento', f_vencimiento);
                    formData.append('tipo_valida', 'TIEMPO');

                    $.ajax({
                        url:"valida_factura_proc.php",
                        type:'post',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: "html"
                    })
                    .done(function(rpta){
                        if (rpta == -1){
                            alert('El tiempo que queda para el vencimiento de la factura no cumple el mínimo requerido.');
                        }
                    });
                }
            }

            // FUNCIONES DE FORMATEO DE NUMEROS
            function formatearNumero(valor) {
                if (!valor) return "";

                let tienePuntoFinal = valor.endsWith(".");

                let partes = valor.split(".");
                let entero = partes[0];
                let decimal = partes[1] || "";

                // Formatear miles SOLO en la parte entera
                entero = entero.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                if (tienePuntoFinal) {
                    return entero + ".";
                }

                return decimal ? `${entero}.${decimal}` : entero;
            }

            // TRATAMIENTO DEL FORMATO DE SUBTOTAL
            document.getElementById("subtotal_view").addEventListener("input", function (e) {
                const input = document.getElementById("subtotal_view");
                const input_real = document.getElementById("subtotal");

                const teclasPermitidas = [
                    "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
                ];
                
                // GUARDAR POSICION DEL CURSOS
                let valor_original = input.value;
                let cursor = input.selectionStart;

                // Contar cuántas comas había antes del cursor
                let antesCursor = valor_original.slice(0, cursor);
                let comasAntes = (antesCursor.match(/,/g) || []).length;

                // Limpiar valor
                let limpio = valor_original.replace(/,/g, "");
                limpio = limpio.replace(/[^0-9.]/g, "");
                
                // Evitar múltiples puntos
                let partes = limpio.split(".");
                if (partes.length > 2) {
                    limpio = partes[0] + "." + partes[1];
                }
                
                // Limitar decimales
                partes = limpio.split(".");
                if (partes[1]) {
                    limpio = partes[0] + "." + partes[1].slice(0, 2);
                }
                
                // Guardar valor real
                input_real.value = limpio;

                // Formatear
                let formateado = formatearNumero(limpio);

                 // Recalcular cursor
                let nuevoAntesCursor = formateado.slice(0, cursor);
                let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

                let nuevaPos = cursor + (comasDespues - comasAntes);

                input.value = formateado;
                input.setSelectionRange(nuevaPos, nuevaPos);

                // RECALCULO DE TOTALES
                calculatotales(0);
            });

            // TRATAMIENTO DEL FORMATO DE ANTICIPOS
            document.getElementById("anticipos_view").addEventListener("input", function (e) {
                const input = document.getElementById("anticipos_view");
                const input_real = document.getElementById("anticipos");

                const teclasPermitidas = [
                    "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
                ];
                
                // GUARDAR POSICION DEL CURSOS
                let valor_original = input.value;
                let cursor = input.selectionStart;

                // Contar cuántas comas había antes del cursor
                let antesCursor = valor_original.slice(0, cursor);
                let comasAntes = (antesCursor.match(/,/g) || []).length;

                // Limpiar valor
                let limpio = valor_original.replace(/,/g, "");
                limpio = limpio.replace(/[^0-9.]/g, "");
                
                // Evitar múltiples puntos
                let partes = limpio.split(".");
                if (partes.length > 2) {
                    limpio = partes[0] + "." + partes[1];
                }
                
                // Limitar decimales
                partes = limpio.split(".");
                if (partes[1]) {
                    limpio = partes[0] + "." + partes[1].slice(0, 2);
                }
                
                // Guardar valor real
                input_real.value = limpio;

                // Formatear
                let formateado = formatearNumero(limpio);

                 // Recalcular cursor
                let nuevoAntesCursor = formateado.slice(0, cursor);
                let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

                let nuevaPos = cursor + (comasDespues - comasAntes);

                input.value = formateado;
                input.setSelectionRange(nuevaPos, nuevaPos);

                // RECALCULO DE TOTALES
                calculatotales(0);
            });

            // TRATAMIENTO DEL FORMATO DE DESCUENTOS
            document.getElementById("descuentos_view").addEventListener("input", function (e) {
                const input = document.getElementById("descuentos_view");
                const input_real = document.getElementById("descuentos");

                const teclasPermitidas = [
                    "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
                ];
                
                // GUARDAR POSICION DEL CURSOS
                let valor_original = input.value;
                let cursor = input.selectionStart;

                // Contar cuántas comas había antes del cursor
                let antesCursor = valor_original.slice(0, cursor);
                let comasAntes = (antesCursor.match(/,/g) || []).length;

                // Limpiar valor
                let limpio = valor_original.replace(/,/g, "");
                limpio = limpio.replace(/[^0-9.]/g, "");
                
                // Evitar múltiples puntos
                let partes = limpio.split(".");
                if (partes.length > 2) {
                    limpio = partes[0] + "." + partes[1];
                }
                
                // Limitar decimales
                partes = limpio.split(".");
                if (partes[1]) {
                    limpio = partes[0] + "." + partes[1].slice(0, 2);
                }
                
                // Guardar valor real
                input_real.value = limpio;

                // Formatear
                let formateado = formatearNumero(limpio);

                 // Recalcular cursor
                let nuevoAntesCursor = formateado.slice(0, cursor);
                let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

                let nuevaPos = cursor + (comasDespues - comasAntes);

                input.value = formateado;
                input.setSelectionRange(nuevaPos, nuevaPos);

                // RECALCULO DE TOTALES
                calculatotales(0);
            });

            // TRATAMIENTO DEL FORMATO DE IMPUESTO A LA VENTA
            document.getElementById("impuestoventa_view").addEventListener("input", function (e) {
                const input = document.getElementById("impuestoventa_view");
                const input_real = document.getElementById("impuestoventa");

                const teclasPermitidas = [
                    "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
                ];
                
                // GUARDAR POSICION DEL CURSOS
                let valor_original = input.value;
                let cursor = input.selectionStart;

                // Contar cuántas comas había antes del cursor
                let antesCursor = valor_original.slice(0, cursor);
                let comasAntes = (antesCursor.match(/,/g) || []).length;

                // Limpiar valor
                let limpio = valor_original.replace(/,/g, "");
                limpio = limpio.replace(/[^0-9.]/g, "");
                
                // Evitar múltiples puntos
                let partes = limpio.split(".");
                if (partes.length > 2) {
                    limpio = partes[0] + "." + partes[1];
                }
                
                // Limitar decimales
                partes = limpio.split(".");
                if (partes[1]) {
                    limpio = partes[0] + "." + partes[1].slice(0, 2);
                }
                
                // Guardar valor real
                input_real.value = limpio;

                // Formatear
                let formateado = formatearNumero(limpio);

                 // Recalcular cursor
                let nuevoAntesCursor = formateado.slice(0, cursor);
                let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

                let nuevaPos = cursor + (comasDespues - comasAntes);

                input.value = formateado;
                input.setSelectionRange(nuevaPos, nuevaPos);

                // RECALCULO DE TOTALES
                calculatotales(0);
            });

            // TRATAMIENTO DEL FORMATO DE OTROS CARGO
            document.getElementById("otroscargos_view").addEventListener("input", function (e) {
                const input = document.getElementById("otroscargos_view");
                const input_real = document.getElementById("otroscargos");

                const teclasPermitidas = [
                    "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
                ];
                
                // GUARDAR POSICION DEL CURSOS
                let valor_original = input.value;
                let cursor = input.selectionStart;

                // Contar cuántas comas había antes del cursor
                let antesCursor = valor_original.slice(0, cursor);
                let comasAntes = (antesCursor.match(/,/g) || []).length;

                // Limpiar valor
                let limpio = valor_original.replace(/,/g, "");
                limpio = limpio.replace(/[^0-9.]/g, "");
                
                // Evitar múltiples puntos
                let partes = limpio.split(".");
                if (partes.length > 2) {
                    limpio = partes[0] + "." + partes[1];
                }
                
                // Limitar decimales
                partes = limpio.split(".");
                if (partes[1]) {
                    limpio = partes[0] + "." + partes[1].slice(0, 2);
                }
                
                // Guardar valor real
                input_real.value = limpio;

                // Formatear
                let formateado = formatearNumero(limpio);

                 // Recalcular cursor
                let nuevoAntesCursor = formateado.slice(0, cursor);
                let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

                let nuevaPos = cursor + (comasDespues - comasAntes);

                input.value = formateado;
                input.setSelectionRange(nuevaPos, nuevaPos);

                // RECALCULO DE TOTALES
                calculatotales(0);
            });

            // TRATAMIENTO DEL FORMATO DE OTROS TRIBUTOS
            document.getElementById("otrostributos_view").addEventListener("input", function (e) {
                const input = document.getElementById("otrostributos_view");
                const input_real = document.getElementById("otrostributos");

                const teclasPermitidas = [
                    "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
                ];
                
                // GUARDAR POSICION DEL CURSOS
                let valor_original = input.value;
                let cursor = input.selectionStart;

                // Contar cuántas comas había antes del cursor
                let antesCursor = valor_original.slice(0, cursor);
                let comasAntes = (antesCursor.match(/,/g) || []).length;

                // Limpiar valor
                let limpio = valor_original.replace(/,/g, "");
                limpio = limpio.replace(/[^0-9.]/g, "");
                
                // Evitar múltiples puntos
                let partes = limpio.split(".");
                if (partes.length > 2) {
                    limpio = partes[0] + "." + partes[1];
                }
                
                // Limitar decimales
                partes = limpio.split(".");
                if (partes[1]) {
                    limpio = partes[0] + "." + partes[1].slice(0, 2);
                }
                
                // Guardar valor real
                input_real.value = limpio;

                // Formatear
                let formateado = formatearNumero(limpio);

                 // Recalcular cursor
                let nuevoAntesCursor = formateado.slice(0, cursor);
                let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

                let nuevaPos = cursor + (comasDespues - comasAntes);

                input.value = formateado;
                input.setSelectionRange(nuevaPos, nuevaPos);

                // RECALCULO DE TOTALES
                calculatotales(0);
            });

        //==== FUNCIONES DEL SPINNER LOADING

        function mostrarLoading() {
            document.getElementById("loadingModal").style.display = "flex";
        }

        function ocultarLoading() {
            document.getElementById("loadingModal").style.display = "none";
        }

        //==== VALIDACION DE FACTURA

        function valida_monto_factura(){
            let formData = new FormData()

            let total = document.getElementById("total").value;
            let moneda_id = document.getElementById("monedaid").value;

            formData.append('total', total);
            formData.append('moneda_id', moneda_id);
            formData.append('tipo_valida', 'MONTO');

            $.ajax({
                url:"valida_factura_proc.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                if (rpta == -1){
                    alert('El Importe Total de factura no cumple con el minimo requerido, revise por favor.');
                }
            });
        }

    </script>

</BODY>
    
</HTML>