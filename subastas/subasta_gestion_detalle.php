<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'SUBGESTION';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            document.frm.accion.value = accion;
            
            if (accion == 'envio_contrato'){
                if (document.frm.link_envio.value == '') alert('Debe ingresar la referencia del envio del contrato al vendedor');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            if (accion == 'contrato'){
                if (document.frm.contrato.value == '') alert('Debe agregar el archivo del contrato con el vendedor');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            if (accion == 'endoso'){
                if (document.frm.endoso.value == '') alert('Debe agregar el archivo de Endoso');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            
            if (accion == 'liquidar'){
                if (document.frm.transferenciafile.value == '' && document.frm.transferenciapath.value == '') alert('Debe agregar el archivo de transferencia de titularidad');
                else{
                    if (document.frm.fondosfile.value == '' && document.frm.fondospath.value == '') alert('Debe agregar el archivo de transferencia de fondos');
                    else{
                        document.frm.action = 'subasta_gestion_proceso.php';
                        document.frm.submit();
                    }
                }
            }
            if (accion == 'fondos'){
                if (document.frm.fondosfile.value == '' && document.frm.fondospath.value == '') alert('Debe agregar el archivo de transferencia de fondos');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            if (accion == 'terminar' || accion == 'anular'){
                document.frm.action = 'subasta_gestion_proceso.php';
                document.frm.submit();
            }
            
            if (accion == 'cerrar'){ 
                var estados = document.frm.estados.value;
                location.href = 'subastas.php?estados='+estados;
            }
        }
        function closemodal(){
            $('.modalclase').fadeOut();
        }
    </script>
</HEAD>
<!-- modal de la transferencia contable -->
<div class="modalclase">
    <!-- <div class="modal-dialog modal-sm modal-dialog-centered"> -->
        <!-- <div class="modal-content"> -->
            <!-- ##### cabecera ##### -->
            <div style="padding:10px 5px;margin:0px;">
                <h4 class="modal-title" style="color:#ffffff;">Contrato de Cesion</h4>
                <button type="button" class="close" style="color:#ffffff;" data-dismiss="modal" onclick="closemodal();">X</button>
            </div>
            <!-- ##### body #### -->
            <div class="bodymodal">
                <iframe id="iframepdf" frameborder="0" scrolling="no" style="width:100%; height:500px;"></iframe>
            </div>
        <!-- </div> -->
    <!-- </div> -->
</div>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objsubasta = new subasta;
$objmaestro = new maestros;

$subastaid = $_GET['id'];
$arrsubasta = $objsubasta->get_subasta($subastaid);
$arrpropuestas = $objsubasta->get_subasta_posiciones($subastaid);
$arrparam = $objmaestro->get_parametros();

$fvencimiento_t = strtotime($arrsubasta['fvencimiento']);
$fvencimiento = date('d-m-Y',$fvencimiento_t);
$vf_subasta = date('d-m-Y', strtotime($arrsubasta['f_subasta'])).' '.$arrsubasta['h_subasta'];
$vt_subasta = strtotime($arrsubasta['f_subasta'].' '.$arrsubasta['h_subasta']);
$v_tiempo = round(((time() - $vt_subasta) / 60) / 60);

if ($arrsubasta['estadosubastaid'] == 31) $v_estado = $arrsubasta['estadosubasta'].' - '.$arrsubasta['estado_compensa'];
else $v_estado = $arrsubasta['estadosubasta'];

if (!isset($_GET['return'])) $v_retorno = 0;
else $v_retorno = $_GET['return'];

if ($v_tiempo < $arrparam['horas medio subasta']['valornum']) $v_bgtiempo = '#ffffff;';
elseif ($v_tiempo >= $arrparam['horas medio subasta']['valornum'] || $v_tiempo <= $arrparam['horas medio subasta']['valorchar']) $v_bgtiempo = '#e89b24;';
else $v_bgtiempo = '#ff3333;';
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'subastas/subastas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");

    $v_estados = $_GET['estados'];
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="subastaid" value="<?=$subastaid?>">
    <input type="hidden" name="accion" value="">
    <input type="hidden" name="retorno" value="<?=$v_retorno?>">
    <input type="hidden" name="factura_id" value="<?=$arrsubasta['facturaid']?>">
    <input type="hidden" name="estados" value="<?=$v_estados?>">
    
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding: 5px;">
        Detalle de Subasta
    </div>
    <div class="frmtransaccion">
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:30px;">ID:</li>
            <li style="margin:0px 3px;padding:0px;width:50px;">Subasta:</li>
            <li style="margin:0px 3px;padding:0px;width:255px;">Emisor:</li>
            <li style="margin:0px 3px;padding:0px;width:255px;">Pagador:</li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Fecha:</li>
        </ul>
        <ul>
            <li><input type="text" name="factura_id" size="5" value="<?=$arrsubasta['facturaid']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="subasta_id" size="5" value="<?=$subastaid?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="emisor" size="40" value="<?=$arrsubasta['emisor']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="pagador" size="40" value="<?=$arrsubasta['cliente']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="f_subasta" value="<?=$vf_subasta?>" class="frminput_text_off" readonly></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:255px;">Estado:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Tipo:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Riesgo Buro:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Riesgo Factureate:</li>
        </ul>
        <ul>
            <li><input type="text" name="estado" size="40" value="<?=$v_estado?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo" value="<?=$arrsubasta['tipofinanciamientonom']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo" value="<?php echo '[ '.$arrsubasta['calificacionscore'].'] '.$arrsubasta['riesgoscore'];?>" class="frminput_text_off" readonly style="background-color:#<?=$arrsubasta['colorscore']?>;"></li>
            <li><input type="text" name="tipo" value="<?php echo '[ '.$arrsubasta['calificacion'].'] '.$arrsubasta['riesgo'];?>" class="frminput_text_off" readonly style="background-color:#<?=$arrsubasta['color']?>;"></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Factura:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Moneda:</li>
            <li style="margin:0px 3px;padding:0px;width:135px;">F Vencimiento:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Monto Subasta:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Horas Transcurridas:</li>
        </ul>
        <ul>
            <li><input type="text" name="estado" size="20" value="<?='['.$arrsubasta['facturaid'].'] '.$arrsubasta['facnumero']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo" value="<?=$arrsubasta['moneda']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo" value="<?=$fvencimiento?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo" style="text-align:right;" value="<?=number_format($arrsubasta['montofin'],2,'.',',')?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo" value="<?=number_format($v_tiempo,2,'.',',')?>" class="frminput_text_off" readonly style="text-align:right;background-color:#<?=$v_bgtiempo?>;"></li>
        </ul>
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <!-- Propuestas -->
    <?
    $grupowin = -1;

    if ($arrsubasta['estadoid'] == 31 || $arrsubasta['estadoid'] == 25 || $arrsubasta['estadoid'] == 26){   // 31-compensado / 25-en compensacion / 26-liquidada
        $grupowin = $arrsubasta['grupowinid'];
        $tiagrupo = $arrpropuestas[0]['tiafinal']*100;

        echo '  <div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 5px;">
                    <ul style="overflow:hidden;list-style:none;">
                        <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">
                            Propuestas ganadoras con una TIA del '.number_format($tiagrupo,2,'.',',').' %
                        </li>
                    </ul>
                    <ul style="overflow:hidden;list-style:none;">
                        <li style="display:block;margin:5px;width:80%;float:left;padding:5px;">
                            <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                                <tr style="background-color:#252525;color:#ffffff;">
                                    <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">MONTO POSICION</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">PARTICIPACION</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">MONTO DISPONIBLE</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">TURNO</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">GRUPO</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">ESTADO</td>
                                </tr>';
        
        for ($i=0; $i<count($arrpropuestas); $i++){
            if ($arrpropuestas[$i]['grupofinal'] == $arrsubasta['grupowinid']){
                $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;
                echo '          <tr>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['propuestaid'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($porcentaje,2,'.',',').' %</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;"">'.number_format($arrpropuestas[$i]['fondo_disponible'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['turno'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['grupofinal'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['estado'].'</td>
                                </tr>';
            }
        }
        ?>
                            </table>
                        </li>
                    </ul>
    <?php
    }

    $v_adicionales = 0;

    for ($i=0; $i<count($arrpropuestas); $i++){
        if ($arrpropuestas[$i]['grupofinal'] != $grupowin) $v_adicionales++;
    }

    if ($v_adicionales > 0){
        echo '
                    <ul style="overflow:hidden;list-style:none;">
                        <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">
                            Propuestas adicionales
                        </li>
                    </ul>
                    <ul style="overflow:hidden;list-style:none;">
                        <li style="display:block;margin:5px;width:80%;float:left;padding:5px;">
                            <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                                <tr style="background-color:#252525;color:#ffffff;">
                                    <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">MONTO POSICION</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">PARTICIPACION</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">TURNO</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">GRUPO</td>
                                    <td style="border: 1px solid;padding:5px;text-align:center;">ESTADO</td>
                                </tr>';
        
        for ($i=0; $i<count($arrpropuestas); $i++){
            if ($arrpropuestas[$i]['grupofinal'] != $grupowin){
                $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;
                echo '          <tr>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['propuestaid'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($porcentaje,2,'.',',').' %</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['turno'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['grupofinal'].'</td>
                                    <td style="border: 1px solid;padding:5px;text-align:right;">'.$arrpropuestas[$i]['estado'].'</td>
                                </tr>';
            }
        }

        echo '              </table>
                        </li>
                    </ul>
                </div>';
    }
    ?>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <!--============== Botonera dependiendo del estado =====================-->
    <?
    if ($arrsubasta['estadoid'] == 31){     // compensada
        if ($arrsubasta['estado_compensa_id'] == 40){  // contrato x enviar
            echo '
                <div class="frmtransaccion">
                    <ul style="margin:0px;padding:0px;">
                        <li style="margin:0px 3px;padding:5px;width:90px;">Link de envio:</li>
                        <li><input type="text" name="link_envio" style="text-align:left;width:200px;" class="frminput_text"></li>
                        <li class="botontransaccionazul"><a href=javascript:acciones("envio_contrato") style="text-decoration:none;color:#ffffff;"><span class="icon-floppy-disk"></span> Guardar Envio</a></li>
                    </ul>
                </div>';
        } elseif ($arrsubasta['estado_compensa_id'] == 43){    // contrato enviado
            echo '
                <div class="frmtransaccion">
                    <ul style="margin:0px;padding:0px;">
                        <li style="margin:0px 3px;padding:0px;width:90px;">Link envio de contrato:</li>
                        <li class="botontransaccionazul"><a href="'.$arrsubasta['ref_envio_contrato'].'" style="text-decoration:none;color:#ffffff;" target="_blank"><span class="icon-link"></span> Ver Envio</a></li>
                    </ul>
                    <ul style="margin:0px;padding:0px;">
                        <li style="margin:0px 3px;padding:0px;width:90px;">Contrato:</li>
                        <li><input type="file" name="contrato"></li>
                        <li class="botontransaccionazul"><a href=javascript:acciones("contrato") style="text-decoration:none;color:#ffffff;"><span class="icon-floppy-disk"></span> Guardar Contrato</a></li>
                    </ul>
                </div>';
        } elseif ($arrsubasta['estado_compensa_id'] == 44){    // contrato recibido o firmado
            echo '
                <div class="frmtransaccion">
                    <ul style="margin:0px;padding:0px;">
                        <li style="margin:0px 3px;padding:0px;width:90px;">Link envio de contrato:</li>
                        <li class="botontransaccionazul"><a href="'.$arrsubasta['ref_envio_contrato'].'" style="text-decoration:none;color:#ffffff;" target="_blank"><span class="icon-link"></span> Ver Envio</a></li>
                    </ul>
                    <ul style="margin:0px;padding:0px;">
                        <li style="margin:0px 3px;padding:0px;width:90px;">Contrato Firmado:</li>
                        <li class="botontransaccionazul"><a href="#" class="contrato_firma" path_contrato="'.$arrsubasta['path_contrato'].'" style="text-decoration:none;color:#ffffff;"><span class="icon-link"></span> Ver Contrato</a></li>
                    </ul>
                    <ul style="margin:0px;padding:0px;">
                        <li style="margin:0px 3px;padding:0px;width:135px;">Endoso:</li>
                        <li><input type="file" name="endoso"></li>
                        <li class="botontransaccionazul"><a href=javascript:acciones("endoso") style="text-decoration:none;color:#ffffff;"><span class="icon-floppy-disk"></span> Guardar Endoso</a></li>
                    </ul>
                </div>';
        }
    }
    ?>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <div class="frmtransaccion">
    <?  //=============== botonera ====================
    echo '
        <ul>';

        if ($arrsubasta['estadoid'] == 24)      // subasta activa
            echo '
                <li class="botontransaccionazul"><a href=javascript:acciones("terminar")><span class="icon-stop2"></span> Terminar</a></li>
                <li class="botontransaccionazul"><a href=javascript:acciones("anular")><span class="icon-blocked"></span> Anular</a></li>';
                
        echo '
                <li class="botontransaccionrojo"><a href=javascript:acciones("cerrar") style="text-decoration: none;"><span class="icon-point-left"></span> Salir</a></li>
        </ul>';
    ?>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <script>
        $('.contrato_firma').click(function(e) {
            e.preventDefault();
            var path_contrato = $(this).attr('path_contrato');
            
            $('.modalclase').fadeIn();
            $('#iframepdf').attr('src',path_contrato)
        });
    </script>
    <script>
        $('.pdffondos').click(function(e) {
            e.preventDefault();
            var direccion = $(this).attr('product');
            
            $('.modalclase').fadeIn();
            $('#iframepdf').attr('src','../pdf/f000002.pdf')
        });
    </script>
</BODY>
</HTML>