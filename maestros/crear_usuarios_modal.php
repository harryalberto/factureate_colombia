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
//###################################################
//################## LOGICA
$vobj_seg_modal = new seguridad;
$vobj_mae_modal = new maestros;

if ($_GET['usuario_id'] > 0){
    $varr_usuario = $vobj_seg_modal->get_datos_usuario($_GET['usuario_id']);
    $v_nombre = $varr_usuario['nombre'];    $v_apellido = $varr_usuario['apellido'];
    $v_tipodoc = $varr_usuario['tipodoc'];  $v_identificacion = $varr_usuario['identificacion'];
    $v_email = $varr_usuario['email'];      $v_telefono = $varr_usuario['telefono'];
    $v_tipousuario = $varr_usuario['tipousuario'];  $v_perfilid = $varr_usuario['perfilid'];
    $v_fiducia = $varr_usuario['fiduciaria_id'];    $v_empresa_id = $varr_usuario['empresaid'];
} else {
    $v_nombre = '';     $v_apellido = '';       $v_tipodoc = 0;     $v_identificacion = '';
    $v_email = '';      $v_telefono = '';       $v_tipousuario = 0; $v_perfilid = 0;
    $v_fiducia = 0;     $v_empresa_id = 0;
}
//###################################################

?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="usuario_id" id="usuario_id" value="<?=$_GET['usuario_id']?>">
    
    <div id="principal" style="padding-left: 10px;height: 500px;"> <!--display:block;height: 75%;-->
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="nombre">NOMBRE:</label>
                    <input type="text" name="nombre" id="nombre" class="formulario_control" value="<?=$v_nombre?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="apellido">APELLIDO:</label>
                    <input type="text" name="apellido" id="apellido" class="formulario_control" value="<?=$v_apellido?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="tipodoc">TIPO DOC:</label>
                    <select name="tipodoc" id="tipodoc" class="formulario_control">
<?php
    $varr_tdocumento = $vobj_mae_modal->get_tipos('TIPOIDENTIF');

    for ($i = 0; $i < count($varr_tdocumento); $i++){
        if ($varr_tdocumento[$i]['id'] == $v_tipodoc)
            echo '      <option value="'.$varr_tdocumento[$i]['id'].'" selected>'.$varr_tdocumento[$i]['nombre'].'</option>';
        else echo '     <option value="'.$varr_tdocumento[$i]['id'].'">'.$varr_tdocumento[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="identificacion">NRO DOC:</label>
                    <input type="text" name="identificacion" id="identificacion" class="formulario_control" value="<?=$v_identificacion?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="email">EMAIL:</label>
                    <input type="text" name="email" id="email" class="formulario_control" value="<?=$v_email?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="telefono">TELEFONO:</label>
                    <input type="text" name="telefono" id="telefono" class="formulario_control" value="<?=$v_telefono?>">
                </div>
                <div class="formulario_grupo_column" style="width: 250px;">
                    <label for="tipousuario">TIPO USER:</label>
                    <select name="tipousuario" id="tipousuario" class="formulario_control">
<?php
    $varr_tusuario = $vobj_mae_modal->get_tipos_seg('TUSER');

    for ($i = 0; $i < count($varr_tusuario); $i++){
        if ($varr_tusuario[$i]['id'] == $v_tipousuario)
            echo '      <option value="'.$varr_tusuario[$i]['id'].'" selected>'.$varr_tusuario[$i]['nombre'].'</option>';
        else echo '     <option value="'.$varr_tusuario[$i]['id'].'">'.$varr_tusuario[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="perfil">PERFIL:</label>
                    <select name="perfil" id="perfil" class="formulario_control">
<?php
    $varr_perfiles = $vobj_mae_modal->get_perfiles();

    for ($i = 0; $i < count($varr_perfiles); $i++){
        if ($varr_perfiles[$i]['perfil_id'] == $v_perfilid)
            echo '      <option value="'.$varr_perfiles[$i]['perfil_id'].'" selected>'.$varr_perfiles[$i]['nombre'].'</option>';
        else echo '     <option value="'.$varr_perfiles[$i]['perfil_id'].'">'.$varr_perfiles[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="fiduciaria">FIDUCIARIA:</label>
                    <select name="fiduciaria" id="fiduciaria" class="formulario_control">
<?php
    if ($v_fiducia == 0)
        echo '          <option value="0" selected>---- SIN FIDUCIARIA ----</option>';
    else echo '         <option value="0">---- SIN FIDUCIARIA ----</option>';

    $varr_fiduciarias = $vobj_mae_modal->get_fiduciarias();

    for ($i = 0; $i < count($varr_fiduciarias); $i++){
        if ($varr_fiduciarias[$i]['fidu_id'] == $v_fiducia)
            echo '      <option value="'.$varr_fiduciarias[$i]['fidu_id'].'" selected>'.$varr_fiduciarias[$i]['fidu_nombre'].'</option>';
        else echo '     <option value="'.$varr_fiduciarias[$i]['fidu_id'].'">'.$varr_fiduciarias[$i]['fidu_nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="empresa">EMPRESA:</label>
                    <select name="empresa" id="empresa" class="formulario_control">
<?php
    if ($v_empresa_id == 0)
        echo '          <option value="0" selected>---- SIN EMPRESA ----</option>';
    else echo '         <option value="0">---- SIN EMPRESA ----</option>';

    $varr_empresas = $vobj_mae_modal->get_empresas_xestado(3,100000,0,'SELECT',0);

    for ($i = 0; $i < count($varr_empresas); $i++){
        if ($varr_empresas[$i]['empresaid'] == $v_empresa_id)
            echo '      <option value="'.$varr_empresas[$i]['empresaid'].'" selected>'.$varr_empresas[$i]['empresa'].'</option>';
        else echo '     <option value="'.$varr_empresas[$i]['empresaid'].'">'.$varr_empresas[$i]['empresa'].'</option>';
    }
?>
                    </select>
                </div>
            </div>
        </div> <!-- FORMULARIO -->

        <nav>
            <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="guardar()">Guardar</button>
        </nav>
        
    </div> <!-- PRINCIPAL -->
    
    </form>

    <script type="text/javascript">
        function guardar(){
            var formData = new FormData(document.getElementById("frm_modal"));
            
            fetch("crear_usuarios_proceso.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.data == 'ok'){
                    $('#InversorDetalle').fadeIn(1000).html(data);
                    $('#InversorDetalle').modal('hide');
                    refresh_page();
                }
            })
            .catch(err => console.log(err))
        }
    </script>
</BODY>
</HTML>