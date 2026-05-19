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
        <input type="hidden" id="user_id" name="user_id" value="<?=$_GET['user_id']?>">
        
        <!--==== contenedor formulario principal ====-->
        <div class="contenedor_formulario">
            <!--==== contenedor bloque 1 ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nombre_modal">Nombre:</label>
                    <input type="text" name="nombre_modal" id="nombre_modal" class="formulario_control" value="<?=$varr_inversor['inversor_nombre']?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="apellido_modal">Apellidos:</label>
                    <input type="text" name="apellido_modal" id="apellido_modal" class="formulario_control" value="<?=$varr_inversor['inversor_apellido']?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="telefono_modal">Telefono:</label>
                    <input type="text" name="telefono_modal" id="telefono_modal" class="formulario_control" value="<?=$varr_inversor['inversor_telefono']?>">
                </div>
            </div>

            <!--==== contenedor de direccion ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 600px;">
                    <label for="direccion_modal">Dirección:</label>
                    <input type="text" name="direccion_modal" id="direccion_modal" class="formulario_control" value="<?=$varr_inversor['direccion']?>">
                </div>
            </div>

            <!--==== contenedor bloque 2 ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="tipodoc_modal">Tipo Documento:</label>
                    <select name="tipodoc_modal" id="tipodoc_modal" class="formulario_control">
<?php
    $varr_tipodoc_modal = $vobj_modal_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($varr_tipodoc_modal); $i++){
        if ($varr_tipodoc_modal[$i]['id'] == $varr_inversor['tipodoc'])
            echo '      <option value="'.$varr_tipodoc_modal[$i]['id'].'" selected>'.$varr_tipodoc_modal[$i]['nombre'].'</option>';
        else
            echo '      <option value="'.$varr_tipodoc_modal[$i]['id'].'">'.$varr_tipodoc_modal[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nrodoc_modal">Nro Documento:</label>
                    <input type="text" name="nrodoc_modal" id="nrodoc_modal" class="formulario_control" value="<?=$varr_inversor['identificacion']?>">
                </div>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_modal">Email:</label>
                    <input type="text" name="email_modal" id="email_modal" class="formulario_control" value="<?=$varr_inversor['inversor_email']?>">
                </div>
            </div>

            <!--==== contenedor bloque 3 ====-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="cond_laboral_modal">Condicion laboral:</label>
                    <select name="cond_laboral_modal" id="cond_laboral_modal" class="formulario_control">
<?php
    $varr_ocupacion_modal = $vobj_modal_mae->get_tipos('OCUPACION');

    if ($varr_inversor['ocupacion_id'] == 0)
            echo '      <option value="0" style="color: var(--color-gris-oscuro)" selected>Seleccione</option>';

    for ($i = 0; $i < count($varr_ocupacion_modal); $i++){
        if ($varr_ocupacion_modal[$i]['id'] == $varr_inversor['ocupacion_id']) 
            echo '      <option value="'.$varr_ocupacion_modal[$i]['id'].'" selected>'.$varr_ocupacion_modal[$i]['nombre'].'</option>';
        else echo '     <option value="'.$varr_ocupacion_modal[$i]['id'].'">'.$varr_ocupacion_modal[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <!--==== contenedor bloque 4 ====-->                
            <div class="contenedor_formulario_column">
                <div style="width: 700px;">
                    <label for="doc_archivo_modal" style="display: inline;margin-right: 36px;">Doc Identidad:</label>
                    <input type="file" name="doc_archivo_modal" id="doc_archivo_modal" class="formulario_control" style="display: inline; width: calc(100% - 200px); font-size: 12px;">
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
        var doc_archivo = $('#doc_archivo_modal').val();
        var nombre = $('#nombre_modal').val();
        var apellido = $('#apellido_modal').val();
        var telefono = $('#telefono_modal').val();
        var direccion = $('#direccion_modal').val();
        var tipodoc = $('#tipodoc_modal').val();
        var nrodoc = $('#nrodoc_modal').val();
        var email = $('#email_modal').val();
        var inversor_id = $('#inversor_id').val();
        var empresa_id = $('#empresa_id').val();
        var cond_laboral = $('#cond_laboral_modal').val();
        var user_id = $('#user_id').val();
        var todo_ok = 1;

        if (telefono == ""){
            alert('Debe ingresar un telefono');
            $('#telefono_modal').focus();
            todo_ok = 0;
        }

        if (direccion == 0 && todo_ok == 1){
            alert('Debe ingresar una direccion');
            $('#direccion_modal').focus();
            todo_ok = 0;
        }

        if (cond_laboral == 0 && todo_ok == 1){
            alert('Debe seleccionar una condicion laboral');
            $('#cond_laboral_modal').focus();
            todo_ok = 0;
        }

        if (doc_archivo == "" && todo_ok == 1){
            alert('Debe adjuntar el documento de identidad');
            $('#doc_archivo_modal').focus();
            todo_ok = 0;
        } 

        if (todo_ok == 1) {
            btn_continuar.disabled = true;
            //---- guardo la informacion ----
            var formaData = new FormData();

            formaData.append('nombre', nombre)
            formaData.append('apellido', apellido)
            formaData.append('telefono', telefono)
            formaData.append('direccion', direccion)
            formaData.append('tipodoc', tipodoc)
            formaData.append('nrodoc', nrodoc)
            formaData.append('email', email)
            formaData.append('inversor_id', inversor_id)
            formaData.append('empresa_id', empresa_id)
            formaData.append('cond_laboral', cond_laboral)
            formaData.append('user_id', user_id)
            formaData.append('que_guardo', 'datos_personales')

            var inputFile_identidad = document.getElementById("doc_archivo_modal");
            var file_identidad = inputFile_identidad.files[0];
            formaData.append('file_identidad', file_identidad)

            $.ajax({
                url: "registro_inversor_proceso.php",
                type: "POST",
                data: formaData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {
                    if (data == 1){
                        cambia_modal_registro('datos_personales',inversor_id,empresa_id);
                    } else {
                        alert('ocurrio un error');
                    }
                }
            });
        }
    }
    
</script>
<!--#############################################-->
</BODY>
</HTML>