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
?>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
<?php
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
            else if (accion == 'aprobar'){
                var direccion = document.frm.direccion.value;
                var actividad = document.frm.actividad.value;
                var t_empresaid = document.frm.t_empresaid.value;

                if (direccion == '' || actividad == ''){
                    alert('Debe completar la informacion de la empresa');
                } else{
                    if (t_empresaid == 48 || t_empresaid == 50 || t_empresaid == 51 || t_empresaid == 52){  // algun tipo relacionado a OP
                        var emp_score = document.frm.emp_riesgoid.value;
                        var nivel_score = document.frm.nivel_score_riesgoid.value;
                        var justi_nivel_score = document.frm.score_riesgo_justi.value;
                        var nivel_factu = document.frm.nivel_factureate_riesgoid.value;
                        var justi_nivel_factu = document.frm.factureate_riesgo_justi.value;
                        var path_score = document.frm.path_score.value;
                        var path_score_file = document.frm.informe_score_file.value;
                        var path_factu = document.frm.path_factu.value;
                        var path_factu_file = document.frm.informe_factu_file.value;
                        
                        if (emp_score == 0 || nivel_score == 0 || nivel_factu == 0) alert('Debe completar los niveles de riesgo');
                        else if (justi_nivel_score == '' || justi_nivel_factu == '') alert('Debe completar las justificaciones de riesgos');
                        else if ((path_score == '' && path_score_file == '') || (path_factu == '' && path_factu_file == '')) alert('Debe registrar los informes de riesgos');
                        else enviar = 1;
                    } else enviar = 1;
                }
            } 

            if (enviar == 1) document.frm.submit();
        }
        function valida_informe(tipo){
            document.frm.action = 'update_riesgo_proceso.php';
            document.frm.accion.value = tipo;

            if (tipo == 'score'){
                var archivo = document.getElementById('informe_score_file');
                var buro = document.getElementById('emp_riesgoid');
                var nivel_buro = document.getElementById('nivel_score_riesgoid');
                var msg_buro = document.getElementById('score_riesgo_justi');
                
                if (archivo.value == ""){
                    alert("Debe cargar el informe del Score de Credito");
                } else{
                    if (buro.value == "0") alert ("Debe seleccionar un Buro de credito");
                    else {
                        if (nivel_buro.value == "0") alert("Debe seleccionar un nivel de riesgo del Buro");
                        else {
                            if (msg_buro.value == "") alert("Debe ingresar un texto de justificacion del nivel de riesgo asignado");
                            else document.frm.submit();
                        }
                    }
                }
            }
        }

        function guarda_accionistas(parametro){
            document.frm_accionistas.action = 'empresa_accionistas_procesar.php';
            document.frm_accionistas.submit();
        }

        function closemodal(){
            $('.modalclase').fadeOut();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$vobj_seg = new seguridad;

$arr_empresa = $obj_mae->get_datos_empresa($_GET['id']);
$varr_param_contratoemi = $obj_mae->get_parametro_detalle(27);

if ($_GET['previo'] == 'empresas') $previo = 'empresas.php';
elseif ($_GET['previo'] == 'emisores') $previo = 'emisores.php';
elseif ($_GET['previo'] == 'obligpago') $previo = 'obligados_pago.php';
elseif ($_GET['previo'] == 'factura') $previo = '../facturas/factura_detalle.php?id='.$_GET['facturaid'];
elseif ($_GET['previo'] == 'empresas_xriesgo') $previo = 'empresas_xriesgo.php';

if ($arr_empresa['t_empresaid'] == 46 || $arr_empresa['t_empresaid'] == 49 || $arr_empresa['t_empresaid'] == 50 || $arr_empresa['t_empresaid'] == 52){
    $readonly = 'readonly';
    $t_empresa = 'emisor';
    $readonly_op = '';
} elseif ($arr_empresa['t_empresaid'] == 47 || $arr_empresa['t_empresaid'] == 49 || $arr_empresa['t_empresaid'] == 51 || $arr_empresa['t_empresaid'] == 52){
    $t_empresa = 'inversionista';
    $readonly = 'readonly';
    $readonly_op = '';
} else{
    $t_empresa = 'obligpago';
    $readonly = '';
    $readonly_op = 'readonly';
}
/*--------------------------------------------------------*/

if ($arr_empresa['estado_legal'] == -1) $v_muestra_estado_legal = '<i class="fa-solid fa-face-angry" style="font-size:16px;color:var(--color-rojo);"></i> Rechazado Legalmente';
else $v_muestra_estado_legal = '';
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    if ($_GET['previo'] == 'empresas_xriesgo') $menu = 'empresas/empresas_xriesgo.php';
    else $menu = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--======================= BODY -->
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="accion" id="accion">
    <input type="hidden" name="previo" id="previo" value="<?=$previo?>">
    <input type="hidden" name="empresaid" id="empresaid" value="<?=$_GET['id']?>">
    <input type="hidden" name="u_envioid" value="<?=$arr_empresa['u_envio_id']?>">
    <input type="hidden" name="t_empresaid" value="<?=$arr_empresa['t_empresaid']?>">
    <input type="hidden" name="estado_id" id="estado_id" value="<?=$arr_empresa['e_empresa_id']?>">
    <!--=== TITULO -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);padding: 5px;">
        Ficha de Empresa <?echo $v_muestra_estado_legal;?>
    </div>
    <!--=== INFORMACION -->
