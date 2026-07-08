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
$obj_factura = new factura;
$obj_mae = new maestros;
$objsubasta = new subasta;
$vobj_cuenta = new cuentas;

$arrsubasta = $objsubasta->get_subasta($_GET['subastaid']);
$arrobpago = $obj_mae->get_datos_obpag($arrsubasta['clienteid']);
$arrfacturariesgo = $obj_factura->riesgo_factura($arrsubasta['facturaid']);

$arrobpagoriesgo = $obj_mae->get_riesgo_obpago($arrsubasta['clienteid']);
$arrhistoria = $obj_mae->get_historianeg_obpago($arrsubasta['clienteid']);
$varr_parametros = $obj_mae->get_parametros();
$varr_cuentas = $vobj_cuenta->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
if ($_SESSION['user']['empresaid'] > 0) $varr_inversor = $obj_mae->get_datos_inversor($_SESSION['user']['empresaid']);
else $varr_inversor = $obj_mae->get_datos_inversor($_SESSION['user']['usuarioid']);

date_default_timezone_set($_SESSION['user']['zona_horaria']);

$hoy = date('Y-m-d');
$dhoy = new DateTime($hoy);
$dvenc = new DateTime($arrsubasta['fvencimiento']);
$dif = $dhoy->diff($dvenc);
$dias = $dif->days;

if (is_null($arrobpago['finicio'])) $finicio = 'EMPTY';
else $finicio = date('d-m-Y',strtotime($arrobpago['finicio']));

