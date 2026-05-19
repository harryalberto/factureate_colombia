<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'CUENTAS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function registrar_saldo(parametro){
            var monto = Number(document.frm.monto_saldo_carga.value);

            if (monto <= 0) alert('Debe ingresar un monto a cargar');
            else {
                var comprobante = document.frm.comprobante_saldo_carga.value;

                if (comprobante == '') alert('Debe cargar un comprobante del saldo que desea cargar');
                else{
                    document.frm.action = 'cuenta_gestion_proceso.php';
                    document.frm.accion.value = 'agregar_saldo';
                    document.frm.submit();
                }
            }
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cuenta = new cuentas;

$varr_cuenta = $obj_cuenta->get_cuenta_detalle($_GET['cuentaid']);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
        <input type="hidden" name="retorno" value="<?=$_GET['retorno']?>">
        <input type="hidden" name="pagina" value="<?=$_GET['pagina']?>">
        <input type="hidden" name="rowcount" value="<?=$_GET['rowcount']?>">
        <input type="hidden" name="cuenta_id" value="<?=$_GET['cuentaid']?>">
        <input type="hidden" name="accion">
        <input type="hidden" name="salto_transito_id">
    <div class="frmtransaccion" style="font-size:12px;">
        <ul style="margin-top:0px;">
            <li style="font-weight:bold;">DATOS DE LA CUENTA:</li>
        </ul>
        <ul>
            <li style="font-weight: bold;width:80px;">INVERSOR:</li>
            <li style="width:200px;"><?echo $varr_cuenta['HEADER']['inversor_nombre'];?></li>
            <li style="font-weight: bold;width:50px;">TIPO</li>
            <li style="width:150px;"><?echo $varr_cuenta['HEADER']['tipo_inversor'];?></li>
            <input type="hidden" name="inversor_id" value="<?=$varr_cuenta['HEADER']['inversor_id']?>">
            <input type="hidden" name="inversor_tipo_id" value="<?=$varr_cuenta['HEADER']['tipo_inversor_id']?>">
        </ul>
        <ul>
            <li style="font-weight: bold;width:120px;">IDENTIFICACION:</li>
            <li style="width:150px;"><? echo $varr_cuenta['HEADER']['inversor_tipodoc'].' '.$varr_cuenta['HEADER']['identificacion'];?></li>
            <li style="font-weight: bold;width:70px;">MONEDA:</li>
            <li style="width:200px;"><?echo $varr_cuenta['HEADER']['moneda'];?></li>
            <input type="hidden" name="moneda_id" value="<?=$varr_cuenta['HEADER']['moneda_id']?>">
        </ul>
        <ul>
            <li style="font-weight: bold;width:150px;">SALDO CONTABLE</li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?echo number_format($varr_cuenta['SALDOS']['saldo_contable'],2,'.',',');?></li>
        </ul>
        <ul>
            <li style="font-weight: bold;width:150px;">SALDO COMPROMETIDO</li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?echo number_format($varr_cuenta['SALDOS']['saldo_comprometido'],2,'.',',');?></li>
        </ul>
        <ul>
            <li style="font-weight: bold;width:150px;">SALDO DISPONIBLE</li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?echo number_format($varr_cuenta['SALDOS']['saldo_disponible'],2,'.',',');?></li>
        </ul>
        <ul>
            <li style="font-weight: bold;width:150px;">SALDO INVERTIDO</li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?echo number_format($varr_cuenta['SALDOS']['saldo_invertido'],2,'.',',');?></li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <?php
    if ($varr_cuenta['SALDOS']['saldo_comprometido'] > 0){
        echo '
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">SALDO COMPROMETIDO:</li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">OPERACION ID</th>
                    <th scope="col">MONTO COMPROMETIDO</th>
                    <th scope="col">MONTO PENDIENTE DE SALDO</th>
                </tr></thead>
                <tbody>';
        
        for ($i=0; $i<count($varr_cuenta['SALDO_COMPROMETIDO']); $i++){
            echo '  <tr>
                        <td data-label="OPERACION ID">'.$varr_cuenta['SALDO_COMPROMETIDO'][$i]['factura_id'].'</td>
                        <td data-label="MONTO COMPROMETIDO">'.number_format($varr_cuenta['SALDO_COMPROMETIDO'][$i]['monto_comprometido'],2,'.',',').'</td>
                        <td data-label="MONTO PENDIENTE DE SALDO">'.number_format($varr_cuenta['SALDO_COMPROMETIDO'][$i]['monto_pendiente'],2,'.',',').'</td>
                    </tr>';
        }

        echo '  </tbody>
            </table>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>';
    }

    if ($varr_cuenta['SALDOS']['saldo_transito'] > 0){
        echo '
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">SALDO POR CONFIRMAR:</li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">MONTO</th>
                    <th scope="col">COMPROBANTE</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';
        
        for ($i=0; $i<count($varr_cuenta['SALDO_TRANSITO']); $i++){
            echo '  <tr>
                        <td data-label="ID">'.$varr_cuentas['SALDO_TRANSITO'][$i]['st_id'].'</td>
                        <td data-label="MONTO">'.number_format($varr_cuentas['SALDO_TRANSITO'][$i]['monto'],2,'.',',').'</td>
                        <td data-label="COMPROBANTE"><a href="'.$varr_cuentas['SALDO_TRANSITO'][$i]['comprobante'].'" target="_blank">Ver comprobante <span class="icon-file-text2" style="font-size:16px;"></span></a></td>
                        <td data-label="ACCION"><a href=javascript:confirma_saldo("'.$varr_cuentas['SALDO_TRANSITO'][$i]['st_id'].'")>Confirmar Saldo <span class="icon-calculator" style="font-size:16px;"></span></a></td>
                    </tr>';
        }

        echo '  </tbody>
            </table>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>';
    }
    ?>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">REGISTRO DE SALDO:</li>
        </ul>
        <ul>
            <li style="font-weight:bold;width:85px;padding-left:5px;padding-right:5px;">MONTO:</li>
            <li><input type="number" name="monto_saldo_carga" size="10" value="0" class="frminput_text"></li>
        </ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:120px;">COMPROBANTE:</li>
            <li><input type="file" name="comprobante_saldo_carga" id="comprobante_saldo_carga" class="frminput_text"></li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    
        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
        <ul style="margin-top:10px;">
    <?
        echo '
            <li class="botontransaccionazul" style="height:40px;"><a href=javascript:registrar_saldo("saldo")><span class="icon-calculator"></span> Registrar Saldo</a></li>';
    ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    
</BODY>
</HTML>