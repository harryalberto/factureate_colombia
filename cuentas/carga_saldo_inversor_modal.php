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

$obj_cuenta = new cuentas;
$obj_mae = new maestros;
$obj_inv = new inversiones;
$objsubasta = new subasta;

$vn_cuenta_id = $_GET['cuenta_id'];
$vn_moneda_id = $_GET['moneda_id'];

$varr_cuenta = $obj_cuenta->get_cuenta_detalle($_GET['cuenta_id']);
$varr_cuentas_full = $obj_cuenta->get_cuentas_inversor($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
$varr_bancos = $obj_mae->get_bancos();

$varr_texto = $_SESSION['user']['nombre'].$_SESSION['user']['apellido'];
$varr_texto_limpio = str_replace(">", "", $varr_texto);
$varr_texto_limpio = str_replace("<", "", $varr_texto_limpio);
$varr_texto_limpio = str_replace("?", "", $varr_texto_limpio);
$varr_texto_limpio = str_replace("=", "", $varr_texto_limpio);
$varr_texto_limpio = str_replace("'", "", $varr_texto_limpio);
$varr_texto_limpio = str_replace("''", "", $varr_texto_limpio);
$varr_nombre_cliente = $varr_texto_limpio;

$arr_saldos = $obj_cuenta->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
$varr_prop = $obj_inv->get_propuestas_pre_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,1000);

$v_monto_enpropuestas = $objsubasta->monto_propuestas_subasta($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$_GET['moneda_id']);
$v_monto_preliminares = $obj_cuenta->get_monto_total_preliminar($vn_cuenta_id,$vn_moneda_id,$varr_prop,$arr_saldos,$v_monto_enpropuestas);

/*--------------------------------------------------------*/

?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php

    //date_default_timezone_set("America/Lima");

    //------ PARTE SUPERIOR ------

    

    //------ PARTE IZQUIERDA ------

