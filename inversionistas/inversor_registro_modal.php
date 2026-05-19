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
$vobj_mae = new maestros;

$v_tipo_persona = 85;
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion">
        
    <div id="principal" style="display: block;padding-left: 10px;height: 70%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="nombre">NOMBRE:</label>
                    <input type="text" name="nombre" id="nombre" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="apellido">APELLIDO:</label>
                    <input type="text" name="apellido" id="apellido" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 350px;">
                    <label for="email">EMAIL:</label>
                    <input type="email" name="email" id="email" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="telefono">TELEFONO:</label>
                    <input type="text" name="telefono" id="telefono" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="tipodoc_id">TIPO DOC:</label>
                    <select name="tipodoc_id" id="tipodoc_id" class="formulario_control">
                        <option value="0" selected>Seleccione Tipo Documento</option>
<?php
    $varr_tipodoc = $vobj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($varr_tipodoc); $i++){
        echo '          <option value="'.$varr_tipodoc[$i]['id'].'">'.$varr_tipodoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="nro_doc">NRO DOC:</label>
                    <input type="text" name="nro_doc" id="nro_doc" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="documento">DOCUMENTO:</label>
                    <input type="file" name="documento" id="documento" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="direccion">DIRECCION:</label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="tipo_persona_nombre">TIPO PERSONA:</label>
                    <input type="text" name="tipo_persona_nombre" id="tipo_persona_nombre" class="formulario_control" value="PERSONA NATURAL" readonly>
                    <input type="hidden" name="tipo_persona" id="tipo_persona" value="<?=$v_tipo_persona?>">
                </div>
            </div>

            <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:10px;"></div>

            <div style="width:100%; float:left;margin-bottom:5px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_grabar" class="btn btn-primary" onclick="grabar()">
                    <i class="fa-solid fa-floppy-disk"></i> Grabar
                </button>
            </div>

        </div>
    </div>
    </form>    
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">
        function grabar(){
            var v_nombre = $('#nombre').val();
            var v_apellido = $('#apellido').val();
            var v_email = $('#email').val();
            var v_telefono = $('#telefono').val();
            var v_tipodoc_id = $('#tipodoc_id').val();
            var v_nro_doc = $('#nro_doc').val();
            var v_direccion = $('#direccion').val();
            var v_tipo_persona = $('#tipo_persona').val();
            var v_documento = $('#documento').val();

            var btn_grabar = document.getElementById("boton_grabar");

            document.frm_modal.accion.value = 'grabar';

            if (v_nombre == '' || v_apellido == '') alert("De ingresar un nombre y apellido valido");
            else{
                if (v_email == '') alert("Debe ingresar un email valido");
                else{
                    if (v_telefono == '') alert("Debe ingresar un telefono valido");
                    else{
                        if (v_tipodoc_id == 0) alert("Debe seleccionar un tipo de documento");
                        else{
                            if (v_nro_doc == '' || v_documento == '') alert("Debe ingresar un Nro de documento y adjunto valido");
                            else{
                                if (v_direccion == '') alert("Debe ingresar una direccion valida");
                                else {
                                    if (v_tipo_persona == 0) alert('Debe seleccionar un tipo de persona');
                                    else{
                                        var formData = new FormData(document.getElementById("frm_modal"));
                                        btn_grabar.disabled = true;

                                        $.ajax({
                                            url:"inversor_registro_proceso.php",
                                            type:'post',
                                            data: formData,
                                            contentType: false,
                                            processData: false,
                                            dataType: "html"
                                        })
                                        .done(function(rpta){
                                            if (rpta == -200){
                                                alert('Ya existe un usuario con ese mismo numero de identificacion, verifique por favor');
                                                btn_grabar.disabled = false;
                                            } else {
                                                alert('El inversor fue registrado, ahora debe pasar por el analisis legal para ser admitido');
                                                location.href = 'inversores.php';
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    </script>
</BODY>
</HTML>