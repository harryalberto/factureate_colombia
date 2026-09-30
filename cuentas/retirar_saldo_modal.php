<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_subasta.php");
?>

<HTML>
<HEAD>

    <?php
        require("../lib/head.php");
        $acceso = 'CUENTAS';
        require("../lib/valida-acceso.php");
    ?>

</HEAD>

<?php

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$vobj_cuentas_mod = new cuentas;
$vobj_mae_mod = new maestros;

$varr_cuenta = $vobj_cuentas_mod->get_cuenta_detalle($_GET['cuenta_id']);
$varr_inversor = $vobj_mae_mod->get_datos_inversor($_GET['inversor_id']);
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <!--+++ datosque seran guardados -->
    <input type="hidden" name="cuenta_id" id="cuenta_id" value="<?=$_GET['cuenta_id']?>">
    <input type="hidden" name="inversor_id" id="inversor_id" value="<?=$_GET['inversor_id']?>">
    <input type="hidden" name="moneda_id" id="moneda_id" value="<?=$varr_cuenta['HEADER']['moneda_id']?>">
    <input type="hidden" name="retiro" id="retiro" value="0">
    <input type="hidden" name="inversor" id="inversor" value="<?=$varr_inversor['inversor_nombre'].' '.$varr_inversor['inversor_apellido']?>">

    <!--+++ div principal +++-->
    <div id="principal" style="padding-left: 10px;overflow: hidden;">

        <div class="contenedor_formulario">

            <!--+++ contenedor 1 +++-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="moneda">Moneda:</label>
                    <input type="text" name="moneda" id="moneda" value="<?=$varr_cuenta['HEADER']['moneda']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="disponible">Disponible:</label>
                    <input type="text" name="disponible" id="disponible" value="<?=number_format($varr_cuenta['SALDOS']['saldo_disponible'],2,'.',',')?>" class="formulario_control" style="text-align: right;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="retiro_view">Retirar:</label>
                    <input type="text" name="retiro_view" id="retiro_view" placeholder="0.00" class="formulario_control" style="text-align: right;" onchange="monto_retirar()">
                </div>
            </div>

<?php
    $varr_cuentas_banco = $vobj_cuentas_mod->get_cuentas_banco_inversor($varr_cuenta['HEADER']['inversor_id']);
    $v_cuentas_banco = 0;

    if (count($varr_cuentas_banco) > 0){
        for ($i = 0; $i < count($varr_cuentas_banco); $i++){
            if ($varr_cuentas_banco[$i]['moneda_id'] == $varr_cuenta['HEADER']['moneda_id']) $v_cuentas_banco ++;
        }
    }

    if ($v_cuentas_banco <= 0){
?>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 500px;">
                    <p>* Lo sentimos pero no tiene cuentas de banco registradas en la moneda seleccionada, debe registrar una cuenta de banco en su perfil de la moneda en que desea hacer el retiro</p>
                </div>
            </div>
<?php
    } else {
?>
            <!--+++ contenedor 2 +++-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="banco">Banco:</label>
                    <select name="banco" id="banco" class="formulario_control" onchange="cambiar_banco()">

<?php
        for ($i = 0; $i < count($varr_cuentas_banco); $i++){
            if ($varr_cuentas_banco[$i]['moneda_id'] == $varr_cuenta['HEADER']['moneda_id']){
                if ($i == 0) echo '
                        <option value="'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" selectec>
                            '.$varr_cuentas_banco[$i]['banco_nombre'].'(Nro '.$varr_cuentas_banco[$i]['cuenta'].' - '.$varr_cuentas_banco[$i]['tcuenta_nombre'].')'.'
                        </option>';
                else echo '
                        <option value="'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'">
                            '.$varr_cuentas_banco[$i]['banco_nombre'].'(Nro '.$varr_cuentas_banco[$i]['cuenta'].' - '.$varr_cuentas_banco[$i]['tcuenta_nombre'].')'.'
                        </option>';
            }
        }
?>

                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="cuenta_nro">Cuenta:</label>
                    <input type="text" name="cuenta_nro" id="cuenta_nro" value="<?=$varr_cuentas_banco[0]['cuenta']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="tcuenta">Tipo Cuenta:</label>
                    <input type="text" name="tcuenta" id="tcuenta" value="<?=$varr_cuentas_banco[0]['tcuenta_nombre']?>" class="formulario_control" readonly>
                </div>
            </div>

<?php
        for ($i = 0; $i < count($varr_cuentas_banco); $i++){
            if ($varr_cuentas_banco[$i]['moneda_id'] == $varr_cuenta['HEADER']['moneda_id']){
                echo '  <input type="hidden" name="tcuenta_'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" id="tcuenta_'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" value="'.$varr_cuentas_banco[$i]['tcuenta_nombre'].'">
                        <input type="hidden" name="cuentanro_'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" id="cuentanro_'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" value="'.$varr_cuentas_banco[$i]['cuenta'].'">
                        <input type="hidden" name="tcuentaid_'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" id="tcuentaid_'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'" value="'.$varr_cuentas_banco[$i]['tcuenta_id'].'">';
            }
        }
    }
