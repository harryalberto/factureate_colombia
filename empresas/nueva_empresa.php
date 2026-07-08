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
    $acceso = 'GESTEMP';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            var enviar, previo;
            
            document.frm.action = 'empresa_gestion_proceso.php';
            document.frm.accion.value = accion;
            previo = document.frm.previo.value;

            if (accion == 'cerrar') location.href = previo;
            else if (accion == 'grabar' || accion == 'grabaraprobar'){
                var v_empresa_id = document.frm.id_empresa.value;
                var v_nombre_empresa = document.frm.nombre_empresa.value;
                var v_direccion = document.frm.direccion.value;
                var v_tamano = document.frm.tamanoid.value;
                var v_sectorid = document.frm.sectorid.value;
                var v_actividad = document.frm.actividad.value;
                var v_nombre_contacto = document.frm.nombre_contacto.value;
                var v_email_contacto = document.frm.email_contacto.value;
                var v_tipodoc_contacto = document.frm.tdoc_contacto.value;
                var v_nrodoc_contacto = document.frm.nrodoc_contacto.value;
                var v_telefono_contacto = document.frm.telefono_contacto.value;

                if (v_empresa_id == '' || v_nombre_empresa == '' || v_direccion == '' || v_tamano == 0 || v_sectorid == 0 || v_actividad == '') alert ('Debe completar la informacion de la empresa');
                else {
                    if (v_nombre_contacto == '' || v_email_contacto == '' || v_tipodoc_contacto == 0 || v_nrodoc_contacto == '' || v_telefono_contacto == '') alert('Debe completar la informacion del contacto');
                    else{
                        if (accion == 'grabar') enviar = 1;
                        else{
                            var emp_score = document.frm.emp_riesgoid.value;
                            var nivel_score = document.frm.nivel_score_riesgoid.value;
                            var justi_nivel_score = document.frm.score_riesgo_justi.value;
                            var path_score_file = document.frm.informe_score_file.value;
                            var nivel_factu = document.frm.nivel_factureate_riesgoid.value;
                            var justi_nivel_factu = document.frm.factureate_riesgo_justi.value;
                            var path_factu_file = document.frm.informe_factu_file.value;

                            if (emp_score == 0 || nivel_score == 0 || justi_nivel_score == '') alert('Debe completar la informacion del riesgo del Score');
                            else if (nivel_factu == 0 || justi_nivel_factu == '') alert('Debe completar la informacion del riesgo Factureate');
                            else if (path_score_file == '') alert('Debe registrar el informe de riesgos del Score');
                            else if (path_factu_file == '') alert('Debe registrar el informe de riesgos de Factureate');
                            else enviar = 1;
                        }
                    }
                }
            }
            
            if (enviar == 1) document.frm.submit();
        }
        function validacion(accion){
            alert('hola');
        }
    </script>
</HEAD>
<?php
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
@@@@@@@@@@@@@@@@@@@@@@@ LOGICA NO VISIBLE */

//=============== EXCLUSIVO PARA REGISTRAR NUEVOS OBLIGADOS AL PAGO
$obj_mae = new maestros;

if ($_GET['tipo'] == 48) $v_tipo_desc = 'OBLIGADO PAGO';
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="accion">
    <input type="hidden" name="previo" value="empresas.php">
    <input type="hidden" name="tipo_empresa_id" value="<?=$_GET['tipo']?>">
    
    <!---@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ TITULO -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);padding: 5px;">
        Ficha de Obligado al Pago
    </div>
    
    <!---@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA PRINCIPAL -->
    <div class="contenedor_principal" id="contenedor_principal">

        <div class="contenedor_formulario">

            <!--================= INFORMACION DE LA EMPRESA -->
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                INFORMACION DE LA EMPRESA
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="id_empresa">RNC</label>
                    <input type="text" name="id_empresa" id="id_empresa" class="formulario_control" onchange="verifica_id_empresa()">
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_empresa">NOMBRE EMPRESA</label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tamanoid">TAMAÑO EMPRESA</label>
                    <select name="tamanoid" id="tamanoid" class="formulario_control">
                        <option value="0">**********</option>
