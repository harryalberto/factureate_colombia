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
    //require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            alert("-");
        }
    </script>
</HEAD>
<!-- modal de la transferencia contable -->
<div class="modalclase">
        <div class="bodymodal">
            <h1>ejemplo modal</h1>
            <iframe id="iframepdf" frameborder="0" scrolling="no" width="100%" height="auto"></iframe>
            <a href="#" class="closemodal" onclick="closemodal();">Cerrar</a>
        </div>
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
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="subastaid" value="<?=$subastaid?>">
    <input type="hidden" name="accion" value="">
    <input type="hidden" name="retorno" value="<?=$v_retorno?>">
    <input type="hidden" name="factura_id" value="<?=$arrsubasta['facturaid']?>">
    <div style="overflow:hidden;text-align:left;font-size: 18px;font-weight: bold;color:#b30a1f;padding:5px;">
        Gesti&oacute;n de Subasta <?echo '('.$arrsubasta['facturaid'].') - '.$arrsubasta['cliente'];?>
    </div>
    <div class="frmtransaccion">
        <ul>
            <li>Emisor: </li>
            <li class='boxview'><?echo $arrsubasta['emisordoc'].' - '.$arrsubasta['emisor'];?></li>
            <li>Pagador: </li>
            <li class='boxview'><?echo $arrsubasta['clientedoc'].' - '.$arrsubasta['cliente'];?></li>
        </ul>
        <ul>
            <li>Riesgo Score: </li>
            <?echo '<li style="background-color:#'.$arrsubasta['colorscore'].'">['.$arrsubasta['calificacionscore'].'] '.$arrsubasta['riesgoscore'].'</li>';?>
            <li>Riesgo Factureate: </li>
            <?echo '<li style="background-color:#'.$arrsubasta['color'].'">['.$arrsubasta['calificacion'].'] '.$arrsubasta['riesgo'].'</li>';?>
        </ul>
        <ul>
            <li>Factura:</li>
            <li class='boxview'><?echo '['.$arrsubasta['facturaid'].'] '.$arrsubasta['facnumero'];?></li>
            <li>Moneda:</li>
            <li class='boxview'><?echo $arrsubasta['moneda'];?></li>
            <li>Monto Subasta:</li>
            <li class='boxview'><?echo number_format($arrsubasta['montofin'],2,'.',',');?></li>
            <li>F Vencimiento:</li>
            <li class='boxview'><?echo $fvencimiento;?></li>
            <li>Tipo Financiamiento:</li>
            <li class='boxview'><?echo $arrsubasta['tipofinanciamientonom'];?></li>
        </ul>
        <ul>
            <li>F Subasta:</li>
            <li class='boxview'><?echo $vf_subasta;?></li>
            <li>Tiempo transcurrido (hrs):</li>
            <li class='boxview' style='background-color:<?=$v_bgtiempo?>'><?echo number_format($v_tiempo,2,'.',',');?></li>
            <li>Estado:</li>
            <li class='boxview'><?echo $v_estado;?></li>
        </ul>
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <!-- Propuestas -->
    <?
    $grupowin = -1;

    if ($arrsubasta['estadoid'] == 31 || $arrsubasta['estadoid'] == 25 || $arrsubasta['estadoid'] == 26){
        $grupowin = $arrsubasta['grupowinid'];
        $tiagrupo = $arrpropuestas[0]['tiafinal']*100;

        echo '<div class="listado">
                <ul class="listado_header"><li style="width:280px;">Propuestas ganadoras con una TIA del '.number_format($tiagrupo,2,'.',',').' %</li></ul>
                <ul class="listado_header">
                    <li style="width:30px;">ID</li>
                    <li style="width:100px;">Posici&oacute;n</li>
                    <li style="width:100px;">Participaci&oacute;n</li>
                    <li style="width:100px;">Disponible</li>
                    <li style="width:50px;">Turno</li>
                    <li style="width:30px;">Grupo</li>
                    <li style="width:100px;">Estado</li>
                </ul>';

        for ($i=0; $i<count($arrpropuestas); $i++){
            if ($arrpropuestas[$i]['grupofinal'] == $arrsubasta['grupowinid']){
                $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;
                echo '<ul>
                            <li style="width:30px;text-align:center;">'.$arrpropuestas[$i]['propuestaid'].'</li>
                            <li style="width:100px;text-align:center;">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</li>
                            <li style="width:100px;text-align:center;">'.number_format($porcentaje,2,'.',',').' %</li>
                            <li style="width:100px;text-align:right;">'.number_format($arrpropuestas[$i]['fondo_disponible'],2,'.',',').' '.$arrsubasta['moneda'].'</li>
                            <li style="width:50px;text-align:center;">'.$arrpropuestas[$i]['turno'].'</li>
                            <li style="width:30px;text-align:center;">'.$arrpropuestas[$i]['grupofinal'].'</li>
                            <li style="width:100px;text-align:center;">'.$arrpropuestas[$i]['estado'].'</li>
                        </ul>';
            }
        }
        echo '</div>';
    }

    echo '<div class="listado">
            <ul class="listado_header"><li style="width:280px;">Propuestas adicionales</li></ul>
            <ul class="listado_header">
                <li style="width:30px;">ID</li>
                <li style="width:100px;">Posici&oacute;n</li>
                <li style="width:100px;">Participaci&oacute;n</li>
                <li style="width:50px;">Turno</li>
                <li style="width:30px;">Grupo</li>
                <li style="width:100px;">Estado</li>
            </ul>';
        
    for ($i=0; $i<count($arrpropuestas); $i++){
        if ($arrpropuestas[$i]['grupofinal'] != $grupowin){
            $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;
            echo '<ul>
                    <li style="width:30px;text-align:center;">'.$arrpropuestas[$i]['propuestaid'].'</li>
                    <li style="width:100px;text-align:center;">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</li>
                    <li style="width:100px;text-align:center;">'.number_format($porcentaje,2,'.',',').' %</li>
                    <li style="width:50px;text-align:center;">'.$arrpropuestas[$i]['turno'].'</li>
                    <li style="width:30px;text-align:center;">'.$arrpropuestas[$i]['grupofinal'].'</li>
                    <li style="width:100px;text-align:center;">'.$arrpropuestas[$i]['estado'].'</li>
                </ul>';
        }
    }

    echo '</div>';
    ?>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <?
    if ($arrsubasta['estadoid'] == 31){     // compensada totalmente
        echo '<div class="frmtransaccion">
                <ul>
                    <li>Transferencia de titularidad:</li>
                    <li><input name="transferenciafile" type="file"></li>
                    <input type="hidden" name="transferenciapath" value="">';
        if (!is_null($arrsubasta['transferenciapath']) && $arrsubasta['transferenciapath'] != ''){
            echo '  <li>'.$arrsubasta['transferenciapath'].'</li>
                    <li><a class="pdftransferencia" href="#" product="3">Ver Transferencia</a></li>
                    <input type="hidden" name="transferenciapath" value="'.$arrsubasta['transferenciapath'].'">';
        } else echo '<input type="hidden" name="transferenciapath" value="">
                    <li style="width:200px;" class="botontransaccion"><a href=javascript:acciones("contrato")>Registrar</a></li>';
        
        echo '  </ul>
            </div>
            <div class="frmtransaccion">
                <ul>
                    <li>Transferencia de fondos:</li>
                    <li><input name="fondosfile" type="file"></li>';
        if (!is_null($arrsubasta['fondospath']) && $arrsubasta['fondospath'] != ''){
            echo '  <li>'.$arrsubasta['fondospath'].'</li>
                    <li><a class="pdffondos" href="#" product="3">Ver Transferencia de Fondos</a></li>
                    <input type="hidden" name="fondospath" value="'.$arrsubasta['fondospath'].'">';
        } else echo '<input type="hidden" name="fondospath" value="">
                    <li style="width:200px;" class="botontransaccion"><a href=javascript:acciones("fondos")>Registrar</a></li>';
        echo '  </ul>
            </div>';
    }
    ?>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <div class="frmtransaccion">
        <?
        //botonera
        echo '<ul>';

        //if ($arrsubasta['estadoid'] == 31)    // compensada completamente
            //echo '<li style="width:200px;" class="botontransaccion"><a href=javascript:acciones("liquidar")>Liquidar</a></li>';
        if ($arrsubasta['estadoid'] == 24)      // subasta activa
            echo '<li style="width:200px;" class="botontransaccion"><a href=javascript:acciones("terminar")>Terminar</a></li>
                    <li style="width:200px;" class="botontransaccion"><a href=javascript:acciones("anular")>Anular</a></li>';
        echo '<li style="width:200px;background-color:#b30a1f;color:#ffffff;" class="botontransaccion"><a href=javascript:acciones("cerrar")>Cerrar</a></li>';
        echo '</ul>';
        ?>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <script>
        $('.pdftransferencia').click(function(e) {
            e.preventDefault();
            var direccion = $(this).attr('product');
            
            $('.modalclase').fadeIn();
            $('#iframepdf').attr('src','../pdf/f000002.pdf')
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