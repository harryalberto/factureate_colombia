<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
$vobj_cuentas = new cuentas;
$vobj_seg = new seguridad;

$varr_inversor = $vobj_mae->get_datos_inversor($_GET['inversor_id']);

if (isset($_GET['tipo_inv'])){
    if ($_GET['tipo_inv'] == 85) $v_tipo_inv = 'PERSONA NATURAL';
    else $v_tipo_inv = 'PERSONA JURIDICA';
} else $v_tipo_inv = 'PERSONA NATURAL';

if ($varr_inversor['tipo_registro'] == 111){
    if ($varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 9 || $varr_inversor['estado_id'] == 10){
        $v_readonly = 'readonly'; $v_disabled = 'disabled';
    } else {
        $v_readonly = ''; $v_disabled = '';
    }
} else {
    $v_readonly = 'readonly'; $v_disabled = 'disabled';
}
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion">
        <input type="hidden" name="inversor_id" id="inversor_id" value="<?=$_GET['inversor_id']?>">
        
    <div id="principal" style="padding-left: 10px; overflow: hidden;">
        <div class="contenedor_formulario" style="width: 100%; overflow: hidden;">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nombre">NOMBRE:</label>
                    <input type="text" name="nombre" id="nombre" class="formulario_control" value="<?=$varr_inversor['inversor_nombre']?>" <?echo $v_readonly;?>>
                </div>
            
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="apellido">APELLIDO:</label>
                    <input type="text" name="apellido" id="apellido" class="formulario_control" value="<?=$varr_inversor['inversor_apellido']?>" <?echo $v_readonly;?>>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tipodoc_id">TIPO DOC:</label>
                    <select name="tipodoc_id" id="tipodoc_id" class="formulario_control" disabled>