<?php
    $arr_ttamano = $obj_mae->get_tipos('TAMANHOEMP');

    for ($i=0; $i<count($arr_ttamano); $i++){
        echo '          <option value="'.$arr_ttamano[$i]['id'].'">'.$arr_ttamano[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="direccion">DIRECCION</label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="sectorid">ACTIVIDAD ECONOMICA</label>
                    <select name="sectorid" id="sectorid" class="formulario_control">
                        <option value="0">***********************</option>
<?php
    $arr_sector = $obj_mae->get_tipos_xnom('SECTORECO');

    for ($i=0; $i<count($arr_sector); $i++){
        echo '          <option value="'.$arr_sector[$i]['id'].'">'.$arr_sector[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="actividad">DESCRIPCION ACTIVIDAD</label>
                    <textarea name="actividad" id="actividad" cols="70" rows="5" class="formulario_control"></textarea>
                </div>
            </div>

            <hr>

            <!--================= INFORMACION DE CONTACTO -->
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                INFORMACION DE CONTACTO
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_contacto">NOMBRE CONTACTO</label>
                    <input type="text" name="nombre_contacto" id="nombre_contacto" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="email_contacto">EMAIL CONTACTO</label>
                    <input type="text" name="email_contacto" id="email_contacto" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_contacto">TIPO DOCUMENTO</label>
                    <select name="tdoc_contacto" id="tdoc_contacto" class="formulario_control">
                        <option value="0">***************************</option>
<?php
    $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($arr_tdoc); $i++){
        echo '          <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrodoc_contacto">NRO DOCUMENTO</label>
                    <input type="text" name="nrodoc_contacto" id="nrodoc_contacto" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono_contacto">TELEFONO</label>
                    <input type="text" name="telefono_contacto" id="telefono_contacto" class="formulario_control">
                </div>
            </div>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            @@@@@@@@@@@ BOTONERA -->

            <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:5px;"></div>

            <div style="width:100%; float:left;margin-bottom:5px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_grabar" onclick="grabar()">
                    <i class="fa-solid fa-floppy-disk"></i> Grabar
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_salir" onclick="salir()">
                    <i class="fa-solid fa-door-open"></i> Salir
                </button>
            </div>

        </div>
    </div>

    </form>
    <!------ END CUERPO VARIABLE ------>
    <script type="text/javascript">
        function grabar(){
            alert('grabar');
            var v_nombre_empresa = $('#nombre_empresa').val();
            var v_id_empresa = $('#id_empresa').val();
            var v_tamanoid = $('#tamanoid').val();
            var v_direccion = $('#direccion').val();
            var v_sector_id = $('#sectorid').val();
            var v_actividad = $('#actividad').val();

            var v_nombre_contacto = $('#nombre_contacto').val();
            var v_email_contacto = $('#email_contacto').val();
            var v_tdoc_contacto = $('#tdoc_contacto').val();
            var v_nrodoc_contacto = $('#nrodoc_contacto').val();
            var v_telefono_contacto = $('#telefono_contacto').val();

            if (v_nombre_empresa == '' || v_id_empresa == '' || v_tamanoid == 0 || v_direccion == '' || v_sector_id == 0 || v_actividad == '') 
                alert('Debe completar la información de la empresa');
            else {
                var btn_grabar = document.getElementById('boton_grabar');
                var btn_salir = document.getElementById('boton_salir');

                btn_grabar.disabled = true;
                btn_salir.disabled = true;

                $.ajax({
                    url:"empresa_gestion_proceso.php",
                    type:'post',
                    data:{
                        "nombre_empresa":v_nombre_empresa,
                        "id_empresa":v_id_empresa,
                        "tamanoid":v_tamanoid,
                        "direccion":v_direccion,
                        "sectorid":v_sector_id,
                        "actividad":v_actividad,
                        "accion":'grabar',
                        "nombre_contacto":v_nombre_contacto,
                        "email_contacto":v_email_contacto,
                        "tdoc_contacto":v_tdoc_contacto,
                        "nrodoc_contacto":v_nrodoc_contacto,
                        "telefono_contacto":v_telefono_contacto,
                        "tipo_empresa_id":48
                    }
                })
                .done(function(rpta){
                    alert('El Obligado al Pago ha sido registrado');
                    location.href = 'empresas.php';
                });
            }
        }

        function salir(){
            location.href = 'empresas.php';
        }
    </script>
</BODY>
</HTML>