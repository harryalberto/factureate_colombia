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
<?php
    require("../lib/head.php");
    $acceso = 'FACTFACTU';
    require("../lib/valida-acceso.php");
?>
</HEAD>

<?php
/*===================================================
===== LOGICA
=====================================================*/
$objfactura = new factura;
$objmaestro = new maestros;

$facturaid = $_GET['facturaid'];
//if ($_GET['ret'] == 'facxe') $v_retorno = 'facturas_xestado.php?estadoid='.$_GET['eid'];
//if (isset($_POST['retorno'])) $v_retorno = $_POST['retorno'];

$arrfactura = $objfactura->get_datos_factura($facturaid);
$varr_fact_libre = $objmaestro->get_parametro_detalle(56);

$femision_t = strtotime($arrfactura['femision']);
$fvencimiento_t = strtotime($arrfactura['fvencimiento']);
$femision = date('d-m-Y',$femision_t);
$fvencimiento = date('d-m-Y',$fvencimiento_t);
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);

    $date1_t = date('Y-m-d');
    $date1 = new DateTime($date1_t);
    $date2 = new DateTime($arrfactura['fvencimiento']);
    $diasvencimiento = $date1->diff($date2);
    $v_monto_adelanto = $arrfactura['porciento_adelanto'] * $arrfactura['total'];
?>

    <form name='frm_factura_modal' method='post' id='frm_factura_modal' enctype="multipart/form-data">
        <input type="hidden" name="facturaid" value="<?=$facturaid?>">
        <input type="hidden" name="clienteid" value="<?=$arrfactura['clienteid']?>">
        <input type="hidden" name="emisorid" value="<?=$arrfactura['emisorid']?>">
        <input type="hidden" name="accion" value="">
        <input type="hidden" name="u_envio_id" value="<?=$arrfactura['u_envio_id']?>">
        <input type="hidden" name="factura_libre" id="factura_libre" value="<?=$varr_fact_libre['valornum']?>">
        <input type="hidden" name="estado_id" id="estado_id" value="<?=$arrfactura['estado']?>">

        <div id="principal" style="padding-left: 10px;height: 95%;">
            <div class="contenedor_formulario">
                <!-- datos de factura -->
                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_row" style="width: 100px;">
                        <label for="factura_id">ID OPERACION:</label>
                        <input type="text" class="formulario_control" id="factura_id" name="factura_id" value="<?=$facturaid?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="estado">ESTADO</label>
                        <input type="text" class="formulario_control" id="estado" name="estado" value="<?=$arrfactura['facestado']?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="factura_nro">FACTURA NRO</label>
                        <input type="text" class="formulario_control" id="factura_nro" name="factura_nro" value="<?=$arrfactura['factura']?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="femision">F EMISION</label>
                        <input type="text" class="formulario_control" id="femision" name="femision" value="<?=$femision?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="fvencimiento">F VENCIMIENTO</label>
                        <input type="text" class="formulario_control" id="fvencimiento" name="fvencimiento" value="<?=$fvencimiento?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="diasvencimiento">DIAS X VENCER</label>
                        <input type="text" class="formulario_control" id="diasvencimiento" name="diasvencimiento" value="<?=$diasvencimiento->days?>" readonly>
                    </div>
                </div>

                <!-- datos emidor -->
                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="emisornro">RNC EMISOR</label>
                        <input type="text" class="formulario_control" id="emisornro" name="emisornro" value="<?=$arrfactura['emisornro']?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:400px;">
                        <label for="emisor">EMISOR</label>
                        <input type="text" class="formulario_control" id="emisor" name="emisor" value="<?=$arrfactura['emisor']?>" readonly>
                    </div>
                </div>

                <!-- datos del pagador -->
                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="clientenro">RNC CLIENTE</label>
                        <input type="text" class="formulario_control" id="clientenro" name="clientenro" value="<?=$arrfactura['identificacion']?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:400px;">
                        <label for="cliente">CLIENTE</label>
                        <input type="text" class="formulario_control" id="cliente" name="cliente" value="<?=$arrfactura['cliente']?>" readonly>
                    </div>
                </div>

                <!-- montos de la factura -->
                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="moneda">MONEDA</label>
                        <input type="text" class="formulario_control" id="moneda" name="moneda" value="<?=$arrfactura['moneda']?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="subtotal">SUBTOTAL</label>
                        <input type="text" class="formulario_control" id="subtotal" name="subtotal" value="<?=number_format($arrfactura['subtotal'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="anticipos">ANTICIPOS</label>
                        <input type="text" class="formulario_control" id="anticipos" name="anticipos" value="<?=number_format($arrfactura['anticipos'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="descuentos">DESCUENTOS</label>
                        <input type="text" class="formulario_control" id="descuentos" name="descuentos" value="<?=number_format($arrfactura['descuentos'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="valor_venta">VALOR VENTA</label>
                        <input type="text" class="formulario_control" id="valor_venta" name="valor_venta" value="<?=number_format($arrfactura['valorventa'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                </div>

                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="impuestoventa">ITBIS</label>
                        <input type="text" class="formulario_control" id="impuestoventa" name="impuestoventa" value="<?=number_format($arrfactura['impuestoventa'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="otroscargos">OTROS CARGOS</label>
                        <input type="text" class="formulario_control" id="otroscargos" name="otroscargos" value="<?=number_format($arrfactura['otroscargos'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="otrostributos">OTROS TRIBUTOS</label>
                        <input type="text" class="formulario_control" id="otrostributos" name="otrostributos" value="<?=number_format($arrfactura['otrostributos'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="total">TOTAL</label>
                        <input type="text" class="formulario_control" id="total" name="total" value="<?=number_format($arrfactura['total'],2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="monto_adelanto">A FINANCIAR</label>
                        <input type="text" class="formulario_control" id="monto_adelanto" name="monto_adelanto" value="<?=number_format($v_monto_adelanto,2,'.',',')?>" style="text-align: right;" readonly>
                    </div>
                </div>

                <!-- datos adicionales de la factura-->
