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
<?
    require("../lib/head.php");
    $acceso = 'PROPUESTA';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function validar(accion,retorno){
            var monto = Number(document.frm.monto.value);
            var tia = Number(document.frm.tia.value);
            var tipofinancia = Number(document.frm.tipofinancia.value);

            if (accion == 'grabar'){
                if (monto <= 0) alert('Debe ingresar una posicion');
                else{
                    if (tia <= 0) alert('De ingresar una tasa de interes');
                    else{
                        document.frm.action = 'propuesta_detalle_proceso.php';
                        document.frm.accion.value = accion;
                        document.frm.submit();
                    }
                }
            } else{
                if (accion == 'anular'){
                    var rpta = confirm("Esta seguro de anular su propuesta?");
                    if (rpta == true){
                        document.frm.action = 'propuesta_detalle_proceso.php';
                        document.frm.accion.value = accion;
                        document.frm.submit();
                    }
                } else location.href = retorno;
            }
        }
        function validapropuesta(accion){
            var montofin = Number(document.frm.montofin.value);
            var monto, montopendiente;
            var montoporc;
            var tia;
            var tim, tid, dias, ganancia;
            var tipofinancia = Number(document.frm.tipofinancia.value);
            
            if (accion == 'monto'){
                monto = Number(document.frm.monto.value);
                var v_monto_f, v_monto_l;
                
                if (montofin < monto){
                    alert('La posicion propuesta no puede ser mayor al monto a financiar');
                    document.frm.monto.value = 0;
                } else{   /* todo ok se llenan el resto de campos */
                    v_monto_f = Number(monto.toFixed(2));
                    v_monto_l = v_monto_f.toLocaleString('en-IN');
                    if (tipofinancia == 23){    /* financiamiento inmediato */
                        montopendiente = Number(document.frm.montopendiente.value);
                        
                        if (monto > montopendiente){
                            alert('La posicion propuesta no puede ser mayor al monto pendiente de financiamiento');
                            document.frm.monto.value = 0;
                        } else{
                            montoporc = Number((monto / montofin) * 100);
                            document.frm.porcmonto.value = montoporc.toFixed(2);
                            //document.frm.inversion.value = monto.toFixed(2);
                            document.frm.inversion.value = v_monto_l;
                        }
                    } else{
                        montoporc = Number((monto / montofin) * 100);
                        document.frm.porcmonto.value = montoporc.toFixed(2);
                        //document.frm.inversion.value = monto.toFixed(2);
                        document.frm.inversion.value = v_monto_l;
                    }
                }
            } else{
                if (accion == 'porcmonto'){
                    montoporc = Number(document.frm.porcmonto.value);

                    if (montoporc > 100){
                        alert('El porcentaje de la posicion no puede ser mayor al 100%');
                        document.frm.porcmonto.value = 0;
                    } else{
                        monto = Number(montofin * (montoporc / 100));
                        var v_monto_f = Number(monto.toFixed(2));
                        var v_monto_l = v_monto_f.toLocaleString('en-IN');

                        if (tipofinancia == 23){    /* financiamiento urgente */
                            montopendiente = Number(document.frm.montopendiente.value);

                            if (monto > montopendiente){
                                alert('La posicion propuesta no puede ser mayor al monto pendiente de financiamiento');
                                document.frm.porcmonto.value = 0;
                            } else{
                                document.frm.monto.value = monto;
                                document.frm.inversion.value = v_monto_l;
                            }
                        } else{
                            document.frm.monto.value = monto;
                            document.frm.inversion.value = v_monto_l;
                        }
                    }
                } else{
                    if (accion == 'tia'){
                        tia = Number(document.frm.tia.value);
                        monto = Number(document.frm.monto.value);
                        dias = Number(document.frm.dias.value);

                        tim = Number(Math.pow((1 + (tia / 100)),Number(1/12)) - 1);
                        tid = Number(Math.pow((1 + tim),Number(1/30)) - 1);
                        ganancia = Number(tid * monto * dias);
                        //var v_ganancia_f = Number(ganancia.toFixed(2));
                        //var v_ganancia_l = v_ganancia_f.toLocaleString('en-IN');
                        document.frm.ganancia.value = ganancia.toFixed(2);
                    }
                }
            }
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$subastaid = $_GET['subid'];
$facturaid = $_GET['facid'];
$cpropuesta = $_GET['cprop'];
$pagina = $_GET['pagina'];
$triesgoid = $_GET['triesgoid'];
$seconomicoid = $_GET['seconomicoid'];

if ($_GET['origen'] == 'panel') $retorno = '../panel/panel_inversionista.php?pagina='.$pagina.'&triesgoid='.$triesgoid.'&seconomicoid='.$seconomicoid;
elseif ($_GET['origen'] == 'subastas') $retorno = '../subastas/subastas_disponibles_inver.php';
//---- datos de la subasta
$objsubasta = new subasta;
$objmae = new maestros;
$objfactura = new factura;

$arrsubasta = $objsubasta->get_subasta($subastaid);
$arrposiciones = $objsubasta->get_subasta_posiciones($subastaid);
$arrobpago = $objmae->get_datos_obpag($arrsubasta['clienteid']);
$arrfacturariesgo = $objfactura->riesgo_factura($arrsubasta['facturaid']);
$arrobpagoriesgo = $objmae->get_riesgo_obpago($arrsubasta['clienteid']);
$arrhistoria = $objmae->get_historianeg_obpago($arrsubasta['clienteid']);

$hoy = date('Y-m-d');
$dhoy = new DateTime($hoy);
$dvenc = new DateTime($arrsubasta['fvencimiento']);
$dif = $dhoy->diff($dvenc);
$dias = $dif->days;
$finicio = date('d-m-Y',strtotime($arrobpago['finicio']));

if ($cpropuesta <= 0){  //sin propuesta
    $monto = number_format(0,2,'.',',');
    $porcmonto = 0;
    $tia = number_format(0,2,'.',',');
    $tdescuento = number_format(0,2,'.',',');
    $grupo = 0;
    $propuestaid = 0;
} else{
    $arrposicion = $objsubasta->get_posicion($subastaid,$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
    
    $monto = $arrposicion['monto'];
    $porcmonto = $arrposicion['representacion'] * 100;
    $tia = $arrposicion['tia'] * 100;
    $propuestaid = $arrposicion['id'];

    if ($arrposicion['creador_usuarioid'] > 0 && $arrposicion['creador_usuarioid'] != $_SESSION['user']['usuarioid']){
        $objseg = new seguridad(); 
        $arr_datos_usuario = $objseg->get_datos_usuario($arrposicion['creador_usuarioid']);
    }
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    if ($_GET['origen'] == 'panel') $menu = 'panel/panel_inversionista.php';
    else $menu = 'subastas/subastas_disponibles_inver.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="retorno" value="<?=$retorno?>">
    <input type="hidden" name="subastaid" value="<?=$subastaid?>">
    <input type="hidden" name="facturaid" value="<?=$facturaid?>">
    <input type="hidden" name="cpropuesta" value="<?=$cpropuesta?>">
    <input type="hidden" name="montofin" value="<?=$arrsubasta['montofin']?>">
    <input type="hidden" name="dias" value="<?=$dias?>">
    <input type="hidden" name="propuestaid" value="<?=$propuestaid?>">
    <input type="hidden" name="accion">
    <input type="hidden" name="tipofinancia" value="<?=$arrsubasta['tipofinancia']?>">

    <div class="frmtransaccion">
        <ul>
            <li style="font-size: 12px;">Pagador:</li>
            <li style="font-size: 18px;font-weight: bold;"><?echo $arrsubasta['cliente'];?></li>
            <li style="font-size: 12px;">Monto a Financiar:</li>
            <li style="font-size: 18px;font-weight: bold;color:#064677"><?echo number_format($arrsubasta['montofin'],2,'.',',');?></li>
            <li style="font-size: 12px;color:#064677"><?echo $arrsubasta['moneda'];?></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:100px;">ID:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Factura:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Monto Factura:</li>
        </ul>
        <ul style="font-size: 14px;">
            <li class="frminput_text_off" style="width:100px;text-align:right;"><?echo $arrsubasta['facturaid'];?></li>
            <li class="frminput_text_off" style="width:130px;text-align:center;"><?echo $arrsubasta['facnumero'];?></li>
            <li class="frminput_text_off" style="width:160px;text-align:right;"><?echo number_format($arrsubasta['total'],2,'.',',').' '.$arrsubasta['moneda'];?></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:180px;">Riesgo Factureate:</li>
            <li style="margin:0px 3px;padding:0px;width:150px;">Riesgo Score Credito:</li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Fecha de pago:</li>
            <li style="margin:0px 3px;padding:0px;width:100px;">Dias x vencer:</li>
        </ul>
        <ul>
            <li class="frminput_text" style="width:180px;text-align:center;background-color:#<?=$arrsubasta['color']?>"><?echo '[ '.$arrsubasta['calificacion'].' ] '.$arrsubasta['riesgo'];?></li>
            <li class="frminput_text" style="width:150px;text-align:center;background-color:#<?=$arrsubasta['colorscore']?>"><?echo '[ '.$arrsubasta['calificacionscore'].' ] '.$arrsubasta['riesgoscore'];?></li>
            <li class="frminput_text_off" style="width:100px;text-align:center;"><?echo date('d-m-Y',strtotime($arrsubasta['fvencimiento']));?></li>
            <li class="frminput_text_off" style="width:100px;text-align:center;"><?echo $dias.' d&iacute;as';?></li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul>
            <li style="font-size: 14px;font-weight: bold;">Tu Propuesta:
            <?
            if ($propuestaid > 0 && $arrposicion['creador_usuarioid'] > 0 && $arrposicion['creador_usuarioid'] != $_SESSION['user']['usuarioid']){
                echo '<b style="color:#b30a1f;"> La propuesta de su empresa fue creada por el usuario: '.$arr_datos_usuario['nombre'].' '.$arr_datos_usuario['nombre'].'</b>';
            }
            ?>
            </li>
        </ul>
    <?  // RECOMENDACION DE INVERSION
        $recomendacion = '';
        $sumposiciones = 0;
        $sumposicion_monto = 0;
        
        if (count($arrposiciones) > 0 && $arrsubasta['tipofinancia'] != 24){     // existen posiciones y es una subasta urgente donde aún no llega al 100%
            for ($i=0; $i<count($arrposiciones); $i++){
                if ($arrposiciones[$i]['posicion_porc'] != 1){  // el grupo que no representa el 100%
                    if ($cpropuesta > 0){
                        if ($arrposicion['id'] != $arrposiciones[$i]['propuestaid']){
                            $sumposiciones = $sumposiciones + $arrposiciones[$i]['posicion_porc'];
                            $sumposicion_monto = $sumposicion_monto + $arrposiciones[$i]['posicion'];
                        }
                    } else{
                        $sumposiciones = $sumposiciones + $arrposiciones[$i]['posicion_porc'];
                        $sumposicion_monto = $sumposicion_monto + $arrposiciones[$i]['posicion'];
                    }
                }
            }

            $porc_pendiente = (1 - $sumposiciones) * 100;
            $monto_pendiente = $arrsubasta['montofin'] - $sumposicion_monto;

            if ($monto_pendiente != $arrsubasta['montofin'])
                $recomendacion .= 'Puede colocar una posici&oacute;n m&aacute;xima de '.number_format($monto_pendiente,2,'.',',').' '.$arrsubasta['moneda'].' ('.$porc_pendiente.'%) o el total 
                            de '.number_format($arrsubasta['montofin'],2,'.',',').' '.$arrsubasta['moneda'].' (100%)';
            echo '<input type="hidden" name="montopendiente" value="'.$monto_pendiente.'">';

            if ($recomendacion != '')
                echo '<ul>
                        <li style="color:#b30a1f;font-size:13px;"><span class="icon-calculator"></span>'.$recomendacion.'</li>
                    </ul>';
        } else echo '<input type="hidden" name="montopendiente" value="'.$arrsubasta['montofin'].'">';
        echo '<ul style="margin:0px;padding:0px;">
                    <li style="margin:0px 3px;padding:0px;width:100px;">Monto a Invertir ('.$arrsubasta['moneda'].'):</li>
                    <li style="margin:0px 3px;padding:0px;width:110px;">% de factura</li>
                    <li style="margin:0px 3px;padding:0px;width:100px;">Tasa de interes anual (%)</li>
                    <li style="margin:0px 3px;padding:0px;width:180px;">Tu inversi&oacute;n:</li>
                    <li style="margin:0px 3px;padding:0px;width:100px;">Ganancia (estimada):</li>
                </ul>
                <ul>
                    <li><input type="number" class="frminput_text" name="monto" value="'.$monto.'" style="width:100px;text-align:right;" onchange=javascript:validapropuesta("monto")></li>
                    <li><input type="number" class="frminput_text" name="porcmonto" value="'.$porcmonto.'" style="width:100px;text-align:right;" onchange=javascript:validapropuesta("porcmonto")></li>
                    <li><input type="number" class="frminput_text" name="tia" value="'.$tia.'" style="width:100px;text-align:right;" onchange=javascript:validapropuesta("tia")></li>
                    <li><input type="text" class="frminput_text_off" name="inversion" value="'.number_format($monto,2,'.',',').'" style="width:130px;text-align:right;font-size:18px;" readonly></li>
                    <li>'.$arrsubasta['moneda'].'</li>';
            // calculo de la ganancia
            $tim = pow((1 + ($tia / 100)),(1/12)) - 1;
            $tid = pow((1 + $tim),(1/30)) - 1;
            $ganancia = $tid * $monto * $dias;
            echo '
                    <li><input type="text" class="frminput_text_off" name="ganancia" value="'.number_format($ganancia,2,'.',',').'" style="width:100px;text-align:right;font-size:18px;" readonly></li>
                    <li>'.$arrsubasta['moneda'].'</li>
                </ul>';
    ?>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul>
            <li>
                <details>
                    <summary style="font-size: 14px;font-weight: bold;">Detalle del Obligado al Pagador</summary>
                    <p style="font-size:12px;max-width:600px;">
                        <b style="color:#064677;">RUC:</b> <?echo $arrobpago['identificacion'];?><br>
                        <b style="color:#064677;">Nombre:</b> <?echo $arrobpago['nombre'];?><br>
                        <b style="color:#064677;">Sector Econ&oacute;mico:</b> <?echo $arrobpago['sectoreconomico'];?><br>
                        <b style="color:#064677;">Descripci&oacute;n de la empresa:</b> <?echo $arrobpago['actividad'];?><br>
                        <b style="color:#064677;">Fecha de fundaci&oacute;n:</b> <?echo $finicio;?><br>
                        <b style="color:#064677;">Pagina Web:</b> <?echo $arrobpago['paginaweb'];?>
                    </p>
                </details>
            </li>
        </ul>
        <ul>
            <li>
                <details>
                    <summary style="font-size: 14px;font-weight: bold;">Riesgos de la Factura y del Obligado al Pago</summary>
                    <?
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
                    <summary style="font-size: 14px;font-weight: bold;">Historial de Negociaci&oacute;n</summary>
                    <?
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
        <!-- botonera -->
        <ul>
        <?
        if ($arrsubasta['estadoid'] == 24)
            echo '<li class="botontransaccion" style="width:150px;"><a href=javascript:validar("grabar","0")><span class="icon-download"></span> Grabar</a></li>
                <li class="botontransaccion" style="width:150px;"><a href=javascript:validar("anular","0")><span class="icon-point-down"></span> Anular</a></li>';
        echo '  <li class="botontransaccionrojo" style="width:150px;"><a href=javascript:validar("cerrar","'.$retorno.'")><span class="icon-point-right"></span> Cerrar</a></li>';
        ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    </BODY>
</HTML>