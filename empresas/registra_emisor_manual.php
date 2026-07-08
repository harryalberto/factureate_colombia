<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'REGEMISOR';
    require("../lib/valida-acceso.php");
?>
    
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$vobj_cuentas = new cuentas;

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!---@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ TITULO -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);padding: 5px;">
        Ficha de Emisor
    </div>
    
    <!---@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA PRINCIPAL -->
    <!--<form name="frm" method="post" id="frm" enctype="multipart/form-data">-->
    
    <div class="contenedor_principal" id="contenedor_principal">

        <div class="contenedor_formulario">

            <div style="font-weight: bold;color:var(--color-rojo);font-size: 10px;margin-top: 10px;width:100%;float:left;"> 
                [*] Información Obligatoria
            </div>

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                INFORMACION DE LA EMPRESA
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_empresa">NOMBRE EMPRESA <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="ruc">RNC</label>
                    <input type="text" name="ruc" id="ruc" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tamanoid">TAMAÑO EMPRESA <b style="color:var(--color-rojo);">[*]</b></label>
                    <select name="tamanoid" id="tamanoid" class="formulario_control">
<?php
    $arr_ttamano = $obj_mae->get_tipos('TAMANHOEMP');

    for ($i=0; $i<count($arr_ttamano); $i++){
        echo '     <option value="'.$arr_ttamano[$i]['id'].'">'.$arr_ttamano[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="direccion">DIRECCION <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="sectorid">ACTIVIDAD ECONOMICA <b style="color:var(--color-rojo);">[*]</b></label>
                    <select name="sectorid" id="sectorid" class="formulario_control">
<?php
    $arr_sector = $obj_mae->get_tipos_xnom('SECTORECO');

    for ($i=0; $i<count($arr_sector); $i++){
        echo '     <option value="'.$arr_sector[$i]['id'].'">'.$arr_sector[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="actividad">DESCRIPCION ACTIVIDAD <b style="color:var(--color-rojo);">[*]</b></label>
                    <textarea id="actividad" name="actividad" cols="100" rows="5"></textarea>
                </div>
            </div>

            <hr>

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                REPRESENTANTE LEGAL
            </div>    

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nombre_repre">NOMBRE <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nombre_repre" id="nombre_repre" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_repre">E-MAIL <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="email_repre" id="email_repre" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_repre">TIPO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <select name="tdoc_repre" id="tdoc_repre" class="formulario_control">
<?php
    $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($arr_tdoc); $i++){
        echo '     <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nrodoc_repre">NRO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nrodoc_repre" id="nrodoc_repre" class="formulario_control">
                </div>
            </div>

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                CONTACTO
            </div>    

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_contacto">NOMBRE <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nombre_contacto" id="nombre_contacto" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_contacto">E-MAIL <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="email_contacto" id="email_contacto" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_contacto">TIPO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <select name="tdoc_contacto" id="tdoc_contacto" class="formulario_control">
<?php
    $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($arr_tdoc); $i++){
        echo '     <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrodoc_contacto">NRO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nrodoc_contacto" id="nrodoc_contacto" class="formulario_control">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono_contacto">TELEFONO <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="telefono_contacto" id="telefono_contacto" class="formulario_control">
                </div>
            </div>
    
            <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:5px;"></div>

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
    <!--@@@@@@@@@@@@ BOTONERA-->

            <div style="width:100%; float:left;margin-bottom:5px;">

                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="grabar" onclick="grabar()">
                    <i class="fa-solid fa-floppy-disk"></i> Grabar
                </button>

            </div>

        </div>  <!-- contenedor formulario -->
        
    </div>  <!-- contenedor principal -->
    <!------ END CUERPO VARIABLE ------>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@ ZONA MODAL -->
    
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@ ZONA JS -->
    <script>
        function grabar(){
            btn_grabar = document.getElementById('grabar');
            btn_grabar.disabled = true;
        
            var v_nombre_empresa = $('#nombre_empresa').val();
            var v_ruc = $('#ruc').val();

            if (v_nombre_empresa != '' && v_ruc != ''){
                $.ajax({
                    url:"buscar_empresa_existe.php",
                    type:'post',
                    data:{
                        "nombre":v_nombre_empresa,
                        "ruc":v_ruc
                    }
                })
                .done(function(rpta){
                    if (rpta == '1'){
                        alert('Ya existe una empresa con el nombre o RNC ingresado, por favor verifique la información ingresada');
                        btn_grabar.disabled = false;
                    } else{
                        if (rpta != '0'){
                            if (confirm('Ya existen empresas con nombre similar :'+rpta+', esta seguro de continuar?') == true) 
                                grabar_final();
                            else btn_grabar.disabled = false;
                        } else grabar_final();
                    }
                });
            } else{
                alert('Debe ingresar datos validos de Nombre Empresa y RNC');
                btn_grabar.disabled = false;
            }
        }

        function grabar_final(){
            var v_nombre_repre = $('#nombre_repre').val();
            var v_email_repre = $('#email_repre').val();
            var v_tdoc_repre = $('#tdoc_repre').val();
            var v_nrodoc_repre = $('#nrodoc_repre').val();
            
            var v_nombre_contacto = $('#nombre_contacto').val();
            var v_email_contacto = $('#email_contacto').val();
            var v_tdoc_contacto = $('#tdoc_contacto').val();
            var v_nrodoc_contacto = $('#nrodoc_contacto').val();
            var v_telefono_contacto = $('#telefono_contacto').val();

            var v_tamanoid = $('#tamanoid').val();
            var v_direccion = $('#direccion').val();
            var v_sectorid = $('#sectorid').val();
            var v_actividad = $('#actividad').val();
            var v_procede = 1;

            var v_nombre_empresa = $('#nombre_empresa').val();
            var v_ruc = $('#ruc').val();
            btn_grabar = document.getElementById('grabar');

            if(v_nombre_empresa == '' || v_ruc == '' || v_direccion == '' || v_actividad == ''){
                alert('Debe completar la informacion de la empresa');
                btn_grabar.disabled = false;
            } else{
                if (v_nombre_repre == '' || v_email_repre == '' || v_nrodoc_repre == ''){
                    alert('Debe completar la información del representante legal');
                    btn_grabar.disabled = false;
                } else{
                    if (v_nombre_contacto == '' || v_email_contacto == '' || v_nrodoc_contacto == '' || v_telefono_contacto == ''){
                        alert('Debe completar la información del contacto');
                        btn_grabar.disabled = false;
                    } else{
                        $.ajax({
                            url:"registra_emisor_manual_proceso.php",
                            type:'post',
                            data:{
                                "nombre":v_nombre_empresa,
                                "ruc":v_ruc,
                                "direccion":v_direccion,
                                "sector_id":v_sectorid,
                                "tamano_id":v_tamanoid,
                                "actividad":v_actividad,
                                "nombre_repre":v_nombre_repre,
                                "email_repre":v_email_repre,
                                "tipodoc_repre":v_tdoc_repre,
                                "nrodoc_repre":v_nrodoc_repre,
                                "nombre_contacto":v_nombre_contacto,
                                "email_contacto":v_email_contacto,
                                "tipodoc_contacto":v_tdoc_contacto,
                                "nrodoc_contacto":v_nrodoc_contacto,
                                "telefono_contacto":v_telefono_contacto
                            }
                        })
                        .done(function(rpta2){
                            alert('La empresa emisora fue registrada, el nuevo emisor debe ingresar a la plataforma y enviar su información!!');
                            direcciona_empresas();
                        });
                    }
                }
            }
        }

        function direcciona_empresas(){
            location.href = "empresas.php";
        }

    </script>
</BODY>
</HTML>