?>

    <!------ CUERPO VARIABLE ------>

    <form name='frm' method='post' id='frm_modal' enctype="multipart/form-data" method="POST">
        <input type="hidden" name="p_tipo_seleccionado" value="rc">
        <input type="hidden" name="p_id_cuenta" value="<?=$_GET['cuenta_id']?>">
        <input type="hidden" name="p_monto_total_preliminar" value="<?=$v_monto_preliminares?>">
        <input type="hidden" name="p_v_moneda" value="<?=$vn_moneda_id?>">
        <input type="hidden" name="p_v_nombre_cliente" value="<?=$varr_nombre_cliente?>">

        <div class="form-group">
            <label for="cuenta_id">Selecciona opcion&nbsp;</label>
            <input class="form-check-input" type="radio" name="rb_add_comp" id="rb_add_comp1" onclick="seleccion_opcion(1)" checked>
            <label class="form-check-label" for="rb_add_comp1">
                Registrar<br>Comprobante
            </label>
            <!--<input class="form-check-input" type="radio" name="rb_bco_pop" id="rb_bco_pop1" onclick="seleccion_opcion(2)">
            <label class="form-check-label" for="rb_bco_pop1">
                Transferir<br>Banco Popular
            </label>-->
        </div>

        <div id="content">
            <div class="form-group">
                <label for="cuenta_id">CUENTA ID</label>
                <input type="text" class="form-control" id="cuenta_id" name="cuenta_id" value="<?=$_GET['cuenta_id']?>" readonly>
            </div>

            <div class="form-group">
                <label for="banco_id">BANCO</label>
                <select id="banco_id" class="form-control" name="banco_id" onchange="CambiaBanco()">
                    <option value="0" selected>Seleccione un Banco</option>
                    <?php
                        for ($i=0; $i<count($varr_bancos); $i++){
                            echo '<option value="'.$varr_bancos[$i]['banco_id'].'">'.$varr_bancos[$i]['banco_nombre'].'</option>';
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="moneda_id">MONEDA</label>
                <?php
                    for ($i=0; $i<count($varr_cuentas_full); $i++){
                        if ($varr_cuentas_full[$i]['cuenta_id'] == $_GET['cuenta_id']){
                            echo '
                            <input type="text" class="form-control" id="moneda_nombre" name="moneda_nombre" value="'.$varr_cuentas_full[$i]['moneda'].'" readonly>
                            <input type="hidden" id="moneda_id" name="moneda_id" value="'.$varr_cuentas_full[$i]['moneda_id'].'">';
                        }            

                        echo '
                            <input type="hidden" id="cta'.$i.'" value="'.$varr_cuentas_full[$i]['banco_id'].'-'.$varr_cuentas_full[$i]['moneda_id'].'">
                            <input type="hidden" id="cuenta'.$varr_cuentas_full[$i]['moneda_id'].'" value="'.$varr_cuentas_full[$i]['cuenta_id'].'">';
                    }

                    echo '<input type="hidden" id="num_cuentas" value="'.count($varr_cuentas_full).'">';
                ?>

                <!-- </select> -->
            </div>

            <div class="form-group">
                <label for="monto">MONTO</label>
                <input type="text" id="monto_view" class="form-control" name="monto_view" placeholder="0.00">
                <input type="hidden" id="monto" name="monto" class="form-control">
            </div>

            <div class="form-group">
                <label for="comprobante">COMPROBANTE</label>
                <input type="file" class="form-control-file" id="comprobante" name="comprobante">
            </div>

            <!-- <hr> -->    

            <!--#######################################################
            ##################### BOTONERA
            ###########################################################-->

            <div>
                <button type="button" class="btn btn-primary" onclick="GuardarDeposito()" style="font-size:12px;background-color:var(--color-azulv2);border:none;" id="btn_registra_deposito">
                    <i class="fa-solid fa-piggy-bank" style="font-size:16px;"></i> Registrar Deposito
                </button>
            </div>
        </div>
    </form>

    <!------ END CUERPO VARIABLE ------>

    <script>
        function envia_pago(){
            var radioBtn = document.getElementById('rb_monto_total');
            if(radioBtn.checked == true)
                document.frm.mto.value = document.frm.monto_total.value;

            var radioBtn = document.getElementById('rb_monto_parcial');
            if(radioBtn.checked == true)
                document.frm.mto.value = document.frm.monto_parcial.value;

            var form = document.frm;
            var nro = form.nro.value;
            
            var mto = form.mto.value;
            var gls = form.gls.value;
            var adi = form.adi.value;
            var url = form.url.value

            var cnv = form.cnv.value;
            var rec = form.rec.value;
            var trx = form.trx.value;

            var mda = form.mda.value;

            document.location.href = "firma.php?nro="+nro+"&mto="+mto+"&gls="+gls+"&adi="+adi+"&url="+url+"&cnv="+cnv+"&rec="+rec+"&trx="+trx+"&mda="+mda;
            //document.frm.action = "firma.php";
            //document.frm.submit();
        }

        function seleccion_monto(param){
            // 1 : monto total - 2 : monto parcial
            if(param == 1){
                var radioBtn = document.getElementById('rb_monto_parcial');
                radioBtn.checked = false;

                var radioBtn = document.getElementById('rb_monto_total');
                radioBtn.checked = true;

                var txtParcial = document.getElementById('monto_parcial');
                txtParcial.value = "";
                txtParcial.disabled = true;                
            }else if(param == 2){
                var radioBtn = document.getElementById('rb_monto_total');
                radioBtn.checked = false;

                var radioBtn = document.getElementById('rb_monto_parcial');
                radioBtn.checked = true;

                var txtParcial = document.getElementById('monto_parcial');
                txtParcial.value = "";
                txtParcial.disabled = false;
            }
        }

        function seleccion_opcion(param){
            if(param == 1){
                //muestra datos de registro de comprobante

                //deshabilita transferir banco popular
                var radioBtn = document.getElementById('rb_bco_pop1');
                radioBtn.checked = false;

                document.frm.p_tipo_seleccionado.value = "rc";

                var p_id_cuenta = document.frm.p_id_cuenta.value;

                //aca viene ajax
                var parametros = new FormData();
                parametros.append('p_tipo',"reg_comprobante");
                parametros.append('p_id_cuenta',p_id_cuenta);
                
                $('#content').html('<div class="loading"><img src="../img/loader-small.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                
                $.ajax({
                    url: "cuenta_ajax.php",
                    type: "POST",
                    data:  parametros,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: function(data){
                        $('#content').html(data);
                    },
                    error: function(data){
                    }   
                });
            }
                

            if(param == 2){ //transferencia BP
                //deshabilita agrega comprobante
                var radioBtn = document.getElementById('rb_add_comp1');
                radioBtn.checked = false;

                document.frm.p_tipo_seleccionado.value = "tbp";
                var p_id_cuenta = document.frm.p_id_cuenta.value;
                var p_monto_total_preliminar = document.frm.p_monto_total_preliminar.value;
                var p_moneda_var = document.frm.p_v_moneda.value;
                var p_nombre_cliente = document.frm.p_v_nombre_cliente.value;

                //aca viene ajax
                var parametros = new FormData();
                parametros.append('p_tipo',"boton_pago");
                parametros.append('p_id_cuenta',p_id_cuenta);
                parametros.append('p_monto_total_preliminar',p_monto_total_preliminar);
                parametros.append('p_id_v_moneda',p_moneda_var);
                parametros.append('p_v_nombre_cliente',p_nombre_cliente);

                $('#content').html('<div class="loading"><img src="../img/loader-small.gif" alt="loading" /><br/>Un momento, por favor...</div>');
                
                $.ajax({
                    url: "cuenta_ajax.php",
                    type: "POST",
                    data:  parametros,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: function(data){
                        $('#content').html(data);
                    },
                    error: function(data){
                    }   
                });
            }
                
        }

        function CambiaBanco(){
            var v_moneda = $('#moneda_id').val();
            var v_cuenta = $('#cuenta_id').val();
            var v_banco = $('#banco_id').val();
            var v_num_cuentas = Number($('#num_cuentas').val());
            var v_proceder, i, v_cta;
            var cta_moneda = v_banco + '-' + v_moneda;            

            v_proceder = 0;

            if (v_num_cuentas > 1){
                for (i=0; i<v_num_cuentas; i++){
                    v_cta = $('#cta'+i).val();

                    if (v_cta == cta_moneda) v_proceder = 1;
                }

                if (v_proceder == 0)
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            } else{
                v_cta = $('#cta0').val();                

                if (v_cta == cta_moneda) v_proceder = 1;
                else 
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            }
        }

        function CambiaMoneda(){
            var v_moneda = $('#moneda_id').val();
            var v_cuenta = $('#cuenta_id').val();
            var v_banco = $('#banco_id').val();
            var v_num_cuentas = Number($('#num_cuentas').val());
            var v_proceder, i, v_cta;
            var cta_moneda = v_banco + '-' + v_moneda;

            v_proceder = 0;

            if (v_num_cuentas > 1){
                for (i=0; i<v_num_cuentas; i++){
                    v_cta = $('#cta'+i).val();

                    if (v_cta == cta_moneda) v_proceder = 1;
                }

                if (v_proceder == 0)
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            } else{
                v_cta = $('#cta0').val();

                if (v_cta == cta_moneda) v_proceder = 1;
                else 
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            }
        }

        function GuardarDeposito(){
            var v_banco = $('#banco_id').val();
            var v_monto = Number($('#monto').val());
            var v_moneda = $('#moneda_id').val();
            var v_cuenta = $('#cuenta'+v_moneda).val();
            var v_file = document.getElementById('comprobante');
            var v_ruta = v_file.value;            

            if (v_banco == 0) alert("Debe seleccionar un Banco");
            else 
                if (v_monto == 0 || v_monto == '') alert('Debe ingresar un monto valido');
                else 
                    if (v_ruta == '') alert('Debe seleccionar un comprobante de transferencia');
                    else{
                        document.getElementById('btn_registra_deposito').disabled = true;

                        var formData = new FormData(document.getElementById("frm_modal"));

                        $.ajax({
                            url:"cargar_saldo_inversor_transito.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html",
                            /*data:{
                                "cuenta_id":v_cuenta,
                                "banco_id":v_banco,
                                "monto":v_monto,
                                "moneda_id":v_moneda,
                                "comprobante":v_file
                            },*/

                            success:function(data,status){
                                $('#SaldosModal').fadeIn(1000).html(data);
                                $('#SaldosModal').modal('hide');
                                refresh_page();
                            }
                        });
                    }
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
    </script>
</BODY>
</HTML>