<?php
        //verifica archivos
        if (is_null($arrfactura['xmlpath']) || $arrfactura['xmlpath'] == '' || $arrfactura['xmlpath'] == '[SIN XML]') $msgxml = 'SIN XML';
        else $msgxml = 'CON XML';
        //verifica descuento maximo
        if ($arrfactura['condescuentomaximo'] == 0) $v_limite = 'SIN LIMITE';
        else{
            $descuento = $arrfactura['descuentomaximo'];
            $v_limite = number_format($descuento,2,'.',',').' %';
        }
?>

                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="txt_xml">XML FACTURA</label>
                        <input type="text" class="formulario_control" id="txt_xml" name="txt_xml" value="<?=$msgxml?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="pdf">PDF FACTURA</label>
                        <label><a href="<?=$arrfactura['facturapath']?>" target="_blank" style="font-size: 12px;font-weight: bold;width:130px;"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a></label>
                    </div>
                    <div class="formulario_grupo_row" style="width:150px;">
                        <label for="tfinanciamiento">TIPO FINANCIAMIENTO</label>
                        <input type="text" class="formulario_control" id="tfinanciamiento" name="tfinanciamiento" value="<?=$arrfactura['tfinancia']?>" readonly>
                    </div>
                    <div class="formulario_grupo_row" style="width:100px;">
                        <label for="limite">LIMITE TASA DCTO</label>
                        <input type="text" class="formulario_control" id="limite" name="limite" value="<?=$v_limite?>" readonly>
                    </div>
                </div>

<?php
    $varr_evaluacion = $objfactura->get_evaluacion_factura($facturaid);
    $arr_riesgo_obp = $objmaestro->get_riesgo_obpago($arrfactura['clienteid']);
    $arrestadocli = $objmaestro->get_estado_cliente($arrfactura['clienteid']);
    $v_estado_emp = $objmaestro->calcula_estado_empresa($arrfactura['clienteid']);
?>

                <!-- ZONA EVALUACION -->
