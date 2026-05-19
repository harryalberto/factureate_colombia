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
    $acceso = 'FACTFACTU';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            var estadocliente;

            if (accion == 'aprobar'){
                estadocliente = document.frm.estadoclienteid.value;
                var v_estado_factura = $('#estado_id').val();
                var v_factura_libre = $('#factura_libre').val();

                if (estadocliente == 3 || estadocliente == 22){
                    if (v_estado_factura == 12 && v_factura_libre == 1){
                        var v_file_libre = $('#file_factura_libre').val();
                        
                        if (v_file_libre != ''){
                            document.frm.accion.value = accion;
                            document.frm.action = 'factura_detalle_proceso.php';
                            document.frm.submit();
                        } else alert('Debe adjuntar el comprobante de libertad de negociacion de la factura');
                    } else {
                        document.frm.accion.value = accion;
                        document.frm.action = 'factura_detalle_proceso.php';
                        document.frm.submit();
                    }
                } else alert('El cliente debe estar revisado');
            } else{
                if (accion == 'grabar'){
                    document.frm.accion.value = accion;
                    document.frm.action = 'factura_detalle_proceso.php';
                    document.frm.submit();
                } else{
                    if (accion == 'anotarencuenta'){
                        if (document.frm.acfile.value == '' && document.frm.acpath.value == '') alert('Debe agregar el archivo de anotacion en cuenta');
                        else{
                            document.frm.accion.value = accion;
                            document.frm.action = 'factura_detalle_proceso.php';
                            document.frm.submit();
                        }
                    } else{
                        if (accion == 'rechazar'){
                            if (document.frm.rechazo.value == '') alert('Debe ingresar un motivo de rechazo');
                            else{
                                document.frm.accion.value = accion;
                                document.frm.action = 'factura_detalle_proceso.php';
                                document.frm.submit();
                            }
                        } else location.href = accion;
                    }
                }
            }
        }
        function closemodal(){
            $('.modalclase').fadeOut();
        }
    </script>
</HEAD>
<!-- modal de la factura -->
<div class="modalclase">
        <div class="bodymodal">
        <iframe id="iframepdf" frameborder="0" scrolling="yes" width="90%" height="90%"></iframe>
            <a href="#" class="closemodal" onclick="closemodal();" style="text-align:center;font-weight:bold;color:#ffffff;font-size: 14px;width:50px;background-color: #e89b24;">Cerrar</a>
        </div>
</div>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$objmaestro = new maestros;

$facturaid = $_GET['id'];
if ($_GET['ret'] == 'facxe') $v_retorno = 'facturas_xestado.php?estadoid='.$_GET['eid'];
if (isset($_POST['retorno'])) $v_retorno = $_POST['retorno'];

$arrfactura = $objfactura->get_datos_factura($facturaid);
$varr_fact_libre = $objmaestro->get_parametro_detalle(56);

$femision_t = strtotime($arrfactura['femision']);
$fvencimiento_t = strtotime($arrfactura['fvencimiento']);
$femision = date('d-m-Y',$femision_t);
$fvencimiento = date('d-m-Y',$fvencimiento_t);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?
    date_default_timezone_set("America/Lima");
    //resta de fechas
    $date1_t = date('Y-m-d');
    $date1 = new DateTime($date1_t);
    $date2 = new DateTime($arrfactura['fvencimiento']);
    $diasvencimiento = $date1->diff($date2);
    $v_monto_adelanto = $arrfactura['porciento_adelanto'] * $arrfactura['total'];
    
    if ($_GET['ret'] == 'facxe') $menu = 'facturas/facturas_xestado.php';
    else $menu = 'facturas/facturas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div class="contenedor_principal">
        <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);padding: 5px;">
            Revisi&oacute;n de Instrumento
        </div>
        
        <form name='frm' method='post' id='frm' enctype="multipart/form-data">
        <input type="hidden" name="facturaid" value="<?=$facturaid?>">
        <input type="hidden" name="clienteid" value="<?=$arrfactura['clienteid']?>">
        <input type="hidden" name="emisorid" value="<?=$arrfactura['emisorid']?>">
        <input type="hidden" name="accion" value="">
        <input type="hidden" name="u_envio_id" value="<?=$arrfactura['u_envio_id']?>">
        <input type="hidden" name="retorno" value="<?=$v_retorno?>">
        <input type="hidden" name="ret" value="<?=$_GET['ret']?>">
        <input type="hidden" name="eid" value="<?=$_GET['eid']?>">
        <input type="hidden" name="factura_libre" id="factura_libre" value="<?=$varr_fact_libre['valornum']?>">
        <input type="hidden" name="estado_id" id="estado_id" value="<?=$arrfactura['estado']?>">

        <!--===========================================
        =================== DATOS DE LA FACTURA -->
        <div class="contenedor_formulario" id="formulario" style="margin-bottom:20px;">
            <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;padding-top: 10px;padding-left: 10px;color: var(--color-oro);">
                <p style="font-weight: bold;"><i class="fa-solid fa-file-invoice"></i> Datos del Instrumento</p>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="factura_id">ID</label>
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

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="emisornro">NIT EMISOR</label>
                    <input type="text" class="formulario_control" id="emisornro" name="emisornro" value="<?=$arrfactura['emisornro']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:400px;">
                    <label for="emisor">EMISOR</label>
                    <input type="text" class="formulario_control" id="emisor" name="emisor" value="<?=$arrfactura['emisor']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="clientenro">NIT CLIENTE</label>
                    <input type="text" class="formulario_control" id="clientenro" name="clientenro" value="<?=$arrfactura['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:400px;">
                    <label for="cliente">CLIENTE</label>
                    <input type="text" class="formulario_control" id="cliente" name="cliente" value="<?=$arrfactura['cliente']?>" readonly>
                </div>
            </div>

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
                    <label for="impuestoventa">IVA</label>
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
        </div>  <!-- DIV FORMULARIO -->