<?php
    $varr_permisos = $vobj_seg->get_permisos($_SESSION['user']['perfilid']);

    if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-UPD-OP')) $v_readonly = ''; else $v_readonly = 'readonly';

?>
    <div class="contenedor_principal" id="contenedor_principal">
        <div class="contenedor_formulario">
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;"> INFORMACION DE LA EMPRESA</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="ruc">ID EMPRESA</label>
                    <input type="text" name="ruc" id="ruc" class="formulario_control" value="<?=$arr_empresa['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_empresa">NOMBRE EMPRESA</label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" class="formulario_control" value="<?=$arr_empresa['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="estado_empresa">ESTADO</label>
                    <input type="text" name="estado_empresa" id="estado_empresa" class="formulario_control" value="<?=$arr_empresa['e_empresa']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="tipo_empresa">TIPO</label>
                    <input type="text" name="tipo_empresa" id="tipo_empresa" class="formulario_control" value="<?=$arr_empresa['t_empresa']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 350px;">
                    <label for="direccion">DIRECCION</label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control" value="<?=$arr_empresa['direccion']?>" <?echo $v_readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tamano">TAMAÑO DE EMPRESA</label>
<?php

    if ($v_readonly == 'readonly'){
        echo '
                    <input type="text" name="tamano" id="tamano" class="formulario_control" value="'.$arr_empresa['tamano'].'" readonly>
                    <input type="hidden" name="tamanoid" id="tamanoid" value="'.$arr_empresa['tamanoid'].'">';
    } else{
        $arr_ttamano = $obj_mae->get_tipos('TAMANHOEMP');

        echo '
                    <select class="formulario_control" name="tamanoid" id="tamanoid">';
                                        
        if ($arr_empresa['tamanoid'] == 0) 
            echo '      <option value="0" selected>Elija una opcion</option>';

        for ($i=0; $i<count($arr_ttamano); $i++){
            if ($arr_ttamano[$i]['id'] == $arr_empresa['tamanoid']) 
                echo '  <option value="'.$arr_ttamano[$i]['id'].'" selected>'.$arr_ttamano[$i]['nombre'].'</option>';
            else echo ' <option value="'.$arr_ttamano[$i]['id'].'">'.$arr_ttamano[$i]['nombre'].'</option>';
        }
            
        echo '      </select>';
    }
?>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="paginaweb">PAGINA WEB</label>
                    <input type="text" name="paginaweb" id="paginaweb" class="formulario_control" value="<?=$arr_empresa['paginaweb']?>" <?echo $v_readonly;?>>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="sectorid">ACTIVIDAD ECONOMICA</label>
<?php
    if ($v_readonly == 'readonly'){
            //CFO, ANALISTA FINANCIERO, CLO, ANALISTA LEGAL
        echo '      <input type="text" name="sector" id="sector" class="formulario_control" value="'.$arr_empresa['sector'].'" readonly>
                    <input type="hidden" name="sectorid" id="sectorid" value="'.$arr_empresa['sectorid'].'">';
    } else{
        $arr_sector = $obj_mae->get_tipos_xnom('SECTORECO');
        echo '      <select class="formulario_control" name="sectorid" id="sectorid">';
                        
        if ($arr_empresa['sectorid'] == 0) 
            echo '      <option value="0" selected>Elija una opcion</option>';

        for ($i=0; $i<count($arr_sector); $i++){
            if ($arr_sector[$i]['id'] == $arr_empresa['sectorid']) 
                echo '  <option value="'.$arr_sector[$i]['id'].'" selected>'.$arr_sector[$i]['nombre'].'</option>';
            else echo ' <option value="'.$arr_sector[$i]['id'].'">'.$arr_sector[$i]['nombre'].'</option>';
        }
                        
        echo '      </select>';
    }
?>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="actividad">DESCRIPCION ACTIVIDAD</label>
                    <textarea name="actividad" id="actividad" cols="70" rows="5" class="formulario_control" <?echo $v_readonly;?>><?echo $arr_empresa['actividad'];?></textarea>
                </div>
            </div>

<?php

    if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-ACC')){
        echo '
            <div style="font-size: 12px;margin-top: 10px;width:200px;"> ACCIONISTAS</div>
            <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="agregarAccionistas()"><i class="fa-solid fa-user-plus"></i> Accionistas</button>';

        //==== RELACION DE ACIONISTAS YA REGISTRADOS
        $varr_accionistas = $obj_mae->get_accionistas($_GET['id']);
        $v_z = 0;

        if (count($varr_accionistas) > 0) $v_tabla_accionistas = '<div>';

        for ($i=0; $i<count($varr_accionistas); $i++){
            $v_z ++;

            if ($i == 0) 
                $v_tabla_accionistas .= '
                <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">Id</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">TipoDoc</th>
                        <th scope="col">Nro Doc</th>
                    </tr></thead>
                    <tbody>';
            
            $v_tabla_accionistas .= '   
                    <tr>
                        <td data-label="Id">'.$varr_accionistas[$i]['accionista_id'].'</td>
                        <td data-label="Nombre">'.$varr_accionistas[$i]['accionista_nombre'].'</td>
                        <td data-label="TipoDoc">'.$varr_accionistas[$i]['tipodoc'].'</td>
                        <td data-label="Nro Doc">'.$varr_accionistas[$i]['nro_documento'].'</td>
                    </tr>';
        }

        if (count($varr_accionistas) > 0) $v_tabla_accionistas .= '</tbody></table></div>';
        echo $v_tabla_accionistas;
        echo '<input type="hidden" name="q_accionistas" id="q_accionistas" value="'.$v_z.'">';

        //==== REPRESENTANTE LEGAL
        echo '
            <hr>
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;"> REPRESENTANTE LEGAL</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_repre">NOMBRE</label>
                    <input type="text" name="nombre_repre" id="nombre_repre" class="formulario_control" value="'.$arr_empresa['nombre_repre'].'">
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_repre">EMAIL</label>
                    <input type="text" name="email_repre" id="email_repre" class="formulario_control" value="'.$arr_empresa['email_repre'].'">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_repre">TIPO DOC</label>
                    <select name="tdoc_repre" class="formulario_control" id="tdoc_repre">';

        $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

        for ($i=0; $i<count($arr_tdoc); $i++){
            if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_repre']) 
                echo '      <option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
            else echo '     <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
        }

        echo '      </select>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrodoc_repre">NRO DOC</label>
                    <input type="text" name="nrodoc_repre" id="nrodoc_repre" class="formulario_control" value="'.$arr_empresa['nrodoc_repre'].'">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono_repre">TELEFONO</label>
                    <input type="text" name="telefono_repre" id="telefono_repre" class="formulario_control" value="'.$arr_empresa['telf_contacto'].'">
                </div>
            </div>';

        if ($t_empresa != 'obligpago'){
            echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="docrepre_file">DOC REPRE</label>
                    <input type="file" name="docrepre_file" id="docrepre_file" class="formulario_control">
                </div>';

            if ($arr_empresa['docrepre_path'] != '' && !is_null($arr_empresa['docrepre_path']))
                echo '
                <div class="formulario_grupo_row" style="width: 80px;">
                    <label for="docrepre_path">PDF</label>
                    <label><a href="'.$arr_empresa['docrepre_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                </div>';

            echo '
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="regmercantil_file">REG MERC / CC</label>
                    <input type="file" name="regmercantil_file" id="regmercantil_file" class="formulario_control">
                </div>';

            if ($arr_empresa['vigencia_path'] != '' && !is_null($arr_empresa['vigencia_path']))
                echo '
                <div class="formulario_grupo_row" style="width: 80px;">
                    <label for="regmercantil_path">PDF</label>
                    <label><a href="'.$arr_empresa['vigencia_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                </div>';

            echo '
            </div>';
        }
    }
