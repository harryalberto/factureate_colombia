<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../lib-trans/c_cuentas.php");
?>

<HTML>
<HEAD>

<?php
    require("../lib/head.php");
    $acceso = 'FINANCIAMIENTO';
    require("../lib/valida-acceso.php");
?>

</HEAD>

<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$vobj_cta_mod = new cuentas;

$varr_ot = $obj_mae->get_orden_transferencia($_GET['ot_id']);
    
$t_forden = strtotime($varr_ot['fecha_orden']);
$v_forden = date('d-m-Y',$t_forden);
$v_fhoy_en = date('Y-m-d');
/*--------------------------------------------------------*/
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>

    <!------ CUERPO VARIABLE ------>
    <div id="principal" style="padding-left: 10px;overflow: hidden;">
        <input type="hidden" name="ot_id" id="ot_id" value="<?=$_GET['ot_id']?>">
        <input type="hidden" name="moneda_id" id="moneda_id" value="<?=$varr_ot['moneda_id']?>">
        <input type="hidden" name="f_hoy" value="<?=$v_fhoy_en?>">
        <input type="hidden" name="motivo_id" id="montivo_id" value="<?=$varr_ot['motivo_id']?>">
        <input type="hidden" name="operacion_id" value="<?=$varr_ot['operacion_id']?>">
        <input type="hidden" name="destinatario_id" id="destinatario_id" value="<?=$varr_ot['destinatario_id']?>">
        <input type="hidden" name="subasta_id" value="<?=$varr_ot['subasta_id']?>">
        <input type="hidden" name="moneda" id="moneda" value="<?=$varr_ot['moneda']?>">
        <input type="hidden" name="accion" value="">

        <!--==== contenedor formulario principal ====-->
        <div class="contenedor_formulario">
            <!--++++++ Cabecera -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="orden_id">ID ORDEN</label>
                    <input type="text" name="orden_id" id="orden_id" value="<?=$_GET['ot_id']?>" style="text-align: center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="motivo">MOTIVO</label>
                    <input type="text" name="motivo" id="motivo" value="<?=$varr_ot['motivo']?>" style="text-align: center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="fecha">FECHA</label>
                    <input type="text" name="fecha" id="fecha" value="<?=$v_forden?>" style="text-align: center;" class="formulario_control" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <p style="width:100%; height: 1px; background-color: #000;"></p>
            </div>

            <!--++++++ Informacion transferencia -->
            <div class="contenedor_formulario_column">
                <p style="font-weight:bold;">INFORMACION DE LA TRANSFERENCIA:</p>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="destinatario">Nombre Destinatario</label>
                    <input type="text" name="destinatario" id="destinatario" value="<?=$varr_ot['destino_nombre']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdocumento">Tipo Doc</label>
                    <input type="text" name="tdocumento" id="tdocumento" value="<?=$varr_ot['tdocumento']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="documento">Tipo Doc</label>
                    <input type="text" name="documento" id="documento" value="<?=$varr_ot['identificacion']?>" class="formulario_control" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="banco">Banco</label>
                    <input type="text" name="banco" id="banco" value="<?=$varr_ot['banco']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tcuenta">Tipo Cuenta</label>
                    <input type="text" name="tcuenta" id="tcuenta" value="<?=$varr_ot['tcuenta']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="cuenta">Nro Cuenta</label>
                    <input type="text" name="cuenta" id="cuenta" value="<?=$varr_ot['cuenta_banco']?>" class="formulario_control" readonly>
                </div>
            </div>

<?php
    $varr_saldos = $vobj_cta_mod->get_saldos($varr_ot['destinatario_id'],0);

    for ($i = 0; $i < count($varr_saldos); $i++){
        if ($varr_saldos[$i]['moneda_id'] == $varr_ot['moneda_id']){
            $v_disponible = $varr_saldos[$i]['saldo_disponible'];
            break;
        }
    }