<?php
    $v_estado_evaluacion_emp = 0;
    $v_estado_evaluacion_req = 0;

    $v_estado_evaluacion_emp = $v_estado_emp;   // 0 es ok / 1 o 2 problema de empresa solo para los que no estan validados / 3 pendiente de riesgo

    if ($arrestadocli['estadoid'] == 1 || $arrestadocli['estadoid'] == 2){   // 1 registrada / 2 en validacion
        if ($v_estado_emp != 3){    //  2 es problema legal // 1 es problema operativo  // 0 sin problemas
            $v_bg_score = $arr_riesgo_obp['color_riesgo_score'];
            $v_color_score = $arr_riesgo_obp['colorfuente_riesgo_score'];
            $v_msg_score = '['.$arr_riesgo_obp['crscore'].'] '.$arr_riesgo_obp['nrscore'];
            $v_bg_factureate = $arr_riesgo_obp['color_riesgo_fact'];
            $v_color_factureate = $arr_riesgo_obp['colorfuente_riesgo_fact'];
            $v_msg_factureate = '['.$arr_riesgo_obp['crfact'].'] '.$arr_riesgo_obp['nrfact'];
            $v_result = '';
            $v_rcliente_atencion = '';
        } else {                    // 3 problema de riesgos
            $v_bg_score = 'FFB500';
            $v_color_score = '000000';
            $v_msg_score = 'PENDIENTE';
            $v_bg_factureate = 'FFB500';
            $v_color_factureate = '000000';
            $v_msg_factureate = 'PENDIENTE';
            $v_result = '<i class="fa-solid fa-triangle-exclamation" style="color: var(--color-rojo)"></i>';
            $v_rcliente_atencion = '<i class="fa-solid fa-triangle-exclamation" style="color: var(--color-rojo)"></i> ATENDER FINANZAS';
        }
    } else {    // validada 3
        $v_bg_score = $arr_riesgo_obp['color_riesgo_score'];
        $v_color_score = $arr_riesgo_obp['colorfuente_riesgo_score'];
        $v_msg_score = '['.$arr_riesgo_obp['crscore'].'] '.$arr_riesgo_obp['nrscore'];
        $v_bg_factureate = $arr_riesgo_obp['color_riesgo_fact'];
        $v_color_factureate = $arr_riesgo_obp['colorfuente_riesgo_fact'];
        $v_msg_factureate = '['.$arr_riesgo_obp['crfact'].'] '.$arr_riesgo_obp['nrfact'];

        if ($arr_riesgo_obp['count'] == 0){
            $v_result = '<i class="fa-solid fa-triangle-exclamation" style="color: var(--color-rojo)"></i>';
            $v_rcliente_atencion = '<i class="fa-solid fa-triangle-exclamation" style="color: var(--color-rojo)"></i> ATENDER FINANZAS';
        } else {
            $v_result = '';
            $v_rcliente_atencion = '';
        }
    }
