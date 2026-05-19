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
$vobj_maestros_modal = new maestros;

$varr_factura = $vobj_factura_modal->get_datos_factura($_GET['fid']);

$v_femision = date('d-m-Y', strtotime($varr_factura['femision']));
$v_fvto = date('d-m-Y', strtotime($varr_factura['fvencimiento']));
$v_fvto_en = $varr_factura['fvencimiento'];

if (is_null($varr_factura['f_confirmacion']) || $varr_factura['f_confirmacion'] == '') $v_fconfirma = '---';
else $v_fconfirma = date('d-m-Y', strtotime($varr_factura['f_confirmacion']));

//==== parametros
$varr_parametros = $vobj_maestros_modal->get_parametros();
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <input type="hidden" name="factura_id" id="factura_id" value="<?=$_GET['fid']?>">
    <input type="hidden" name="maximo_prorroga" id="maximo_prorroga" value="<?=$varr_parametros['MAXIMO PRORROGA']['valornum']?>">

    <div id="principal" style="padding-left: 10px; overflow: hidden;">

        <div class="contenedor_formulario" style="width: 100%; overflow: hidden;">

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="factura">FACTURA NRO:</label>
                    <input type="text" name="factura" id="factura" class="formulario_control" value="<?=$varr_factura['factura']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="f-emision">F EMISION:</label>
                    <input type="text" name="f-emision" id="f-emision" class="formulario_control" value="<?=$v_femision?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="f-vencimiento">F VTO:</label>
                    <input type="text" name="f-vencimiento" id="f-vencimiento" class="formulario_control" value="<?=$v_fvto?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="f-acuerdo">F PAGO PROG:</label>
                    <input type="text" name="f-acuerdo" id="f-acuerdo" class="formulario_control" value="<?=$v_fconfirma?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="f-acuerdo-nuevo">PROG PAGO:</label>
                    <div style="display: flex;">
                        <input type="text" id="f-acuerdo-nuevo-view" name="f-acuerdo-nuevo-view" value="<?=$v_fvto?>" class="formulario_control" style="background:#fff; width:80px;" readonly>
                        <input type='date' name='f-acuerdo-nuevo' id='f-acuerdo-nuevo' value='<?=$v_fvto_en?>' class="formulario_control" onchange="javascript:cambia_fecha()" style="width: 30px;font-size:18px;color:transparent;cursor:pointer;">
                    </div>
                </div>
            </div>

        </div>  <!-- END CONTENEDOR FORMULARIO -->

        <!--========================================
        ===================== BOTONERA
        ============================================-->
        <div style="width:100%; float:left;margin-bottom:5px;overflow: hidden;">
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_prog" class="btn btn-primary" onclick="programarPago()">
                    <i class="fa-regular fa-calendar"></i> Programar Pago
            </button>
        </div>

    </div> <!-- END CONTENEDOR PRINCIPAL -->

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">

        function cambia_fecha(){
            var v_fecha = $('#f-acuerdo-nuevo').val();
            let [anio, mes, dia] = v_fecha.split("-");
            let v_fecha_esp = `${dia}-${mes}-${anio}`;

            let obj_fecha_view = document.getElementById('f-acuerdo-nuevo-view');
            obj_fecha_view.value = v_fecha_esp;
        }

        function programarPago(){
            var factura_id = document.getElementById('factura_id').value;
            var fecha_prog = document.getElementById('f-acuerdo-nuevo').value;
            var fecha_venc = document.getElementById('f-vencimiento').value;
            var maximo = $('#maximo_prorroga').val();
            var validacion = 0;

            //validacion de la postergacion maxima
            var acuerdo = new Date(fecha_prog);
            var vencimiento = new Date(fecha_venc);
            var diff = acuerdo.getTime() - vencimiento.getTime();
            var diferencia = Math.round(diff / (1000 * 60 * 60 * 24));

            if (diferencia > maximo){
                var texto = "No esta permitido postergar el pago tantos dias";
                alert(texto);
            } else validacion = 1;

            if (validacion > 0){
                let formaData = new FormData()
                formaData.append('factura_id', factura_id)
                formaData.append('f_prog', fecha_prog)

                fetch("programa_pago_op_proc.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    //content.innerHTML = data.data
                    alert('El acuerdo de pago fue registrado');
                    getData();
                    cierraModal();
                })
                .catch(err => console.log(err))
            }
        }

    </script>

</BODY>
</HTML>