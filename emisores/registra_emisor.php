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

$arr_empresa = $obj_mae->get_datos_emisor_full($_SESSION['user']['empresaid']);

if ($_SESSION['user']['perfilid'] == 4) $v_perfil = 'ADMIN';
elseif ($_SESSION['user']['perfilid'] == 5) $v_perfil = 'USER';

if ($v_perfil == 'USER'){
    $readonly = 'readonly'; $disabled = 'disabled'; $readonly_total = 'readonly'; $disabled_total = 'disabled';
} elseif ($v_perfil == 'ADMIN') {
    if ($arr_empresa['estado'] == 1){
        $readonly = ''; $disabled = ''; $readonly_total = ''; $disabled_total = '';
    } elseif ($arr_empresa['estado'] == 2) {
        $readonly = 'readonly'; $disabled = 'disabled'; $readonly_total = 'readonly'; $disabled_total = 'disabled';
    } elseif ($arr_empresa['estado'] == 3) {
        $readonly = 'readonly'; $disabled = 'disabled'; $readonly_total = ''; $disabled_total = '';
    } else{
        $readonly = 'readonly'; $disabled = 'disabled'; $readonly_total = 'readonly'; $disabled_total = 'disabled';
    }
}

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'emisores/registra_emisor.php';
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
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="accion">
    <input type="hidden" name="estado_id" id="estado_id" value="<?=$arr_empresa['estado']?>">
    <input type="hidden" name="cambios_repre" id="cambios_repre" value="0">
    <input type="hidden" name="cambios_contacto" id="cambios_contacto" value="0">
    <input type="hidden" name="empresa_id" id="empresa_id" value="<?=$_SESSION['user']['empresaid']?>">

    <div class="contenedor_principal" id="contenedor_principal">
        <div class="contenedor_formulario">
<?php

    if (isset($_GET['estado'])){
        if ($_GET['estado'] == 2)
            echo '
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                Su información esta siendo evaluada por nuestros analistas, en breve recibirá un correo de confirmación con el link del contrato.
            </div>';
    }
?>
            <div style="font-weight: bold;color:var(--color-rojo);font-size: 10px;margin-top: 10px;width:100%;float:left;"> 
                [*] Información Obligatoria
            </div>

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                INFORMACION DE LA EMPRESA
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_empresa">NOMBRE EMPRESA <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" class="formulario_control" value="<?=$arr_empresa['nombre']?>" <?php echo $readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="ruc">RNC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="ruc" id="ruc" class="formulario_control" value="<?=$arr_empresa['identificacion']?>" <?php echo $readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="ruc">ESTADO</label>
                    <input type="text" name="estado_nombre" id="estado_nombre" class="formulario_control" value="<?=$arr_empresa['estado_nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tamanoid">TAMAÑO EMPRESA <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="hidden" name="tamanoid_old" id="tamanoid_old" value="<?=$arr_empresa['tamanoid']?>">
                    <select name="tamanoid" id="tamanoid" class="formulario_control" <?php echo $disabled_total;?>>