<?php
    $varr_tipodoc = $vobj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($varr_tipodoc); $i++){
        if ($varr_tipodoc[$i]['id'] == $varr_inversor['tipodoc'])
            echo '      <option value="'.$varr_tipodoc[$i]['id'].'" selected>'.$varr_tipodoc[$i]['nombre'].'</option>';
        else
            echo '      <option value="'.$varr_tipodoc[$i]['id'].'">'.$varr_tipodoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nro_doc">NRO DOC:</label>
                    <input type="text" name="nro_doc" id="nro_doc" class="formulario_control" value="<?=$varr_inversor['identificacion']?>" readonly>
                </div>

                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="documento">PDF:</label>
<?php
    if ($varr_inversor['documento'] != '')
        echo '      <span id="documento"><a href="'.$varr_inversor['documento'].'" target="_blanck"><i class="fa-solid fa-file-pdf" style="font-size: 18px;"></i></a></span>';

    if ($varr_inversor['tipo_registro'] == 111)
        echo '      <input type="file" name="documento" id="documento" class="formulario_control">';
?>
                </div>
                <input type="hidden" name="documento_old" id="documento_old" value="<?=$varr_inversor['documento']?>">

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email">EMAIL:</label>
                    <input type="email" name="email" id="email" class="formulario_control" value="<?=$varr_inversor['inversor_email']?>" <?echo $v_readonly;?>>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono">TELEFONO:</label>
                    <input type="text" name="telefono" id="telefono" class="formulario_control" value="<?=$varr_inversor['telefono']?>" <?echo $v_readonly;?>>
                </div>

                <div class="formulario_grupo_row" style="width: 350px;">
                    <label for="direccion">DIRECCION:</label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control" value="<?=$varr_inversor['direccion']?>" <?echo $v_readonly;?>>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tipo_persona_nombre">TIPO PERSONA:</label>
                    <input type="text" name="tipo_persona_nombre" id="tipo_persona_nombre" class="formulario_control" value="<?=$v_tipo_inv?>" readonly>
                    <input type="hidden" name="tipo_persona" id="tipo_persona" value="<?=$varr_inversor['tipo_inversor']?>">
                </div>

                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="categoria">CATEGORIA:</label>
                    <input type="text" name="categoria" id="categoria" class="formulario_control" value="<?=$varr_inversor['categoria']?>" readonly>
                </div>

<?php
    $v_comision = ($varr_inversor['comision'] * 100).' %';
?>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="comision">COMISION:</label>
                    <input type="text" name="comision" id="comision" class="formulario_control" value="<?=$v_comision?>" readonly>
                </div>
            </div>

<?php
    $varr_permisos = $vobj_seg->get_permisos($_SESSION['user']['perfilid']);

    if ($vobj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'LEG-INV')){
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 50px;">
                    <label for="pep">PEP:</label>
                    <input type="checkbox" name="pep" id="pep" class="formulario_control" value="<?=$varr_inversor['pep']?>" onclick="cambiapep(this)">
                </div>

                <div class="formulario_grupo_row" style="width: 400px;" id="content_pep">
                    
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="condicion_laboral">CONDICION LABORAL:</label>
                    <input type="text" name="condicion_laboral" id="condicion_laboral" class="formulario_control" value="<?=$varr_inversor['ocupacion_nombre']?>" readonly>
                </div>
            </div>

<?php
        if ($varr_inversor['ocupacion_id'] == 114){
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 600px;">
                    <label for="explicacion_actividad">EXPLICACION ACTIVIDAD:</label>
                    <textarea name="explicacion_actividad" id="explicacion_actividad" class="formulario_control" rows="2" cols="80"><?echo $varr_inversor['actividad_descripcion'];?></textarea>
                </div>
            </div>
<?php
        }
?>

            <!--================= APROBACION -->
            <!--==== VALIDACION SI HAY FIDEICOMISO -->

<?php
        $varr_fideicomiso = $vobj_mae->get_parametro_detalle(60);
        echo '<input type="hidden" name="fideicomiso" id="fideicomiso" value="'.$varr_fideicomiso['valornum'].'">';

        if ($varr_fideicomiso['valornum'] == 0){
            //==== NO HAY FIDEICOMISO
?>
            <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:10px;"></div>
            
            <div style="width:100%; float:left;margin-bottom:5px;"><label>APROBACION</label></div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="informe_plaft">ANALISIS PLAFT:</label>
<?php
            if ($varr_inversor['informe_plaft'] != '')
            echo '  <a href="'.$varr_inversor['informe_plaft'].'" target="_blanck"><i class="fa-solid fa-file-pdf" style="font-size: 18px;"></i></a>';
?>
                    <input type="file" name="plaft" id="plaft" class="formulario_control" style="background-color:#fff;">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="contrato">CONTRATO:</label>
<?php
            $varr_contrato_atm = $vobj_mae->get_parametro_detalle(28);
    
            if ($varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 9 || $varr_inversor['estado_id'] == 10)   // aprobado / rechazado / bloqueado
            echo '  <a href="'.$varr_inversor['contrato_path'].'" target="_blanck"><i class="fa-solid fa-file-pdf" style="font-size: 18px;"></i></a>';
            else{
                if ($varr_contrato_atm['valornum'] == 0){
                    if ($varr_inversor['contrato_enviado'] == 0){
                    echo '
                        <input type="text" name="contrato" id="contrato" class="formulario_control" class="formulario_control">';
                    } else {
                    echo '
                        <input type="file" name="contrato_firmado" id="contrato_firmado" class="formulario_control" style="background-color:#fff;">';
                    }
                } else {
                    if ($varr_inversor['contrato_enviado'] == 0){
                    echo '
                        <input type="text" name="notifica" id="notifica" value="VERIFIQUE CON EL AREA DE TECNOLOGIA" style="background-color:var(--color-rojo);color:#fff;" readonly>';
                    } else {
                    echo '
                        <input type="text" name="notifica" id="notifica" value="CONTRATO ENVIADO" readonly>';
                    }
                }
            } 
?>
                </div>
            </div>
<?php
        } else {
            //==== FIDEICOMISO EN EL MODELO
            $varr_contrato_atm = $vobj_mae->get_parametro_detalle(28);

            if ($varr_inversor['estado_id'] == 8 && $varr_inversor['estado_fiducia'] == 60 && $varr_contrato_atm['valornum'] == 0){
            //---- REVISADO POR FACTUREATE Y DEPURADO POR LA FIDUCIARIA
            echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="contrato">CONTRATO VINCULACION:</label>
                    <input type="text" name="contrato" id="contrato" class="formulario_control" class="formulario_control">
                </div>
            </div>';
            }

            if ($varr_inversor['estado_id'] == 58 && $varr_inversor['estado_fiducia'] == 60 && $varr_contrato_atm['valornum'] == 0){
            //---- CONTRATO ENVIADO Y DEPURADO POR FIDUCIARIA
            echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 600px;">
                    <label for="contrato">CONTRATO VINCULACION:</label>
                    <input type="file" name="contrato_firmado" id="contrato_firmado" class="formulario_control" style="background-color:#fff;">
                </div>
            </div>';
            }
        }

        //=====================================
        // MOTIVO DE RECHAZO POR FACTUREATE
        //=====================================
        if ($varr_inversor['estado_id'] == 7){
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 600px;">
                    <label for="motivo_rechazo">MOTIVO DE RECHAZO:</label>
                    <textarea name="motivo_rechazo" id="motivo_rechazo" class="formulario_control" rows="2" cols="80"></textarea>
                </div>
            </div>
<?php
        }
    }
?>
        </div>  <!-- END CONTENEDOR FORMULARIO -->
        <!--========================================================-->

        <!--========================================
        ===================== CUENTAS BANCARIAS
        ============================================-->
