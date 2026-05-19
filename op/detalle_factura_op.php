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

<?
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>

</HEAD>

<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@ LOGICA
$vobj_factura_modal = new factura;
$vobj_mae_modal = new maestros;

$varr_factura = $vobj_factura_modal->get_datos_factura($_GET['fid']);

$v_femision = date('d-m-Y', strtotime($varr_factura['femision']));
$v_fvto = date('d-m-Y', strtotime($varr_factura['fvencimiento']));

if (is_null($varr_factura['f_confirmacion']) || $varr_factura['f_confirmacion'] == '') $v_fconfirma = '---';
else $v_fconfirma = date('d-m-Y', strtotime($varr_factura['f_confirmacion']));

$v_monto_total = number_format($varr_factura['total'], 2, '.',',');

$varr_param = $vobj_mae_modal->get_parametros();
$v_factureate_rz = $varr_param['RAZON SOCIAL FACTUREATE']['valorchar'];
$v_id_factureate = $varr_param['ID FACTUREATE']['valorchar'];

$varr_tipo_cuenta = $vobj_mae_modal->get_tipos('MONEDA');
$varr_bancos = $vobj_mae_modal->get_bancos();

if ($varr_factura['monedaid'] == 20) {
    $v_nro_cuenta = $varr_param['CUENTA NACIONAL']['valorchar'];
    $v_tipo_cuentaid = $varr_param['CUENTA NACIONAL']['valornum'];
    $v_bancoid = $varr_param['BANCO CTA NACIONAL']['valornum'];

    for ($i = 0; $i < count($varr_tipo_cuenta); $i++){
        if ($v_tipo_cuentaid == $varr_tipo_cuenta[$i]['id']) $v_tipo_cuenta = $varr_tipo_cuenta[$i]['nombre'];
    }

    for ($j = 0; $j < count($varr_bancos); $j++){
        if ($v_bancoid == $varr_bancos[$j]['banco_id']) $v_banco = $varr_bancos[$j]['banco_nombre'];
    }
} else {
    $v_nro_cuenta = $varr_param['CUENTA DOL']['valorchar'];
    $v_tipo_cuentaid = $varr_param['CUENTA DOL']['valornum'];
    $v_bancoid = $varr_param['BANCO CTA DOL']['valornum'];

    for ($i = 0; $i < count($varr_tipo_cuenta); $i++){
        if ($v_tipo_cuentaid == $varr_tipo_cuenta[$i]['id']) $v_tipo_cuenta = $varr_tipo_cuenta[$i]['nombre'];
    }

    for ($j = 0; $j < count($varr_bancos); $j++){
        if ($v_bancoid == $varr_bancos[$j]['banco_id']) $v_banco = $varr_bancos[$j]['banco_nombre'];
    }
}

?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <input type="hidden" name="factura_id" id="factura_id" value="<?=$_GET['fid']?>">

    <div id="principal" style="padding-left: 10px; overflow: hidden;">

        <div class="contenedor_formulario" style="width: 100%; overflow: hidden;">

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="factura">FACTURA NRO:</label>
                    <input type="text" name="factura" id="factura" class="formulario_control" value="<?=$varr_factura['factura']?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="f-emision">F EMISION:</label>
                    <input type="text" name="f-emision" id="f-emision" class="formulario_control" value="<?=$v_femision?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="f-vencimiento">F VTO:</label>
                    <input type="text" name="f-vencimiento" id="f-vencimiento" class="formulario_control" value="<?=$v_fvto?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="f-acuerdo">F PAGO:</label>
                    <input type="text" name="f-acuerdo" id="f-acuerdo" class="formulario_control" value="<?=$v_fconfirma?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="emisor-id">PROVEEDOR RNC:</label>
                    <input type="text" name="emisor-id" id="emisor-id" class="formulario_control" value="<?=$varr_factura['emisorid']?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="emisor">PROVEEDOR:</label>
                    <input type="text" name="emisor" id="emisor" class="formulario_control" value="<?=$varr_factura['emisor']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="moneda">MONEDA:</label>
                    <input type="text" name="moneda" id="moneda" class="formulario_control" value="<?=$varr_factura['moneda']?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="monto">MONTO TOTAL:</label>
                    <input type="text" name="monto" id="monto" class="formulario_control" value="<?=$v_monto_total?>" style="text-align: right;" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <p style="width: 500px; background: var(--color-oro); margin-top: 10px; padding: 3px;">
                    Puede realizar el pago de la factura por el BOTON DE PAGO o realizar una transferencia a la cuenta de <?echo $v_factureate_rz;?>, RNC <?echo $v_id_factureate;?>,
                    moneda <?echo $varr_factura['moneda'];?>, Nro de cuenta <?echo $v_nro_cuenta;?>, Tipo de cuenta <?echo $v_tipo_cuenta;?>, Banco <?echo $v_banco;?>,
                    indicando como referencia o comentario el texto OPERACION <?echo $_GET['fid'];?>
                </p>
            </div>

        </div>  <!-- END CONTENEDOR FORMULARIO -->

        <!--========================================
        ===================== BOTONERA
        ============================================-->
        <div style="width:100%; float:left;margin-bottom:5px;overflow: hidden;">
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_pagar" class="btn btn-primary" onclick="pagarFactura()">
                    <i class="fa-solid fa-money-check-dollar"></i> Boton de pago
            </button>
        </div>

    </div> <!-- END CONTENEDOR PRINCIPAL -->

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">

        function pagarFactura(){
            var factura_id = document.getElementById("factura_id").value;

            botonPago(factura_id);
        }

    </script>

</BODY>
</HTML>