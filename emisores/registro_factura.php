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
    <script type="text/javascript">
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
            var identificacion = document.frm.numerocliente.value;
            window.open('../maestros/validanombreempresa.php?identificacion='+identificacion+'&tipo=regfact','maestros','menubar=0,resizable=0,location=1,status=0,scrollbars=1,width=100,height=100');
            /* deja el resultado en retrievenombreempresa */
        }
        function validafactura(){
            var fnumero = document.frm.nrofactura.value;
            window.open('../maestros/validafactura.php?fnumero='+fnumero,'maestros','menubar=0,resizable=0,location=1,status=0,scrollbars=1,width=100,height=100');
            /* deja el resultado en retrievevalidafactura */
        }
        function retrievevalidafactura(resultado){
            if (resultado > 0){
                alert('El numero de factura que esta ingresando ya existe, verifique por favor el numero de la factura');
                document.frm.nrofactura.value = '';
            }
        }
        function retrievenombreempresa(nombre){
            document.frm.cliente.value = nombre;
            var elementocliente = document.getElementById('cliente');

            if (nombre != ''){
                elementocliente.readOnly = true;
            } else{
                elementocliente.readOnly = false;
            }
        }
        function calculatotales(dato){
                        
            var subtotal = Number(document.frm.subtotal.value);
            var anticipos = Number(document.frm.anticipos.value);
            var descuentos = Number(document.frm.descuentos.value);
            var valorventa;
            var porcimpuestoventa = Number(document.frm.porcimpuestoventa.value);
            var impuestoventa;
            var otroscargos = Number(document.frm.otroscargos.value);
            var otrostributos = Number(document.frm.otrostributos.value);
            var total, monto_inicial, monto_remanente, monto_financiado, tasa_dcto, monto_inicial_r, monto_remanente_r, monto_financiado_r;
            var porc_financia = Number(document.frm.porc_financia.value);
            var porc_dcto = Number(document.frm.porc_dcto.value);
            var porc_comision = Number(document.frm.porc_comision.value);
            var v_ted = Number(document.frm.ted_param.value);
            var v_valorventa_f, v_valorventa_l, v_igv_f, v_igv_l, v_total_f, v_total_l, v_adelanto_l, v_remanente_l, v_totalrecibido_l;
            var v_femision = new Date(document.getElementById("femision").value);
            var v_fvencimiento = new Date(document.getElementById("fvencimiento").value);
            var v_dias;

            if (v_fvencimiento > v_femision){
                var diff = v_fvencimiento.getTime() - v_femision.getTime();
                v_dias = Math.round(diff / (1000*60*60*24));
            } else{
                alert ('Verifique las fechas de emsion y vencimiento por favor !!');
                v_dias = 0;
            }
            
            if (subtotal < 0){
                alert('No puede ingresar valores negativos');
                document.frm.subtotal.value = 0;
            } else{
                valorventa = Number(subtotal - anticipos - descuentos);
                v_valorventa_f = Number(valorventa.toFixed(2));
                v_valorventa_l = v_valorventa_f.toLocaleString('en-US');
                
                if (dato == 1) impuestoventa = Number(document.frm.impuestoventa_l.value);
                else impuestoventa = Number(porcimpuestoventa) * Number(subtotal - descuentos);
                /*else impuestoventa = Number(porcimpuestoventa * valorventa);*/

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

                document.frm.valorventa_l.value = v_valorventa_l;
                document.frm.valorventa.value = valorventa;

                document.frm.impuestoventa_l.value = v_igv_l;
                document.frm.impuestoventa.value = impuestoventa;

                document.frm.total_l.value = v_total_l;
                document.frm.total.value = total;

                /*document.frm.adelanto.value = v_adelanto_l;
                document.frm.remanente.value = v_remanente_l;
                document.frm.totalrecibido.value = v_totalrecibido_l;*/
                calcula_financiamiento();
            }
        }
        function enviar(){
            var btn_enviar = document.getElementById('btn_enviar');
            btn_enviar.disabled = 'true';
            document.frm.tipoaccion.value = 'envia';
            document.frm.submit();
        }
        function anular(){
            var opcion = confirm("Esta seguro de anular la factura?")

            if (opcion == true){
                document.frm.action = 'anular_factura.php';
                document.frm.submit();
            }
        }

        function simbolo_moneda(){
            var simbolo = Number(document.frm.monedaid.value);
        }

        function validar(accion){
            var nrofactura,femision,fvencimiento;
            var numerocliente,cliente,monedaid;
            var subtotal,tipofinanciamiento;
            var fhoy = new Date();

            nrofactura = document.frm.nrofactura.value;
            femision = document.frm.femision.value;
            fvencimiento = document.frm.fvencimiento.value;
            numerocliente = document.frm.numerocliente.value;
            cliente = document.frm.cliente.value;
            monedaid = document.frm.monedaid.value;
            subtotal = Number(document.frm.subtotal.value);
            tipofinanciamiento = document.frm.tipofinanciamiento.value;

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
                                        document.frm.submit();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    </script>
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


if ($tipo == 'new'){
    $hoy = date('Y-m-d');
    $fnula = strtotime($hoy); $ffnula = date('Y-m-d', $fnula);
    $femision = $ffnula; $fvencimiento = $ffnula;
    $nrofactura = ''; $numerocliente = ''; $cliente = ''; $nombrepdf = ''; $xmlpath = ''; $pdfpath = ''; $readonly = ''; $v_simbolo = ''; $v_condesc_maximo = '';
    $monedaid = 0; $subtotal = 0; $anticipos = 0; $descuentos = 0; $valorventa = 0; $impuestoventa = 0; $otroscargos = 0; $otrostributos = 0; $total = 0; $tfinid = 0; $v_desc_maximo = 0;
    $nombrexml = '[SIN XML]';
    $enviar = 'off'; $anular = 'off';
    $estadoid = 11; $estadofinanciamientoid = 14;
    $v_desc_maximo_readonly = 'readonly';
    $v_desc_maximo_bgc = '#aaaaaa;';
    $v_comi_fact_emi = $arrparametros['COMISION']['valornum'];
    $v_porc_adelanto = $arrparametros['% FINANCIA']['valornum'];
} elseif ($tipo == 'upd' || $tipo == 'view'){
    $facturaid = $_GET['id'];
    $arrfactura = $objfactura->get_datos_factura($facturaid);
    $nrofactura = $arrfactura['factura'];
    $femision = $arrfactura['femision'];
    $fvencimiento = $arrfactura['fvencimiento'];
    $numerocliente = $arrfactura['identificacion'];
    $cliente = $arrfactura['cliente'];
    $monedaid = $arrfactura['monedaid'];
    $subtotal = $arrfactura['subtotal'];
    $anticipos = $arrfactura['anticipos'];
    $descuentos = $arrfactura['descuentos'];
    $valorventa = $arrfactura['valorventa'];
    $impuestoventa = $arrfactura['impuestoventa'];
    $otroscargos = $arrfactura['otroscargos'];
    $otrostributos = $arrfactura['otrostributos'];
    $total = $arrfactura['total'];
    $nombrexml = '[ARCHIVO XML]';
    $tfinid = $arrfactura['tipofinanciamiento'];
    $nombrepdf = 'Ver <span class="icon-eye"></span>';
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
        $v_desc_maximo_bgc = '#aaaaaa;';
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

    if ($tipo == 'upd') $readonly = '';
    else {
        $readonly = 'readonly';
        $enviar = 'off';
        $anular = 'off';
    }
}
// colores de la leyenda
if ($estadoid == 11 || $estadoid == 17){
    $colorregistro = 'var(--color-azulv2);';
    $colorevaluacion = 'var(--color-gris-oscuro);';
    $colorsubasta = 'var(--color-gris-oscuro);';
    $colorfinancia = 'var(--color-gris-oscuro);';
} elseif ($estadoid == 12){
    $colorregistro = 'var(--color-gris-oscuro);';
    $colorevaluacion = 'var(--color-azulv2);';
    $colorsubasta = 'var(--color-gris-oscuro);';
    $colorfinancia = 'var(--color-gris-oscuro);';
} elseif ($estadoid == 13){
    if ($estadofinanciamientoid == 18){
        $colorregistro = 'var(--color-gris-oscuro);';
        $colorevaluacion = 'var(--color-gris-oscuro);';
        $colorsubasta = 'var(--color-azulv2);';
        $colorfinancia = 'var(--color-gris-oscuro);';
    }else{
        $colorregistro = 'var(--color-gris-oscuro);';
        $colorevaluacion = 'var(--color-gris-oscuro);';
        $colorsubasta = 'var(--color-gris-oscuro);';
        $colorfinancia = 'var(--color-azulv2);';
    }
}

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding: 5px;">
        Registro de Factura
<?php
    if ($tipo == 'upd' || $tipo == 'view') echo ' ID:'.$facturaid;
?>
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <div style="overflow:hidden;font-size: 10px;width:60%;">
        <ul style="overflow:hidden;list-style:none;margin:3px;padding-left:10px;padding-top:3px;">
            <?
            if ($enviar == 'on'){
                echo '<li style="float:left;display: block;font-size:14px;color:#D42329; margin:2px;padding:5px;font-weight: bold;">
                        NO SE OLVIDE DE ENVIAR SU FACTURA
                    </li>';
            }
            echo '
            <li style="float:left;display: block;font-size:12px;color:'.$colorregistro.'margin:2px;padding:3px;font-weight: bold;">
                <span class="icon-pencil"></span> Registro<span class="icon-arrow-right" style="margin-left:10px;"></span>
            </li>
            <li style="float:left;display: block;font-size:12px;color:'.$colorevaluacion.'margin:2px;padding:3px;">
                <span class="icon-eye"></span> Evaluaci&oacute;n<span class="icon-arrow-right" style="margin-left:10px;"></span>
            </li>
            <li style="float:left;display: block;font-size:12px;color:'.$colorsubasta.'margin:2px;padding:3px;">
                <span class="icon-hour-glass"></span> Subasta<span class="icon-arrow-right" style="margin-left:10px;"></span>
            </li>
            <li style="float:left;display: block;font-size:12px;color:'.$colorfinancia.'margin:2px;padding:3px;">
            <span class="icon-coin-dollar" style="margin-right:10px;"></span> Financiamiento
            </li>';
            ?>
        </ul>
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data" action="graba_factura.php">
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

    <div class="frmtransaccion">
        <ul>
            <li style="color:#b30a1f;font-weight: bold;">[*] Requisito para regisro de factura</li>
            <li style="color:#b30a1f;font-weight: bold;">[**] Requisito para envio de factura</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;text-transform: uppercase;">Id Emisor:</li>
            <li style="margin:0px 3px;padding:0px;width:270px;text-transform: uppercase;">Emisor:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;text-transform: uppercase;">Nro Factura <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:130px;text-transform: uppercase;">Moneda: <b style="color:#b30a1f;">[*]</b></li>
        </ul>
        <ul>
            <li><input type="text" name="numeroemisor" value="<?=$arremisor['identificacion']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="emisor" size="50" value="<?=$arremisor['nombre']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="nrofactura" id="nrofactura" <?=$readonly?> value="<?=$nrofactura?>" class="frminput_text"></li>
            <li>
                <select class="frminput_text" name="monedaid" id="monedaid" onchange="javascript:simbolo_moneda()" <?=$readonly?>>
                    <? 
                    $arrmoneda = $objmaestros->get_tipos('MONEDA');
                    if ($monedaid == 0){
                        echo '<option value="0" selected>Elija Moneda</option>';
                        $v_simbolo = '';
                    }
                    
                    for ($i=0; $i<count($arrmoneda); $i++){
                        if ($monedaid == $arrmoneda[$i]['id']){
                            echo '<option value="'.$arrmoneda[$i]['id'].'" selected>'.$arrmoneda[$i]['nombre'].'</option>';
                            $v_simbolo = $arrmoneda[$i]['dato1'];
                        } else
                            echo '<option value="'.$arrmoneda[$i]['id'].'">'.$arrmoneda[$i]['nombre'].'</option>';
                    }
                    ?>
                </select>
            </li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;text-transform: uppercase;">NIT: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:270px;text-transform: uppercase;">Cliente: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:105px;text-transform: uppercase;">Fecha Emisi&oacute;n <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:135px;text-transform: uppercase;">Fecha Vencimiento <b style="color:#b30a1f;">[*]</b></li>
        </ul>
        <ul>
            <li><input type="text" name="numerocliente" id="numerocliente" <?=$readonly?> value="<?=$numerocliente?>" class="frminput_text"></li>
            <li><input id="cliente" type="text" name="cliente" readonly size="50" value="<?=$cliente?>" class="frminput_text"></li>
            <li><input type='date' name='femision' id='femision' <?=$readonly?> value='<?=$femision?>' min='1900-01-01' class="frminput_text" onchange="javascript:calculatotales(0)"></li>
            <li><input type='date' name='fvencimiento' id='fvencimiento' <?=$readonly?> value='<?=$fvencimiento?>' min='1900-01-01' class="frminput_text" onchange="javascript:calculatotales(0)"></li>
            <input type="hidden" name="numeroclienteold" value="<?=$numerocliente?>">
            <input type="hidden" name="clienteid" value="<?=$clienteid?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:115px;text-transform: uppercase;">SubTotal Venta:<b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:110px;text-transform: uppercase;">Anticipos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;text-transform: uppercase;">Descuentos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;text-transform: uppercase;">Valor Venta: <b style="color:#b30a1f;">[*]</b></li>
        </ul>
        <ul>
            <?
            $subtotalp = number_format($subtotal,2,'.',',');
            $valorventap = number_format($valorventa,2,'.',',');
            //$total = number_format($total,2,'.',',');
            //$impuestoventa = number_format($impuestoventa,2,'.',',');
            
            echo '<li><input type="number" step="any" class="frminput_text" name="subtotal" id="subtotal" '.$readonly.' style="width:110px;text-align:right;" onchange="javascript:calculatotales(0)" value='.$subtotal.'></li>';
            ?>
            <li><input type="number" class="frminput_text" name="anticipos" <?=$readonly?> style="width:110px;text-align:right;" onchange="javascript:calculatotales(0)" value="<?=$anticipos?>"></li>
            <li><input type="number" name="descuentos" class="frminput_text" <?=$readonly?> style="width:110px;text-align:right;" onchange="javascript:calculatotales(0)" value="<?=$descuentos?>"></li>
            <li><input type="text" name="valorventa_l" class="frminput_text_off" readonly style="width:100px;text-align:right;" value="<?=$valorventap?>"></li>
            <input type="hidden" name="valorventa" value="<?=$valorventa?>">
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:110px;text-transform: uppercase;">IVA: <b style="color:#b30a1f;">[*]</b></li> <!-- parametro y calculo -->
            <li style="margin:0px 3px;padding:0px;width:110px;text-transform: uppercase;">Otros Cargos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:120px;text-transform: uppercase;">Otros Tributos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;text-transform: uppercase;">Importe Total:</li>
        </ul>
        <ul>
            <li><input type="text" name="impuestoventa_l" class="frminput_text" style="width:110px;text-align:right;" value="<?=$impuestoventa?>" onchange=javascript:calculatotales(1)></li>
            <input type="hidden" name="impuestoventa" value="<?=$impuestoventa?>">
            <li><input type="number" name="otroscargos" <?=$readonly?> class="frminput_text" style="width:110px;text-align:right;" onchange="javascript:calculatotales(0)" value="<?=$otroscargos?>"></li>
            <li><input type="number" name="otrostributos" <?=$readonly?> class="frminput_text" style="width:110px;text-align:right;" onchange="javascript:calculatotales(0)" value="<?=$otrostributos?>"></li>
            <li><input type="text" name="total_l" class="frminput_text_off" style="width:100px;text-align:right;" readonly value="<?=$total?>"></li> <!-- calculo -->
            <input type="hidden" name="total" id="total" value="<?=$total?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;margin-top:10px;"></div>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:170px;text-transform: uppercase;">Tipo de financiamiento: <b style="color:#b30a1f;">[**]</b></li>
            <li style="margin:0px 3px;padding:0px;width:145px;text-transform: uppercase;">M&aacute;ximo descuento (%)</li>
        </ul>
        <ul>
            <li style="padding-right:40px;">
                <select name="tipofinanciamiento" id="tipofinanciamiento" <?=$readonly?> class="frminput_text">
                    <?
                    $arrtipofin = $objmaestros->get_tipos('TFINANCIAMIENTO');
                    if ($tfinid == 0) echo '<option value="0" selected>Elegir</option>';

                    for ($i=0; $i<count($arrtipofin); $i++){
                        if ($tfinid == $arrtipofin[$i]['id'])
                            echo '<option value="'.$arrtipofin[$i]['id'].'" selected>'.$arrtipofin[$i]['nombre'].'</option>';
                        else
                            echo '<option value="'.$arrtipofin[$i]['id'].'">'.$arrtipofin[$i]['nombre'].'</option>';
                    }
                    ?>
                </select>
            </li>

            <!-- DESCUENTO MAXIMO -->
<?php
    if ($arr)
?>
            <li><input id="conmaxdescuento" type="checkbox" <?=$readonly?> class="frminput_text" name="conmaxdescuento" value="1" <?echo $v_condesc_maximo;?> onchange="javascript:checkdescuento('conmaxdescuento','maxdescuento')"></li>
            <li><input id="maxdescuento" type="number" name="maxdescuento" class="frminput_text_off" value="<?=$v_desc_maximo?>" min="2" max="20" <?echo $v_desc_maximo_readonly;?> style="width:40px;text-align:right;background-color:<?echo $v_desc_maximo_bgc;?>"></li>
            <li style="color:#b30a1f;">Es el % m&aacute;ximo que esta dispuesto a aceptar que le descuenten, este valor es calculado siempre que su cliente pague en la fecha de vencimiento</li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin:10px;">
            <li style="font-size: 12px;font-weight: bold;text-transform: uppercase;">Archivos:</li>
        </ul>
        <ul>
            <li style="text-transform: uppercase;">Imagen o PDF de la factura: <b style="color:#b30a1f;">[**]</b></li>
            <?
            if ($pdfpath != '') echo '<li><a href="'.$pdfpath.'" target="_blank">'.$nombrepdf.'</a></li>';
            
            if ($tipo != 'view') echo '<li><input name="facturafile" id="facturafile" type="file" class="frminput_text"></li>';
            ?>
            <li style="text-transform: uppercase;">XML de la factura:</li>
            <li><?echo $xmlpath;?></li>
            <input type="hidden" name="xmlpath" value="<?=$nombrexml?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!-- rechazo -->
        <?
        if ($estadoid == 17){   // rechazado
            if ($arrfactura['rechazopath'] != '') $rechazopath = '<a href="'.$arrfactura['rechazopath'].'">Archivo de Rechazo</a>';
            else $rechazopath = '[SIN ARCHIVO]';

            echo '<ul>
                    <li style="font-size: 12px;font-weight: bold;">Motivo de Rechazo:</li>
                </ul>
                <ul>
                    <li>Documento de Rechazo:</li>
                    <li>'. $rechazopath.'</li>
                </ul>
                <ul>
                    <li><textarea name="rechazo" cols="100" rows="5" class="frminput_text_off" readonly>'.$arrfactura['motivorechazo'].'</textarea></li>
                </ul>';
        }
        ?>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin:10px;">
            <li style="font-size: 12px;font-weight: bold;">CALCULO APROXIMADO DE SU FINANCIAMIENTO:</li>
        </ul>
        <?php
        $v_dcto_promedio = $arrparametros['DESCUENTO PROMEDIO']['valornum'] * 100;
        ?>
        <ul>
            <!--############# MONTO DE ADELANTO -->
            <li style="width:100px;">Monto de adelanto aproximado 
                <b style="color:#b30a1f;"><abbr title="Es el monto que recibiría al inicio, esta es solo una simulación aproximada, el monto definitivo se conocerá luego de la evaluación de su factura">(Mas info)</abbr></b>:
            </li> <!-- el 85% es un parametro de BD -->
            <li><?echo $v_simbolo;?></li>
            <li><input type="text" name="adelanto" id="adelanto" class="frminput_text_off" value="<?=$v_inicial?>" readonly style="text-align:right;"></li>
            <li><span class="icon-plus"></span></li>

            <!--############# MONTO REMANENTE -->
            <li style="width:100px;">Monto remanente aproximado 
                <b style="color:#b30a1f;"><abbr title="Es el monto que recibirá cuando su cliente pague, esta es solo una simulación aproximada, el monto definitivo depende de las condiciones del inversor y luego que su cliente pague">(Mas info)</abbr></b>:
            </li> <!-- parametros de BD -->
            <li><?echo $v_simbolo;?></li>
            <li><input type="text" name="remanente" id="remanente" class="frminput_text_off" value="<?=$v_remanente?>" readonly style="text-align:right;"></li>
            <li><i class="fa-solid fa-equals"></i></li>

            <!--############# MONTO TOTAL -->
            <li style="width:100px;">Monto Total aproximado  
                <b style="color:#b30a1f;"><abbr title="Es el monto total que recibirá, que es la suma de los dos montos anteriores (Monto Inicial + Remanente)">(Mas info)</abbr></b>:
            </li>
            <li><?echo $v_simbolo;?></li>
            <li><input type="text" name="totalrecibido" id="totalrecibido" class="frminput_text_off" value="<?=$v_financiado?>" readonly style="text-align:right;"></li>

            <!--############# TASA DESCUENTO -->
            <li style="width:100px;">Tasa de Descuento (%) 
                <b style="color:#b30a1f;"><abbr title="Es la tasa de descuento que obtendra = monto total que recibira / monto de la factura">(Mas info)</abbr></b>:
            </li>
            <li><input type="text" name="tasa_descuento" id="tasa_descuento" class="frminput_text_off" readonly style="text-align:right;" value="<?=$v_tasa_dcto?>"></li>
        </ul>

        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!-- BOTONERA -->
        <ul style="margin:10px;">
            <?
            if ($tipo != 'view'){
                echo '
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="grabarFactura()" id="btn_grabar">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Factura</button>';

                if ($enviar != 'on')
                    echo '
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="grabarEnviarFactura()" id="btn_enviar">
                <i class="fa-solid fa-paper-plane"></i> Solicitar Financiamiento</button>';
            }

            if ($enviar == 'on') echo '
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="enviar()" id="btn_enviar">
                <i class="fa-solid fa-paper-plane"></i> Solicitar Financiamiento</button>';
            
            if ($anular == 'on') echo '
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;" onclick="anular()" id="btn_anular">
                <i class="fa-solid fa-delete-left"></i> Anular Factura</button>';
            
            echo '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;margin-left:10px;" onclick="volver()" id="btn_volver">
                <i class="fa-solid fa-rotate-left"></i> Volver</button>';
            ?>
        </ul>
    </div>
    </form>
<?php
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //@@@@@@@@@@@@@@@@@ AVISO
    if ($arrparametros['RECUERDA SOLICITUD']['valornum'] > 0 && $estadoid == 11 && $tipo == 'upd'){
        echo '<script>
                alert("Recuerda que debes de presionar el boton SOLICITAR FINANCIAMIENTO para recibir adelanto de tu factura");
            </script>';
    }
?>
    <!------ END CUERPO VARIABLE ------>
    <script>
        
        function volver(){
            location.href = 'panel_emisor.php';
        }

        function grabarEnviarFactura(){
            var btn_grabar = document.getElementById('btn_grabar');
            var btn_enviar = document.getElementById('btn_enviar');

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
                    btn_grabar.disabled = 'true';
                    btn_enviar.disabled = 'true';
                    document.frm.tipoaccion.value = 'grabaenvia';
                    document.frm.submit();
                }
            }
        }

        function validaGrabar(){
            var nrofactura,femision,fvencimiento;
            var numerocliente,cliente,monedaid;
            var subtotal,tipofinanciamiento;
            var fhoy = new Date();
            
            nrofactura = document.frm.nrofactura.value;
            femision = document.frm.femision.value;
            fvencimiento = document.frm.fvencimiento.value;
            numerocliente = document.frm.numerocliente.value;
            cliente = document.frm.cliente.value;
            monedaid = document.frm.monedaid.value;
            subtotal = Number(document.frm.subtotal.value);
            tipofinanciamiento = document.frm.tipofinanciamiento.value;

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
                document.frm.submit();
            }
        }


    </script>
</BODY>
        <script>
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
                    /*alert('%financia='+v_porc_financia+' adelanto='+v_adelanto+' dias='+v_dias+' ted='+v_ted+' tarifa='+v_tarifa_registro+' %comi='+v_porc_comision);*/
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
                    //alert('%comision'+v_porc_comision); alert('tarifa'+v_tarifa_registro); alert('dias'+v_dias); alert('ted'+v_ted);
                }
            }
        </script>
</HTML>