if ($_GET['pid'] != 0){     // YA EXISTE UNA PROPUESTA
    $arrposicion = $objsubasta->get_posicion($_GET['subastaid'],$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
    
    $monto = $arrposicion['monto'];
    $porcmonto = $arrposicion['representacion'] * 100;
    $tia = $arrposicion['tia'] * 100;
    $propuestaid = $arrposicion['id'];

    $v_tim = pow((1 + $arrposicion['tia']),(1/12)) - 1;
    $v_tid = pow((1 + $v_tim),(1/30)) - 1;
    $ganancia = $v_tid * $monto * $dias;

    if ($arrposicion['creador_usuarioid'] > 0 && $arrposicion['creador_usuarioid'] != $_SESSION['user']['usuarioid']){
        $objseg = new seguridad(); 
        $arr_datos_usuario = $objseg->get_datos_usuario($arrposicion['creador_usuarioid']);
    }

    if ($arrposicion['estado_id'] == 54) $v_alerta_preliminar = '<abbr title="La propuesta es preliminar porque no cuenta con saldo disponible, por lo que no sera considerada en la subasta hasta que cargue saldo disponible">
                                                                <i class="fa-solid fa-triangle-exclamation" style="font-size:18px;color:var(--color-amarillo);"></i></abbr>';
    else $v_alerta_preliminar = '';
} else{     // NO TIENE PROPUESTA
    $tia = 0;
    $propuestaid = 0;
    $porcmonto = 100;
    $monto = $arrsubasta['montofin'];
    $v_alerta_preliminar = '';
    $ganancia = 0;
}
//======== SALDO DEL INVERSOR
$v_saldo_disponible = 0;
$v_monto_enpropuestas = $objsubasta->monto_propuestas_subasta($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$arrsubasta['monedaid']);

for ($i=0; $i<count($varr_cuentas); $i++){
    if ($varr_cuentas[$i]['moneda_id'] == $arrsubasta['monedaid']) $v_saldo_disponible = $varr_cuentas[$i]['saldo_disponible'];
}

$v_saldo_disponible = $v_saldo_disponible - $v_monto_enpropuestas;

//========== TASA DE DESCUENTO MAXIMA
if ($arrsubasta['condesc_maximo'] > 0) $v_tea_maxima = $obj_factura->get_tea_maxima($arrsubasta['facturaid']) * 100;
else $v_tea_maxima = 0;

//==== GESTION DE LA TASA DE INTERES

$v_tea_minima = $varr_parametros['TEA MINIMA']['valornum'] * 100;
$v_tasa_usura = $varr_parametros['TASA DE USURA']['valornum'];
$v_proteccion_usura = $varr_parametros['PROTECCION USURA']['valornum'];
$v_tea_maxima_param = $varr_parametros['TEA MAXIMA INVERSOR AUTOREG']['valornum'] * 100;
$v_tasa_usura = $v_tasa_usura - $v_proteccion_usura;

if ($v_tea_maxima > 0){
    // el emisor coloco una tasa de descuento maxima
    if ($v_tea_minima > $v_tea_maxima) $v_tea_minima = $v_tea_maxima;
    
    if ($v_tasa_usura > 0){
        if ($v_tea_maxima > $v_tasa_usura){
            if ($v_tasa_usura < $v_tea_maxima_param) $v_tea_maxima_param = $v_tasa_usura;
        } else $v_tea_maxima_param = $v_tea_maxima;
    } else {
        if ($v_tea_maxima < $v_tea_maxima_param) $v_tea_maxima_param = $v_tea_maxima;
    }
} else {
    // sin restricciones de tasa por el emisor
    if ($v_tasa_usura > 0){
        if ($v_tasa_usura < $v_tea_maxima_param) $v_tea_maxima_param = $v_tasa_usura;
    }
}

if ($_GET['pid'] == 0) $tia = $v_tea_minima;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="propuestaid" id="propuestaid" value="<?=$propuestaid?>">
        <input type="hidden" name="factura_id" value="<?=$_GET['fid']?>">

        <input type="hidden" name="subasta_id" id="subasta_id" value="<?=$_GET['subastaid']?>">
        <input type="hidden" name="accion">
        <input type="hidden" name="montofin" value="<?=$arrsubasta['montofin']?>">
        <input type="hidden" name="tipofinancia" id="tipofinancia" value="<?=$arrsubasta['tipofinancia']?>">
        <input type="hidden" name="restringe_saldo" id="restringe_saldo" value="<?=$varr_parametros['RESTRINGIR A SALDO']['valornum']?>">
        <input type="hidden" name="porcmonto_old" id="porcmonto_old" value="<?=$porcmonto?>">
        <input type="hidden" name="tasa_usura" id="tasa_usura" value="<?=$varr_parametros['TASA DE USURA']['valornum']?>">
        <input type="hidden" name="proteccion_usura" id="proteccion_usura" value="<?=$varr_parametros['PROTECCION USURA']['valornum']?>">
        <input type="hidden" name="con_maxima_tasa" id="con_maxima_tasa" value="<?=$arrsubasta['condesc_maximo']?>">
        <input type="hidden" name="maxima_tasa" id="maxima_tasa" value="<?=$arrsubasta['desc_maximo']?>">
        <input type="hidden" name="tea_maxima" id="tea_maxima" value="<?=$v_tea_maxima?>">
        <input type="hidden" name="tea_minima" id="tea_minima" value="<?=$varr_parametros['TEA MINIMA']['valornum']?>">
        <input type="hidden" name="tea_maxima_param" id="tea_maxima_param" value="<?=$varr_parametros['TEA MAXIMA INVERSOR AUTOREG']['valornum']?>">
        
    <div class="frmtransaccion" style="font-size:12px;">
        <ul>
            <li style="margin-left:32px;font-weight: bold;width:300px;">PAGADOR</li>
            <li style="font-weight: bold;width:200px;">CALIFICACION FACTURA</li>
        </ul>
        <ul>
            <li><span class="icon-office" style="font-size:25px;color:#1F9A8E;"></span></li>
            <li style="width:300px;"><?php echo ' '.$arrsubasta['cliente'];?></li>
    <?php
    echo '  <li style="padding-left:5px;padding-right:5px;background-color:#'.$arrsubasta['riesgo_factura_color'].';color:#'.$arrsubasta['riesgo_factura_color_fuente'].';">['.$arrsubasta['riesgo_factura_califica'].'] '.$arrsubasta['riesgo_factura_nombre'].'</li>
        </ul>';

    if ($varr_parametros['VER CATEGORIA INVERSOR']['valornum'] == 1){
        $v_comision = $varr_inversor['comision']*100;
        echo '
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="font-weight:bold;">
            <li style="font-size:12px;margin-top:5px;">CATEGORIA INVERSOR: </li>
            <li style="font-size:12px;margin-top:5px;"><abbr title="La tasa de comisión para su categoria es de '.$v_comision.'%"><span class="icon-user-tie" style="font-size:12px;"> '.$varr_inversor['categoria'].'</abbr></li>
        </ul>';
    }
    ?>

        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="font-weight:bold;">
            <li><span class="icon-star-full" style="font-size:20px;color:var(--color-amarillo);"></span></li>
            <li style="font-size:16px;margin-top:5px;">MONTO DISPONIBLE PARA INVERTIR: </li>
            <li style="font-size:16px;margin-top:5px;"><?php echo $arrsubasta['simbolo_moneda'].' '.number_format($v_saldo_disponible,2,'.',',');?></li>
            <input type="hidden" name="saldo_disponible" id="saldo_disponible" value="<?=$v_saldo_disponible?>">
        </ul>

        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;width:120px;padding-left:5px;padding-right:5px;">ID FACTURA:</li>
            <li style="padding-left:5px;padding-right:5px;width:75px;"><?php echo $arrsubasta['facturaid'];?></li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;">MONTO A FINANCIAR:</li>
            <li style="padding-left:5px;padding-right:5px;"><?php echo number_format($arrsubasta['montofin'],2,'.',',').' '.$arrsubasta['moneda'];?></li>
            <input type="hidden" name="monto_financia" id="monto_financia" value="<?=$arrsubasta['montofin']?>">
        </ul>
        <ul>
            <li style="font-weight:bold;width:120px;padding-left:5px;padding-right:5px;">FECHA DE PAGO:</li>
            <li style="padding-left:5px;padding-right:5px;width:75px;"><?php echo date('d-m-Y',strtotime($arrsubasta['fvencimiento']));?></li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;">DIAS x VENCER:</li>
            <li style="padding-left:5px;padding-right:5px;"><?php echo $dias.' d&iacute;as';?></li>
            <input type="hidden" name="dias" value="<?=$dias?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;width:300px;padding-left:5px;padding-right:5px;">CALIFICACION DEL PAGADOR (SCORE):</li>
            <li style="padding-left:5px;padding-right:5px;background-color:#<?=$arrsubasta['colorscore']?>"><?php echo '[ '.$arrsubasta['calificacionscore'].' ] '.$arrsubasta['riesgoscore'];?></li>
        </ul>
        <ul>
            <li style="font-weight:bold;width:300px;padding-left:5px;padding-right:5px;">CALIFICACION DEL PAGADOR (FACTUREATE):</li>
            <li style="padding-left:5px;padding-right:5px;background-color:#<?=$arrsubasta['color']?>"><?php echo '[ '.$arrsubasta['calificacion'].' ] '.$arrsubasta['riesgo'];?></li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">PROPUESTA (<?php echo $arrsubasta['moneda'];?>): <?php echo $v_alerta_preliminar;?></li>
        </ul>
    <?php
    $varr_parametros = $obj_mae->get_parametros();

    if ($varr_parametros['CROWD']['valornum'] == 0){        // NO SE ADMITE EL CROWD OSEA SE COMPRA EL 100%
        echo '
        <ul>
            <li style="font-weight:bold;">MONTO A INVERTIR:</li>
            <li><input type="text" class="formulario_control" name="monto_label" value="'.number_format($monto,2,'.',',').'" style="width:100px;text-align:right;" readonly></li>
            <input type="hidden" name="monto" id="monto" value="'.$monto.'">
            <li style="font-weight:bold;">GANANCIA ESTIMADA:</li>
            <li><input type="text" class="frminput_text_off" name="ganancia" value="'.number_format($ganancia,2,'.',',').'" style="width:100px;text-align:right;" readonly></li>
        </ul>
        
        <ul>
            <li style="font-weight:bold;">% DE LA FACTURA:</li>
            <li><input type="range" id="porcmonto" name="porcmonto" min="10" max="100" step="10" value="'.$porcmonto.'" class="formulario_control" style="width:300px; accent-color:var(--color-oro);" onchange="cambia_porciento()" disabled></li>
            <li><label id="label_porcmonto" style="font-weight: bold; color: var(--color-oro); font-size:16px;">'.$porcmonto.'%</label></li>
        </ul>

        <ul>
            <li style="font-weight:bold;"><abbr title="Tasa de Interes Anual que desea ganar por su inversión">% Tasa Interes (TEA):</abbr></li>
            <li><input type="range" id="tia" name="tia" min="'.$v_tea_minima.'" max="'.$v_tea_maxima_param.'" step="0.5" value="'.$tia.'" class="formulario_control" style="width:300px; accent-color:var(--color-azul);" onchange=javascript:validapropuesta("tia")></li>
            <li><label id="label_tia" style="font-weight: bold; color: var(--color-azul); font-size:16px;">'.$tia.'%</label></li>
        </ul>';
    } else{
        echo '
        <ul>
            <li style="font-weight:bold;">MONTO A INVERTIR:</li>
            <li><input type="text" class="formulario_control" name="monto_label" value="'.number_format($monto,2,'.',',').'" style="width:100px;text-align:right;" readonly></li>
            <input type="hidden" name="monto" value="'.$monto.'" id="monto">

            <li style="font-weight:bold;">GANANCIA ESTIMADA:</li>
            <li><input type="text" class="frminput_text_off" name="ganancia" value="'.number_format($ganancia,2,'.',',').'" style="width:100px;text-align:right;" readonly></li>            
        </ul>

        <ul>
            <li style="font-weight:bold;">% DE LA FACTURA:     </li>
            <li><input type="range" id="porcmonto" name="porcmonto" min="10" max="100" step="10" value="'.$porcmonto.'" class="formulario_control" style="width:300px; accent-color:var(--color-oro);" onchange="cambia_porciento()"></li>
            <li><label id="label_porcmonto" style="font-weight: bold; color: var(--color-oro); font-size:16px;">'.$porcmonto.'%</label></li>
        </ul>

        <ul>
            <li style="font-weight:bold;"><abbr title="Tasa de Interes Anual que desea ganar por su inversión">% Tasa Interes (TEA):</abbr></li>
            <li><input type="range" id="tia" name="tia" min="'.$v_tea_minima.'" max="'.$v_tea_maxima_param.'" step="0.5" value="'.$tia.'" class="formulario_control" style="width:300px; accent-color:var(--color-azul);" onchange=javascript:validapropuesta("tia")></li>
            <li><label id="label_tia" style="font-weight: bold; color: var(--color-azul); font-size:16px;">'.$tia.'%</label></li>
        </ul>';
    }
    ?>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!--#########################################################
        ############# DETALLE DEL OP
        #############################################################-->
        <ul>
            <li>
                <details>
                    <summary style="font-size: 14px;font-weight: bold;"><i class="fa-solid fa-diagram-successor"></i> Detalle del Obligado al Pagador</summary>
                    <p style="font-size:12px;max-width:600px;">
                        <b style="color:#064677;">RNC:</b> <?php echo $arrobpago['identificacion'];?><br>
                        <b style="color:#064677;">Nombre:</b> <?php echo $arrobpago['nombre'];?><br>
                        <b style="color:#064677;">Sector Econ&oacute;mico:</b> <?php echo $arrobpago['sectoreconomico'];?><br>
                        <b style="color:#064677;">Descripci&oacute;n de la empresa:</b> <?php echo $arrobpago['actividad'];?><br>
                        <b style="color:#064677;">Fecha de fundaci&oacute;n:</b> <?php echo $finicio;?><br>
                        <b style="color:#064677;">Pagina Web:</b> <?php echo $arrobpago['paginaweb'];?>
                    </p>
                </details>
            </li>
        </ul>
        <ul>
            <li>
                <details>
                    <summary style="font-size: 14px;font-weight: bold;"><i class="fa-solid fa-diagram-successor"></i> Riesgos de la Factura y del Obligado al Pago</summary>
                    <?php
                    if ($arrfacturariesgo['riesgoid'] != 0){
                        echo '<p style="font-size:12px;max-width:600px;color:#064677;font-weight: bold;">Riesgo Factura</p>
                                <p style="font-size:12px;max-width:600px;">['.$arrfacturariesgo['calificacion_riesgo'].'] '.$arrfacturariesgo['nombre_riesgo'].'</p>
                                <p style="font-size:12px;max-width:600px;">'.$arrfacturariesgo['desc_riesgo'].'</p>';
                    }
                    if ($arrobpagoriesgo['riesgo_factid'] != 0){
                        echo '<p style="font-size:12px;max-width:600px;color:#064677;font-weight: bold;">Calificaci&oacute;n de Riesgo Factureate</p>
                                <p style="font-size:12px;max-width:600px;">['.$arrobpagoriesgo['crfact'].'] '.$arrobpagoriesgo['nrfact'].'</p>
                                <p style="font-size:12px;max-width:600px;">'.$arrobpagoriesgo['desc_riesgofact'].'</p>';
                    }

                    echo '<p style="font-size:12px;max-width:600px;color:#064677;font-weight: bold;">Calificación de Empresa Score de Riesgo</p>
                            <p style="font-size:12px;max-width:600px;font-weight: bold;>Nombre de Calificadora: '.$arrobpagoriesgo['nombrescore'].'</p>
                            <p style="font-size:12px;max-width:600px;">['.$arrobpagoriesgo['crscore'].'] '.$arrobpagoriesgo['nrscore'].'</p>
                            <p style="font-size:12px;max-width:600px;">'.$arrobpagoriesgo['desc_riesgoscore'].'</p>';
                    ?>
                </details>
            </li>
        </ul>
        <ul>
            <li>
                <details>
                    <summary style="font-size: 14px;font-weight: bold;"><i class="fa-solid fa-diagram-successor"></i> Historial de Negociaci&oacute;n</summary>
                    <?php
                    if ($arrhistoria['noperaciones'] > 0){
                        echo '<p style="font-size:12px;max-width:600px;">El obligado al pago cuenta con el siguiente historial de negociaci&oacute;n:</p>
                            <p style="font-size:12px;max-width:600px;"><b>Nro TOTAL de operaciones:</b>'.$arrhistoria['noperaciones'].'<br>
                                <b>Nro de operaciones en proceso:</b>'.$arrhistoria['enproceso'].'<br>
                                <b>Nro de operaciones pagadas a tiempo:</b>'.$arrhistoria['pagadaontime'].'<br>
                                <b>Nro de operaciones pagadas con retrazo:</b>'.$arrhistoria['pagadadelay'].'<br>
                                <b>Nro de operaciones en cobranza:</b>'.$arrhistoria['cobranza'].'
                            </p>
                            <p style="font-size:12px;max-width:600px;">Historial en montos:</p>';
                        if ($arrhistoria['montofinanciadosol'] > 0) echo '<p style="font-size:12px;max-width:600px;">Financiado S/. : '.number_format($arrhistoria['montofinanciadosol'],2,'.',',').'</p>';
                        if ($arrhistoria['montofinanciadodol'] > 0) echo '<p style="font-size:12px;max-width:600px;">Financiado US$ : '.number_format($arrhistoria['montofinanciadodol'],2,'.',',').'</p>';
                        if ($arrhistoria['montofinanciadoeur'] > 0) echo '<p style="font-size:12px;max-width:600px;">Financiado EUR : '.number_format($arrhistoria['montofinanciadoeur'],2,'.',',').'</p>';
                        if ($arrhistoria['montocobranzasol'] > 0) echo '<p style="font-size:12px;max-width:600px;">En Cobranza S/. : '.number_format($arrhistoria['montocobranzasol'],2,'.',',').'</p>';
                        if ($arrhistoria['montocobranzadol'] > 0) echo '<p style="font-size:12px;max-width:600px;">En Cobranza US$ : '.number_format($arrhistoria['montocobranzadol'],2,'.',',').'</p>';
                        if ($arrhistoria['montocobranzaeur'] > 0) echo '<p style="font-size:12px;max-width:600px;">En Cobranza EUR : '.number_format($arrhistoria['montocobranzaeur'],2,'.',',').'</p>';
                    } else echo '<p style="font-size:12px;max-width:600px;">El obligado al pago no tiene historial de negociaci&oacute;n a&uacute;n</p>';
                    ?>
                </details>
            </li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
        <ul style="margin-top:10px;">
    <?php
        if ($propuestaid != 0){     // YA EXISTE LA PROPUESTA
    ?>
            <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="Grabar()">
                <i class="fa-solid fa-floppy-disk"></i> Modificar</button>
            <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;" onclick="Anular()">
                <span class="icon-point-down"></span> Anular</button>
    <?php
        } else {    // PROPUESTA NUEVA
    ?>
            <button type="button" id="btn_ofertar" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="Ofertar()">
                <i class="fa-solid fa-money-bill"></i> Ofertar</button>
    <?php
        }
    ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <!-- FUNCIONES DE LOS BOTONES -->

    <!-- div para el spinner de loadgin -->
    <div id="loadingModal" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-title">Procesando </div>
            <div class="loading-subtitle">............................</div>
        </div>
    </div>
    <!-- fin del spinner loading -->

    <script>
        //==== CALCULO DE LA GANANCIA 
        document.addEventListener("DOMContentLoaded", validapropuesta('tia'));

        function Ofertar(){
            var subasta_id = document.frm_modal.subasta_id.value;
            var propuestaid = document.frm_modal.propuestaid.value;
            var porcmonto = document.frm_modal.porcmonto.value;
            var monto = Number(document.frm_modal.monto.value);
            var tia = Number(document.frm_modal.tia.value);
            var saldo_disponible = Number(document.frm_modal.saldo_disponible.value);
            
            var tipofinancia = Number($('#tipofinancia').val());
            var restringe_saldo = Number($('#restringe_saldo').val());
            var tasa_usura = Number($('#tasa_usura').val());
            var proteccion_usura = Number($('#proteccion_usura').val());

            var procede = 0;
            
            if (tasa_usura > 0){
                tasa_usura = tasa_usura - proteccion_usura;
            }

            if (monto > saldo_disponible && restringe_saldo > 0) alert('Usted no cuenta con saldo disponible en su cuenta, por lo que se guardara la propuesta PRELIMINAR, recargue su cuenta para que su propuesta este en firme');
                
            if (monto <= 0){ 
                alert('Debe ingresar un monto de inversión valido');
                $('#monto').val(0);
            } else{
                if (tia <= 0){ 
                    alert('Debe ingresar una TEA valida');
                    $('#tia').val(0);
                } else{
                    var btn_ofertar = document.getElementById('btn_ofertar');

                    btn_ofertar.disabled = true;

                    var formData = new FormData();
                    
                    formData.append('subasta_id', subasta_id)
                    formData.append('propuestaid', propuestaid)
                    formData.append('porcmonto', porcmonto)
                    formData.append('tia', tia)
                    formData.append('saldo_disponible', saldo_disponible)
                    formData.append('monto', monto)
                    
                    $.ajax({
                        url:"propuesta_detalle_proceso.php",
                        type:'post',
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false,
                        success:function(data){
                            if (propuestaid == 0) alert('Se generó la posición de su inversión');
                            else alert('Se actualizó la posición de su inversión');

                            refresh_page();
                        }
                    });
                }
            }
        }

        function Grabar(){
            var monto = Number($('#monto').val());
            var monto_financia = Number($('#monto_financia').val());
            var tia = Number($('#tia').val());
            var tipofinancia = Number($('#tipofinancia').val());
            var saldo_disponible = Number($('#saldo_disponible').val());
            var restringe_saldo = Number($('#restringe_saldo').val());
            
            var subasta_id = $('#subasta_id').val();
            var propuestaid = $('#propuestaid').val();
            var porcmonto = Number($('#porcmonto').val());
            var retorno = $('#retorno').val();
            var accion = 'grabar';
            var faltante;
            var porcmonto_old = Number($('#porcmonto_old').val());
            
            if (porcmonto_old != porcmonto){
                if (porcmonto_old > porcmonto) faltante = 0;
                else{
                    faltante = Number(((porcmonto-porcmonto_old)/100) * monto_financia);

                    if (faltante <= saldo_disponible) faltante = 0;
                }
            } else faltante = 0;

            if (faltante > 0 && restringe_saldo > 0) alert('Usted no cuenta con saldo disponible en su cuenta para poder colocar una oferta, recargue su cuenta y vuelva a intentarlo');
            else{
                if (monto <= 0){ 
                    alert('Debe ingresar un monto de inversión valido');
                    $('#monto').val(0);
                } else{
                    if (tia <= 0){ 
                        alert('Debe ingresar una TEA valida');
                        $('#tia').val(0);
                    } else{
                        $.ajax({
                            url:"propuesta_detalle_proceso.php",
                            type:'post',
                            data:{
                                "subasta_id":subasta_id,
                                "propuestaid":propuestaid,
                                "porcmonto":porcmonto,
                                "retorno":retorno,
                                "monto":monto,
                                "tia":tia,
                                "accion":accion
                            },
                            success:function(data,status){
                                $('#PropuestaDetalle').fadeIn(1000).html(data);
                                $('#PropuestaDetalle').modal('hide');
                                refresh_page();
                            }
                        });
                    }
                }
            }
        }

        function Anular(){
            var propuestaid = $('#propuestaid').val();
            var accion = 'anular';
            var subasta_id = $('#subasta_id').val();
            var rpta = confirm("Esta seguro de anular su propuesta?");
            
            if (rpta == true){
                $.ajax({
                    url:"propuesta_detalle_proceso.php",
                    type:'post',
                    data:{
                        "propuestaid":propuestaid,
                        "subasta_id":subasta_id,
                        "accion":accion
                    },
                    success:function(data,status){
                        $('#PropuestaDetalle').fadeIn(1000).html(data);
                        $('#PropuestaDetalle').modal('hide');
                        refresh_page();
                    }
                });
            }
        }

        function validapropuesta(accion){
            if (accion == 'tia'){
                var tia = Number(document.frm_modal.tia.value);
                var monto = Number(document.frm_modal.monto.value);
                var dias = Number(document.frm_modal.dias.value);
                var tasa_usura = Number(document.frm_modal.tasa_usura.value);
                var proteccion_usura = Number(document.frm_modal.proteccion_usura.value);
                var conmaxima_tasa = document.frm_modal.con_maxima_tasa.value;
                var maxima_tasa = Number(document.frm_modal.maxima_tasa.value);

                let label_tia = document.getElementById("label_tia");
                label_tia.innerHTML = tia+"%";
                
                /*if (tasa_usura > 0){
                    tasa_usura = tasa_usura - proteccion_usura;
                }

                if (tia < 0){
                    alert('El valor de la TEA no puede ser menor a CERO, ingrese in valor correcto');
                    document.frm_modal.tia.value = 0;
                    tia = 0;
                }

                if (conmaxima_tasa > 0){
                    var tea_maxima = Number(document.frm_modal.tea_maxima.value);

                    if (tia > tea_maxima){
                        alert('La operación tiene una restricción de tasa, intente una tasa menor');
                        document.frm_modal.tia.value = 0;
                        tia = 0;
                    }
                }*/

                /*if (tia > tasa_usura && tasa_usura > 0){
                    alert('El rango de proteccion que utilizamos para la TASA DE USURA ha sido superado por la TEA, por favor intente una tasa menor');
                    document.frm_modal.tia.value = 0;
                    tia = 0;
                }*/
                
                var tim = Number(Math.pow((1 + (tia / 100)),Number(1/12)) - 1);
                var tid = Number(Math.pow((1 + tim),Number(1/30)) - 1);
                var ganancia = Number(tid * monto * dias);
                //document.frm_modal.ganancia.value = ganancia.toFixed(2);
                document.frm_modal.ganancia.value = ganancia.toLocaleString('en-US', {style: 'decimal', maximumFractionDigits: 2});
            }
        }

        function cambia_porciento(){
            var porciento = Number(document.frm_modal.porcmonto.value);
            var monto_financia = Number(document.frm_modal.monto_financia.value);
            var monto_invertir = Number(Number(porciento/100)*monto_financia);
            
            let label_porc = document.getElementById("label_porcmonto");
            label_porc.innerHTML = porciento+"%";

            document.frm_modal.monto.value = monto_invertir;

            monto_invertir = monto_invertir.toLocaleString('en-US',{minimumFractionDigits:2, maximumFractionDigits:2});
            document.frm_modal.monto_label.value = monto_invertir;

            ///////////////////////
            var tia = Number(document.frm_modal.tia.value);
            var monto = Number(document.frm_modal.monto.value);
            var dias = Number(document.frm_modal.dias.value);

            var tim = Number(Math.pow((1 + (tia / 100)),Number(1/12)) - 1);
            var tid = Number(Math.pow((1 + tim),Number(1/30)) - 1);
            var ganancia = Number(tid * monto * dias);

            document.frm_modal.ganancia.value = ganancia.toLocaleString('en-US', {style: 'decimal', maximumFractionDigits: 2});
        }

        //==== FUNCIONES DEL SPINNER LOADING

        function mostrarLoading() {
            document.getElementById("loadingModal").style.display = "flex";
        }

        function ocultarLoading() {
            document.getElementById("loadingModal").style.display = "none";
        }

        //======================================
    </script>
</BODY>
</HTML>