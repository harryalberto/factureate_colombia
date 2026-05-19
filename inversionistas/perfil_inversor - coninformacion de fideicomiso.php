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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!--########### ZONA SCRIPTS JS ##############-->

<!--##########################################-->
</HEAD>
<?
//############# LOGICA PAGINA ##################
$vobj_mae = new maestros;
$vobj_seg = new seguridad;
$vobj_cuentas = new cuentas;

$varr_tipodoc = $vobj_mae->get_tipos('TIPOIDENTIF');
$varr_usuario = $vobj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);

if ($_SESSION['user']['empresaid'] > 0){ 
    $varr_inversor = $vobj_mae->get_datos_inversor($_SESSION['user']['empresaid']);
    $v_inversor_id = $_SESSION['user']['empresaid'];
} elseif ($_SESSION['user']['empresaid'] < 0){
    $v_inversor_id = $_SESSION['user']['empresaid'] * -1;
    $varr_inversor = $vobj_mae->get_datos_inversor($v_inversor_id);
} else{ 
    $varr_inversor = $vobj_mae->get_datos_inversor($_SESSION['user']['usuarioid']);
    $v_inversor_id = $_SESSION['user']['usuarioid'];
}

// SI ES USUARIO ADMINISTRADOR BUSCO LOS BROKERS
if ($_SESSION['user']['perfilid'] == 2) $varr_brokers = $vobj_mae->get_brokers($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);

$v_comision = ($varr_inversor['comision'] * 100).' %';

$varr_cuentas_banco = $vobj_cuentas->get_cuentas_banco_inversor($v_inversor_id);

if ($varr_inversor['estado_id'] == 7 || $varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64){
    $v_readonly = 'readonly';
    $v_disabled = 'disabled';
} else {
    $v_readonly = '';
    $v_disabled = '';
}
//##############################################
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'inversionistas/perfil_inversor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--##################### BLOQUE PRNCIPAL ########################-->
    <div class="contenedor_principal">

        <div style="text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);max-width:700px;margin: 0px auto;padding: 10px;">
            Perfil <?php echo $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];?>
        </div>

        <div class="contenedor_formulario" id="formulario" style="margin-bottom:20px;">
            <form name='frm_datos' method='post' id='frm_datos' enctype="multipart/form-data">
            <input type="hidden" id="user_id" value="<?=$_SESSION['user']['usuarioid']?>">
            <input type="hidden" id="emp_id" value="<?=$_SESSION['user']['empresaid']?>">
            <input type="hidden" id="estado_id" value="<?=$varr_inversor['estado_id']?>">
            <input type="hidden" id="inversor_id" name="inversor_id" value="<?=$v_inversor_id?>">
            <input type="hidden" id="accion" name="accion" value="guarda_datos">

            <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold;"><i class="fa-solid fa-user"></i> Información</p>
            </div>
<?php
    if ($_SESSION['user']['empresaid'] > 0 && $varr_usuario['perfiltipo'] == 10){
        //@@@@ INVERSOR EMPRESA
        $varr_empresa = $vobj_mae->get_datos_empresa($_SESSION['user']['empresaid']);

        echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:600px;">
                    <label for="empresa_nombre">Nombre Empresa</label>
                    <input type="text" class="formulario_control" id="empresa_nombre" value="'.$varr_empresa['nombre'].'" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="empresa_rnc">RNC</label>
                    <input type="text" class="formulario_control" id="empresa_rnc" value="'.$varr_empresa['identificacion'].'" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:400px;">
                    <label for="empresa_direccion">Dirección Empresa</label>
                    <input type="text" class="formulario_control" id="empresa_direccion" value="'.$varr_empresa['direccion'].'">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="empresa_telefono">Telefono</label>
                    <input type="text" class="formulario_control" id="empresa_telefono" value="'.$varr_empresa['telf_contacto'].'">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="empresa_email">Email</label>
                    <input type="text" class="formulario_control" id="empresa_email" value="'.$varr_empresa['email_contacto'].'">
                </div>
            </div>';
    }
?>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             INFORMACION PERSONAL 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="formulario_control" id="nombre" name="nombre" value="<?=$_SESSION['user']['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="apellido">Apellidos</label>
                    <input type="text" class="formulario_control" id="apellido" name="apellido" value="<?=$_SESSION['user']['apellido']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="tipo_doc_nom">Tipo Documento</label>
<?php
    if ($varr_inversor['estado_id'] == 6){      // REGISTRADO
        echo '      <select id="tipo_doc_nom" name="tipo_doc_nom" class="formulario_control">';

        for ($i=0; $i<count($varr_tipodoc); $i++){
            if ($varr_tipodoc[$i]['id'] == $varr_usuario['tipodoc'])
                echo '      <option value="'.$varr_tipodoc[$i]['id'].'" selected>'.$varr_tipodoc[$i]['nombre'].'</option>';
            else
                echo '      <option value="'.$varr_tipodoc[$i]['id'].'">'.$varr_tipodoc[$i]['nombre'].'</option>';
        }

        echo '      </select>';
    } else{
        echo '      <input type="text" class="formulario_control" id="tipo_doc_nom" name="tipo_doc_nom" value="'.$varr_usuario['tipodoc_nombre'].'" readonly>
                    <input type="hidden" id="tipo_doc" name="tipo_doc" value="'.$varr_usuario['tipodoc'].'">';
    }
?>                
                </div>
