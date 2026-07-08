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
//==========================================
//=======Logica
$vobj_cuentas_mod = new cuentas;
$vobj_sub_mod = new subasta;
$vobj_inv_mod = new inversiones;
$vobj_mae_mod = new maestros;

$varr_cuentas = $vobj_cuentas_mod->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
$varr_preprop = $vobj_inv_mod->get_propuestas_pre_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,1000);

//==== encuentro el saldo disponible
for ($i = 0; $i < count($varr_cuentas); $i++){
    if ($varr_cuentas[$i]['moneda_id'] == $_GET['moneda_id']) {
        $v_disponible = $varr_cuentas[$i]['saldo_disponible'];
        $v_moneda = $varr_cuentas[$i]['moneda'];
        $v_cuenta_id = $varr_cuentas[$i]['cuenta_id'];
        break;
    }
}

//==== calculo el monto de las preliminares
$v_monto_preliminar = 0;

for ($j= 0; $j < count($varr_preprop); $j++){
    if ($varr_preprop[$j]['estado_id'] == 54){  // estado preliminar
        if ($varr_preprop[$j]['moneda_id'] == $_GET['moneda_id']){
            $v_monto_preliminar = $v_monto_preliminar + $varr_preprop[$j]['monto'];
        }
    }
}

//==== calculo del monto pendiente
$v_monto_enpropuestas = $vobj_sub_mod->monto_propuestas_subasta($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$_GET['moneda_id']);
$v_pendiente = $v_disponible - $v_monto_enpropuestas;
$v_pendiente = $v_monto_preliminar - $v_pendiente;
//==========================================
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <div id="principal" style="padding-left: 10px; overflow: hidden;">
        <div class="contenedor_formulario" style="width: 100%; overflow: hidden;">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="moneda">MONEDA:</label>
                    <input type="text" name="moneda" id="moneda" class="formulario_control" value="<?=$v_moneda?>" readonly>
                    <input type="hidden" name="moneda_id" id="moneda_id" value="<?= $_GET['moneda_id'] ?>">
                    <input type="hidden" name="cuenta_id" id="cuenta_id" value="<?= $v_cuenta_id ?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="sdisponible">SALDO DISPO:</label>
                    <input type="text" name="sdisponible" id="sdisponible" class="formulario_control" value="<?=number_format($v_disponible,2,'.',',')?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="propuesta">EN PROPUESTA:</label>
                    <input type="text" name="propuesta" id="propuesta" class="formulario_control" value="<?=number_format($v_monto_enpropuestas,2,'.',',')?>" readonly>
                </div>
            </div>

            <!--==== listado de preliminares -->
            <div style="width:90%; float:left;margin-bottom:5px;overflow: hidden;margin-top: 10px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID OPERACION</th>      <th scope="col" class="sort asc">PAGADOR</th>
                        <th scope="col" class="sort asc">PRE PROPUESTA</th>
                    </tr>
                </thead>
                <tbody>

<?php
    for ($i = 0; $i < count($varr_preprop); $i++){
    //===========================================
        if ($varr_preprop[$i]['estado_id'] == 54){  // estado preliminar
            if ($varr_preprop[$i]['moneda_id'] == $_GET['moneda_id']){
?>
                    <tr>
                        <td data-label="ID OPERACION"><?php echo $varr_preprop[$i]['factura_id']; ?></td>    <td data-label="PAGADOR"><?php echo $varr_preprop[$i]['cliente_nombre']; ?></td>
                        <td data-label="PRE PROPUESTA"><?php echo number_format($varr_preprop[$i]['monto'],2,'.',','); ?></td>
                    </tr>

<?php
            }
        }
    //===========================================
    }
?>
                </tbody>
            </table>
            </div>

            <!--==== gestion de pendiente -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="preliminar">PRELIMINAR:</label>
                    <input type="text" name="preliminar" id="preliminar" class="formulario_control" value="<?=number_format($v_monto_preliminar,2,'.',',')?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="pendiente">PENDIENTE:</label>
                    <input type="text" name="pendiente" id="pendiente" class="formulario_control" value="<?=number_format($v_pendiente,2,'.',',')?>" readonly>
                </div>
            </div>

            <!-- registro de comprobante -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="banco_id">BANCO:</label>
                    <select name="banco_id" id="banco_id" class="formulario_control" onchange="cambiaBanco()">
                        <option value="0" selected>Seleccione un Banco</option>

<?php
    $varr_bancos = $vobj_mae_mod->get_bancos();

    for ($i=0; $i<count($varr_bancos); $i++){
        echo '          <option value="'.$varr_bancos[$i]['banco_id'].'">'.$varr_bancos[$i]['banco_nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="banco_id">MONTO:</label>
                    <input type="text" id="monto_view" class="formulario_control" name="monto_view" value="<?=number_format($v_pendiente,2,'.',',')?>">
                    <input type="hidden" id="monto" name="monto" value="<?=$v_pendiente?>">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="comprobante">COMPROBANTE:</label>
                    <input type="file" class="formulario_control" id="comprobante" name="comprobante" style="background-color:#fff;">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <button type="button" class="btn btn-primary" onclick="GuardarDeposito()" style="font-size:12px;background-color:var(--color-azulv2);border:none;" id="btn_registra_deposito">
                    <i class="fa-solid fa-piggy-bank" style="font-size:16px;"></i> Registrar Deposito
                </button>
            </div>

        </div>
    </div>

    <!--===============================================
    ============ div para el spinner de loadgin -->
    <div id="loadingModal" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-title">Procesando </div>
            <div class="loading-subtitle">............................</div>
        </div>
    </div>
    <!--===============================================
    ============ fin del spinner loading -->

    <!--=============================================
    ======== Zona JS -->
    <script>
        function cambiaBanco(){
            alert('Considere que la cuenta de banco desde donde realizó la transferencia debe estar a su nombre, de lo contrario la transferencia será rechazada');
        }

        document.getElementById("monto_view").addEventListener("input", function (e) {
            const input = document.getElementById("monto_view");
            const input_real = document.getElementById("monto");

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

        function GuardarDeposito(){
            var v_banco = $('#banco_id').val();
            var v_monto = Number($('#monto').val());
            var v_moneda = $('#moneda_id').val();
            var v_moneda_nombre = $('#moneda').val();
            var v_cuenta = $('#cuenta_id').val();
            var v_file = document.getElementById('comprobante');
            var v_ruta = v_file.value;

            if (v_banco == 0) alert("Debe seleccionar un Banco");
            else
                if (v_monto == 0 || v_monto == '') alert('Debe ingresar un monto valido');
                else
                    if (v_ruta == '') alert('Debe seleccionar un comprobante de transferencia');
                    else{
                        document.getElementById('btn_registra_deposito').disabled = true;

                        var formData = new FormData();

                        formData.append('cuenta_id', v_cuenta);
                        formData.append('comprobante', $('#comprobante')[0].files[0]);
                        formData.append('monto', v_monto);
                        formData.append('banco_id', v_banco);
                        formData.append('moneda_nombre', v_moneda_nombre);

                        //==== llamada al spinner
                        mostrarLoading();

                        $.ajax({
                            url:"pendiente_deposito_proceso.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html",

                            success:function(data,status){
                                //==== ocultar el spinner
                                ocultarLoading();

                                alert('Su deposito se encuentra en saldo en transito el cual será validado por nuestros analistas para pasar a ser disponible en su cuenta');
                                refresh_page();
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
        //=======================================
    </script>
    <!--=============================================-->
</BODY>
</HTML>