?>
            <!--============== CONTACTO -->
            <hr>
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width: 100%;float: left;">INFORMACION DE CONTACTO</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_contacto">NOMBRE</label>
                    <input type="text" name="nombre_contacto" id="nombre_contacto" class="formulario_control" value="<?=$arr_empresa['nombre_contacto']?>" <?echo $v_readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_contacto">EMAIL</label>
                    <input type="text" name="email_contacto" id="email_contacto" class="formulario_control" value="<?=$arr_empresa['email_contacto']?>" <?echo $v_readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_contacto">TIPO DOC</label>

<?php
    if ($v_readonly == 'readonly'){
        echo '      <input type="text" name="tdoc" id="tdoc" value="'.$arr_empresa['doc_contacto'].'" readonly class="formulario_control"></li>
                    <input type="hidden" name="tdoc_contacto" id="tdoc_contacto" value="'.$arr_empresa['tdoc_conotacto'].'">';
    } else{
        echo '      <select name="tdoc_contacto" class="formulario_control" id="tdoc_contacto">';

        $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

        for ($i=0; $i<count($arr_tdoc); $i++){
            if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_repre']) 
                echo '  <option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
            else echo ' <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
        }

        echo '      </select>';
    }
?>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrodoc_contacto">NRO DOC</label>
                    <input type="text" name="nrodoc_contacto" id="nrodoc_contacto" class="formulario_control" value="<?=$arr_empresa['nrodoc_contacto']?>" <?echo $v_readonly;?>>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono_contacto">TELEFONO</label>
                    <input type="text" name="telefono_contacto" id="telefono_contacto" class="formulario_control" value="<?=$arr_empresa['telf_contacto']?>" <?echo $v_readonly;?>>
                </div>
            </div>