<?php
    $varr_cuentas_banco = $vobj_cuentas->get_cuentas_banco_inversor($_GET['inversor_id']);
?>
        <div style="width:100%; float:left;margin-bottom:5px;overflow: hidden;margin-top: 10px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID CUENTA</th>     <th scope="col" class="sort asc">BANCO</th>
                        <th scope="col" class="sort asc">TIPO</th>          <th scope="col" class="sort asc">MONEDA</th>
                        <th scope="col" class="sort asc">CUENTA</th>        <th scope="col" class="sort asc">ESTADO</th>
                        <th scope="col" class="sort asc">CERT</th>
                        <th scope="col" class="sort asc">APROB</th>         <th scope="col" class="sort asc">CANC</th>
                    </tr>
                </thead>
                <tbody id="content">
<?php
    echo '<input type="hidden" name="nro_cuentas" id="nro_cuentas" value="'.count($varr_cuentas_banco).'">';

    for ($i=0; $i<count($varr_cuentas_banco); $i++){
        if ($varr_cuentas_banco[$i]['estado_id'] == 1) $v_estado = '<i class="fa-solid fa-circle-check" style="margin-left:5px;color:var(--color-verde);"></i>';
        else $v_estado = '<abbr title="Pendiente de verificar por Factureate"><i class="fa-solid fa-triangle-exclamation" style="margin-left:5px;color:var(--color-amarillo);"></i></abbr>';

        echo '      <tr>    
                        <td data-label="ID CUENTA">'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'</td>
                        <td data-label="BANCO">'.$varr_cuentas_banco[$i]['banco_nombre'].'</td>
                        <td data-label="TIPO CUENTA">'.$varr_cuentas_banco[$i]['tcuenta_nombre'].'</td>
                        <td data-label="MONEDA">'.$varr_cuentas_banco[$i]['moneda_nombre'].'</td>
                        <td data-label="CUENTA">'.$varr_cuentas_banco[$i]['cuenta'].'</td>
                        <td data-label="ESTADO">'.$varr_cuentas_banco[$i]['estado_nombre'].$v_estado.'</td>
                        <td data-label="CERT"><a href="'.$varr_cuentas_banco[$i]['certificado_path'].'" target="_blanck"><i class="fa-solid fa-file-pdf" style="font-size: 18px;"></i></a></td>';

        if ($varr_cuentas_banco[$i]['estado_id'] == 1)
            echo '      <td data-label="APROB"></td>
                        <td data-label="CANC"><input type="checkbox" name="checkc'.$i.'" id="checkc'.$i.'" value="'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'"></td>';
        else
            echo '      <td data-label="APROB"><input type="checkbox" name="checka'.$i.'" id="checka'.$i.'" value="'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'"></td>
                        <td data-label="CANC"></td>';

        echo '      <input type="hidden" name="cuentae'.$i.'" id="cuentae'.$i.'" value="'.$varr_cuentas_banco[$i]['estado_id'].'">
                    </tr>';
    }
?>
                </tbody>
            </table>
        </div>

        <!--========================================
        ===================== BOTONERA 
        ============================================-->
        <div style="width:100%; float:left;margin-bottom:5px;overflow: hidden;">
<?php
    if ($vobj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'LEG-INV')){
        // CLO
        if ($varr_inversor['estado_id'] == 6 || $varr_inversor['estado_id'] == 7){
            // REGISTRADO / EN VALIDACION
            if ($varr_inversor['contrato_enviado'] == 1){
                echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_grabar" class="btn btn-primary" onclick="grabarContrato()">
                    <i class="fa-solid fa-file-import"></i> Reg Contrato
                </button>';
            } else {
                echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_aprobar" class="btn btn-primary" onclick="aprobar()">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" id="boton_rechazar" class="btn btn-primary" onclick="rechazar()">
                    <i class="fa-solid fa-circle-xmark"></i> Rechazar
                </button>';
            }
        } elseif ($varr_inversor['estado_id'] == 8){
            if ($varr_inversor['estado_fiducia'] == 60)
            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_enviar_contrato" class="btn btn-primary" onclick="enviarContrato()">
                    <i class="fa-solid fa-envelope-circle-check"></i> Enviar Contrato
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" id="boton_rechazar" class="btn btn-primary" onclick="rechazar()">
                    <i class="fa-solid fa-circle-xmark"></i> Rechazar
                </button>';
        } elseif ($varr_inversor['estado_id'] == 58){
            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_grabar" class="btn btn-primary" onclick="grabarContrato()">
                    <i class="fa-solid fa-floppy-disk"></i> Contrato Vinculacion
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" id="boton_rechazar" class="btn btn-primary" onclick="rechazarXcontrato()">
                    <i class="fa-solid fa-circle-xmark"></i> Rechazar sin Contrato
                </button>';
        }
    } elseif ($vobj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'FIN-INV')){
        if ($varr_inversor['estado_id'] == 64){
            // VINCULADO
            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_grabar" class="btn btn-primary" onclick="grabarFinanciero()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>';
        }
    }
