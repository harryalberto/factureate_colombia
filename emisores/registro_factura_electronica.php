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
        function calculatotales(){
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
            var v_valorventa_f, v_valorventa_l, v_igv_f, v_igv_l, v_total_f, v_total_l, v_adelanto_l, v_remanente_l, v_totalrecibido_l;
            
            if (subtotal < 0){
                alert('No puede ingresar valores negativos');
                document.frm.subtotal.value = 0;
            } else{
                valorventa = Number(subtotal - anticipos - descuentos);
                v_valorventa_f = Number(valorventa.toFixed(2));
                v_valorventa_l = v_valorventa_f.toLocaleString('en-US');
                
                impuestoventa = Number(porcimpuestoventa * valorventa);
                v_igv_f = Number(impuestoventa.toFixed(2));
                v_igv_l = v_igv_f.toLocaleString('en-US');

                total = Number(valorventa + impuestoventa + otroscargos + otrostributos);
                v_total_f = Number(total.toFixed(2));
                v_total_l = v_total_f.toLocaleString('en-US');

                monto_inicial = Number(total) * porc_financia;
                tasa_dcto = 0.03;
                monto_remanente = (Number(total) - Number(monto_inicial)) - (Number(monto_inicial) * Number(tasa_dcto)) - (Number(total) * porc_comision);
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

                document.frm.adelanto.value = v_adelanto_l;
                document.frm.remanente.value = v_remanente_l;
                document.frm.totalrecibido.value = v_totalrecibido_l;
            }
        }
        function enviar(){
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
$objmaestros = new maestros;
$objfactura = new factura;

$arremisor = $objmaestros->get_datos_emisor($_SESSION['user']['empresaid']);
$arrparametros = $objmaestros->get_parametros();
$v_arr_monedas = $objmaestros->get_tipos('MONEDA');
$hoy = date('Y-m-d');
//============= carga de factura electronica =============
$v_path_temp = '../xml/U-'.$_SESSION['user']['usuarioid'].'-'.$_FILES['xmlfile']['name'];
move_uploaded_file($_FILES['xmlfile']['tmp_name'],  $v_path_temp);
$v_arr_datos = $objfactura->carga_factura_electronica($v_path_temp);
$v_factura_existe = $objmaestros->valida_factura_existencia($v_arr_datos['nro_factura']);
$v_cliente_result = $objmaestros->validar_cliente($v_arr_datos);

$nrofactura = $v_arr_datos['nro_factura'];
$femision = $v_arr_datos['f_emision'];
$fvencimiento = $v_arr_datos['f_vencimiento'];
$numerocliente = $v_arr_datos['cliente_nro'];
$cliente = $v_arr_datos['cliente'];
$monedaid = $v_arr_datos['moneda_id'];
$subtotal = $v_arr_datos['subtotal'];
$anticipos = $v_arr_datos['anticipos'];
$descuentos = $v_arr_datos['descuentos'];
$valorventa = $v_arr_datos['valor_venta'];
$impuestoventa = $v_arr_datos['impuesto_venta'];
$otroscargos = $v_arr_datos['otros_cargos'];
$otrostributos = $v_arr_datos['otros_tributos'];
$total = $v_arr_datos['total'];
$nombrexml = '[ARCHIVO ELECTRONICO]';
$tfinid = 0;
$nombrepdf = '';
$enviar = 'off';
$xmlpath = $v_path_temp;
$pdfpath = '';
$anular = 'off';
$clienteid = $v_arr_datos['cliente_id'];
$estadoid = 11;
$estadofinanciamientoid = 14;
$facturaid = 0;
$tipo = 0;

for ($i=0; $i<count($v_arr_monedas); $i++){
    if ($v_arr_monedas[$i]['id'] == $monedaid) $v_simbolo_moneda = $v_arr_monedas[$i]['dato1'];
}
//============================= calcular los montos de resumen =============================
if ($v_cliente_result == '') $v_porc_financiamiento = $arrparametros['% FINANCIA']['valornum'];
else{
    $v_arr_op = array('id'=>0, 'identificacion'=>$numerocliente);
    $v_arr_riesgos = $objfactura->get_riesgo_op($v_arr_op);
    $v_porc_financiamiento = $v_arr_riesgos['porc_financiamiento'];
}

$v_inicial_bruto = $total * $v_porc_financiamiento;
$v_inicial = number_format($v_inicial_bruto, 2, '.', ',');
$v_remanente_bruto = ($total - $v_inicial_bruto) - ($total * $arrparametros['TASA DSCTO PROM']['valornum']);
$v_remanente = number_format($v_remanente_bruto,2,'.',',');
$v_total_finan_bruto = $v_inicial_bruto + $v_remanente_bruto;
$v_total_finan = number_format($v_total_finan_bruto,2,'.',',');
//=============================== colores de la leyenda ====================================
if ($estadoid == 11 || $estadoid == 17){
    $colorregistro = '#a4d21d;';
    $colorevaluacion = '#555555;';
    $colorsubasta = '#555555;';
    $colorfinancia = '#555555;';
} elseif ($estadoid == 12){
    $colorregistro = '#555555;';
    $colorevaluacion = '#a4d21d;';
    $colorsubasta = '#555555;';
    $colorfinancia = '#555555;';
} elseif ($estadoid == 13){
    if ($estadofinanciamientoid == 18){
        $colorregistro = '#555555;';
        $colorevaluacion = '#555555;';
        $colorsubasta = '#a4d21d;';
        $colorfinancia = '#555555;';
    }else{
        $colorregistro = '#555555;';
        $colorevaluacion = '#555555;';
        $colorsubasta = '#555555;';
        $colorfinancia = '#a4d21d;';
    }
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding: 5px;">
        Registro de Factura Electronica
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <!--================= LEYENDA ===================-->
    <div style="overflow:hidden;font-size: 10px;margin:1px auto;width:60%;">
        <ul style="overflow:hidden;list-style:none;">
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
    <!--==================== CUERPO DEL FORMULARIO ===================-->
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data" action="graba_factura.php">
        <input type="hidden" name="porcimpuestoventa" value="<?=$arrparametros['IMPUESTOVENTA']['valornum']?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
        <input type="hidden" name="tipoaccion" value="<?=$tipo?>">
        <input type="hidden" name="facturaid" value="<?=$facturaid?>">
        <input type="hidden" name="porc_financia" value="<?=$arrparametros['% FINANCIA']['valornum']?>">
        <input type="hidden" name="porc_dcto" value="<?=$arrparametros['DESCUENTO PROMEDIO']['valornum']?>">
        <input type="hidden" name="porc_comision" value="<?=$arrparametros['COMISION']['valornum']?>">
    <div class="frmtransaccion">
        <ul>
            <li style="color:#b30a1f;font-weight: bold;">[*] Requisito para regisro de factura</li>
            <li style="color:#b30a1f;font-weight: bold;">[**] Requisito para envio de factura</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Id Emisor:</li>
            <li style="margin:0px 3px;padding:0px;width:310px;">Emisor:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Nro Factura <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Moneda: <b style="color:#b30a1f;">[*]</b></li>
        </ul>
        <ul>
            <li><input type="text" name="numeroemisor" value="<?=$arremisor['identificacion']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="emisor" size="50" value="<?=$arremisor['nombre']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="nrofactura" value="<?=$nrofactura?>" class="frminput_text_off" readonly></li>
            <li>
                <select class="frminput_text_off" name="monedaid" disabled>
                    <? 
                    $arrmoneda = $objmaestros->get_tipos('MONEDA');
                    if ($monedaid == 0) echo '<option value="0" selected>Elija Moneda</option>';
                    
                    for ($i=0; $i<count($arrmoneda); $i++){
                        if ($monedaid == $arrmoneda[$i]['id'])
                            echo '<option value="'.$arrmoneda[$i]['id'].'" selected>'.$arrmoneda[$i]['nombre'].'</option>';
                        else
                            echo '<option value="'.$arrmoneda[$i]['id'].'">'.$arrmoneda[$i]['nombre'].'</option>';
                    }
                    ?>
                </select>
            </li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Id Cliente: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:310px;">Cliente: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:105px;">Fecha Emisi&oacute;n <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:110px;">Fecha Vencimiento <b style="color:#b30a1f;">[*]</b></li>
        </ul>
        <ul>
            <li><input type="text" name="numerocliente" value="<?=$numerocliente?>" class="frminput_text_off" readonly></li>
            <li><input id="cliente" type="text" name="cliente" readonly size="50" value="<?=$cliente?>" class="frminput_text_off"></li>
            <li><input type='date' name='femision' value='<?=$femision?>' min='1900-01-01' class="frminput_text_off" readonly></li>
            <li><input type='date' name='fvencimiento' value='<?=$fvencimiento?>' min='1900-01-01' class="frminput_text_off" readonly></li>
            <input type="hidden" name="numeroclienteold" value="<?=$numerocliente?>">
            <input type="hidden" name="clienteid" value="<?=$clienteid?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:105px;">SubTotal Venta:<b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Anticipos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Descuentos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Valor Venta: <b style="color:#b30a1f;">[*]</b></li>
        </ul>
        <ul>
            <?
            $subtotalp = number_format($subtotal,2,'.',',');
            $valorventap = number_format($valorventa,2,'.',',');
            
            echo '
            <li><input type="number" step="any" class="frminput_text_off" name="subtotal" style="width:100px;text-align:right;" value='.$subtotal.' readonly></li>';
            ?>
            <li><input type="number" class="frminput_text_off" name="anticipos" style="width:100px;text-align:right;" value="<?=$anticipos?>" readonly></li>
            <li><input type="number" name="descuentos" class="frminput_text_off" style="width:100px;text-align:right;" value="<?=$descuentos?>" readonly></li>
            <li><input type="text" name="valorventa_l" class="frminput_text_off" style="width:100px;text-align:right;" value="<?=$valorventap?>" readonly></li>
            <input type="hidden" name="valorventa" value="<?=$valorventa?>">
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:100px;">IVA: <b style="color:#b30a1f;">[*]</b></li> <!-- parametro y calculo -->
            <li style="margin:0px 3px;padding:0px;width:100px;">Otros Cargos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Otros Tributos: <b style="color:#b30a1f;">[*]</b></li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Importe Total:</li>
        </ul>
        <ul>
            <li><input type="text" name="impuestoventa_l" class="frminput_text_off" style="width:100px;text-align:right;" readonly value="<?=$impuestoventa?>"></li>
            <input type="hidden" name="impuestoventa" value="<?=$impuestoventa?>">
            <li><input type="number" name="otroscargos" class="frminput_text_off" style="width:100px;text-align:right;" value="<?=$otroscargos?>" readonly></li>
            <li><input type="number" name="otrostributos" class="frminput_text_off" style="width:100px;text-align:right;" value="<?=$otrostributos?>" readonly></li>
            <li><input type="text" name="total_l" class="frminput_text_off" style="width:100px;text-align:right;" readonly value="<?=$total?>"></li> <!-- calculo -->
            <input type="hidden" name="total" value="<?=$total?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:145px;">Tipo de financiamiento: <b style="color:#b30a1f;">[**]</b></li>
            <li style="margin:0px 3px;padding:0px;width:120px;">M&aacute;ximo descuento (%)</li>
        </ul>
        <ul>
            <li>
                <select name="tipofinanciamiento" <?=$readonly?> class="frminput_text">
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
            <li><input id="conmaxdescuento" type="checkbox" <?=$readonly?> class="frminput_text" name="conmaxdescuento" value="1" onchange="javascript:checkdescuento('conmaxdescuento','maxdescuento')"></li>
            <li><input id="maxdescuento" type="number" name="maxdescuento" class="frminput_text_off" value="0" min="0" max="100" readonly style="width:40px;text-align:right;background-color:#aaaaaa;"></li>
            <li style="color:#b30a1f;">Es el % m&aacute;ximo que esta dispuesto a aceptar que le descuenten si lo mantiene en cero queda abierto a lo que le propongan</li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul>
            <li style="font-size: 12px;font-weight: bold;">Archivos:</li>
        </ul>
        <ul>
            <li>Archivo de la factura:</li>
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
        <ul>
            <li style="font-size: 12px;font-weight: bold;">Resumen:</li>
        </ul>
        <?php
        $v_dcto_promedio = $arrparametros['DESCUENTO PROMEDIO']['valornum'] * 100;
        ?>
        <ul>
            <li style="width:100px;">Monto inicial que recibir&aacute; <b style="color:#b30a1f;">(A)</b>:</li> <!-- el 85% es un parametro de BD -->
            <li><?echo $v_simbolo_moneda;?></li>
            <li><input type="text" name="adelanto" class="frminput_text_off" value="<?=$v_inicial?>" readonly style="text-align:right;"></li>
            <li><span class="icon-plus"></span></li>
            <li style="width:100px;">Monto remanente aproximado que recibir&iacute;a <b style="color:#b30a1f;">(B)</b>:</li> <!-- parametros de BD -->
            <li><?echo $v_simbolo_moneda;?></li>
            <li><input type="text" name="remanente" class="frminput_text_off" value="<?=$v_remanente?>" readonly style="text-align:right;"></li>
            <li><span class="icon-calculator"></span></li>
            <li style="width:100px;">Financiamiento aproximado Total <b style="color:#b30a1f;">(C)</b>:</li>
            <li><?echo $v_simbolo_moneda;?></li>
            <li><input type="text" name="totalrecibido" class="frminput_text_off" value="<?=$v_total_finan?>" readonly style="text-align:right;"></li>
        </ul>
        <ul>
            <li><b style="color:#b30a1f;">(A)</b> El monto inicial que recibir&aacute; puede variar luego de la evaluaci&oacute;n de su factura<br>
                <b style="color:#b30a1f;">(B)</b> El calculo del monto remanente depende de la propuesta del inversionista y la fecha de pago de su cliente, 
                                                utilizaremos como ejemplo un promedio de tasa de descuento del <?echo $v_dcto_promedio;?>% anual (es un ejemplo promedio)<br>
                <b style="color:#b30a1f;">(C)</b> El calculo del monto total depende del remanente.<br>
                El descuento de su financiamiento depende del valor del riesgo de su factura, cuanto mejor valorada sea su factura el % de descuento ser&aacute; menor.
            </li><!-- el x% es un calculo -->
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul>
            <?
            if ($tipo != 'view') echo '<li class="botontransaccion" style="width:150px;"><a href=javascript:validar("grabar")><span class="icon-download"></span> Grabar</a></li>';
            if ($enviar == 'on') echo '<li class="botontransaccion" style="width:150px;"><a href=javascript:enviar()><span class="icon-upload"></span> Enviar</a></li>';
            else echo '<li class="botontransaccionoff" style="width:150px;"><span class="icon-upload"></span> Enviar</li>';
            if ($anular == 'on') echo '<li class="botontransaccionrojo" style="width:150px;"><a href=javascript:anular()><span class="icon-switch"></span> Anular</a></li>';
            if ($tipo == 'view')
                if ($_GET['ret'] == 'pan') echo '<li class="botontransaccionrojo" style="width:150px;"><a href="panel_emisor.php"><span class="icon-point-right"></span> Cerrar</a></li>';
                elseif ($_GET['ret'] == 'fac') echo '<li class="botontransaccionrojo" style="width:150px;"><a href="facturas_emisor.php"><span class="icon-point-right"></span> Cerrar</a></li>';
            ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>