?>

            <!--==== contenedor bloque botonera ====-->
            <div style="margin-top: 50px;width: 100%; float: right;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="retirar()" id="btn_retirar">
                    Retirar <i class="fa-solid fa-download"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- div para el spinner de loadgin -->
    <div id="loadingModal" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-title">Procesando </div>
            <div class="loading-subtitle">............................</div>
        </div>
    </div>
    <!-- fin del spinner loading -->

    <!--++++++++++++++++++++++++++++++++++++++++++++++++
    +++++++++ zona JS
    ++++++++++++++++++++++++++++++++++++++++++++++++++++-->
    <script>
        function formatearNumero(valor) {
            if (!valor) return "";

            let tienePuntoFinal = valor.endsWith(".");

            let partes = valor.split(".");
            let entero = partes[0];
            let decimal = partes[1] || "";

            // Formatear miles SOLO en la parte entera
            entero = entero.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            if (tienePuntoFinal) {
                return entero + ".";
            }

            return decimal ? `${entero}.${decimal}` : entero;
        }

        document.getElementById("retiro_view").addEventListener("input", function (e) {
            const input = document.getElementById("retiro_view");
            const input_real = document.getElementById("retiro");

            const teclasPermitidas = [
                "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
            ];

            // GUARDAR POSICION DEL CURSOS
            let valor_original = input.value;
            let cursor = input.selectionStart;

            // Contar cuántas comas había antes del cursor
            let antesCursor = valor_original.slice(0, cursor);
            let comasAntes = (antesCursor.match(/,/g) || []).length;

            // Limpiar valor
            let limpio = valor_original.replace(/,/g, "");
            limpio = limpio.replace(/[^0-9.]/g, "");

            // Evitar múltiples puntos
            let partes = limpio.split(".");
            if (partes.length > 2) {
                limpio = partes[0] + "." + partes[1];
            }

            // Limitar decimales
            partes = limpio.split(".");
            if (partes[1]) {
                limpio = partes[0] + "." + partes[1].slice(0, 2);
            }

            // Guardar valor real
            input_real.value = limpio;

            // Formatear
            let formateado = formatearNumero(limpio);

            // Recalcular cursor
            let nuevoAntesCursor = formateado.slice(0, cursor);
            let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

            let nuevaPos = cursor + (comasDespues - comasAntes);

            input.value = formateado;
            input.setSelectionRange(nuevaPos, nuevaPos);
        });

        function cambiar_banco(){
            var cuenta_id = document.getElementById("banco").value;
            var cuentanro = document.getElementById("cuentanro_"+cuenta_id).value;
            var tcuenta =  document.getElementById("tcuenta_"+cuenta_id).value;

            var obj_cuentanro = document.getElementById("cuenta_nro");
            var obj_tcuenta = document.getElementById("tcuenta");

            obj_cuentanro.value = cuentanro;
            obj_tcuenta.value = tcuenta;
        }

        function retirar(){
            var cuenta_banco_id = document.getElementById("banco").value;
            var moneda_id = document.getElementById("moneda_id").value;
            var cuenta_id = document.getElementById("cuenta_id").value;
            var nro_cuenta = document.getElementById("cuenta_nro").value;
            var tcuenta_id = document.getElementById("tcuentaid_"+cuenta_banco_id).value;
            var inversor_id = document.getElementById("inversor_id").value;
            var inversor = document.getElementById("inversor").value;

            var disponible = document.getElementById("disponible").value;
            var retiro = document.getElementById("retiro").value;

            if (retiro <= disponible){
                var formData = new FormData();

                formData.append('cuenta_id', cuenta_id)
                formData.append('cuenta_banco_id', cuenta_banco_id)
                formData.append('moneda_id', moneda_id)
                formData.append('nro_cuenta', nro_cuenta)
                formData.append('tcuenta_id', tcuenta_id)
                formData.append('inversor_id', inversor_id)
                formData.append('inversor', inversor)
                formData.append('monto', retiro)

                //==== llamada al spinner
                mostrarLoading();

                $.ajax({
                    url: "retirar_saldo_proceso.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data)
                    {
                        //==== ocultar el spinner
                        ocultarLoading();

                        if (data == 1){
                            alert('Todo salio bien, ya estamos realizando la transferencia a su cuenta bancaria');
                            refresh_page();
                        } else {
                            if (data == 2){
                                alert('Todo salio bien, se realizo la transferencia a su cuenta bancaria')
                                refresh_page();
                            } else alert('Algo salio mal, contactese con soporte');
                        }
                    }
                });
            } else alert("No puede retirar mas del monto disponible");
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