?>
        </div>
        
    </div>
    </form>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">
        function enviarContrato(){
            var v_contrato = $('#contrato').val();

            if (v_contrato == '') alert('Debe ingresar el URL del contrato de vinculacion');
            else {
                var btn_enviar_contrato = document.getElementById("boton_enviar_contrato");
                var btn_rechazar = document.getElementById("boton_rechazar");

                document.frm_modal.accion.value = 'envia_contrato';

                var formData = new FormData(document.getElementById("frm_modal"));
                btn_enviar_contrato.disabled = true;
                btn_rechazar.disabled = true;

                $.ajax({
                    url:"inversor_registro_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    alert('El contrato de vinculacion fue enviado al Inversor');
                    location.href = 'inversores.php';
                });
            }
        }

        function aprobar(){
            var v_fideicomiso = $('#fideicomiso').val();
            var v_procede = 0;

            if (v_fideicomiso == 0){
                var v_plaft = $('#plaft').val();
                var v_contrato = $('#contrato').val();

                if (v_plaft == '' || v_contrato == ''){
                    alert('Para aprobar al inversor debe ingresar el informe PLAFT y el link del contrato que se debe enviar al inversor');
                    v_procede = 0;
                } else v_procede = 1;
            } else {
                v_procede = 1;
            }

            var btn_aprobar = document.getElementById("boton_aprobar");
            var btn_rechazar = document.getElementById("boton_rechazar");

            if (v_procede == 1) {
                document.frm_modal.accion.value = 'aprobar';

                var formData = new FormData(document.getElementById("frm_modal"));
                btn_aprobar.disabled = true;
                btn_rechazar.disabled = true;

                $.ajax({
                    url:"inversor_registro_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    //alert(rpta);
                    alert('Los datos del inversor fueron actualizados');
                    location.href = 'inversores.php';
                });
            }
        }

        function grabarContrato(){
            var v_contrato = $('#contrato_firmado').val();
            var btn_grabar = document.getElementById("boton_grabar");
            var btn_rechazar = document.getElementById("boton_rechazar");

            if (v_contrato == '') alert('Debe adjuntar el contrato firmado por el inversor!!');
            else {
                document.frm_modal.accion.value = 'guarda_contrato';

                var contrato_file = document.getElementById('contrato_firmado').files[0];

                if (contrato_file && contrato_file.size > 1 * 1024 * 1024) {
                    alert('El archivo no puede superar los 1 MB');
                    return;
                } else {
                    var formData = new FormData(document.getElementById("frm_modal"));
                    btn_grabar.disabled = true;
                    btn_rechazar.disabled = true;

                    $.ajax({
                        url:"inversor_registro_proceso.php",
                        type:'post',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: "html"
                    })
                    .done(function(rpta){
                        alert('El contrato se guardo, el inversor ya tiene los accesos a la plataforma');
                        location.href = 'inversores.php';
                    });
                }
            }
        }

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
            var v_documento_old = $('#documento_old').val();

            var btn_grabar = document.getElementById("boton_grabar");

            document.frm_modal.accion.value = 'upd';

            if (v_nombre == '' || v_apellido == '') alert("De ingresar un nombre y apellido valido");
            else{
                if (v_email == '') alert("Debe ingresar un email valido");
                else{
                    if (v_telefono == '') alert("Debe ingresar un telefono valido");
                    else{
                        if (v_tipodoc_id == 0) alert("Debe seleccionar un tipo de documento");
                        else{
                            if (v_nro_doc == '') alert("Debe ingresar un Nro de documento y adjunto valido");
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
                                                alert('Los datos del inversor fueron actualizados');
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

        function cambiapep(obj){
            let content_pep = document.getElementById("content_pep")
            var form = document.forms.namedItem("frm_modal");

            if (obj.value){
                content_pep.innerHTML = '<label for="pep_motivo">DESCRIPCION:</label><textarea name="pep_motivo" id="pep_motivo" class="formulario_control" rows="2" cols="80"></textarea>';
                form.pep_motivo.focus();
            } else {
                content_pep.innerHTML = "";
            }
        }

        function grabarFinanciero(){
            document.frm_modal.accion.value = 'graba_financiero';

            var formData = new FormData(document.getElementById("frm_modal"));
            boton_grabar.disabled = true;

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
                    alert('No selecciono ninguna cuenta de banco para autorizar');
                    boton_grabar.disabled = false;
                } else {
                    alert('Las cuentas seleccionadas fueron autorizadas');
                    location.href = 'inversores_finanzas.php';
                }
            });
        }
    </script>
</BODY>
</HTML>