<?php
    $varr_evaluacion = $objfactura->get_evaluacion_factura($facturaid);
    $arr_riesgo_obp = $objmaestro->get_riesgo_obpago($arrfactura['clienteid']);
    $arrestadocli = $objmaestro->get_estado_cliente($arrfactura['clienteid']);
    $v_estado_emp = $objmaestro->calcula_estado_empresa($arrfactura['clienteid']);

    if ($v_estado_emp == 0) $v_estado_msg = ''; elseif ($v_estado_emp == 1) $v_estado_msg = '(PENDIENTE REVISION OPERATIVA)'; 
    elseif ($v_estado_emp == 2) $v_estado_msg = '(PENDIENTE REVISION LEGAL)'; elseif ($v_estado_emp == 3) $v_estado_msg = '(PENDIENTE EVALUAR RIESGOS)';
?>
        <!--=========================================================
        ======================= EVALUACION DE LA FACTURA -->
        <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;padding-top: 10px;padding-left: 10px;color: var(--color-oro);">
            <p style="font-weight: bold;"><i class="fa-solid fa-dna"></i> Evaluación <?echo $v_estado_msg;?></p>
        </div>

        <div>
<?php
    if ($arrestadocli['estadoid'] == 1 || $arrestadocli['estadoid'] == 2){   // 1 registrada / 2 en validacion
        if ($v_estado_emp != 3){
            $v_bg_score = $arr_riesgo_obp['color_riesgo_score'];
            $v_color_score = $arr_riesgo_obp['colorfuente_riesgo_score'];
            $v_msg_score = '['.$arr_riesgo_obp['crscore'].'] '.$arr_riesgo_obp['nrscore'];
            $v_bg_factureate = $arr_riesgo_obp['color_riesgo_fact'];
            $v_color_factureate = $arr_riesgo_obp['colorfuente_riesgo_fact'];
            $v_msg_factureate = '['.$arr_riesgo_obp['crfact'].'] '.$arr_riesgo_obp['nrfact'];
            $v_result = '';
            $v_rcliente_atencion = '';
        } else {
            $v_bg_score = 'FFB500';
            $v_color_score = '000000';
            $v_msg_score = 'PENDIENTE';
            $v_bg_factureate = 'FFB500';
            $v_color_factureate = '000000';
            $v_msg_factureate = 'PENDIENTE';
            $v_result = '<span class="icon-warning"></span>';
            $v_rcliente_atencion = '<a href="../empresas/empresa_gestion_detalle.php?id='.$arrfactura['clienteid'].'&previo=factura&facturaid='.$facturaid.'" style="color:#000000;">ATENDER <span class="icon-warning"></span></a>';
        }
    } else{ 
        $v_bg_score = $arr_riesgo_obp['color_riesgo_score'];
        $v_color_score = $arr_riesgo_obp['colorfuente_riesgo_score'];
        $v_msg_score = '['.$arr_riesgo_obp['crscore'].'] '.$arr_riesgo_obp['nrscore'];
        $v_bg_factureate = $arr_riesgo_obp['color_riesgo_fact'];
        $v_color_factureate = $arr_riesgo_obp['colorfuente_riesgo_fact'];
        $v_msg_factureate = '['.$arr_riesgo_obp['crfact'].'] '.$arr_riesgo_obp['nrfact'];
        $v_result = '';
        $v_rcliente_atencion = '';
    }

    echo '
        <input type="hidden" name="estadoclienteid" value="'.$arrestadocli['estadoid'].'">
            <ul>
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ANALISIS</th>
                    <th scope="col">CALIFICACION</th>
                    <th scope="col">RESULT</th>
                    <th scope="col">ATENCION</th>
                </tr></thead>
                <tbody>
                    <tr>
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
    
    for ($i=0; $i<count($varr_evaluacion); $i++){
        if ($varr_evaluacion[$i]['evaluacion_tipo'] == 78){
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
            } elseif ($varr_evaluacion[$i]['calificacion'] == 3){   //PENDIENTE DE ATENDER
                $v_calificacion = 'PENDIENTE';
                $v_bg_califica = 'FFB500';
                $v_color_califica = '000000';
                $v_resultado = '<span class="icon-warning"></span>';
                $v_color_result = 'FFB500';
                $v_atencion = '<a href="../empresas/empresa_gestion_detalle.php?id='.$arrfactura['clienteid'].'&previo=factura&facturaid='.$facturaid.'">ATENDER <span class="icon-warning"></span></a>';
            }
        }

        echo '
                    <tr>
                        <td data-label="ANALISIS">'.$varr_evaluacion[$i]['evaluacion_tipo_nom'].'</td>
                        <td data-label="CALIFICACION" style="background-color:#'.$v_bg_califica.';color:#'.$v_color_califica.';">'.$v_calificacion.'</td>
                        <td data-label="RESULT" style="color:#'.$v_color_result.';">'.$v_resultado.'</td>
                        <td data-label="ATENCION" style="color:#;">'.$v_atencion.'</td>
                    </tr>';
    }

    echo '
                </tbody>
            </table>
            </ul>';    
