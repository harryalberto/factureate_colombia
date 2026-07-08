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
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@ LOGICA
$vobj_mae = new maestros;

?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
        <input type="hidden" name="emisor_id" id="emisor_id" value="<?=$_SESSION['user']['empresaid']?>">
        
    <div id="principal" style="display: block;padding-left: 10px;height: 60%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="nombre_usuario">NOMBRE:</label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="apellido_usuario">APELLIDO:</label>
                    <input type="text" name="apellido_usuario" id="apellido_usuario" class="formulario_control">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="email">EMAIL:</label>
                    <input type="text" name="email" id="email" class="formulario_control">
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
    $varr_tipodoc = $vobj_mae->get_tipos_seg('TDOCU');

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

            <div style="width:100%; float:left;margin-bottom:5px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="grabar()">
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
            var v_tipodoc_id = $('#tipodoc_id').val();
            var v_nombre = $('#nombre_usuario').val();
            var v_apellido = $('#apellido_usuario').val();
            var v_email = $('#email').val();
            var v_telefono = $('#telefono').val();
            var v_nro_doc = $('#nro_doc').val();
            var v_emisor_id = $('#emisor_id').val();

            if (v_nombre == '') alert("De ingresar un nombre valido");
            else{
                if (v_apellido == '') alert("Debe un apellido valido");
                else{
                    if (v_email == '') alert("Debe ingresar un email valido");
                    else{
                        if (v_telefono == '') alert("Debe ingresar un telefono valido");
                        else{
                            if (v_tipodoc_id == 0) alert("Debe seleccionar un tipo de documento");
                            else{
                                if (v_nro_doc == '') alert("Debe ingresar un Nro de documento valido");
                                else{
                                    $.ajax({
                                        url:"agregar_usuario_emisor_proceso.php",
                                        type:'post',
                                        data:{
                                            "emisor_id":v_emisor_id,
                                            "nombre":v_nombre,
                                            "apellido":v_apellido,
                                            "email":v_email,
                                            "telefono":v_telefono,
                                            "tipodoc_id":v_tipodoc_id,
                                            "nro_doc":v_nro_doc
                                        }/*,
                                        success:function(data,status){
                                            $('#EmisorModal').fadeIn(1000).html(data);
                                            $('#EmisorModal').modal('hide');
                                            refresh_page();
                                        }*/
                                    })
                                    .done(function(rpta){
                                        //alert(rpta);
                                        alert('La cuenta fue guardad con exito');
                                        refresh_page();
                                    });
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