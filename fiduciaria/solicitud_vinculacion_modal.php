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
$vobj_mae_modal = new maestros;

$varr_inversor = $vobj_mae_modal->get_inversor_detalle($_GET['inversor_id']);
$vt_f_solicitud = strtotime($varr_inversor['f_aprueba_factureate']);
$v_f_solicitud = date('d-m-Y',$vt_f_solicitud);

$v_tipo_persona = 85;
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion">
        <input type="hidden" name="inversor_id" id="inversor_id" value="<?=$_GET['inversor_id']?>">
        <input type="hidden" name="tpersona_id" id="tpersona_id" value="85">
        
    <div id="principal" style="display: block;padding-left: 10px;height: 75%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="nombre">NOMBRE:</label>
                    <input type="text" name="nombre" id="nombre" class="formulario_control" value="<?=$varr_inversor['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="apellido">APELLIDO:</label>
                    <input type="text" name="apellido" id="apellido" class="formulario_control" value="<?=$varr_inversor['apellido']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tipodoc_id">TIPO DOC:</label>
                    <input type="text" name="tipodoc" id="tipodoc" class="formulario_control" value="<?=$varr_inversor['tipo_documento_nom']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nro_doc">NRO DOC:</label>
                    <input type="text" name="nro_doc" id="nro_doc" class="formulario_control" value="<?=$varr_inversor['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono">TELEFONO:</label>
                    <input type="text" name="telefono" id="telefono" class="formulario_control" value="<?=$varr_inversor['telefono']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="email">EMAIL:</label>
                    <input type="email" name="email" id="email" class="formulario_control" value="<?=$varr_inversor['email']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tipo_persona">TIPO PERSONA:</label>
                    <input type="text" name="tipo_persona" id="tipo_persona" class="formulario_control" value="NATURAL" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="f_solicitud">F SOLICITUD:</label>
                    <input type="text" name="f_solicitud" id="f_solicitud" class="formulario_control" value="<?=$v_f_solicitud?>" readonly>
                </div>
            </div>
            
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="documento">DOCUMENTO:</label>
                    <label id="documento"><a target="_blanck" href="<?echo $varr_inversor['documento_path'];?>" style="font-size:20px;"><i class="fa-solid fa-file-pdf"></i></a></label>
                </div>
            </div>

<?php
    if ($varr_inversor['estado_fiducia'] == 59){
        //---- EN DEPURACION
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="doc_depuracion">INFORME DEPURACION:</label>
                    <input type="file" name="doc_depuracion" id="doc_depuracion" class="formulario_control">
                </div>
            </div>
<?php
    } elseif ($varr_inversor['estado_fiducia'] == 60){
        //---- DEPURADO
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="doc_depuracion">INFORME DEPURACION:</label>
                    <label id="doc_depuracion"><a target="_blanck" href="<?echo $varr_inversor['informe_depuracion_path'];?>" style="font-size:20px;"><i class="fa-solid fa-file-pdf"></i></a></label>
                </div>
            </div>
<?php
        if ($varr_inversor['contrato_path'] != ''){
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="contrato">CONTRATO VINCULACION:</label>
                    <label id="contrato"><a target="_blanck" href="<?echo $varr_inversor['contrato_path'];?>" style="font-size:20px;"><i class="fa-solid fa-file-pdf"></i></a></label>
                </div>
            </div>
<?php
        }
?>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="doc_dd">INFORME DEBIDA DILIGENCIA:</label>
                    <input type="file" name="doc_dd" id="doc_dd" class="formulario_control">
                </div>
            </div>
<?php
    } elseif ($varr_inversor['estado_fiducia'] == 62){
        //---- APROBADO
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="doc_depuracion">INFORME DEPURACION:</label>
                    <label id="doc_depuracion"><a target="_blanck" href="<?echo $varr_inversor['informe_depuracion_path'];?>" style="font-size:20px;"><i class="fa-solid fa-file-pdf"></i></a></label>
                </div>
            </div>

<?php
        if ($varr_inversor['contrato_path'] != ''){
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="contrato">CONTRATO VINCULACION:</label>
                    <label id="contrato"><a target="_blanck" href="<?echo $varr_inversor['contrato_path'];?>" style="font-size:20px;"><i class="fa-solid fa-file-pdf"></i></a></label>
                </div>
            </div>
<?php
        }
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 450px;">
                    <label for="doc_dd">INFORME DEBIDA DILIGENCIA:</label>
                    <label id="doc_dd"><a target="_blanck" href="<?echo $varr_inversor['informe_plaft'];?>" style="font-size:20px;"><i class="fa-solid fa-file-pdf"></i></a></label>
                </div>
            </div>
<?php
    }
?>

            <div style="overflow:hidden;background-color:var(--color-gris-oscuro);height:1px;width:100%; float:left;margin-top:10px;"></div>

            <!--========================================
            ================== BOTONERA ========= -->

            <div style="width:100%; float:left;margin-bottom:5px;margin-top: 10px;">
<?php
    if ($varr_inversor['estado_fiducia'] == 59){
?>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_aprueba" class="btn btn-primary" onclick="depurar('ok')">
                    <i class="fa-solid fa-floppy-disk"></i> Aprueba Depuracion
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" id="boton_rechaza" class="btn btn-primary" onclick="depurar('nook')">
                    <i class="fa-solid fa-user-xmark"></i> Rechaza Depuracion
                </button>
<?php
    } elseif ($varr_inversor['estado_fiducia'] == 60){
?>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_dd" class="btn btn-primary" onclick="registraDD()">
                    <i class="fa-solid fa-scale-balanced"></i> Dedida Diligencia
                </button>
<?php
    }
?>
            </div>

        </div>
    </div>
    </form>    
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">
        function depurar(p_estado){
            var v_doc_depuracion = $('#doc_depuracion').val();

            var btn_aprueba = document.getElementById("boton_aprueba");
            var btn_rechaza = document.getElementById("boton_rechaza");
            var accion = document.getElementById("accion");

            if (v_doc_depuracion == '') alert('Debe adjuntar el Informe de Depuracion');
            else {
                accion.value = p_estado;
                var formData = new FormData(document.getElementById("frm_modal"));
                btn_aprueba.disabled = true;
                btn_rechaza.disabled = true;
                
                //document.frm_modal.accion.value = p_estado;

                $.ajax({
                    url:"solicitud_vinculacion_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html",
                    success:function(data,status){
                        $('#InversorDetalle').fadeIn(1000).html(data);
                        $('#InversorDetalle').modal('hide');
                        refresh_page();
                    }
                });
            }
        }

        function registraDD(){
            var v_doc_dd = $('#doc_dd').val();

            var btn_dd = document.getElementById("boton_dd");
            var accion = document.getElementById("accion");

            if (v_doc_dd == '') alert('Debe adjuntar el informe de Debida Diligencia');
            else {
                accion.value = 'dd';
                var formData = new FormData(document.getElementById("frm_modal"));
                btn_dd.disabled = true;

                $.ajax({
                    url:"vinculados_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html",
                    success:function(data,status){
                        $('#InversorDetalle').fadeIn(1000).html(data);
                        $('#InversorDetalle').modal('hide');
                        refresh_page();
                    }
                });
            }
        }

    </script>
</BODY>
</HTML>