<?php
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //@@@@@@@@@@@@@@@@@@@@@@@@ EVALUACION LEGAL

    if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-LEG')){
        echo '
            <hr>
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;width:100%;float:left;"> EVALUACION LEGAL</div>';
        if (is_null($arr_empresa['evaluacion_accionistas']) || $arr_empresa['evaluacion_accionistas'] == ''){
            $v_evaluacion = 'No hay evaluación de accionistas !!';
            $v_bg_evaluacion = 'style="font-weight: bold;"';
        } else{ 
            $v_evaluacion = $arr_empresa['evaluacion_accionistas'];
            $v_bg_evaluacion = 'style="font-weight: bold;color:#fff;background-color:var(--color-rojo);padding: 1px 4px;"';
        }

        echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label>SOBRE LOS ACCIONISTAS</label>
                    <label '.$v_bg_evaluacion.'>'.$v_evaluacion.'</label>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="informe_legal">INFORME LEGAL</label>
                    <input type="file" name="informe_legal" id="informe_legal" class="formulario_control" style="background-color:#fff;">
                </div>';

        if (!is_null($arr_empresa['informe_legal_path']))
            echo '
                <div class="formulario_grupo_row" style="width: 70px;">
                    <label>PDF</label>
                    <label><a href="'.$arr_empresa['informe_legal_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                    <input type="hidden" name="informe_legal_save" id="informe_legal_save" value="'.$arr_empresa['informe_legal_path'].'">
                </div>';
        else
            echo '<input type="hidden" name="informe_legal_save" id="informe_legal_save" value="">';

        echo '  <div class="formulario_grupo_row" style="width: 100px;">
                    <label>FECHA LEGAL</label>';

        if (is_null($arr_empresa['f_informe_legal']))
            echo '  <label> --- </label>';
        else {
            $v_dt_informelegal = strtotime($arr_empresa['f_informe_legal']);
            $v_finforme_legal = date('d-m-Y',$v_dt_informelegal);
            
            echo '  <label>'.$v_finforme_legal.'</label>';
        }
                    
        echo '  </div>
            </div>';
    }

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //@@@@@@@@@@@@@@@@@@@@@@@@ CONTRATO DE VINCULACION

    if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-LEG')){
        if ($t_empresa != 'obligpago'){
            echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label>CONTRATO DE VINCULACION</label>';

            if ($varr_param_contratoemi['valorchar'] == 'SI'){      // INTEGRADO CON BIOMETRIA
                echo '
                    <label>PROCESADO AUTOMATICO !!</label>
                    <input type="hidden" name="valida_contrato" id="valida_contrato" value="NO">';
            } else{
                echo '<input type="hidden" name="valida_contrato" id="valida_contrato" value="SI">';

                if ($arr_empresa['estado'] == 2){
                    // EN VALIDACION
                    echo '
                    <label>PROCESO MANUAL <i class="fa-solid fa-pen" style="font-size:18px;"></i></label>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="url_contrato">URL CONTRATO</label>
                    <input type="text" name="url_contrato" id="url_contrato" class="formulario_control">';
                } elseif ($arr_empresa['estado'] == 50) {
                    // PRE APROBADA
                    echo '
                    <label>PROCESO MANUAL <i class="fa-solid fa-pen" style="font-size:18px;"></i></label>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="url_contrato">CONTRATO FIRMADO</label>
                    <input type="file" name="file_contrato_vinculacion" id="file_contrato_vinculacion" class="formulario_control" style="background-color:#fff;">';
                } elseif ($arr_empresa['estado'] == 1) {
                    // REGISTRADO
                    echo '
                    <label>PROCESO MANUAL <i class="fa-solid fa-pen" style="font-size:18px;"></i></label>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="url_contrato">CONTRATO</label>
                    <label>----</label>';
                } else{
                    echo '
                    <label>PROCESO MANUAL <i class="fa-solid fa-pen" style="font-size:18px;"></i></label>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="url_contrato">CONTRATO</label>
                    <label><a href="'.$arr_empresa['contrato_path'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>';
                }
            }

            echo '
                </div>
            </div>';
        }
    }

    //==== RIESGOS DEL OP

    if (($arr_empresa['t_empresaid'] == 48 || $arr_empresa['t_empresaid'] == 50 || $arr_empresa['t_empresaid'] == 51 || $arr_empresa['t_empresaid'] == 52) && ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-RISK'))){
        $v_arr_riesgos = $obj_mae->get_riesgo_obpago($_GET['id']);
        
        if ($v_arr_riesgos['count'] == 0){
            $f_riesgo = '';
            $v_feval_score = '';
            $v_riesgo_id = 0;
            $v_dias_xvencer_score = 0;
            $v_dias_xvencer_factu = 0;
        } else {
            $v_riesgo_id = $v_arr_riesgos['riesgo_id'];
            $f_riesgo_t = strtotime($v_arr_riesgos['f_evaluacion']);
            $f_riesgo = date('d-m-Y',$f_riesgo_t);
            $arr_dias_min = $obj_mae->dias_vigencia_riesgos(7); //cantidad de dias de vigencia del riesgo factureate
            $arr_dias_minfact = $obj_mae->dias_vigencia_riesgos(23); //cantidad de dias de alerta para modificacion de evaluacion

            $date1_t = date('Y-m-d');
            $date1 = new DateTime($date1_t);
            $date2 = new DateTime($v_arr_riesgos['f_evaluacion']);
            $v_diasvencimiento = $date1->diff($date2);      // hoy - fecha evaluacion
            $diasvencimiento = $v_diasvencimiento->days;    // dias transcurrido
            $v_dias_alerta = $arr_dias_min['valor'] - $arr_dias_minfact['valor'];   // dias para alertar
            $v_dias_xvencer_factu = $arr_dias_min['valor'] - $diasvencimiento;

            if ($arr_dias_min['valor'] >= $diasvencimiento){    // esta vigente
                if ($v_dias_alerta <= $diasvencimiento){    // alertar
                    $mensaje_vencimiento = $arr_dias_min['descripcion'].' urgente los riesgos';
                    $v_bg = "background-color:#b30a1f;color:#ffffff;";
                } else{
                    $mensaje_vencimiento = 'calificacion VIGENTE';
                    $v_bg = "color:#000000;";
                }
            } else{    // vencido
                $mensaje_vencimiento = 'calificacion VENCIDA';
                $v_bg = "background-color:#b30a1f;color:#ffffff;";
                $v_dias_xvencer_factu = $v_dias_xvencer_factu * -1;
            }

            $v_feval_score_t = strtotime($v_arr_riesgos['f_evalua_score']);
            $v_feval_score = date('d-m-Y',$v_feval_score_t);
            $arr_dias_score = $obj_mae->dias_vigencia_riesgos(22); //cantidad de dias de vigencia de evaluacion

            $v_dt_feval_score = new DateTime($v_arr_riesgos['f_evalua_score']);
            $v_obj_dias_transcurridos = $date1->diff($v_dt_feval_score);
            $v_dias_transcurridos_score = $v_obj_dias_transcurridos->days;
            $v_dias_alerta_score = $arr_dias_score['valor'] - $arr_dias_minfact['valor'];
            $v_dias_xvencer_score = $arr_dias_score['valor'] - $v_dias_transcurridos_score;

            if ($arr_dias_score['valor'] >= $v_dias_transcurridos_score){    // esta vigente
                if ($v_dias_alerta_score <= $v_dias_transcurridos_score){    // alertar
                    $mensaje_vencimiento_score = $arr_dias_score['descripcion'].' urgente los riesgos';
                    $v_bg_score = "background-color:#b30a1f;color:#ffffff;";
                } else{
                    $mensaje_vencimiento_score = 'calificacion VIGENTE';
                    $v_bg_score = "color:#000000;";
                }
            } else{    // vencido
                $mensaje_vencimiento_score = 'calificacion VENCIDA';
                $v_bg_score = "background-color:#b30a1f;color:#ffffff;";
                $v_dias_xvencer_score = -1 * $v_dias_xvencer_score;
            }
        }
?>
            <input type="hidden" name="riesgo_id" id="riesgo_id" value="<?=$v_riesgo_id?>">

            <hr>
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 20px;width: 100%; float: left;">RIESGOS DEL OBLIGADO AL PAGO</div>

            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 10px;width: 100%; float: left;">CALIFICACION BURO DE CREDITO</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="f_evaluacion_score">FECHA CALIFICACION</label>
                    <input type="text" name="f_evaluacion_score" id="f_evaluacion_score" class="formulario_control" value="<?=$v_feval_score?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="dias_xvencer_score">DIAS X VENCER</label>
                    <input type="text" name="dias_xvencer_score" id="dias_xvencer_score" class="formulario_control" value="<?=$v_dias_xvencer_score?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="mensaje">NOTA</label>
                    <input type="text" name="mensaje" id="mensaje" class="formulario_control" value="<?=$mensaje_vencimiento_score?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="emp_riesgoid">BURO</label>
                    <select name="emp_riesgoid" id="emp_riesgoid" class="formulario_control">
<?php
        $arr_emp_riesgos = $obj_mae->empresas_riesgo();

        if (is_null($v_arr_riesgos['empresa_scoreid']) || !isset($v_arr_riesgos[0]) || (count($v_arr_riesgos) == 0)) 
            echo '      <option value="0" selected><-- Seleccionar --></option>';
        else echo '     <option value="0"><-- Seleccionar --></option>';

        for ($i=0; $i<count($arr_emp_riesgos); $i++){
            if ($arr_emp_riesgos[$i]['id'] == $v_arr_riesgos['empresa_scoreid']) 
                echo '  <option value="'.$arr_emp_riesgos[$i]['id'].'" selected>'.$arr_emp_riesgos[$i]['nombre'].'</option>';
            else echo ' <option value="'.$arr_emp_riesgos[$i]['id'].'">'.$arr_emp_riesgos[$i]['nombre'].'</option>';
        }
?>
                    </select>
                    <input type='hidden' name="emp_riesgoid_old" id="emp_riesgoid_old" value="<?=$v_arr_riesgos['empresa_scoreid']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nivel_score_riesgoid">NIVEL RIESGO</label>
                    <select name="nivel_score_riesgoid" id="nivel_score_riesgoid" class="formulario_control">
<?php
        $arr_niveles_riesgo = $obj_mae->niveles_riesgo();
        $v_variable = 'score';

        echo '          <option value="0"><-- Seleccionar --></option>';

        for ($i=0; $i<count($arr_niveles_riesgo); $i++){
            if ($arr_niveles_riesgo[$i]['id'] == $v_arr_riesgos['riesgo_scoreid']) 
                echo '  <option value="'.$arr_niveles_riesgo[$i]['id'].'" selected>['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.$arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
            else echo ' <option value="'.$arr_niveles_riesgo[$i]['id'].'">['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.$arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
        }
?>
                    </select>
                    <input type='hidden' name="nivel_score_riesgoid_old" id="nivel_score_riesgoid_old" value="<?=$v_arr_riesgos['riesgo_scoreid']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label style="color:#fff;">ACCION</label>
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="valida_informe(<?=$v_variable?>)">
                        <i class="fa-regular fa-floppy-disk"></i> Grabar BURO
                    </button>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 450px;">
                    <label for="score_riesgo_justi">JUSTIFICACION</label>
                    <textarea name="score_riesgo_justi" id="score_riesgo_justi" cols="70" rows="5" class="formulario_control"><?echo $v_arr_riesgos['desc_riesgoscore'];?></textarea>
                    <input type='hidden' name="score_riesgo_justi_old" id="score_riesgo_justi_old" value="<?=$v_arr_riesgos['desc_riesgoscore']?>">
                </div>
<?php
        if ($v_arr_riesgos['path_informe_score'] != ''){
            echo '  
                <div class="formulario_grupo_row" style="width: 70px;">
                    <label>PDF</label>
                    <label><a href="'.$v_arr_riesgos['path_informe_score'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                </div>';
        }
?>
                <div class="formulario_grupo_row" style="width: 350px;">
                    <label for="informe_score_file">INFORME</label>
                    <input type="file" name="informe_score_file" id="informe_score_file" class="formulario_control">
                    <input type="hidden" name="path_score" value="<?=$v_arr_riesgos['path_informe_score']?>">
                </div>
            </div>

            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 10px;width: 100%;float: left;">CALIFICACION FACTUREATE</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="f_evaluacion">FECHA CALIFICACION</label>
                    <input type="text" name="f_evaluacion" id="f_evaluacion" class="formulario_control" value="<?=$f_riesgo?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="dias_xvencer">DIAS X VENCER</label>
                    <input type="text" name="dias_xvencer" id="dias_xvencer" class="formulario_control" value="<?=$v_dias_xvencer_factu?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="mensaje_factu">NOTA</label>
                    <input type="text" name="mensaje_factu" id="mensaje_factu" class="formulario_control" value="<?=$mensaje_vencimiento?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="f_evaluacion">NIVEL RIESGO</label>
                    <select name="nivel_factureate_riesgoid" id="nivel_factureate_riesgoid" class="formulario_control">
<?php
        $arr_niveles_riesgo = $obj_mae->niveles_riesgo();
        $v_variable = 'factreate';

        echo '          <option value="0"><-- Seleccionar --></option>';

        for ($i=0; $i<count($arr_niveles_riesgo); $i++){
            if ($arr_niveles_riesgo[$i]['id'] == $v_arr_riesgos['riesgo_factid']) 
                echo '  <option value="'.$arr_niveles_riesgo[$i]['id'].'" selected>['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.$arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
            else echo ' <option value="'.$arr_niveles_riesgo[$i]['id'].'">['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.$arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
        }
?>
                    </select>
                    <input type='hidden' name="nivel_factureate_riesgoid_old" value="<?=$v_arr_riesgos['riesgo_factid']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label style="color:#fff;">ACCION</label>
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarRiesgoFactureate()">
                        <i class="fa-regular fa-floppy-disk"></i> Grabar Factureate
                    </button>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 450px;">
                    <label for="factureate_riesgo_justi">JUSTIFICACION</label>
                    <textarea class="formulario_control" name="factureate_riesgo_justi" id="factureate_riesgo_justi" cols="70" rows="5"><?echo $v_arr_riesgos['desc_riesgofact'];?></textarea>
                    <input type='hidden' name="factureate_riesgo_justi_old" value="<?=$v_arr_riesgos['desc_riesgofact']?>">
                </div>
<?php
        if ($v_arr_riesgos['path_informe_factu'] != ''){
            echo '  
                <div class="formulario_grupo_row" style="width: 70px;">
                    <label>PDF</label>
                    <label><a href="'.$v_arr_riesgos['path_informe_factu'].'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>
                </div>';
        }
?>
                <div class="formulario_grupo_row" style="width: 350px;">
                    <label for="informe_score_file">INFORME</label>
                    <input type="file" name="informe_factu_file" id="informe_factu_file" class="formulario_control">
                    <input type="hidden" name="path_factu" value="<?=$v_arr_riesgos['path_informe_factu']?>">
                </div>
            </div>
            
<?php
    }

            //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            //@@@@@@@@@@@@@@@@ BOTONERA

    echo '  <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:5px;"></div>
            <div style="width:100%; float:left;margin-bottom:5px;">';

            if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-LEG')){
                if ($arr_empresa['t_empresaid'] != 48 && $arr_empresa['t_empresaid'] != 50){    //EMISOR
                    if ($arr_empresa['estado'] == 2){  // ESTADO EN VALIDACION
                    echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_aprobacion" onclick="aprobarLegal()">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar Legal
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_rechazo" onclick="rechazarLegal()">
                    <i class="fa-solid fa-thumbs-down"></i> Rechazar Legal
                </button>';
                    }
                
                    if ($arr_empresa['estado'] == 50)  // ESTADO PRE APROBADO (CUANDO FALTA EL CONTRATO)
                    echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_contrato" onclick="registrarContrato()">
                    <i class="fa-solid fa-file-invoice"></i> Reg Contrato
                </button>';

                    if ($arr_empresa['estado'] == 3)    // APROBADA  
                    echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarLegal()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>';
                }

                if ($arr_empresa['t_empresaid'] == 48 || $arr_empresa['t_empresaid'] == 50){    // OP
                    if ($arr_empresa['estado'] == 2){  // ESTADO EN VALIDACION
                        if ($arr_empresa['estado_legal'] == 1)  // APROBADO POR LEGAL
                            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarLegal()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="rechazarLegal()">
                    <i class="fa-solid fa-hand"></i> Rechazar Legal
                </button>';
                        elseif ($arr_empresa['estado_legal'] == 0) {    // AUN NO ESTA APROBADO LEGALMENTE
                            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarLegal()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="aprobarLegalOP()">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar Legal
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="rechazarLegal()">
                    <i class="fa-solid fa-hand"></i> Rechazar Legal
                </button>';
                        } else{     // RECHAZADO LEGAL
                            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="aprobarLegalOP()">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar Legal
                </button>';
                        }
                    } elseif ($arr_empresa['estado'] == 3) {    // VALIDADA
                        if ($arr_empresa['estado_legal'] == 1){
                            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarLegal()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="rechazarLegal()">
                    <i class="fa-solid fa-hand"></i> Rechazar Legal
                </button>';    
                        } elseif ($arr_empresa['estado_legal'] == -1) {      //RECHAZADO LEGAL
                            echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="aprobarLegalOP()">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar Legal
                </button>';
                        }
                    }
                }
            }

            if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-UPD-OP')){   // COO, ANALISTA OP
                if ($arr_empresa['estado'] == 2){       // EN VALIDACION
                    if ($arr_empresa['t_empresaid'] == 48){     // OP
                        echo '
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarOperativo()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>';
                    }
                }
            }

            echo '
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_cerrar" onclick="cerrar()">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar
                </button>';