<?php
    if ($varr_usuario['perfiltipo'] == 10)     // ADMINISTRADOR
        $v_documento_identidad = '<a href="'.$varr_inversor['documento'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:22px;"></i></a>';
    else $v_documento_identidad = '';

    if ($varr_usuario['email'] != "") $v_mail_color = ""; else $v_mail_color = "border-color: var(--color-rojo);";
    if ($varr_inversor['telefono'] != "") $v_telefono_color = ""; else $v_telefono_color = "border-color: var(--color-rojo);";
?>                
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="nro_doc">Nro Documento</label>
                    <input type="text" class="formulario_control" id="nro_doc" name="nro_doc" value="<?=$varr_usuario['identificacion']?>" <?echo $v_readonly;?>>
                    <input type="hidden" name="archivo_documento" id="archivo_documento" value="<?=$varr_inversor['documento']?>">
                </div>
                <div class="formulario_grupo_row" style="width:50px;">
                    <label for="doc">Doc</label>
                    <span id="doc"><?echo $v_documento_identidad;?></span>
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="email_persona">Email</label>
                    <input type="text" class="formulario_control" id="email_persona" name="email_persona" value="<?=$varr_usuario['email']?>" style="transition: var(--color-gris-oscuro) .15s easy-in-out;<?echo $v_mail_color;?>" onkeypress="focusFunction(this)" onchange="focusFunction(this)" <?echo $v_readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="telefono_persona">Telefono</label>
                    <input type="text" class="formulario_control" id="telefono_persona" name="telefono_persona" value="<?=$varr_inversor['telefono']?>" style="transition: var(--color-gris-oscuro) .15s easy-in-out;<?echo $v_telefono_color;?>" onkeypress="focusFunction(this)" onchange="focusFunction(this)" <?echo $v_readonly;?>>
                </div>
            </div> <!-- INFORMACION PERSONAL -->

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            LINEA DE DATOS DE COMISION Y CATEGORIA
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="pep">PEP</label>
<?php
    if ($varr_inversor['pep'] == 1){
?>
                    <input type="text" class="formulario_control" id="pep" name="pep" value="SI" style="text-align:center;" readonly>
<?php
    } else {
?>
                    <input type="text" class="formulario_control" id="pep" name="pep" value="NO" style="text-align:center;" readonly>
<?php
    }
?>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="categoria">Categoria Inversor</label>
                    <input type="text" class="formulario_control" id="categoria" value="<?=$varr_inversor['categoria']?>" style="text-align: center;" readonly>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="comision">Comisión Factureate <abbr title="Es el % de la ganancia de cada operación que cobrará Factureate"><i class="fa-solid fa-circle-question"></i></abbr></label>
                    <input type="text" class="formulario_control" id="comision" value="<?=$v_comision?>" readonly>
                </div>
            </div> <!-- COMISIONES Y CATEGORIA -->

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            SECCION PARA INVERSORES ADMINISTRADORES PERSONA FISICA -->
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $_SESSION['user']['empresaid'] == 0){
        if ($varr_inversor['ocupacion_id'] > 0) $v_ocupacion_color = ""; else $v_ocupacion_color = "border-color: var(--color-rojo);";
?>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="condicion_laboral">Condicion Laboral</label>
                    <select name="condicion_laboral" id="condicion_laboral" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out;<?echo $v_ocupacion_color;?>" onchange="cambiaCondicion()" <?echo $v_disabled;?>>
<?php
        if ($varr_inversor['ocupacion_id'] == 0)
            echo '      <option value="0" style="color: var(--color-gris-oscuro)" selected>Seleccione</option>';

        $varr_ocupacion = $vobj_mae->get_tipos('OCUPACION');

        for ($i = 0; $i < count($varr_ocupacion); $i++){
            if ($varr_ocupacion[$i]['id'] == $varr_inversor['ocupacion_id']) 
                echo '  <option value="'.$varr_ocupacion[$i]['id'].'" selected>'.$varr_ocupacion[$i]['nombre'].'</option>';
            else echo ' <option value="'.$varr_ocupacion[$i]['id'].'">'.$varr_ocupacion[$i]['nombre'].'</option>';
        }
?>
                    </select>               
                </div>
                <div class="formulario_grupo_row" style="width:300px;" id="container_justificacion">
<?php
        if ($varr_inversor['ocupacion_id'] == 113){     //ASALARIADO
            if ($varr_inversor['ec_laboral_path'] != ""){ 
                $v_ec_laboral = '<a href="'.$varr_inversor['ec_laboral_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a>
                                 <input type="hidden" name="ec_nomnina_path" id="ec_nomnina_path" value="'.$varr_inversor['ec_laboral_path'].'">';
                $v_nomina_color = "";
            } else{ 
                $v_ec_laboral = '<input type="hidden" name="ec_nomnina_path" id="ec_nomnina_path">';
                $v_nomina_color = "border-color: var(--color-rojo);";
            }
?>
                    <label for="ec_nomina"><abbr title="Ultimos 3 estados de cuenta de nomina">Estado Cuenta Nomina</abbr></label>
                    <input type="file" name="ec_nomina" id="ec_nomina" class="formulario_control" onkeypress="focusFunction(this)" onchange="focusFunction(this)" <?echo $v_disabled;?>>
<?php
        }

        if ($varr_inversor['ocupacion_id'] == 114){     //INDEPENDIENTE
            if ($varr_inversor['declaracion_impuestos_path'] != ""){ 
                $v_declaracion_impuestos = '<a href="'.$varr_inversor['declaracion_impuestos_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a>
                                            <input type="hidden" name="declaracion_impuestos_path" id="declaracion_impuestos_path" value="'.$varr_inversor['declaracion_impuestos_path'].'">';
                $v_impuestos_color = "";
            } else{ 
                $v_declaracion_impuestos = '<input type="hidden" name="declaracion_impuestos_path" id="declaracion_impuestos_path">';
                $v_impuestos_color = "border-color: var(--color-rojo);";
            }
?>
                    <label for="declaracion_impuestos">
                        <abbr title="Última declaración de impuestos del país donde residió el último año">Impuestos</abbr></label>
                    <input type="file" name="declaracion_impuestos" id="declaracion_impuestos" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; font-size: 12px;<?echo $v_impuestos_color;?>" onkeypress="focusFunction(this)" onchange="focusFunction(this)">
<?php
        }

        if ($varr_inversor['ocupacion_id'] == 115){     //EMPRESARIO
            if ($varr_inversor['eeff_path'] != "") {
                $v_eeff = '<a href="'.$varr_inversor['eeff_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a>
                            <input type="hidden" name="eeff_path" id="eeff_path" value="'.$varr_inversor['eeff_path'].'">';
                $v_eeff_color = "";
            } else {
                $v_eeff = '<input type="hidden" name="eeff_path" id="eeff_path">';
                $v_eeff_color = "border-color: var(--color-rojo);";
            }
?>
                    <label for="eeff"><abbr title="Estados Financieros auditados del último año o IR2 del último año">EEFF / IR2</abbr></label>
                    <input type="file" name="eeff" id="eeff" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out;font-size: 12px;<?echo $v_eeff_color;?>" onkeypress="focusFunction(this)" onchange="focusFunction(this)">
<?php
        }
?>
                </div>
<?php
        if ($v_ec_laboral != '' || $v_declaracion_impuestos != '' || $v_eeff != ''){
?>
                <div class="formulario_grupo_row" style="width:50px;">
                    <label for="doc1">PDF</label>
                    <span id="doc1"><?echo $v_ec_laboral.$v_declaracion_impuestos.$v_eeff;?></span>
                </div>
<?php
        }
?>

                <div class="formulario_grupo_row" style="width:300px;" id="container_kwc_docs">
<?php
        if ($varr_inversor['ocupacion_id'] == 113 || $varr_inversor['ocupacion_id'] == 114 || $varr_inversor['ocupacion_id'] == 115){     //ASALARIADO
            if ($varr_inversor['f_servicios_path'] != ""){ 
                $v_f_servicios = '<a href="'.$varr_inversor['f_servicios_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a>
                                  <input type="hidden" name="f_servicios_path" id="f_servicios_path" value="'.$varr_inversor['f_servicios_path'].'">';
                $v_servicios_color = "";
            } else{ 
                $v_f_servicios = '<input type="hidden" name="f_servicios_path" id="f_servicios_path">';
                $v_servicios_color = "border-color: var(--color-rojo);";
            }
?>
                    <label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">Factura Servicios</abbr></label>
                    <input type="file" name="f_servicios" id="f_servicios" class="formulario_control" onkeypress="focusFunction(this)" onchange="focusFunction(this)" <?echo $v_disabled;?>>
<?php
        }
?>
                </div>
<?php
        if ($v_f_servicios != ''){
?>
                <div class="formulario_grupo_row" style="width:50px;">
                    <label for="doc2">PDF</label>
                    <span id="doc2"><?echo $v_f_servicios;?></span>
                </div>
<?php
        }
?>
            </div> <!-- OCUPACION -->

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            INFORMACION COMPLEMENTARIA DE DE VINCULACION 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:800px;" id="container_explicacion">
<?php
        if ($varr_inversor['ocupacion_id'] == 114){     //INDEPENDIENTE
            if ($varr_inversor['actividad_descripcion'] != "") $v_actividad_color = ""; else $v_actividad_color = "border-color: var(--color-rojo);";
?>
                    <label for="explica_actividad">Explicación Actividad</label>
                    <textarea name="explica_actividad" id="explica_actividad" class="formulario_control" onkeypress="focusFunctionText(this)" onchange="focusFunctionText(this)" placeholder="Descripcion de actividad economica" cols="80"><?echo $varr_inversor['actividad_descripcion'];?></textarea>
<?php
        }
?>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:300px;" id="container_movimientos_banco">
<?php
        if ($varr_inversor['ocupacion_id'] == 114 || $varr_inversor['ocupacion_id'] == 115){     //INDEPENDIENTE / EMPRESARIO
            if ($varr_inversor['movimientos_banco_path'] != "") {
                $v_movimientos_banco = '<a href="'.$varr_inversor['movimientos_banco_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a>
                                        <input type="hidden" name="movimientos_banco_path" id="movimientos_banco_path" value="'.$varr_inversor['movimientos_banco_path'].'">';
                $v_bancos_color = "";
            } else {
                $v_movimientos_banco = '<input type="hidden" name="movimientos_banco_path" id="movimientos_banco_path">';
                $v_bancos_color = "border-color: var(--color-rojo);";
            }
?>
                    <label for="movimientos_banco"><abbr title="Movimientos bancarios de los 6 ultimos meses">Mov Bancarios</abbr></label>
                    <input type="file" name="movimientos_banco" id="movimientos_banco" class="formulario_control" onkeypress="focusFunction(this)" onchange="focusFunction(this)">
<?php
        }
?>
                </div>
<?php
        if ($v_movimientos_banco != ''){
?>
                <div class="formulario_grupo_row" style="width:50px;">
                    <label for="movimientos_banco_pdf">PDF</label>
                    <span name="movimientos_banco_pdf" id="movimientos_banco_pdf"><?echo $v_movimientos_banco;?></span>
                </div>
<?php
        }
?>

                <div class="formulario_grupo_row" style="width:300px;" id="container_registro_mercantil">
<?php
        if ($varr_inversor['ocupacion_id'] == 115){     //EMPRESARIO
            if ($varr_inversor['registro_mercantil_path'] != "") {
                $v_registro_mercantil = '<a href="'.$varr_inversor['registro_mercantil_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:20px;"></i></a>
                                         <input type="hidden" name="registro_mercantil_path" id="registro_mercantil_path" value="'.$varr_inversor['registro_mercantil_path'].'">';
                $v_rm_color = "";
            } else {
                $v_registro_mercantil = '<input type="hidden" name="registro_mercantil_path" id="registro_mercantil_path">';
                $v_rm_color = "border-color: var(--color-rojo);";
            }
?>
                    <label for="registro_mercantil"><abbr title="Registro Mercantil vigente">Registro Mercantil</abbr></label>
                    <input type="file" name="registro_mercantil" id="registro_mercantil" class="formulario_control" onkeypress="focusFunction(this)" onchange="focusFunction(this)">
<?php
        }
?>
                </div>
<?php
        if ($v_registro_mercantil != ''){
?>
                <div class="formulario_grupo_row" style="width:50px;">
                    <label for="rm_pdf">PDF</label>
                    <span name="rm_pdf" id="rm_pdf"><?echo $v_registro_mercantil;?></span>
                </div>
<?php
        }
?>
            </div>

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            FORMUALRIO KYC 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
        if ($varr_inversor['kwc'] == 0){ 
            $v_download_kyc = '<a href="KYC.pdf" target="_blank"><abbr title="Download Formulario Conoce a tu Cliente"><i class="fa-solid fa-download" style="font-size:20px;margin-right:5px;"></i></abbr></a>';
            $v_input_kyc = '<input type="file" name="formulario_kyc" id="formulario_kyc" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;">';
            $v_pdf_kyc = '';
        } else {
            $v_download_kyc = '';
            $v_input_kyc = '';
            $v_pdf_kyc = '<a href="'.$varr_inversor['kyc_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:22px;"></i></a>
                          <input type="hidden" name="formulario_kyc" id="formulario_kyc" value="'.$varr_inversor['kyc_path'].'">';
        }
?>
            <div class="contenedor_formulario_column">
                <input type="hidden" name="kwc" id="kwc" value="<?=$varr_inversor['kwc']?>">
<?php
        if ($v_download_kyc != ''){
?>
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="downkyc">Download</label>
                    <span id="downkyc"><?echo $v_download_kyc;?></span>
                </div>
<?php
        }

        if ($v_input_kyc != ''){
?>
                <div class="formulario_grupo_row" style="width:300px;">
                    <label for="formulario_kyc"><abbr title="Formulacio Conoce a tu cliente">Formulario KYC</abbr></label>
                    <?echo $v_input_kyc;?>
                </div>
<?php
        }

        if ($v_pdf_kyc != ''){
?>
                <div class="formulario_grupo_row" style="width:100px;">
                    <label for="formulario_kyc"><abbr title="Formulacio Conoce a tu cliente">Formulario KYC</abbr></label>
                    <span id="kyc"><?echo $v_pdf_kyc;?></span>
                </div>
<?php
        }
?>
            </div>

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            BOTONES DE INFORMACION GENERAL -->
<?php
        if ($varr_inversor['estado_id'] == 6 || $varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64){
?>
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="guardarDatos('directo')" id="btn_guardar">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Datos
                </button>
                
<?php
        }
?>
            </div>

            <!-- @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA CUENTA DE BANCO 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
        if ($_SESSION['user']['perfilid'] == 2){    // administrador
        echo '
            <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold;"><i class="fa-solid fa-building-columns"></i> Datos Cuentas Bancarias</p>
                <input type="hidden" name="q_cuentas_banco" id="q_cuentas_banco" value="'.count($varr_cuentas_banco).'">
            </div>';
?>
            <div style="overflow:hidden;margin:5px;padding:5px;" id="tabla_cuentas">
                <table class="tabla_resize">
                    <thead>
                        <tr>
                            <th scope="col" class="sort asc">ID CUENTA</th>     <th scope="col" class="sort asc">BANCO</th>
                            <th scope="col" class="sort asc">TIPO CUENTA</th>   <th scope="col" class="sort asc">MONEDA</th>
                            <th scope="col" class="sort asc">CUENTA</th>        <th scope="col" class="sort asc">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody id="content">
            
<?php
            for ($i=0; $i<count($varr_cuentas_banco); $i++){
                if ($varr_cuentas_banco[$i]['estado_id'] == 1) $v_estado = '<i class="fa-solid fa-circle-check" style="margin-left:5px;color:var(--color-verde);"></i>';
                else $v_estado = '<abbr title="Pendiente de verificar por Factureate"><i class="fa-solid fa-triangle-exclamation" style="margin-left:5px;color:var(--color-amarillo);"></i></abbr>';

                echo '   <tr>    
                            <td data-label="ID CUENTA">'.$varr_cuentas_banco[$i]['cuenta_banco_id'].'</td>
                            <td data-label="BANCO">'.$varr_cuentas_banco[$i]['banco_nombre'].'</td>
                            <td data-label="TIPO CUENTA">'.$varr_cuentas_banco[$i]['tcuenta_nombre'].'</td>
                            <td data-label="MONEDA">'.$varr_cuentas_banco[$i]['moneda_nombre'].'</td>
                            <td data-label="CUENTA">'.$varr_cuentas_banco[$i]['cuenta'].'</td>
                            <td data-label="ESTADO">'.$varr_cuentas_banco[$i]['estado_nombre'].$v_estado.'</td>
                        </tr>';
            }
?>
                    </tbody>
                </table>
            </div>
<?php
            if ($varr_inversor['estado_id'] == 6 || $varr_inversor['estado_id'] == 8 || $varr_inversor['estado_id'] == 64)
                echo '
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="agregarCuenta()">
                    <i class="fa-solid fa-circle-plus"></i> Agregar Cuenta
                </button>
            </div>';
        }
    }
?>
            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA DE PERFIL DE INVERSION 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $varr_inversor['estado_id'] == 64){    
        $vobj_inversiones = new inversiones;

        $varr_moneda_nac = $vobj_mae->get_parametro_detalle(49);
        $v_monedanac_id = $varr_moneda_nac['valornum'];
        $varr_monedas = $vobj_mae->get_tipos('MONEDA');

        for ($i=0; $i<count($varr_monedas); $i++){
            if ($v_monedanac_id == $varr_monedas[$i]['id']) $v_moneda_simbolo = $varr_monedas[$i]['dato1'];
        }

        $varr_perfil_inversor = $vobj_inversiones->get_perfil_inversor($v_inversor_id);
        
        if ($varr_perfil_inversor['count'] > 0){
            //$varr_perfil_inversor_detalle = $vobj_inversiones->get_perfil_inversor_detalle($v_inversor_id);
            $v_perfil_check = 'checked';
            
            if ($varr_perfil_inversor['invierte_auto'] == 1) $v_automatico = 'checked'; else $v_automatico = '';
            if ($varr_perfil_inversor['notifica_email'] == 1) $v_notifica_email_legal = 'checked'; else $v_notifica_email_legal = '';
            if ($varr_perfil_inversor['notifica_msg'] == 1) $v_notifica_msg_legal = 'checked'; else $v_notifica_msg_legal = '';
            if ($varr_perfil_inversor['notifica_email_oport'] == 1) $v_notifica_email_opor = 'checked'; else $v_notifica_email_opor = '';
            if ($varr_perfil_inversor['notifica_msg_oport'] == 1) $v_notifica_msg_opor = 'checked'; else $v_notifica_msg_opor = '';
            if ($varr_perfil_inversor['notifica_cada_oport'] == 1) $v_notifica_cada = 'checked'; else $v_notifica_cada = '';
            $v_monto_minimo = $varr_perfil_inversor['monto_min'];
            $v_monto_maximo = $varr_perfil_inversor['monto_max'];
            $v_tea_automatica = $varr_perfil_inversor['tea_automatica'];
        } else {
            $v_perfil_check = '';
            $v_automatico = '';
            $v_notifica_email_legal = 'checked';
            $v_notifica_msg_legal = 'checked';
            $v_notifica_email_opor = 'checked';
            $v_notifica_msg_opor = 'checked';
            $v_notifica_cada = 'checked';
            $v_monto_minimo = 0;
            $v_monto_maximo = 0;
            $v_tea_automatica = 0;
        }
?>
            <div style="display: inline-flex;width: 100%;margin-top:15px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold;"><i class="fa-solid fa-money-bill-trend-up"></i> Perfil de Inversión</p>
            </div>
            <div class="contenedor_formulario_column">
                <div style="width:100%;display: inline-flex;">
                    <input type="checkbox" id="activa_perfil" name="activa_perfil" value="1" class="formulario_control" <?php echo $v_perfil_check;?>>
                    <label for="activa_perfil" style="margin-right:30px;margin-left:15px;">
                        <i class="fa-solid fa-id-card-clip"></i> Perfil de inversión 
                        <abbr title="Activa su nivel de inversion donde solo le pareceran las oportunidades que se ajusten a su perfil"><i class="fa-solid fa-circle-question"></i></abbr>
                    </label>
                    <input type="checkbox" id="activa_inversion_atm" name="activa_inversion_atm" value="1" class="formulario_control" <?php echo $v_automatico;?>>
                    <label for="activa_inversion_atm" style="margin-left:15px;">
                        <i class="fa-solid fa-robot"></i> Inversión automática 
                        <abbr title="Activa la inversión automática en las oportunidades que cumplen con su perfil"><i class="fa-solid fa-circle-question"></i></abbr>
                    </label>
                </div>
            </div>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_email">Notifica por Email Legal</label>
                    <input type="checkbox" class="formulario_control" id="notifica_email" name="notifica_email" value="1" <?php echo $v_notifica_email_legal;?>>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_msg">Notifica Mensajes Legal</label>
                    <input type="checkbox" class="formulario_control" id="notifica_msg" name="notifica_msg" value="1" <?php echo $v_notifica_msg_legal;?>>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_email_opor">Notifica Email Oportunidad</label>
                    <input type="checkbox" class="formulario_control" id="notifica_email_opor" name="notifica_email_opor" value="1" <?php echo $v_notifica_email_opor;?>>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_msg_opor">Notifica Mensajes Oportunidad</label>
                    <input type="checkbox" class="formulario_control" id="notifica_msg_opor" name="notifica_msg_opor" value="1" <?php echo $v_notifica_msg_opor;?>>
                </div>
                <div class="formulario_grupo_row" style="width:150px;">
                    <label for="notifica_cada_opor">Notifica Cada Vez hay Oportunidad</label>
                    <input type="checkbox" class="formulario_control" id="notifica_cada_opor" name="notifica_cada_opor" value="1" <?php echo $v_notifica_cada;?>>
                </div>
            </div>
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="monto_minimo">Monto minimo inversion en <abbr title="Se realizara la conversion a todas las monedas disponibles"><?php echo $v_moneda_simbolo;?></abbr></label>
                    <input type="number" class="formulario_control" id="monto_minimo" name="monto_minimo" value="<?=$v_monto_minimo?>" style="text-align:right;">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="monto_maximo">Monto maximo inversion en <abbr title="Se realizara la conversion a todas las monedas disponibles"><?php echo $v_moneda_simbolo;?></abbr></label>
                    <input type="number" class="formulario_control" id="monto_maximo" name="monto_maximo" value="<?=$v_monto_maximo?>" style="text-align:right;">
                </div>
                <div class="formulario_grupo_row" style="width:200px;">
                    <label for="tea_automatica">% TEA automática</label>
                    <input type="number" class="formulario_control" id="tea_automatica" name="tea_automatica" value="<?=$v_tea_automatica?>" style="text-align:right;">
                </div>
            </div>
            <div class="contenedor_formulario_column">
<?php
    $varr_variables = $vobj_mae->get_tipos('VARIABLE PERFIL INV');

        for ($i=0; $i<count($varr_variables); $i++){
        echo '  <div class="formulario_grupo_row" style="width:200px;">
                    <input type="hidden" value="'.$varr_variables[$i]['id'].'" id="'.$varr_variables[$i]['nombre'].'-1" name="'.$varr_variables[$i]['nombre'].'-1">
                    <label for="'.$varr_variables[$i]['nombre'].'[]">'.$varr_variables[$i]['nombre'].'</label>
                    <select name="'.$varr_variables[$i]['nombre'].'[]" id="'.$varr_variables[$i]['nombre'].'[]" class="form-control basic-multiple" multiple="multiple" name="'.$varr_variables[$i]['nombre'].'[]">';

            $varr_variable_valor = $vobj_mae->get_variables_valor($varr_variables[$i]['nombre'],0);     // todas
            // las variables particulares del inversor
            if ($_SESSION['user']['empresaid'] > 0) $varr_variable_valor_inv = $vobj_mae->get_variables_valor($varr_variables[$i]['nombre'], $_SESSION['user']['empresaid']);
            else $varr_variable_valor_inv = $vobj_mae->get_variables_valor($varr_variables[$i]['nombre'], $_SESSION['user']['usuarioid']);
            
            if (count($varr_variable_valor_inv) == 0) $v_sin_variable = 1;

            for ($j=0; $j<count($varr_variable_valor); $j++){
                if ($v_sin_variable > 0)
                echo '  <option value="'.$varr_variable_valor[$j]['id'].'">'.$varr_variable_valor[$j]['nombre'].'</option>';
                else{
                    $v_encontrado = 0;

                    for ($z=0; $z<count($varr_variable_valor_inv); $z++){
                        if ($varr_variable_valor[$j]['id'] == $varr_variable_valor_inv[$z]['id']) $v_encontrado = 1;
                    }

                    if ($v_encontrado > 0) echo '  
                        <option value="'.$varr_variable_valor[$j]['id'].'" selected>'.$varr_variable_valor[$j]['nombre'].'</option>';
                    else echo '  
                        <option value="'.$varr_variable_valor[$j]['id'].'">'.$varr_variable_valor[$j]['nombre'].'</option>';
                }
            }

        echo '      </select>
                </div>
                <input type="hidden" id="variables_count" value="'.count($varr_variables).'">';
        }
?>
            </div>
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="guardaPerfilInversion()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Perfil Inversión
                </button>
            </div>
<?php
    }
?>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
            ZONA LEGAL 
            @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $varr_inversor['estado_id'] == 64){    //INVERSOR ADMINISTRADOR
?>
            <div style="display: inline-flex;width: 100%;margin-top:20px;font-size:16px;background-color:var(--color-gris-claro);padding-top: 10px;padding-left: 10px;">
                <p style="font-weight: bold;"><i class="fa-solid fa-scale-balanced"></i> Contrato</p>
            </div>
            <div class="contenedor_formulario_column" style="margin-bottom:20px;">
                <div style="width:100%;display: inline-flex;">
                    <label for="contrato" style="margin-right:30px;">
                        Contrato de Vinculación: <a href="<?=$varr_inversor['contrato']?>" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a>
                    </label>
<?php
        if ($varr_inversor['estado_anulacion_id'] == 0)
        echo '      <button style="font-size:12px;background-color:var(--color-rojo);border:none;" type="button" class="btn btn-primary" onclick="anularContrato()">
                        <i class="fa-solid fa-bell-concierge"></i> Solicitar Anulación
                    </button>';
        elseif ($varr_inversor['estado_anulacion_id'] == 55 || $varr_inversor['estado_anulacion_id'] == 56) {
        echo '      <label style="margin-right:30px;color:var(--color-rojo);">Anulación: '.$varr_inversor['estado_anulacion_nombre'].'</label>
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="anularSolicitudAnul()">
                        <i class="fa-solid fa-hand"></i> Anular solicitud
                    </button>';
    }
?>

                </div>
            </div>
<?php
    }
?>
            </form>
        </div> <!-- CONTENEDOR FORMULARIO -->

        <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ 
        BOTONERA TOTAL
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ -->
        <div style="width:100%; float: left;overflow: hidden;height: 1px;background-color: var(--color-gris-claro);margin-top: 10px;"></div>
        <div style="width:100%; float: left;overflow: hidden;margin-top: 10px;">
<?php
    if ($_SESSION['user']['perfilid'] == 2 && $varr_inversor['estado_id'] == 6){    //ADMINISTRADOR / REGISTRADO
?>
            <!--<button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="grabarInformacion()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Información
            </button>-->
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" id="btn_enviar" onclick="enviarInformacion()">
                <i class="fa-solid fa-paper-plane"></i> Enviar Información
            </button>
<?php
    }
?>
        </div>
    </div> <!-- CONTENEDOR PRINCIPAL -->
    <!--###################################################-->
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    ZONA MODAL 
    @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->
    <div class="modal fade" id="PerfilInversor" tabindex="-1" role="dialog">
        <div class="modal-dialog">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">DETALLE DE PROPUESTA</h5></li>
                        <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
                    </ul>
                </div>
                <div class="modal-body">
                </div>
                <!--<div class="modal-footer">-->
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
                <!--</div>-->
            </div>
        </div>
    </div>

    <!--#################### ZONA JS ###################-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
    <script>
        /*==== llamada a funcion que verifica si el inversor esta en estado REGISTRADO ====*/
        document.addEventListener("DOMContentLoaded", gestionaInversorNuevo);

        //==== funciones cuando el inversor esta en estado REGISTRADO ====
        function gestionaInversorNuevo(){
            var estado_id = document.getElementById("estado_id").value;
            var inversor_id = document.getElementById("inversor_id").value;
            var empresa_id = $('#emp_id').val();
            var user_id = $('#user_id').val();
            
            if (estado_id == 6){
                // estado registrado
                $('.modal-title').text('REGISTRO');
                
                var datos_personales = verifica_datos_personales();

                if (datos_personales == 1){
                    $('.modal-body').load('reg_datos_personales_inversor.php?inversor_id='+inversor_id+'&empresa_id='+empresa_id+'&user_id='+user_id,function(){
                    $('#PerfilInversor').modal({show:true});
                    });
                } else {
                    $('.modal-body').load('reg_datos_cuentas_inversor.php?inversor_id='+inversor_id+'&empresa_id='+empresa_id+'&user_id='+user_id,function(){
                    $('#PerfilInversor').modal({show:true});
                    });
                }
            }
        }

        function verifica_datos_personales(){
            var resultado;
            var nombre = document.getElementById("nombre").value;
            var apellido = document.getElementById("apellido").value;
            var tipo_doc_nom = document.getElementById("tipo_doc_nom").value;
            var nro_doc = document.getElementById("nro_doc").value;
            var archivo_documento = document.getElementById("archivo_documento").value;
            var email_persona = document.getElementById("email_persona").value;
            var telefono_persona = document.getElementById("telefono_persona").value;

            if (nombre != "" && apellido != "" && tipo_doc_nom > 0 && nro_doc != "" && archivo_documento != "" && email_persona != "" && telefono_persona != "")
                resultado = 0;
            else resultado = 1;

            return resultado;
        }

        function cambia_modal_registro(origen, inversor, empresa){
            //$('#contenedor_principal').modal('hide');
            $('#PerfilInversor').modal({show:false});

            if (origen == 'datos_personales'){
                $('.modal-title').text('REGISTRO');
                $('.modal-body').load('reg_datos_cuentas_inversor.php?inversor_id='+inversor+'&empresa_id='+empresa,function(){
                    $('#PerfilInversor').modal({show:true});
                });
            }

            if (origen == 'datos_cuenta'){
                alert('Enhorabuena!! terminaste el registro, tu información será analizada por nuestra área legal, luego de ello recibirás un correo donde te avisaremos que ya puedes invertir con nosotros');
                refresh_page();
            }
        }
        //==== END funciones cuando el inversor esta en estado REGISTRADO ====

        $(document).ready(function () {
            $('.basic-multiple').select2();
        });

        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES VISUALES
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        function focusFunction(obj){
            if(obj.value == "") obj.style = "border:1px solid var(--color-rojo)";
            else obj.style = "";
        }

        function focusFunctionFile(obj){
            if(obj.value == "") obj.style = "border-color: var(--color-rojo); font-size: 12px;";
            else obj.style = "font-size: 12px;";
        }

        function focusFunctionText(obj){
            if(obj.value == "") obj.style = "transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;";
            else obj.style = "transition: var(--color-gris-oscuro) .15s easy-in-out; font-size: 12px;";
        }

        function cambiaCondicion(){
            var v_condicion = $('#condicion_laboral').val();
            var obj = document.getElementById('condicion_laboral');
            obj.style = "font-size: 12px;";

            var container_justificacion = document.getElementById('container_justificacion');
            var container_kwc_docs = document.getElementById('container_kwc_docs');
            var container_movimientos_banco = document.getElementById('container_movimientos_banco');
            var container_explicacion = document.getElementById('container_explicacion');
            var container_registro_mercantil = document.getElementById('container_registro_mercantil');

            var justificacion_fondos, kwc_docs, explicacion_actividad, movimientos_banco, registro_mercantil;

            if (v_condicion == 113){
                // ASALARIADO
                justificacion_fondos = '<label for="ec_nomina"><abbr title="Ultimos 3 estados de cuenta de nomina">E.C. Nomina</abbr></label><input type="file" name="ec_nomina" id="ec_nomina" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo);font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="ec_nomnina_path" id="ec_nomnina_path">';

                kwc_docs = '<label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">F. Servicios</abbr></label><input type="file" name="f_servicios" id="f_servicios" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="f_servicios_path" id="f_servicios_path">';

                container_justificacion.innerHTML = justificacion_fondos;
                container_kwc_docs.innerHTML = kwc_docs;
                container_movimientos_banco.innerHTML = '';
                container_explicacion.innerHTML = '';
                container_registro_mercantil.innerHTML = '';
            }

            if (v_condicion == 114){
                // INDEPENDIENTE
                justificacion_fondos = '<label for="declaracion_impuestos"><abbr title="Ultima declaracion de impuestos del pais que residio el ultimo año">Impuestos</abbr></label><input type="file" name="declaracion_impuestos" id="declaracion_impuestos" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;"><input type="hidden" name="declaracion_impuestos_path" id="declaracion_impuestos_path">';

                kwc_docs = '<label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">F. Servicios</abbr></label><input type="file" name="f_servicios" id="f_servicios" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;"><input type="hidden" name="f_servicios_path" id="f_servicios_path">';

                explicacion_actividad = '<label for="explica_actividad">Explicación Actividad</label><textarea name="explica_actividad" id="explica_actividad" class="formulario_control" onkeypress="focusFunctionText(this)" onchange="focusFunctionText(this)" placeholder="Descripcion de actividad economica" cols="80"></textarea>';

                movimientos_banco = '<label for="movimientos_banco"><abbr title="Movimientos bancarios de los 6 ultimos meses">Mov Bancarios</abbr></label><input type="file" name="movimientos_banco" id="movimientos_banco" class="formulario_control" onchange="focusFunctionFile(this)" style="background-color:#fff;"><input type="hidden" name="movimientos_banco_path" id="movimientos_banco_path">';

                container_justificacion.innerHTML = justificacion_fondos;
                container_kwc_docs.innerHTML = kwc_docs;
                container_explicacion.innerHTML = explicacion_actividad;
                container_movimientos_banco.innerHTML = movimientos_banco;
                container_registro_mercantil.innerHTML = '';
            }

            if (v_condicion == 115){
                // EMPRESARIO
                justificacion_fondos = '<label for="eeff"><abbr title="Estados Financieros auditados del último año o IR2 del último año">EEF / IR2</abbr></label><input type="file" name="eeff" id="eeff" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="eeff_path" id="eeff_path">';

                kwc_docs = '<label for="f_servicios"><abbr title="Factura de servicios que contengan nombre, direccion, documento de identidad">F. Servicios</abbr></label><input type="file" name="f_servicios" id="f_servicios" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="f_servicios_path" id="f_servicios_path">';

                movimientos_banco = '<label for="movimientos_banco"><abbr title="Movimientos bancarios de los 6 ultimos meses">Mov Bancarios</abbr></label><input type="file" name="movimientos_banco" id="movimientos_banco" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="movimientos_banco_path" id="movimientos_banco_path">';

                registro_mercantil = '<label for="registro_mercantil"><abbr title="Registro Mercantil vigente">Registro Mercantil</abbr></label><input type="file" name="registro_mercantil" id="registro_mercantil" class="formulario_control" style="transition: var(--color-gris-oscuro) .15s easy-in-out; border-color: var(--color-rojo); font-size: 12px;" onchange="focusFunctionFile(this)"><input type="hidden" name="registro_mercantil_path" id="registro_mercantil_path">';

                container_justificacion.innerHTML = justificacion_fondos;
                container_kwc_docs.innerHTML = kwc_docs;
                container_movimientos_banco.innerHTML = movimientos_banco;
                container_registro_mercantil.innerHTML = registro_mercantil;
                container_explicacion.innerHTML = '';
            }
        }

        function pintaCuentas(p_tabla){
            var tabla_cuentas = document.getElementById("tabla_cuentas");
            tabla_cuentas.innerHTML = p_tabla;
            $('#PerfilInversor').modal('hide');
            refresh_page();
        }
        /*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        FUNCIONES DE NEGOCIO
        @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        function guardarDatos(p_origen){
            var btn_guardar = document.getElementById("btn_guardar");
            btn_guardar.disabled = true;

            var usuario_id = document.getElementById("user_id").value;
            let telefono_persona = document.getElementById("telefono_persona").value
            let email_persona = document.getElementById("email_persona").value
            let estado_id = document.getElementById("estado_id").value
            let inversor_id = document.getElementById("inversor_id").value
            let tipo_doc_nom = document.getElementById("tipo_doc_nom").value
            let nro_doc = document.getElementById("nro_doc").value
            let condicion_laboral = document.getElementById("condicion_laboral").value
            let nombre = document.getElementById("nombre").value
            let apellido = document.getElementById("apellido").value
            let accion = 'guarda_datos'
            let kwc = document.getElementById("kwc").value

            var retorno = 1;
            var formaData = new FormData();

            formaData.append('usuario_id', usuario_id)
            formaData.append('telefono_persona', telefono_persona)
            formaData.append('email_persona', email_persona)
            formaData.append('estado_id', estado_id)
            formaData.append('inversor_id', inversor_id)
            formaData.append('tipo_doc_nom', tipo_doc_nom)
            formaData.append('nro_doc', nro_doc)
            formaData.append('condicion_laboral', condicion_laboral)
            formaData.append('nombre', nombre)
            formaData.append('apellido', apellido)
            formaData.append('accion', accion)

            if (estado_id == 6 && condicion_laboral > 0){
                // REGISTRADO
                var inputFileServicios = document.getElementById("f_servicios");
                var fileServicios = inputFileServicios.files[0];

                if (kwc == 0){
                    var inputFileKyc = document.getElementById("formulario_kyc");
                    var fileKyc = inputFileKyc.files[0];
                    formaData.append('formulario_kyc', fileKyc)
                }
                
                formaData.append('f_servicios', fileServicios)

                if (condicion_laboral == 113){
                    // ASALARIADO
                    var inputFileNomina = document.getElementById("ec_nomina");
                    var fileNomina = inputFileNomina.files[0];

                    formaData.append('ec_nomina', fileNomina)
                }

                if (condicion_laboral == 114){
                    // INDEPENDIENTE
                    var inputFileImpuestos = document.getElementById("declaracion_impuestos");
                    var fileImpuestos = inputFileImpuestos.files[0];
                    var inputFileBancos = document.getElementById("movimientos_banco");
                    var fileBancos = inputFileBancos.files[0];
                    let explica_actividad = document.getElementById("explica_actividad").value

                    formaData.append('declaracion_impuestos', fileImpuestos)
                    formaData.append('movimientos_banco', fileBancos)
                    formaData.append('explica_actividad', explica_actividad)
                }

                if (condicion_laboral == 115){
                    // EMPRESARIO
                    var inputFileEeff = document.getElementById("eeff");
                    var fileEeff = inputFileEeff.files[0];
                    var inputFileBancos = document.getElementById("movimientos_banco");
                    var fileBancos = inputFileBancos.files[0];
                    var inputFileRmercantil = document.getElementById("registro_mercantil");
                    var fileRmercantil = inputFileRmercantil.files[0];

                    formaData.append('eeff', fileEeff)
                    formaData.append('movimientos_banco', fileBancos)
                    formaData.append('registro_mercantil', fileRmercantil)
                }
            }

            $.ajax({
                    url: "perfil_inversor_proceso.php",
                    type: "POST",
                    data: formaData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data)
                    {
                        if (p_origen == 'directo') alert('Los datos fueron guardados');
                        btn_guardar.disabled = false;
                    }
                });
            
            return retorno;
        }

        function agregarBroker(){
            var v_usuario_id = $('#user_id').val();
            var v_empresa_id = $('#emp_id').val();
            var v_inversor_id;

            if (v_empresa_id > 0) v_inversor_id = v_empresa_id;
            else v_inversor_id = v_usuario_id;

            $('.modal-title').text('NUEVO BROKER');
            $('.modal-body').load('nuevo_broker_modal.php?inversor_id='+v_inversor_id+'&empresa_id='+v_empresa_id,function(){
                $('#PerfilInversor').modal({show:true});
            });
        }

        function agregarCuenta(){
            var v_inversor_id = $('#inversor_id').val();
            var v_empresa_id = $('#emp_id').val();
            var v_nro_doc = $('#nro_doc').val();

            $('.modal-title').text('NUEVA CUENTA BANCARIA');
            $('.modal-body').load('nueva_cuenta_modal.php?inversor_id='+v_inversor_id+'&empresa_id='+v_empresa_id+'nro_doc='+v_nro_doc,function(){
                $('#PerfilInversor').modal({show:true});
            });
        }

        function eliminarCuenta(p_cuenta_banco_id, p_q_cuentas, p_i){
            if (p_q_cuentas == 1) alert('Lo sentimos debe mantener al menos una cuenta activa, no se puede elimnar la unica cuenta que tiene registrada');
            else{
                var v_moneda_eliminar = $('#moneda_id-'+p_i).val();
                var i, v_moneda;
                var encontrado = 0;

                for (let i=0; i<p_q_cuentas; i++){
                    if (i != p_i){
                        v_moneda = $('#moneda_id-'+i).val();

                        if (v_moneda == v_moneda_eliminar) encontrado = 1;
                    }
                }

                if (encontrado == 1){
                    $.ajax({
                        url:"perfil_inversor_proceso.php",
                        type:'post',
                        data:{
                            "cuenta_id":p_cuenta_banco_id,
                            "accion": 'eliminar_cuenta'
                        },
                        success:function(data,status){
                                    $('#formulario').fadeIn(1000).html(data);
                                    $('#formulario').modal('hide');
                                    refresh_page();
                        }
                    });
                } else alert('Lo sentimos debe mantener al menos una cuenta de cada moneda activa, no se puede elimnar la unica cuenta de esa moneda que tiene registrada');
            }
        }

        function guardaPerfilInversion(){
            var vobj_accion = document.getElementById('accion');
            vobj_accion.value = 'perfil_inversion';
            
            var formData = new FormData(document.getElementById("frm_datos"));
            
            $.ajax({
                url:"perfil_inversor_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html",
                success:function(data,status){
                    $('#formulario').fadeIn(1000).html(data);
                    $('#formulario').modal('hide');
                    refresh_page();
                }
            });
        }

        function anularContrato(){
            if (confirm("Esta seguro de anular el contrato con Factureate? Una vez aceptada la solicitud de anulacion de contrato por nuestros analistas no podra realizar ninguna operacion en la plataforma") == true){
                var v_inversor_id = $('#inversor_id').val();

                $('.modal-title').text('SOLICITUD DE ANULACION DE CONTRATO');
                $('.modal-body').load('solicitud_anulacion_inversor_modal.php?inversor_id='+v_inversor_id,function(){
                    $('#PerfilInversor').modal({show:true});
                });
            }
        }

        function anularSolicitudAnul(){
            var v_inversor_id = $('#inversor_id').val();

            $.ajax({
                url:"solicita_anular_inversor_proceso.php",
                type:'post',
                data:{
                    "inversor_id":v_inversor_id,
                    "accion": 'anular_solicitud'
                },
                success:function(data,status){
                    $('#formulario').fadeIn(1000).html(data);
                    $('#formulario').modal('hide');
                    refresh_page();
                }
            });            
        }

        function refresh_page(){
            location.href = "perfil_inversor.php";
        }

        function grabarInformacion(){
            alert('guardado');

        }

        function enviarInformacion(){
            var v_nro_doc = $('#nro_doc').val();
            var v_email = $('#email_persona').val();
            var v_telefono = $('#telefono_persona').val();
            var v_condicion = $('#condicion_laboral').val();
            var v_kwc = $('#kwc').val();
            var v_q_cuentas_banco = $('#q_cuentas_banco').val();

            var procede = 1;

            if (v_email == "" || v_telefono == "" || v_nro_doc == ""){
                procede = 0;
                alert('Debe completar la informacion de Nro de documento de identidad, email y telefono');
                $('#email_persona').focus();
            }

            if (procede == 1 && v_condicion == 0){
                procede = 0;
                alert('Debe seleccionar una condicion laboral');
                $('#condicion_laboral').focus();
            }

            if (procede == 1){
                if (v_condicion == 113){
                    var v_nomina = $('#ec_nomina').val();
                    var v_nomina_path = $('#ec_nomina_path').val();
                    var v_servicios = $('#f_servicios').val();
                    var v_servicios_path = $('#f_servicios_path').val();

                    if ((v_nomina == "" && v_nomina_path == "") || (v_servicios == "" && v_servicios_path == "")){
                        procede = 0;
                        alert('Debe adjuntar los comprobantes de nomina y comprobantes de servicios de su domicilio');
                        $('#ec_nomina').focus();
                    }
                }

                if (v_condicion == 114){
                    var v_impuestos = $('#declaracion_impuestos').val();
                    var v_servicios = $('#f_servicios').val();
                    var v_actividad = $('#explica_actividad').val();
                    var v_bancos = $('#movimientos_banco').val();
                    var v_impuestos_path = $('#declaracion_impuestos_path').val();
                    var v_servicios_path = $('#f_servicios_path').val();
                    var v_bancos_path = $('#movimientos_banco_path').val();

                    if ((v_impuestos == "" && v_impuestos_path == "") || (v_servicios == "" && v_servicios_path == "")){
                        procede = 0;
                        alert('Debe adjuntar la ultima declaracion de impuestos y comprobante de servicios de su domicilio');
                        $('#declaracion_impuestos').focus();
                    }

                    if (procede == 1 && v_actividad == ""){
                        procede = 0;
                        alert('Debe registrar una explicacion de su actividad economica');
                        $('#explica_actividad').focus();
                    }

                    if (procede == 1 && v_bancos == "" && v_bancos_path == ""){
                        procede = 0;
                        alert('Debe adjuntar los movimientos bancarios requeridos');
                        $('#movimientos_banco').focus();
                    }
                }

                if (v_condicion == 115){
                    var v_eeff = $('#eeff').val();
                    var v_servicios = $('#f_servicios').val();
                    var v_bancos = $('#movimientos_banco').val();
                    var v_mercantil = $('#registro_mercantil').val();

                    var v_eeff_path = $('#eeff_path').val();
                    var v_servicios_path = $('#f_servicios_path').val();
                    var v_bancos_path = $('#movimientos_banco_path').val();
                    var v_mercantil_path = $('#registro_mercantil_path').val();

                    if ((v_eeff == "" && v_eeff_path == "") || (v_servicios == "" && v_servicios_path == "")){
                        procede = 0;
                        alert('Debe adjuntar los estados financieros de su empresa y comprobante de servicios de su domicilio');
                        $('#eeff').focus();
                    }

                    if (procede == 1 && v_bancos == "" && v_bancos_path == ""){
                        procede = 0;
                        alert('Debe adjuntar los movimientos de la cuenta bancaria de su empresa');
                        $('#movimientos_banco').focus();
                    }

                    if (procede == 1 && v_mercantil == "" && v_mercantil_path == ""){
                        procede = 0;
                        alert('Debe adjuntar el registro mercantil vigente de su empresa');
                        $('#registro_mercantil').focus();
                    }
                }
            }

            if (procede == 1 && v_kwc == 0){
                var v_formulario_kyc = $('#formulario_kyc').val();

                if (v_formulario_kyc == ""){
                    procede = 0;
                    alert('Debe completar el formulario KWC (conozca a su cliente)');
                }
            }

            if (procede == 1 && v_q_cuentas_banco == 0){
                procede = 0;
                alert('Debe registrar por lo menos 1 cuenta de banco, donde recibira sus ganancias');
            }

            if (procede == 1){
                var result_save = guardarDatos('envio');

                let inversor_id = document.getElementById("inversor_id").value
                let nombre = document.getElementById("nombre").value
                let apellido = document.getElementById("apellido").value
                var v_accion = 'enviar';
                var formData = new FormData();

                var btn_enviar = document.getElementById("btn_enviar");

                formData.append('accion', v_accion)
                formData.append('inversor_id', inversor_id)
                formData.append('nombre', nombre)
                formData.append('apellido', apellido)

                $.ajax({
                    url:"perfil_inversor_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(data){
                        //alert(data);
                        btn_enviar.disabled = true;
                        alert('Su información fue enviada, nuestro departamento legal le enviará una notificación por correo luego de la revisión');
                        refresh_page();
                    }
                });
            }
        }
        
    </script>
    <!--###################################################-->
</BODY>
</HTML>