?>
                <div class="contenedor_formulario_column">
                    <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;"><i class="fa-solid fa-dna"></i> EVALUACION:</p>
                </div>

                <input type="hidden" name="estadoclienteid" value="<?=$arrestadocli['estadoid']?>">
                <input type=hidden name=rfactureate_id value="<?=$arr_riesgo_obp["riesgo_factid"]?>">
                <input type=hidden name=rfactureate_desc value="<?=$arr_riesgo_obp["desc_riesgofact"]?>">
                <input type="hidden" name="rscore_id" value="<?=$arr_riesgo_obp['riesgo_scoreid']?>">
                <input type="hidden" name="rscore_desc" value="<?=$arr_riesgo_obp['desc_riesgoscore']?>">

                <div class="contenedor_formulario_column">
                    <table class="tabla_resize">
                        <thead>
                            <tr>
                                <th scope="col">ANALISIS</th>
                                <th scope="col">CALIFICACION</th>
                                <th scope="col">RESULT</th>
                                <th scope="col">ATENCION</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    if ($v_estado_evaluacion_emp == 1){
        // ==== estado evaluacion es 1 es problema operativo (tamano empresa, actividad, act desc)
        echo '              <tr>
                                <td data-label="ANALISIS">Evaluacion de empresa OP</td>
                                <td data-label="CALIFICACION" style="background-color:var(--color-rojo);color:#fff;">PENDIENTE VALIDAR</td>
                                <td data-label="RESULT" style="color:var(--color-rojo);"><i class="fa-solid fa-triangle-exclamation"></i></td>
                                <td data-label="ATENCION" style="color:var(--color-rojo);">OPERACIONES <i class="fa-solid fa-triangle-exclamation"></i></td>
                            </tr>';
    } elseif ($v_estado_evaluacion_emp == 2) {
        // ==== estado evaluacion 2 es problema legal (accionistas / informe)
        echo '              <tr>
                                <td data-label="ANALISIS">Evaluacion de empresa LEG</td>
                                <td data-label="CALIFICACION" style="background-color:var(--color-rojo);color:#fff;">PENDIENTE VALIDAR</td>
                                <td data-label="RESULT" style="color:var(--color-rojo);"><i class="fa-solid fa-triangle-exclamation"></i></td>
                                <td data-label="ATENCION" style="color:var(--color-rojo);">LEGAL <i class="fa-solid fa-triangle-exclamation"></i></td>
                            </tr>';
    } else {
        // EVALUACION DE RIESGOS DEL OP
        echo '              <tr>
                                <td data-label="ANALISIS">Riesgo BURO (Cliente)</td>
                                <td data-label="CALIFICACION" style="background-color:#'.$v_bg_score.';color:#'.$v_color_score.';">'.$v_msg_score.'</td>
                                <td data-label="RESULT">'.$v_result.'</td>
                                <td data-label="ATENCION">'.$v_rcliente_atencion.'</td>
                            </tr>
                            <tr>
                                <td data-label="ANALISIS">Riesgo FACTUREATE (Cliente)</td>
                                <td data-label="CALIFICACION" style="background-color:#'.$v_bg_factureate.';color:#'.$v_color_factureate.';">'.$v_msg_factureate.'</td>
                                <td data-label="RESULT">'.$v_result.'</td>
                                <td data-label="ATENCION">'.$v_rcliente_atencion.'</td>
                            </tr>';
    }

    // EVALUACION DE PARAMETROS DE LA FACTURA
    $v_estado_evaluacion_req = 0;

    for ($i=0; $i<count($varr_evaluacion); $i++){
        //if ($varr_evaluacion[$i]['evaluacion_tipo'] == 78){     //MONTO DE LA FACTURA
            if ($varr_evaluacion[$i]['calificacion'] == 1){     // TODO OK
                $v_calificacion = 'CUMPLE';
                $v_bg_califica = 'A4D21D';
                $v_color_califica = '000000';
                $v_resultado = '<span class="icon-checkmark"></span>';
                $v_color_result = 'A4D21D';
                $v_atencion = '';
            } elseif ($varr_evaluacion[$i]['calificacion'] == 0){   // RECHAZADO
                $v_calificacion = 'NO CUMPLE';
                $v_bg_califica = 'A93032';
                $v_color_califica = 'ffffff';
                $v_resultado = '<span class="icon-cross"></span>';
                $v_color_result = 'A93032';
                $v_atencion = '';
                $v_estado_evaluacion_req = 1;
            } elseif ($varr_evaluacion[$i]['calificacion'] == 3){   //PENDIENTE DE ATENDER
                $v_calificacion = 'PENDIENTE';
                $v_bg_califica = 'FFB500';
                $v_color_califica = '000000';
                $v_resultado = '<span class="icon-warning"></span>';
                $v_color_result = 'FFB500';
                $v_atencion = 'ATENDER POR FINANZAS <span class="icon-warning"></span>';
                $v_estado_evaluacion_req = 1;
            }
        //}

        echo '
                            <tr>
                                <td data-label="ANALISIS">'.$varr_evaluacion[$i]['evaluacion_tipo_nom'].'</td>
                                <td data-label="CALIFICACION" style="background-color:#'.$v_bg_califica.';color:#'.$v_color_califica.';">'.$v_calificacion.'</td>
                                <td data-label="RESULT" style="color:#'.$v_color_result.';">'.$v_resultado.'</td>
                                <td data-label="ATENCION" style="color:#;">'.$v_atencion.'</td>
                            </tr>';
    }
?>
                        </tbody>
                    </table>
                </div>

<?php
    if ($varr_fact_libre['valornum'] > 0 && $arrfactura['estado'] == 12){
        // APLICA PARA COLOMBIA
        echo '
                <div class="contenedor_formulario_column">
                    <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;">COMPROBANTE DE TITULO LIBRE PARA NEGOCIAR:</p>
                </div>

                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_column" style="width: 400px;">
                        <label for="file_factura_libre">Comprobante Titulo Libre:</label>
                        <input type="file" name="file_factura_libre" id="file_factura_libre" class="formulario_control">
                    </div>
                </div>';
    }

    /*==============================================
    ================= ZONA DE RECHAZO */

    if ($arrfactura['estado'] == 12){
        // FACTURA EN SOLICITUD
?>
                <div class="contenedor_formulario_column">
                    <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;">MOTIVO DE RECHAZO:</p>
                </div>
                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_column" style="width: 400px;">
                        <label for="rechazofile">Informe:</label>
                        <input type="file" name="rechazofile" id="rechazofile" class="formulario_control">
                    </div>
                </div>

                <div class="contenedor_formulario_column">
                    <div class="formulario_grupo_column" style="width: 600px;">
                        <label for="rechazo">Justifica Rechazo:</label>
                        <textarea name="rechazo" id="rechazo" cols="100" rows="5" class="formulario_control"></textarea>
                    </div>
                </div>
<?php
    }

    /*==============================================
    ===================== BOTONERA */
    echo '      ';

    $v_aprobar = "'aprobar'";
    $v_rechazar = "'rechazar'";

    if ($arrfactura['estado'] == 12 && $v_estado_evaluacion_emp == 0 && $v_estado_evaluacion_req == 0){
        echo '  <div class="contenedor_formulario_column">
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;margin-right: 5px;" type="button" class="btn btn-primary" id="boton_aprobar" onclick="acciones('.$v_aprobar.')"><i class="fa-solid fa-check-to-slot"></i> Aprobar</button>
                    <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_aprobar" onclick="acciones('.$v_rechazar.')"><i class="fa-solid fa-circle-xmark"></i> Rechazar</button>
                </div>';
    }
