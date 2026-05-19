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
<?
    require("../lib/head.php");
    $acceso = '';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?

//------ LOGICA NO VISIBLE ------
$vobj_modal_mae = new maestros;

$varr_inversor = $vobj_modal_mae->get_datos_inversor($_GET['inversor_id']);

//------ END LOGICA NO VISIBLE ------
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<div id="principal" style="padding-left: 10px;overflow: hidden;">
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" id="inversor_id" name="inversor_id" value="<?=$_GET['inversor_id']?>">
        <input type="hidden" id="empresa_id" name="empresa_id" value="<?=$_GET['empresa_id']?>">
        
        <!--==== titulo explicativo ====-->
        <p style="color:var(--color-rojo);">La cuenta bancaria que requerímos es donde se depositarán sus ganancias y el retorno de tu inversión</p>
        <!--==== contenedor formulario principal ====-->
        <div class="contenedor_formulario">
            <!--==== contenedor bloque 1 ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="banco_modal">Banco:</label>
                    <select name="banco_modal" id="banco_modal" class="formulario_control">
                        <option value="0" selected>Seleccione Banco</option>
<?php
    $varr_bancos_modal = $vobj_modal_mae->get_bancos();

    for ($i=0; $i<count($varr_bancos_modal); $i++){
        echo '  <option value="'.$varr_bancos_modal[$i]['banco_id'].'">'.$varr_bancos_modal[$i]['banco_nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="t_cuenta_modal">Tipo Cuenta:</label>
                    <select name="t_cuenta_modal" id="t_cuenta_modal" class="formulario_control">
                        <option value="0" selected>Tipo cuenta</option>
<?php
    $varr_tcuenta_modal = $vobj_modal_mae->get_tipos('TIPO CUENTA BANCO');

    for ($i=0; $i<count($varr_tcuenta_modal); $i++){
        echo '          <option value="'.$varr_tcuenta_modal[$i]['id'].'">'.$varr_tcuenta_modal[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="moneda_modal">Moneda:</label>
                    <select name="moneda_modal" id="moneda_modal" class="formulario_control">
                        <option value="0" selected>Moneda</option>
<?php
    $varr_monedas_modal = $vobj_modal_mae->get_tipos('MONEDA');

    for ($i=0; $i<count($varr_monedas_modal); $i++){
        echo '          <option value="'.$varr_monedas_modal[$i]['id'].'">'.$varr_monedas_modal[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <!--==== contenedor bloque 2 ====-->                
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="cuenta_modal">Cuenta:</label>
                    <input type="text" name="cuenta_modal" id="cuenta_modal" class="formulario_control">
                </div>
            </div>

            <!--==== contenedor bloque 3 ====-->                
            <div class="contenedor_formulario_column">
                <div style="width: 700px;">
                    <label for="doc_certificado" style="display: inline;margin-right: 36px;">Certificado cuenta: <abbr title="Documento del banco donde aparezca el numero de cuenta y su nombre"><i class="fa-solid fa-circle-question"></i></abbr></label>
                    <input type="file" name="doc_certificado" id="doc_certificado" class="formulario_control" style="display: inline; width: calc(100% - 200px); font-size: 12px;">
                </div>
            </div>

            <!--==== contenedor bloque botonera ====-->
            <div style="margin-top: 50px;width: 100%; float: right;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="continuar()" id="btn_continuar">
                    Continuar <i class="fa-solid fa-angles-right"></i>
                </button>
            </div>
        </div>  <!--==== END contenedor formulario principal ====-->
    </form>
</div>  <!--==== END contenedor principal ====-->
      
<!--################ ZONA JS ####################-->
<script>
    function continuar(){
        var banco = $('#banco_modal').val();
        var t_cuenta = $('#t_cuenta_modal').val();
        var moneda = $('#moneda_modal').val();
        var cuenta = $('#cuenta_modal').val();
        var doc_certificado = $('#doc_certificado').val();
        var inversor_id = $('#inversor_id').val();
        var empresa_id = $('#empresa_id').val();
        var proceder = 1;

        if (banco == 0){
            proceder = 0;
            alert('Debe seleccionar un banco');
            $('#banco_modal').focus();
        }

        if (t_cuenta == 0 && procede == 1){
            proceder = 0;
            alert('Debe seleccionar un tipo de cuenta');
            $('#t_cuenta_modal').focus();
        }

        if (moneda == 0 && procede == 1){
            proceder = 0;
            alert('Debe seleccionar la moneda de la cuenta');
            $('#moneda_modal').focus();
        }

        if (cuenta == "" && procede == 1){
            proceder = 0;
            alert('Debe ingresar un numero de cuenta');
            $('#cuenta_modal').focus();
        }

        if (doc_certificado == ""){
            proceder = 0;
            alert('Debe adjuntar el certificado de su cuenta de banco, documento donde aparezca su nombre y el numero de cuenta');
            $('#doc_certificado').focus();
        } 

        if (proceder == 1) {
            btn_continuar.disabled = true;
            //---- guardo la informacion ----
            var formaData = new FormData();

            formaData.append('banco', banco)
            formaData.append('t_cuenta', t_cuenta)
            formaData.append('moneda', moneda)
            formaData.append('cuenta', cuenta)
            formaData.append('que_guardo', 'datos_cuenta')
            formaData.append('inversor_id', inversor_id)
            formaData.append('empresa_id', empresa_id)

            var inputFile_certificado = document.getElementById("doc_certificado");
            var file_certificado = inputFile_certificado.files[0];
            formaData.append('file_certificado', file_certificado)

            $.ajax({
                url: "registro_inversor_proceso.php",
                type: "POST",
                data: formaData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {   
                    if (data > 0){
                        cambia_modal_registro('datos_cuenta',inversor_id,empresa_id);
                    } else {
                        alert('La cuenta ya existe');
                    }
                }
            });
        }
    }
    
</script>
<!--#############################################-->
</BODY>
</HTML>