?>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="moneda">Moneda</label>
                    <input type="text" name="moneda" id="moneda" value="<?=$varr_ot['moneda']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="retiro">Monto Retiro</label>
                    <input type="text" name="retiro" id="retiro" value="<?=number_format($varr_ot['monto'],2,'.',',')?>" class="formulario_control" style="text-align:right;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="disponible">Monto Disponible</label>
                    <input type="text" name="disponible" id="disponible" value="<?=number_format($v_disponible,2,'.',',')?>" class="formulario_control" style="text-align:right;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="refresh">.</label>
                    <p id="refresh"><button type="button" class="btn btn-primary" style="font-size:11px;border:none;" onclick="refresh_disponible()"><i class="fa-solid fa-arrows-rotate"></i></button></p>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <p style="width:100%; height: 1px; background-color: #000;"></p>
            </div>

            <!--++++++ Ejecucion de transferencia -->
            <div class="contenedor_formulario_column">
                <p style="font-weight:bold;">EJECUCION DE TRANSFERENCIA:</p>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nro_operacion">Nro Operacion</label>
                    <input type="text" name="nro_operacion" id="nro_operacion" placeholder="# operacion banco" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="moneda">Moneda</label>
                    <input type="text" name="moneda" id="moneda" value="<?=$varr_ot['moneda']?>" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="monto_transferencia_view">Monto</label>
                    <input type="text" name="monto_transferencia_view" id="monto_transferencia_view" value="<?=number_format($varr_ot['monto'],2,'.',',')?>" class="formulario_control" style="text-align:right;">
                    <input type="hidden" name="monto_transferencia" id="monto_transferencia" value="<?=$varr_ot['monto']?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 400px;">
                    <label for="comprobante">Comprobante:</label>
                    <input type="file" name="comprobante" id="comprobante" class="formulario_control" style="background-color:#fff;">
                </div>
            </div>

            <!--++++ botones -->
            <div class="contenedor_formulario_column">
                <p style="width:100%; height: 1px; background-color: #000;"></p>
            </div>

            <div class="contenedor_formulario_column">
                <p>
                    <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="acciones('transferir')"><i class="fa-solid fa-money-bill-transfer"></i> Transferir</button>
                </p>
            </div>
        </div>

    </div>
    <!------ END DIV PRINCIPAL ------>

    <!--++++++ zona JS ++++++-->
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

        document.getElementById("monto_transferencia_view").addEventListener("input", function (e) {
            const input = document.getElementById("monto_transferencia_view");
            const input_real = document.getElementById("monto_transferencia");

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

        function acciones(p_accion){
            var validacion = 0;

            if (p_accion == 'transferir'){
                var nro_operacion = document.getElementById("nro_operacion").value;
                var monto_transferencia = Number(document.getElementById("monto_transferencia").value);
                var comprobante = document.getElementById("comprobante").value;

                if (nro_operacion == '' || monto_transferencia <= 0) alert('Debe completar los datos de la transferencia');
                else{
                    if (comprobante == '') alert ('Debe ingresar el comprobante de la transferencia');
                    else validacion = 1;
                }
            }

            if (validacion > 0){
                var formData = new FormData();
                var inputFile_comprobante = document.getElementById("comprobante");
                var file_comprobante = inputFile_comprobante.files[0];
                var moneda_id = document.getElementById("moneda_id").value;
                var monto = document.getElementById("monto_transferencia").value;
                var ot_id = document.getElementById("ot_id").value;
                var inversor_id = document.getElementById("destinatario_id").value;
                var operacion_banco =document.getElementById("nro_operacion").value;
                var moneda = document.getElementById("moneda").value;
                var banco = document.getElementById("banco").value;
                var tcuenta = document.getElementById("tcuenta").value;
                var cuenta = document.getElementById("cuenta").value;

                formData.append('accion', 'transferencia_inver')
                formData.append('file_comprobante', file_comprobante)
                formData.append('moneda_id', moneda_id)
                formData.append('monto', monto)
                formData.append('ot_id', ot_id)
                formData.append('inversor_id', inversor_id)
                formData.append('operacion_banco', operacion_banco)
                formData.append('moneda', moneda)
                formData.append('banco', banco)
                formData.append('tcuenta', tcuenta)
                formData.append('cuenta', cuenta)

                $.ajax({
                    url: "orden_transferencia_proceso_v2.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data)
                    {
                        if (data == 1){
                            alert('La transferencia fue realizada');
                            refresh_page();
                        } else alert('Ocurrio un error');
                    }
                });
            }
        }

        function refresh_disponible(){
            var inversor_id = document.getElementById("destinatario_id").value;
            var moneda_id = document.getElementById("moneda_id").value;
            var disponible = document.getElementById("disponible");

            var formData = new FormData();

            formData.append('inversor_id', inversor_id)
            formData.append('moneda_id', moneda_id)

            $.ajax({
                url: "revisa_disponible_proceso.php",
                type: "POST",
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {
                    let formateado = formatearNumero(data);
                    disponible.value = formateado;
                }
            });
        }
    </script>
    
</BODY>
</HTML>