?>

            </div>  <!-- DIV FORMULARIO -->
        </div>  <!-- DIV PRINCIPAL -->
    </form>

    <!-- div para el spinner de loadgin -->
    <div id="loadingModal" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-title">Procesando </div>
            <div class="loading-subtitle">............................</div>
        </div>
    </div>
    <!-- fin del spinner loading -->

    <!--================================================
    ======== ZONA JS
    ==================================================-->
    <script type="text/javascript">
        function acciones(accion){
            var estadocliente;
            var v_procede = 0;

            if (accion == 'aprobar'){
                estadocliente = document.frm_factura_modal.estadoclienteid.value;
                var v_estado_factura = $('#estado_id').val();
                var v_factura_libre = $('#factura_libre').val();
                
                if (estadocliente == 3 || estadocliente == 22){
                    if (v_estado_factura == 12 && v_factura_libre == 1){
                        var v_file_libre = $('#file_factura_libre').val();
                        
                        if (v_file_libre != ''){
                            document.frm_factura_modal.accion.value = accion;
                            //document.frm.action = 'factura_detalle_proceso.php';
                            //document.frm.submit();
                            v_procede = 1;
                        } else alert('Debe adjuntar el comprobante de libertad de negociacion de la factura');
                    } else {
                        document.frm_factura_modal.accion.value = accion;
                        //document.frm.action = 'factura_detalle_proceso.php';
                        //document.frm.submit();
                        v_procede = 1;
                    }
                } else alert('El cliente debe estar revisado');
            } else{
                if (accion == 'grabar'){
                    document.frm_factura_modal.accion.value = accion;
                    //document.frm.action = 'factura_detalle_proceso.php';
                    //document.frm.submit();
                    v_procede = 1;
                } else{
                    if (accion == 'anotarencuenta'){
                        if (document.frm_factura_modal.acfile.value == '' && document.frm_factura_modal.acpath.value == '') alert('Debe agregar el archivo de anotacion en cuenta');
                        else{
                            document.frm_factura_modal.accion.value = accion;
                            //document.frm.action = 'factura_detalle_proceso.php';
                            //document.frm.submit();
                            v_procede = 1;
                        }
                    } else{
                        if (accion == 'rechazar'){
                            if (document.frm_factura_modal.rechazo.value == '') alert('Debe ingresar un motivo de rechazo');
                            else{
                                document.frm_factura_modal.accion.value = accion;
                                //document.frm.action = 'factura_detalle_proceso.php';
                                //document.frm.submit();
                                v_procede = 1;
                            }
                        } else location.href = "facturas_xestado.php";
                    }
                }
            }

            if (v_procede == 1){
                var formData = new FormData(document.getElementById("frm_factura_modal"));

                //Llamada al spinner
                mostrarLoading();

                $.ajax({
                    url:"factura_detalle_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    //==== ocultar spinner
                    ocultarLoading();

                    if (rpta == "1"){
                        alert('La factura fue aprobada');
                        refresh_page();
                    } else {
                        if (rpta == "2") {
                            alert('La factura fue guardada');
                            refresh_page();
                        } else {
                            if (rpta == "3"){
                                alert('La factura fue rechazada');
                                refresh_page();
                            } else alert('Ocurrio un error = '+rpta);
                        }
                    }
                });
            }
        }

        //==== FUNCIONES DEL SPINNER LOADING

            function mostrarLoading() {
                document.getElementById("loadingModal").style.display = "flex";
            }

            function ocultarLoading() {
                document.getElementById("loadingModal").style.display = "none";
            }
        
    </script>
</BODY>
</HTML>