<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = '';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php

//------ LOGICA NO VISIBLE ------
$vobj_modal_mae = new maestros;


//------ END LOGICA NO VISIBLE ------
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<div id="principal" style="padding-left: 10px;overflow: hidden;">
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" id="empresa_id" name="empresa_id" value="<?=$_GET['empresa_id']?>">

        <!--==== contenedor formulario principal ====-->
        <div class="contenedor_formulario">
            <!--==== contenedor bloque 1 ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="banco_id">Banco:</label>
                    <select id="banco_id" name="banco_id" class="formulario_control">
                        <option value="0"><++ Seleccione Banco ++></option>

<?php
    $varr_bancos = $vobj_modal_mae->get_bancos();

    for ($i = 0; $i < count($varr_bancos); $i++){
        echo '          <option value='.$varr_bancos[$i]["banco_id"].'>'.$varr_bancos[$i]["banco_nombre"].'</option>';
    }
?>

                    </select>
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="tipo_cuenta">Tipo Cuenta:</label>
                    <select id="tipo_cuenta" name="tipo_cuenta" class="formulario_control">
                        <option value="0"><++ Tipo Cuenta ++></option>

<?php
    $varr_tcuenta = $vobj_modal_mae->get_tipos('TIPO CUENTA BANCO');

    for ($i = 0; $i < count($varr_tcuenta); $i++){
        echo '          <option value='.$varr_tcuenta[$i]["id"].'>'.$varr_tcuenta[$i]["nombre"].'</option>';
    }
?>

                    </select>
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="moneda_id">Moneda:</label>
                    <select id="moneda_id" name="moneda_id" class="formulario_control">
                        <option value="0"><++ Seleccione Moneda ++></option>

<?php
    $varr_monedas = $vobj_modal_mae->get_tipos('MONEDA');

    for ($i = 0; $i < count($varr_monedas); $i++){
        echo '          <option value='.$varr_monedas[$i]["id"].'>'.$varr_monedas[$i]["nombre"].'</option>';
    }
?>

                    </select>
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nro_cuenta">Nro Cuenta:</label>
                    <input type="text" name="nro_cuenta" id="nro_cuenta" class="formulario_control">
                </div>
            </div>

            <!--==== contenedor bloque 2 ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 400px;">
                    <label for="cert_banco">Certificado Banco:</label>
                    <input type="file" id="cert_banco" name="cert_banco" class="formulario_control">
                </div>
            </div>

            <!--==== contenedor bloque botonera ====-->
            <div style="margin-top: 50px;width: 100%; float: right;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="continuar()" id="btn_continuar">
                    Terminar <i class="fa-solid fa-check-double"></i>
                </button>
            </div>
        </div>  <!--==== END contenedor formulario principal ====-->
    </form>
</div>  <!--==== END contenedor principal ====-->

    <!-- div para el spinner de loadgin -->
    <div id="loadingModal" class="loading-overlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div class="loading-title">Procesando </div>
            <div class="loading-subtitle">............................</div>
        </div>
    </div>
    <!-- fin del spinner loading -->

<!--################ ZONA JS ####################-->
<script>
    function continuar(){
        var banco = $('#banco_id').val();
        var tipo_cuenta = $('#tipo_cuenta').val();
        var nro_cuenta = $('#nro_cuenta').val();
        var empresa_id = $('#empresa_id').val();
        var moneda_id = $('#moneda_id').val();
        var certificado = $('#cert_banco').val();

        var todo_ok = 1;

        if (banco == "0"){
            alert('Debe seleccionar un Banco');
            $('#banco_id').focus();
            todo_ok = 0;
        }

        if (tipo_cuenta == "0" && todo_ok == 1){
            alert('Debe seleccionar un tipo de cuenta');
            $('#tipo_cuenta').focus();
            todo_ok = 0;
        }

        if (moneda_id == "0" && todo_ok == 1){
            alert('Debe seleccionar una Moneda');
            $('#moneda_id').focus();
            todo_ok = 0;
        }

        if (nro_cuenta == "" && todo_ok == 1){
            alert('Debe ingresar un nro de cuenta');
            $('#nro_cuenta').focus();
            todo_ok = 0;
        }

        if (certificado == "" && todo_ok == 1){
            alert('Debe adjuntar el certificado bancario');
            $('#cert_banco').focus();
            todo_ok = 0;
        }

        if (todo_ok == 1) {
            btn_continuar.disabled = true;
            //---- guardo la informacion ----
            var formaData = new FormData();

            formaData.append('empresa_id', empresa_id)
            formaData.append('accion', 'cuentas_emisor')
            formaData.append('banco', banco)
            formaData.append('tipo_cuenta', tipo_cuenta)
            formaData.append('moneda', moneda_id)
            formaData.append('nro_cuenta', nro_cuenta)

            var inputFile_cuenta = document.getElementById("cert_banco");
            var file_cuenta = inputFile_cuenta.files[0];

            formaData.append('file_cuenta', file_cuenta)
            //==== llamada al spinner
            mostrarLoading();

            $.ajax({
                url: "registra_emisor_proceso.php",
                type: "POST",
                data: formaData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {alert(data);
                    //==== ocultar el spinner
                    ocultarLoading();

                    if (data == 1){
                        cambia_modal_registro('proceso_terminado',empresa_id);
                    } else {
                        alert('ocurrio un error'+data);
                    }
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

</script>
<!--#############################################-->
</BODY>
</HTML>