?>
            </div>
        </div>  <!-- class formulario -->
            
    </div>  <!-- contenedor principal -->    
    </form>
    
    <!--###############################################
    ################### ZONA MODAL -->
    
    <div class="modal fade" id="MasAccionistas" tabindex="-1" role="dialog">
        <div class="modal-dialog">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">.</h5></li>
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
    <!-- ###############################################
    ############### SCRIPT -->
    <script type="text/javascript">
        function agregarAccionistas(){
            var v_empresa_id = $('#empresaid').val();
            
            $('.modal-title').text('AGREGAR ACCIONISTAS');
            $('.modal-body').load('agregar_accionistas_modal.php?empresa_id='+v_empresa_id,function(){
                $('#MasAccionistas').modal({show:true});
            });
        }

        function aprobarLegal(){
            var v_empresa_id = $('#empresaid').val();
            var v_q_accionistas = $('#q_accionistas').val();

            if (v_q_accionistas == 0) alert('Debe realizar un analisis de los accionistas de la empresa');
            else{
                var v_informe_legal = $('#informe_legal').val();

                if (v_informe_legal == '') alert('Debe ingresar el respectivo informe legal');
                else {
                    var v_url_contrato = $('#url_contrato').val();

                    if (v_url_contrato == '') alert('Debe registrar el contrato que debe firmar la empresa');
                    else {
                        document.frm.accion.value = 'aprobar';
                        var vbtn_aprobacion = document.getElementById('boton_aprobacion');
                        var vbtn_rechazo = document.getElementById('boton_rechazo');
                        var vbtn_cerrar = document.getElementById('boton_cerrar');

                        vbtn_aprobacion.disabled = true;
                        vbtn_rechazo.disabled = true;
                        vbtn_cerrar.disabled = true;

                        var formData = new FormData(document.getElementById("frm"));
                                
                        $.ajax({
                            url:"empresa_gestion_proceso.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html"
                        })
                        .done(function(rpta){
                                alert('La empresa fue aprobada legalmente');
                                location.href = 'empresa_gestion_detalle.php?id='+v_empresa_id+'&previo=empresas';
                        });
                    }
                }
            }
        }

        function registrarContrato(){
            var v_contrato = $('#file_contrato_vinculacion').val();

            if (v_contrato == '') alert('Debe cargar el contrato firmado por la empresa');
            else {
                var vbtn_contrato = document.getElementById('boton_contrato');
                var vbtn_cerrar = document.getElementById('boton_cerrar');

                vbtn_contrato.disabled = true;
                vbtn_cerrar.disabled = true;

                document.frm.accion.value = 'reg_contrato';

                var formData = new FormData(document.getElementById("frm"));
                                
                $.ajax({
                    url:"empresa_gestion_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    alert('Se registro el contrato firmado por la empresa');
                    location.href = 'empresa_gestion_detalle.php?id='+v_empresa_id+'&previo=empresas';
                });
            }
        }

        function guardarLegal(){
            alert('guardar');
        }

        function rechazarLegal(){
            alert('rechazar legal');
        }

        function guardarRiesgoFactureate(){
            var archivo = document.getElementById('informe_factu_file');
            var nivel_factu = document.getElementById('nivel_factureate_riesgoid');
            var msg_factu = document.getElementById('factureate_riesgo_justi');
            var accion = document.getElementById('accion');
            var v_empresa_id = $('#empresaid').val();

            accion = 'factureate';

            if (archivo.value == ""){
                alert("Debe cargar el informe de analisis de Riesgo de Factureate");
            } else{
                if (nivel_factu.value == "0") alert("Debe ingresar un nivel de riesgo Factureate");
                else {
                    if (msg_factu.value == "") alert("Debe ingresar un texto de justificacion del nivel de riesgo Factureate");
                    else{
                        var formData = new FormData(document.getElementById("frm"));
                    
                        $.ajax({
                            url:"update_riesgo_proceso.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html",
                            success:function(data,status){
                                $('#contenedor_principal').fadeIn(1000).html(data);
                                $('#contenedor_principal').modal('hide');
                                refresh_page(v_empresa_id);
                            }
                        });
                    }
                }
            }
        }

        function guardarOperativo(){
            var direccion = document.frm.direccion.value;
            var actividad = Number(document.frm.sectorid.value);
            var t_empresaid = Number(document.frm.t_empresaid.value);
            var v_paginaweb = document.frm.paginaweb.value;
            var v_actividaddesc = document.frm.actividad.value;
            var v_contactonom = document.frm.nombre_contacto.value;
            var v_contactoemail = document.frm.email_contacto.value;
            var v_contactotdoc = Number(document.frm.tdoc_contacto.value);
            var v_contactonrodoc = document.frm.nrodoc_contacto.value;
            var v_contactotelefono = document.frm.telefono_contacto.value;
            var v_empresa_id = $('#empresaid').val();

            document.frm.accion.value = "update";

            if (direccion == '' || t_empresaid == 0 || v_paginaweb == '' || actividad == 0 || v_actividaddesc == '') alert ('Debe completar la informacion de la empresa');
            else {
                var formData = new FormData(document.getElementById("frm"));
                    
                $.ajax({
                        url:"empresa_gestion_proceso.php",
                        type:'post',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: "html",
                        success:function(data,status){
                            $('#contenedor_principal').fadeIn(1000).html(data);
                            $('#contenedor_principal').modal('hide');
                            refresh_page(v_empresa_id);
                        }
                });
            }
        }

        function aprobarLegalOP(){
            var nombre_repre = document.frm.nombre_repre.value;
            var email_repre = document.frm.email_repre.value;
            var nrodoc_repre = document.frm.nrodoc_repre.value;
            var telefono_repre = document.frm.telefono_repre.value;
            var tdoc_repre = Number(document.frm.tdoc_repre.value);
            var informe_legal_file = document.getElementById('informe_legal');
            var informe_legal_path = informe_legal_file.value;
            var v_empresa_id = $('#empresaid').val();

            document.frm.accion.value = "aprobar_legal_op";

            if (nombre_repre == '' || email_repre == '' || nrodoc_repre == '' || telefono_repre == '' || tdoc_repre == 0) alert('Debe completar los datos del representante legal');
            else{
                if (informe_legal_path == '') alert('Debe registrar el informe legal');
                else{
                    var formData = new FormData(document.getElementById("frm"));
                    
                    $.ajax({
                        url:"empresa_gestion_proceso.php",
                        type:'post',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: "html",
                        success:function(data,status){
                            $('#contenedor_principal').fadeIn(1000).html(data);
                            $('#contenedor_principal').modal('hide');
                            refresh_page(v_empresa_id);
                        }
                    });
                }
            }
        }

        function cerrar(){
            var v_previo = $('#previo').val();
            location.href = v_previo;
        }

        function refresh_page(p_empresa_id){
            //var v_empresa_id = $('#empresaid').val();
            location.href = "empresa_gestion_detalle.php?id="+p_empresa_id+"&previo=empresas";
        }
    </script>
</BODY>
</HTML>