<?php
    $arr_ttamano = $obj_mae->get_tipos('TAMANHOEMP');

    for ($i=0; $i<count($arr_ttamano); $i++){
        if ($arr_ttamano[$i]['id'] == $arr_empresa['tamanoid']) 
            echo '      <option value="'.$arr_ttamano[$i]['id'].'" selected>'.$arr_ttamano[$i]['nombre'].'</option>';
        else echo '     <option value="'.$arr_ttamano[$i]['id'].'">'.$arr_ttamano[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="direccion">DIRECCION <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control" value="<?=$arr_empresa['direccion']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="direccion_old" id="direccion_old" value="<?=$arr_empresa['direccion']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="sectorid">ACTIVIDAD ECONOMICA <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="hidden" name="sectorid_old" id="sectorid_old" value="<?=$arr_empresa['sectorid']?>">
                    <select name="sectorid" id="sectorid" class="formulario_control" <?php echo $disabled_total;?>>
<?php
    $arr_sector = $obj_mae->get_tipos('SECTORECO');

    for ($i=0; $i<count($arr_sector); $i++){
        if ($arr_sector[$i]['id'] == $arr_empresa['sectorid']) 
            echo '      <option value="'.$arr_sector[$i]['id'].'" selected>'.$arr_sector[$i]['nombre'].'</option>';
        else echo '     <option value="'.$arr_sector[$i]['id'].'">'.$arr_sector[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="actividad">DESCRIPCION ACTIVIDAD <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="hidden" name="actividad_old" id="actividad_old" value="<?=$arr_empresa['actividad']?>">
                    <textarea id="actividad" name="actividad" cols="100" rows="5" <?php echo $readonly_total;?>><?php echo $arr_empresa['actividad'];?></textarea>
                </div>
            </div>

            <hr>

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                REPRESENTANTE LEGAL
            </div>    

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nombre_repre">NOMBRE <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nombre_repre" id="nombre_repre" class="formulario_control" value="<?=$arr_empresa['nombre_repre']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="nombre_repre_old" id="nombre_repre_old" value="<?=$arr_empresa['nombre_repre']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_repre">E-MAIL <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="email_repre" id="email_repre" class="formulario_control" value="<?=$arr_empresa['email_repre']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="email_repre_old" id="email_repre_old" value="<?=$arr_empresa['email_repre']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_repre">TIPO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="hidden" name="tdoc_repre_old" id="tdoc_repre_old" value="<?=$arr_empresa['tdoc_repre']?>">
                    <select name="tdoc_repre" id="tdoc_repre" class="formulario_control" <?php echo $disabled_total;?>>
<?php
    $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($arr_tdoc); $i++){
        if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_repre']) 
            echo '      <option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
        else echo '     <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="nrodoc_repre">NRO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nrodoc_repre" id="nrodoc_repre" class="formulario_control" value="<?=$arr_empresa['nrodoc_repre']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="nrodoc_repre_old" id="nrodoc_repre_old" value="<?=$arr_empresa['nrodoc_repre']?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="file_dni">DOCUMENTO <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="file" name="file_dni" id="file_dni" class="formulario_control" <?php echo $disabled_total;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 70px;">
                    <label for="documento_view">PDF</label>
<?php
    if ($arr_empresa['docrepre_path'] != '' && !is_null($arr_empresa['docrepre_path']))
        echo '
                    <label><a href="'.$arr_empresa['docrepre_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                    <input type="hidden" name="docrepre_path" id="docrepre_path" value="'.$arr_empresa['docrepre_path'].'">';
    else echo '
                    <label><i class="fa-solid fa-file-circle-xmark" style="font-size:18px;"></i></label>
                    <input type="hidden" name="docrepre_path" id="docrepre_path" value="">';
?>
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="file_poderes">REGISTRO MERCANTIL <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="file" name="file_poderes" id="file_poderes" class="formulario_control" <?php echo $disabled_total;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 70px;">
                    <label for="poderes_view">PDF</label>
<?php
    if ($arr_empresa['vigencia_path'] != '' && !is_null($arr_empresa['vigencia_path']))
        echo '
                    <label><a href="'.$arr_empresa['vigencia_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                    <input type="hidden" name="poderes_path" id="poderes_path" value="'.$arr_empresa['vigencia_path'].'">';
    else echo '
                    <label><i class="fa-solid fa-file-circle-xmark" style="font-size:18px;"></i></label>
                    <input type="hidden" name="poderes_path" id="poderes_path" value="">';
?>
                </div>
            </div>

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> 
                CONTACTO
            </div>    

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_contacto">NOMBRE <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nombre_contacto" id="nombre_contacto" value="<?=$arr_empresa['nombre_contacto']?>" class="formulario_control" <?php echo $readonly_total;?>>
                    <input type="hidden" name="nombre_contacto_old" id="nombre_contacto_old" value="<?=$arr_empresa['nombre_contacto']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_contacto">E-MAIL <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="email_contacto" id="email_contacto" class="formulario_control" value="<?=$arr_empresa['email_contacto']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="email_contacto_old" id="email_contacto_old" value="<?=$arr_empresa['email_contacto']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_contacto">TIPO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="hidden" name="tdoc_contacto_old" id="tdoc_contacto_old" value="<?=$arr_empresa['tdoc_contacto']?>">
                    <select name="tdoc_contacto" id="tdoc_contacto" class="formulario_control" <?php echo $disabled_total;?>>
<?php
    $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($arr_tdoc); $i++){
        if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_contacto']) 
            echo '      <option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
        else echo '     <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrodoc_contacto">NRO DOC <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="nrodoc_contacto" id="nrodoc_contacto" class="formulario_control" value="<?=$arr_empresa['nrodoc_contacto']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="nrodoc_contacto_old" id="nrodoc_contacto_old" value="<?=$arr_empresa['nrodoc_contacto']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono_contacto">TELEFONO <b style="color:var(--color-rojo);">[*]</b></label>
                    <input type="text" name="telefono_contacto" id="telefono_contacto" class="formulario_control" value="<?=$arr_empresa['telf_contacto']?>" <?php echo $readonly_total;?>>
                    <input type="hidden" name="telefono_contacto_old" id="telefono_contacto_old" value="<?=$arr_empresa['telf_contacto']?>">
                </div>
            </div>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                @@@@@@@@@@@@@@ CUENTAS BANCARIAS -->
            <div style="margin-top: 20px;width:100%;float:left;"> 
                <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;width:300px;float:left;">CUENTAS DE BANCO</div>
                <div style="float:right;margin-right: 30px;">
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="agregarCuenta()">
                        <i class="fa-solid fa-piggy-bank"></i> Agregar Cuenta de Banco
                    </button>
                </div>
            </div>

            <div style="margin-top: 10px;width:100%;float:left;">
                <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">BANCO</th>
                        <th scope="col">TIPO CUENTA</th>
                        <th scope="col">MONEDA</th>
                        <th scope="col">NRO CUENTA</th>
                        <th scope="col">ELIMINAR</th>
                        <th scope="col">MODIFICAR</th>
                    </tr></thead>
                    <tbody id="content-cuentas-banco">

                    </tbody>
                    <input type="hidden" name="q_cuentas" id="q_cuentas" value="0">
                </table>
            </div>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                @@@@@@@@@@@@@@ USUARIOS DEL EMISOR -->
<?php
if ($_SESSION['user']['perfilid'] == 4){
// EMISOR ADMINISTRADOR
    echo '  <div style="margin-top: 20px;width:100%;float:left;"> 
                <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;width:300px;float:left;">USUARIOS DEL EMISOR</div>
                <div style="float:right;margin-right: 30px;">
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="agregarUsuario()">
                        <i class="fa-solid fa-piggy-bank"></i> Agregar Usuario
                    </button>
                </div>
            </div>

            <div style="margin-top: 10px;width:100%;float:left;">
                <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">ID</th>
                        <th scope="col">NOMBRE</th>
                        <th scope="col">E-MAIL</th>
                        <th scope="col">TELEFONO</th>
                        <th scope="col">TIPO DOC</th>
                        <th scope="col">NRO DOC</th>
                        <th scope="col">F APERTURA</th>
                        <th scope="col">ANULAR</th>
                    </tr></thead>
                    <tbody>';

    $varr_usuarios_empresa = $obj_mae->get_usuarios_xempresa($_SESSION['user']['empresaid']);

    for ($i=0; $i<count($varr_usuarios_empresa); $i++){
        if (is_null($varr_usuarios_empresa[$i]['fapertura'])) $v_fapertura = '-';
        else $v_fapertura = date('d-m-Y', strtotime($varr_usuarios_empresa[$i]['fapertura']));

        $v_btn_usuario = "'".$varr_usuarios_empresa[$i]['usuario_id']."'";

        echo '          <tr>
                            <td data-label="ID">'.$varr_usuarios_empresa[$i]['usuario_id'].'</td>
                            <td data-label="NOMBRE">'.$varr_usuarios_empresa[$i]['nombre'].'</td>
                            <td data-label="E-MAIL">'.$varr_usuarios_empresa[$i]['email'].'</td>
                            <td data-label="TELEFONO">'.$varr_usuarios_empresa[$i]['telefono'].'</td>
                            <td data-label="TIPO DOC">'.$varr_usuarios_empresa[$i]['tipodoc_nombre'].'</td>
                            <td data-label="NRO DOC">'.$varr_usuarios_empresa[$i]['nro_doc'].'</td>
                            <td data-label="F APERTURA">'.$v_fapertura.'</td>';

        if ($varr_usuarios_empresa[$i]['perfil_id'] == 4)
            echo '          <td data-label="ANULAR">--</td>';
        else echo '         <td data-label="ANULAR"><button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="anularUsuario('.$v_btn_usuario.')"><i class="fa-solid fa-circle-xmark"></i> Anular</button></td>';
                            
        echo '          </tr>';
    }

    echo '          </tbody>
                </table>
            </div>';
}
?>
            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                @@@@@@@@@@@@@@ TARIFAS -->

<?php
    $varr_monedas = $obj_mae->get_tipos('MONEDA');
    $varr_comisiones = $obj_mae->get_intervalo_comision_emisor();

    $v_comision1 = $varr_comisiones[0]['comision'] * 100;
    $v_comision2 = $varr_comisiones[1]['comision'] * 100;
    $v_texto_comision = 'La comision FACTUEATE varia dependiendo del nivel de riesgo de la factura entre el '.$v_comision1.'% y '.$v_comision2.'% del monto de adelanto';

    $varr_parametro = $obj_mae->get_parametro_detalle(25);
    $v_tarifa_nacional = number_format($varr_parametro['valornum'],2,'.',',');

    $varr_parametro = $obj_mae->get_parametro_detalle(26);
    $v_tarifa_ext = number_format($varr_parametro['valornum'],2,'.',',');

    for ($i=0; $i<count($varr_monedas); $i++){
        if ($varr_monedas[$i]['id'] == 20) $v_simbolo_nacional = $varr_monedas[$i]['dato1'];
        if ($varr_monedas[$i]['id'] == 21) $v_simbolo_extranjero = $varr_monedas[$i]['dato1'];
    }
?>

            <div style="margin-top: 20px;width:100%;float:left;font-weight: bold;color:var(--color-azulv2);font-size: 14px;width:300px;float:left;">TARIFAS FACTUREATE</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 200px;">
                    <label for="tarifa_nacional">TARIFA REGISTRO <?php echo $v_simbolo_nacional;?></label>
                    <input type="text" name="tarifa_nacional" id="tarifa_nacional" value="<?=$v_tarifa_nacional?>" class="formulario_control" style="text-align: right;" readonly>
                </div>
                <div class="formulario_grupo_column" style="width: 200px;">
                    <label for="tarifa_ext">TARIFA REGISTRO <?php echo $v_simbolo_extranjero;?></label>
                    <input type="text" name="tarifa_ext" id="tarifa_ext" value="<?=$v_tarifa_ext?>" class="formulario_control" style="text-align: right;" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 800px;">
                    <label for="comision">COMISION FINANCIAMIENTO</label>
                    <input type="text" name="comision" id="comision" value="<?=$v_texto_comision?>" class="formulario_control" style="text-align: right;" readonly>
                </div>
            </div>

            <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                @@@@@@@@@@@@ BOTONERA -->

            <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:5px;"></div>
<?php
    echo '  <div style="width:100%; float:left;margin-bottom:5px;">';

    if ($v_perfil == 'ADMIN'){
        if ($arr_empresa['estado'] == 1){   //REGISTRADA
            $v_grabar = 1;

            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_grabar" onclick="grabar('.$v_grabar.')">
                    <i class="fa-solid fa-floppy-disk"></i> Grabar
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_enviar" onclick="enviar()">
                    <i class="fa-solid fa-paper-plane"></i> Enviar
                </button>';
        } elseif ($arr_empresa['estado'] == 3) {
            $v_grabar = 2;

            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="modificar" onclick="grabar('.$v_grabar.')">
                    <i class="fa-solid fa-pen"></i> Modificar
                </button>';
        }
        echo '';
    }

    echo '  </div>';
?>    

        </div>  <!-- contenedor formulario -->
    </div>  <!-- contenedor principal -->
    </form>
    <!------ END CUERPO VARIABLE ------>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@ ZONA MODAL -->
    <div class="modal fade" id="EmisorModal" tabindex="-1" role="dialog">
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
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
                </div>
            </div>
        </div>
    </div>

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@ ZONA JS -->
    <!-- ==== CARGA DE DATOS DE LAS TABLAS ==== -->
    <script type="text/javascript">
        // llamada a las funciones cuando se carga por primera vez la pagina
        document.addEventListener("DOMContentLoaded", getCuentasBanco);

        // funcion para obtener las cuentas de banco con AJAX
        function getCuentasBanco(){
            let empresa_id = document.getElementById("empresa_id").value
            let content_cuentas_banco = document.getElementById("content-cuentas-banco")

            let formaData = new FormData()

            formaData.append('empresa_id', empresa_id)

            fetch("cuentas_banco_empresas_load.php", {
                    method: "POST",
                    body: formaData
            })
            .then(response => response.json())
            .then(data => {
                content_cuentas_banco.innerHTML = data.data
                document.getElementById("q_cuentas").value = data.totalDatos;
            })
            .catch(err => console.log(err))
            //cierra_modal();
        }

        function cierra_modal(){
            //$('#EmisorModal').modal('hide');
            //$('#EmisorModal').modal({show:false});
            refresh_page();
        }
    
        $('.pdfdni').click(function(e) {
            e.preventDefault();
            var path = $(this).attr('path');
            
            $('.modalclase').fadeIn();
            $('#iframepdf').attr('src',path)
        });
        $('.pdfpod').click(function(e) {
            e.preventDefault();
            var pathpod = $(this).attr('pathpod');
            
            $('.modalclase').fadeIn();
            $('#iframepdf').attr('src',pathpod)
        });

        function grabar(p_tipo){
            var btn_grabar;
            
            if (p_tipo == 1){
                btn_grabar = document.getElementById('boton_grabar');
                btn_grabar.disabled = true;
            } else{
                btn_grabar = document.getElementById('modificar');
                btn_grabar.disabled = true;
            }

            document.frm.accion.value = 'grabar';
            var v_nombre_repre = $('#nombre_repre').val();
            var v_email_repre = $('#email_repre').val();
            var v_tdoc_repre = $('#tdoc_repre').val();
            var v_nrodoc_repre = $('#nrodoc_repre').val();
            var v_file_dni = $('#file_dni').val();
            var v_file_poderes = $('#file_poderes').val();

            var v_nombre_repre_old = $('#nombre_repre_old').val();
            var v_email_repre_old = $('#email_repre_old').val();
            var v_tdoc_repre_old = $('#tdoc_repre_old').val();
            var v_nrodoc_repre_old = $('#nrodoc_repre_old').val();

            var v_nombre_contacto = $('#nombre_contacto').val();
            var v_email_contacto = $('#email_contacto').val();
            var v_tdoc_contacto = $('#tdoc_contacto').val();
            var v_nrodoc_contacto = $('#nrodoc_contacto').val();
            var v_telefono_contacto = $('#telefono_contacto').val();

            var v_nombre_contacto_old = $('#nombre_contacto_old').val();
            var v_email_contacto_old = $('#email_contacto_old').val();
            var v_tdoc_contacto_old = $('#tdoc_contacto_old').val();
            var v_nrodoc_contacto_old = $('#nrodoc_contacto_old').val();
            var v_telefono_contacto_old = $('#telefono_contacto_old').val();

            var v_cambios_repre = 0;
            var v_cambios_contacto = 0;
            var v_procede = 1;

            if (v_nombre_repre != v_nombre_repre_old || v_email_repre != v_email_repre_old || v_tdoc_repre != v_tdoc_repre_old || v_nrodoc_repre != v_nrodoc_repre_old){
                if (v_file_dni != '' || v_file_poderes != '')
                    if (confirm('Esta realizando cambios en los datos del representante, si continua los cambios seran evaluados por nuestro departamento Legal, esta seguro de continuar?') == false)
                        v_procede = 0;
                    else document.frm.cambios_repre.value = 1;
            } 

            if (v_procede == 1){
                if (v_nombre_contacto != v_nombre_contacto_old || v_email_contacto != v_email_contacto_old || v_tdoc_contacto != v_tdoc_contacto_old || v_nrodoc_contacto != v_nrodoc_contacto_old || v_telefono_contacto != v_telefono_contacto_old)
                    if (confirm('Esta realizando cambios en los datos de contacto, esta seguro de continuar?') == false) v_procede = 0;
                    else document.frm.cambios_contacto.value = 1;
            }

            if (v_procede == 1){
                var formData = new FormData(document.getElementById("frm"));
                    
                $.ajax({
                            url:"registra_emisor_proceso.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html",
                            success:function(data,status){
                                $('#contenedor_principal').fadeIn(1000).html(data);
                                $('#contenedor_principal').modal('hide');
                                refresh_page();
                            }
                });
            }
        }

        function enviar(){
            var btn_grabar = document.getElementById('boton_grabar');
            btn_grabar.disabled = true;
            var btn_enviar = document.getElementById('boton_enviar');
            btn_enviar.disabled = true;
            
            document.frm.accion.value = 'enviar';

            var v_nombre_empresa = $('#nombre_empresa').val();
            var v_ruc = $('#ruc').val();
            var v_tamano_id = $('#tamanoid').val();
            var v_direccion = $('#direccion').val();
            var v_sector_id = $('#sectorid').val();
            var v_actividad = $('#actividad').val();

            var v_nombre_repre = $('#nombre_repre').val();
            var v_email_repre = $('#email_repre').val();
            var v_tdoc_repre = $('#tdoc_repre').val();
            var v_nrodoc_repre = $('#nrodoc_repre').val();
            var v_file_dni = $('#docrepre_path').val();
            var v_file_poderes = $('#poderes_path').val();

            var v_nombre_contacto = $('#nombre_contacto').val();
            var v_email_contacto = $('#email_contacto').val();
            var v_tdoc_contacto = $('#tdoc_contacto').val();
            var v_nrodoc_contacto = $('#nrodoc_contacto').val();
            var v_telefono_contacto = $('#telefono_contacto').val();

            var v_q_cuentas = $('#q_cuentas').val();

            var v_cambios_repre = 0;
            var v_cambios_contacto = 0;
            var v_procede = 1;

            if (v_nombre_empresa == '' || v_ruc == '' || v_tamano_id == 0 || v_direccion == '' || v_sector_id == 0 || v_actividad == ''){
                alert("Debe completar los datos de la empresa");
                btn_grabar.disabled = false;
                btn_enviar.disabled = false;
            } else{
                if (v_nombre_repre == '' || v_email_repre == '' || v_tdoc_repre == 0 || v_nrodoc_repre == ''){
                    alert("Debe completar los datos del representante legal");
                    btn_grabar.disabled = false;
                    btn_enviar.disabled = false;
                } else{
                    if (v_file_dni == '' || v_file_poderes == ''){
                        alert("Debe cargar los documentos del representante legal");
                        btn_grabar.disabled = false;
                        btn_enviar.disabled = false;
                    } else{
                        if (v_nombre_contacto == '' || v_email_contacto == '' || v_tdoc_contacto == 0 || v_nrodoc_contacto == '' || v_telefono_contacto == ''){
                            alert("Debe ingresar los datos de contacto");
                            btn_grabar.disabled = false;
                            btn_enviar.disabled = false;
                        } else{
                            if (v_q_cuentas == 0){
                                alert("Debe ingresar al menos una cuenta bancaria donde recibira el financiamiento, la cuenta debe estar en la misma moneda de las facturas que venderá");
                                btn_grabar.disabled = false;
                                btn_enviar.disabled = false;
                            } else {
                                var formData = new FormData(document.getElementById("frm"));
                                
                                $.ajax({
                                    url:"registra_emisor_proceso.php",
                                    type:'post',
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    dataType: "html"
                                })
                                .done(function(rpta){
                                    alert('La informacion fue enviada, en breve recibira la confirmacion que su empresa esta habilitada para financiarse mediante sus facturas');
                                });
                            }
                        }
                    }
                }
            }

        }

        function agregarUsuario(){
            var v_empresa_id = $('#empresa_id').val();

            $('.modal-title').text('AGREGAR USUARIO');
            $('.modal-body').load('agregar_usuario_emisor_modal.php?emisor_id='+v_empresa_id,function(){
                $('#EmisorModal').modal({show:true});
            });
        }

        function agregarCuenta(){
            var v_empresa_id = $('#empresa_id').val();

            $('.modal-title').text('AGREGAR CUENTA BANCARIA');
            $('.modal-body').load('cuenta_banco_emisor_modal.php?emisor_id='+v_empresa_id+'&moneda_id=0',function(){
                $('#EmisorModal').modal({show:true});
            });
        }

        function modificarCuenta(p_moneda_id, p_id){
            var v_empresa_id = $('#empresa_id').val();

            $('.modal-title').text('MODIFICAR CUENTA BANCARIA');
            $('.modal-body').load('cuenta_banco_emisor_modal.php?emisor_id='+v_empresa_id+'&moneda_id='+p_moneda_id+'&id='+p_id,function(){
                $('#EmisorModal').modal({show:true});
            });
        }

        function eliminarCuenta(p_moneda_id, p_id){
            var v_empresa_id = $('#empresa_id').val();
            var v_q_cuentas = $('#q_cuentas').val();

            if (v_q_cuentas > 1){
                if (confirm('Considere que si al momento de vender una factura no se encuentran cuentas de banco registrada de la moneda de la factura no se podra realizar el deposito, esta seguro de continuar?') == true){
                    $.ajax({
                        url:"cuenta_banco_emisor_proceso.php",
                        type:'post',
                        data:{
                            "moneda_id":p_moneda_id,
                            "emisor_id":v_empresa_id,
                            "id":p_id,
                            "accion":'eliminar'
                        }/*,
                                        success:function(data,status){
                                            $('#EmisorModal').fadeIn(1000).html(data);
                                            $('#EmisorModal').modal('hide');
                                            refresh_page();
                                        }*/
                    })
                    .done(function(rpta){
                        alert('Cuenta eliminada');
                        refresh_page();
                    });
                }
            } else alert('No puede eliminar la unica cuenta de banco que tiene registrada, debe contar con al menos una cuenta de banco registrada');
        }

        function refresh_page(){
            location.href = 'registra_emisor.php'
        }
    </script>
</BODY>
</HTML>