?>
        <!-- riesgo del obligado al pago -->
        <input type=hidden name=rfactureate_id value="<?=$arr_riesgo_obp["riesgo_factid"]?>">
        <input type=hidden name=rfactureate_desc value="<?=$arr_riesgo_obp["desc_riesgofact"]?>">
        <input type="hidden" name="rscore_id" value="<?=$arr_riesgo_obp['riesgo_scoreid']?>">
        <input type="hidden" name="rscore_desc" value="<?=$arr_riesgo_obp['desc_riesgoscore']?>">
        </div>

        <div class="contenedor_formulario" style="margin-bottom:20px;">
<?php
        /*==============================================
        ===================== COMPROBANTE QUE LA FACTURA ESTA LIBRE PARA SER NEGOCIADA */
    if ($varr_fact_libre['valornum'] > 0 && $arrfactura['estado'] == 12){
        echo '
            <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;padding-top: 10px;padding-left: 10px;color: var(--color-oro);">
                <p style="font-weight: bold;"><i class="fa-regular fa-file-lines"></i> Comprobante Titulo Libre</p>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 400px;">
                    <label for="file_factura_libre">Comprobante Titulo Libre:</label>
                    <input type="file" name="file_factura_libre" id="file_factura_libre" class="formulario_control">
                </div>
            </div>';
    }    
?>

            <!--===============================================
            ==================== ZONA RECHAZO -->
            <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;padding-top: 10px;padding-left: 10px;color: var(--color-oro);">
                <p style="font-weight: bold;"><i class="fa-solid fa-hand-point-left"></i> Motivo de Rechazo</p>
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
        </div>

        </form>

        <div style="overflow:hidden;background-color:#555555;height:1px;margin-top: 10px;margin-bottom: 10px;float: left; width: 100%;"></div>

        <!--=========================================================
        ======================== BOTONERA -->
        <div style="width: 100%; overflow: hidden; float: left;">
            <ul>
<?php
    $v_aprobar = "'aprobar'";
    $v_rechazar = "'rechazar'";
    $v_retornar = "'".$v_retorno."'";

    if ($arrfactura['estado'] == 12 && ($arrestadocli['estadoid'] == 3 || $arrestadocli['estadoid'] == 22))    // instrumento enviado / OP validado
        echo '
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_aprobar" onclick="acciones('.$v_aprobar.')"><i class="fa-solid fa-check-to-slot"></i> Aprobar</button>
            <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_aprobar" onclick="acciones('.$v_rechazar.')"><i class="fa-solid fa-circle-xmark"></i> Rechazar</button>';
        
    echo '  <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_aprobar" onclick="acciones('.$v_retornar.')"><i class="fa-solid fa-door-open"></i> Cerrar</button>';
?>
            </ul>
        </div>
    </div>
    
    <!------ END CUERPO VARIABLE ------>
    <script>
        $('.pdffact').click(function(e) {
            e.preventDefault();
            var path = $(this).attr('path');
            
            $('.modalclase').fadeIn();
            $('#iframepdf').attr('src',path)
        });
    </script>
</BODY>
</HTML>