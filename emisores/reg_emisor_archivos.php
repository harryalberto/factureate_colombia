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
                    <label for="cert_existencia">Certificado de Existencia (por Camara de Comercio, max 3 meses antiguedad):</label>
                    <input type="file" id="cert_existencia" name="cert_existencia" class="formulario_control">
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="reg_accionistas">Certificado de Composicion Accionistas (max 3 meses antiguedad):</label>
                    <input type="file" name="reg_accionistas" id="reg_accionistas" class="formulario_control">
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="doc_representante">Documento Representante legal:</label>
                    <input type="file" name="doc_representante" id="doc_representante" class="formulario_control">
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
        var reg_mercantil = $('#cert_existencia').val();
        var reg_accionistas = $('#reg_accionistas').val();
        var doc_representante = $('#doc_representante').val();
        var empresa_id = $('#empresa_id').val();

        var todo_ok = 1;

        if (reg_mercantil == ""){
            alert('Debe adjuntar el archivo de Certificado de Existencia');
            $('#cert_existencia').focus();
            todo_ok = 0;
        }

        if (reg_accionistas == "" && todo_ok == 1){
            alert('Debe adjuntar el documento de Certificado de Composición de Accionistas');
            $('#reg_accionistas').focus();
            todo_ok = 0;
        }

        if (doc_representante == "" && todo_ok == 1){
            alert('Debe adjuntar el documento del Representante Legal');
            $('#doc_representante').focus();
            todo_ok = 0;
        }

        if (todo_ok == 1) {
            btn_continuar.disabled = true;
            //---- guardo la informacion ----
            var formaData = new FormData();

            formaData.append('empresa_id', empresa_id)
            formaData.append('accion', 'archivos_emisor')

            var inputFile_regmercantil = document.getElementById("cert_existencia");
            var file_regmercantil = inputFile_regmercantil.files[0];
            var inputFile_regaccionistas = document.getElementById("reg_accionistas");
            var file_regaccionistas = inputFile_regaccionistas.files[0];
            var inputFile_docrepresentante = document.getElementById("doc_representante");
            var file_docrepresentante = inputFile_docrepresentante.files[0];

            formaData.append('file_cert_existencia', file_regmercantil)
            formaData.append('file_reg_accionistas', file_regaccionistas)
            formaData.append('file_doc_representante', file_docrepresentante)

            $.ajax({
                url: "registra_emisor_proceso.php",
                type: "POST",
                data: formaData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {
                    if (data == 1){
                        cambia_modal_registro('cuenta_banco',empresa_id);
                    } else {
                        alert('ocurrio un error'+data);
                    }
                }
            });
        }
    }

</script>
<!--#############################################-->
</BODY>
</HTML>