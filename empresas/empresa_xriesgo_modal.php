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

</HEAD>

<?php
/*===================================================
===== LOGICA
=====================================================*/
$obj_mae = new maestros;
$vobj_seg_modal = new seguridad;

$arr_empresa = $obj_mae->get_datos_empresa($_GET['id']);
$varr_param_contratoemi = $obj_mae->get_parametro_detalle(27);

//++++ configuracion de parametros
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

if ($arr_empresa['estado_legal'] == -1) $v_muestra_estado_legal = '<i class="fa-solid fa-face-angry" style="font-size:16px;color:var(--color-rojo);"></i> Rechazado Legalmente';
else $v_muestra_estado_legal = '';

//==== VERIFICO PERMISOS
$varr_permisos = $vobj_seg_modal->get_permisos($_SESSION['user']['perfilid']);
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion">
        <input type="hidden" name="empresaid" id="empresaid" value="<?=$_GET['id']?>">
        <input type="hidden" name="t_empresaid" value="<?=$arr_empresa['t_empresaid']?>">
        <input type="hidden" name="estado_id" id="estado_id" value="<?=$arr_empresa['e_empresa_id']?>">
        <input type="hidden" name="concentracion_param" id="concentracion_param" value="<?=$varr_concentracion_param['valor_num']?>">

    <div id="principal" style="padding-left: 10px;overflow: hidden;">
        <div class="contenedor_formulario">
            <!--+++++++++++++++++++++++++++++++++++++++++++++++++
            ++++ bloque informacion de la empresa-->

            <div class="contenedor_formulario_column">
                <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;">INFORMACION DE LA EMPRESA:</p>
            </div>

            <!--++++ bloque 1 de informacion de la empresa -->
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

            <!--++++ bloque 2 de informacion de la empresa -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 360px;">
                    <label for="direccion">DIRECCION</label>
                    <input type="text" name="direccion" id="direccion" class="formulario_control" value="<?=$arr_empresa['direccion']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="tamano">TAMAÑO DE EMPRESA</label>
<?php
    $arr_ttamano = $obj_mae->get_tipos('TAMANHOEMP');

    if ($_SESSION['user']['perfilid'] == 14 || $_SESSION['user']['perfilid'] == 10 || $_SESSION['user']['perfilid'] == 12 || $_SESSION['user']['perfilid'] == 13 || $_SESSION['user']['perfilid'] == 15){
        //CFO o ANSLISTA FINANCIERO, CLO, ANALISTA LEGAL,  CEO
        echo '
                    <input type="text" name="tamano" id="tamano" class="formulario_control" value="'.$arr_empresa['tamano'].'" readonly>
                    <input type="hidden" name="tamanoid" id="tamanoid" value="'.$arr_empresa['tamanoid'].'">';
    } else{
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
                    <input type="text" name="paginaweb" id="paginaweb" class="formulario_control" value="<?=$arr_empresa['paginaweb']?>" readonly>
                </div>
            </div>

            <!--++++ bloque 3 informacion de la empresa -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="sectorid">ACTIVIDAD ECONOMICA</label>
<?php
    if ($_SESSION['user']['perfilid'] == 14 || $_SESSION['user']['perfilid'] == 10 || $_SESSION['user']['perfilid'] == 12 || $_SESSION['user']['perfilid'] == 13 || $_SESSION['user']['perfilid'] == 15){
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
                    <textarea name="actividad" id="actividad" cols="70" rows="5" class="formulario_control" readonly><?php echo $arr_empresa['actividad'];?></textarea>
                </div>
            </div>

            <!--+++++++++++++++++++++++++++++++++++++++++++++++++
            ++++ FIN bloque informacion de la empresa-->

            <!--+++++++++++++++++++++++++++++++++++++++++++++++++
            ++++ bloque legal de la empresa-->

            <!--++++ bloque de accionistas -->
<?php
    if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-ACC')){
        echo '
            <div class="contenedor_formulario_column">
                <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;">
                    ACCIONISTAS: <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="agregarAccionistas()"><i class="fa-solid fa-user-plus"></i> Accionistas</button>
                </p>
            </div>';
    }

    //==== RELACION DE ACIONISTAS YA REGISTRADOS
    $varr_accionistas = $obj_mae->get_accionistas($_GET['id']);
    $v_z = 0;

    if (count($varr_accionistas) > 0) $v_tabla_accionistas = '<div>';
    else $v_tabla_accionistas = '';

    for ($i=0; $i<count($varr_accionistas); $i++){
        $v_z ++;

        if ($i == 0)
            $v_tabla_accionistas .= '
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">TipoDoc</th>
                        <th scope="col">Nro Doc</th>
                    </tr>
                </thead>
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
    echo '  <input type="hidden" name="q_accionistas" id="q_accionistas" value="'.$v_z.'">';

            //++++ Bloque REPRESENTANTE LEGAL
    echo '
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 10px;"> REPRESENTANTE LEGAL</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_repre">NOMBRE</label>
                    <input type="text" name="nombre_repre" id="nombre_repre" class="formulario_control" value="'.$arr_empresa['nombre_repre'].'" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="email_repre">EMAIL</label>
                    <input type="text" name="email_repre" id="email_repre" class="formulario_control" value="'.$arr_empresa['email_repre'].'" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tdoc_repre">TIPO DOC</label>
                    <select name="tdoc_repre" class="formulario_control" id="tdoc_repre" disabled>';

    $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

    for ($i=0; $i<count($arr_tdoc); $i++){
        if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_repre'])
                echo '      <option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
        else echo '         <option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
    }

    echo '          </select>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nrodoc_repre">NRO DOC</label>
                    <input type="text" name="nrodoc_repre" id="nrodoc_repre" class="formulario_control" value="'.$arr_empresa['nrodoc_repre'].'" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="telefono_repre">TELEFONO</label>
                    <input type="text" name="telefono_repre" id="telefono_repre" class="formulario_control" value="'.$arr_empresa['telf_contacto'].'" readonly>
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

    //+++ REVISAR
    if ($_SESSION['user']['perfilid'] == 12 || $_SESSION['user']['perfilid'] == 13){
        echo '
            <div class="contenedor_formulario_column">
                <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;">EVALUACION LEGAL:</p>
            </div>';

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
?>

            <!-- CONTRATO DE VINCULACION
                REVISAR-->
<?php
    if ($_SESSION['user']['perfilid'] == 12 || $_SESSION['user']['perfilid'] == 11 || $_SESSION['user']['perfilid'] == 15){ // CLO, COO Y CLEVEL
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
?>

            <!--+++++++++++++++++++++++++++++++++++++++++
            +++++++++++ EVALUACION DE RIESGOS -->
<?php
    $mensaje_vencimiento_score = '';
    $mensaje_vencimiento ='';

    // obtengo las variables de parametro
    $arr_dias_minfact = $obj_mae->dias_vigencia_riesgos(23); //cantidad de dias de alerta para modificacion de evaluacion

    if ($t_empresa == 'obligpago' && $obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'MNGRISK')){
        //++++ informacion de los riesgos del OP
        $v_arr_riesgos = $obj_mae->get_riesgo_obpago($_GET['id']);

        //revisarmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm
        if (count($v_arr_riesgos) == 0 || is_null($v_arr_riesgos) || !isset($v_arr_riesgos)){
            $v_empresa_score_id = 0;
            $v_path_informe_scrore = '';
            $v_path_informe_factu = '';
        } else {
            if (!isset($v_arr_riesgos['empresa_scoreid'])) $v_empresa_score_id = 0;
            else $v_empresa_score_id = $v_arr_riesgos['empresa_scoreid'];

            if (!isset($v_arr_riesgos['path_informe_score'])) $v_path_informe_scrore = '';
            else $v_path_informe_scrore = $v_arr_riesgos['path_informe_score'];

            if (!isset($v_arr_riesgos['path_informe_factu'])) $v_path_informe_factu = '';
            else $v_path_informe_factu = $v_arr_riesgos['path_informe_factu'];
        }
        
        if ($v_arr_riesgos['count'] == 0){
            $f_riesgo = '';
            $v_feval_score = '';
            $v_riesgo_id = 0;
            $v_dias_xvencer_score = 0;
            $v_dias_xvencer_factu = 0;
            $v_score_buro = 0;
            $v_score_buro_convertido = 0;
        } else {
            $v_riesgo_id = $v_arr_riesgos['riesgo_id'];
            $f_riesgo_t = strtotime($v_arr_riesgos['f_evaluacion']);
            $f_riesgo = date('d-m-Y',$f_riesgo_t);
            $arr_dias_min = $obj_mae->dias_vigencia_riesgos(7); //cantidad de dias de vigencia del riesgo factureate

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
                $v_dias_xvencer_factu = $v_dias_xvencer_factu;
            }
        }
?>

            <!--++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            EVALUACION DE RIESGOS +++++++++++++++++++++++++++++++++++-->

            <!--++++ id del registro del riesgo de la empresa -->
            <input type="hidden" name="riesgo_id" id="riesgo_id" value="<?=$v_arr_riesgos['riesgo_id']?>">

            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 20px;width: 100%; float: left;">EVALUACION DE RIESGOS</div>

            <!--++++ Riesgo OP - Buro de credito-->
            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 10px;width: 100%; float: left;padding: 5px;background-color: var(--color-oro);">
                CALIFICACION BURO DE CREDITO
            </div>

<?php
        if ($v_arr_riesgos['f_evalua_score'] != '') $v_feval_score = date('d-m-Y',strtotime($v_arr_riesgos['f_evalua_score'])); else $v_feval_score = '';

        if ($v_feval_score != ''){
            $arr_dias_score = $obj_mae->dias_vigencia_riesgos(22); //cantidad de dias de vigencia de evaluacion

            $v_dt_feval_score = new DateTime($v_arr_riesgos['f_evalua_score']);     // fecha de evaluacion del buro
            $fechaHoy = new DateTime();     // fecha hoy
            $dif_dias_transcurridos_score = $fechaHoy->diff($v_dt_feval_score);
            $v_dias_transcurridos_score = $dif_dias_transcurridos_score->days;
            $v_dias_xvencer_score = $arr_dias_score['valor'] - $v_dias_transcurridos_score;

            // mensaje de buro
            if ($arr_dias_score['valor'] > $v_dias_transcurridos_score){
                // esta vigente
                $v_dias_alerta_score = $arr_dias_score['valor'] - $arr_dias_minfact['valor'];

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
                $v_dias_xvencer_score = 0;
            }
        } else {
            $v_dias_xvencer_score = 0;
            $mensaje_vencimiento_score = '';
        }
?>

            <!--++++ bloque de datos de evaluacion buro -->
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

            <!--++++ bloque 1 del buro -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="emp_riesgoid">BURO</label>
                    <select name="emp_riesgoid" id="emp_riesgoid" class="formulario_control" onchange="puntajeBuro()">
<?php
        $arr_emp_riesgos = $obj_mae->empresas_riesgo();

        if (is_null($v_arr_riesgos['empresa_scoreid']) || count($v_arr_riesgos) == 0 || $v_arr_riesgos['empresa_scoreid'] == 0){
            $v_score_buro = 0;
            $v_score_buro_convertido = 0;
            echo '      <option value="0" selected>--Elegir--</option>';
        } else {
            $v_score_buro = $v_arr_riesgos['score_buro'];
            $v_score_buro_convertido = $v_arr_riesgos['score_buro_convertido'];
            echo '     <option value="0">--Elegir--</option>';
        }

        for ($i=0; $i<count($arr_emp_riesgos); $i++){
            if ($arr_emp_riesgos[$i]['id'] == $v_empresa_score_id) 
                echo '  <option value="'.$arr_emp_riesgos[$i]['id'].'" selected>'.$arr_emp_riesgos[$i]['nombre'].'</option>';
            else echo ' <option value="'.$arr_emp_riesgos[$i]['id'].'">'.$arr_emp_riesgos[$i]['nombre'].'</option>';
        }
?>
                    </select>
                    <input type='hidden' name="emp_riesgoid_old" id="emp_riesgoid_old" value="<?=$v_empresa_score_id?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="puntaje_buro_view">PUNTAJE BURO</label>
                    <input type="text" name="puntaje_buro_view" id="puntaje_buro_view" value="<?=$v_score_buro?>" placeholder="0.00" style="text-align: right;" class="formulario_control" onchange="puntajeBuro()">
                    <input type="hidden" name="puntaje_buro" id="puntaje_buro" value="<?=$v_score_buro?>">
                    <input type="hidden" name="puntaje_buro_old" id="puntaje_buro_old" value="<?=$v_score_buro?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="puntaje_conv_view">PUNTAJE CONV</label>
                    <input type="text" name="puntaje_conv_view" id="puntaje_conv_view" value="<?=$v_score_buro_convertido?>" style="text-align: right;" class="formulario_control" readonly>
                    <input type="hidden" name="puntaje_conv" id="puntaje_conv" value="<?=$v_score_buro_convertido?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="riesgo_buro">NIVEL RIESGO</label>
                    <input type="text" name="riesgo_buro" id="riesgo_buro" value="<?=$v_arr_riesgos['calificacion_buro']?>" style="text-align: center;" class="formulario_control" readonly>
                    <input type="hidden" name="nivel_score_riesgoid" id="nivel_score_riesgoid" value="<?=$v_arr_riesgos['riesgo_scoreid']?>">
                    <input type="hidden" name="nivel_score_riesgoid_old" id="nivel_score_riesgoid_old" value="<?=$v_arr_riesgos['riesgo_scoreid']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="informe_score_file">INFORME BURO</label>
                    <input type="file" name="informe_score_file" id="informe_score_file" class="formulario_control" style="background: #fff;">
                    <input type="hidden" name="path_score" value="<?=$v_path_informe_scrore?>">
                </div>
                <div class="formulario_grupo_row" style="width: 70px;" id="pdf_buro">

<?php
        if ($v_path_informe_scrore != ''){
?>

                    <label for="pdf_buro">PDF</label>
                    <p id="pdf_buro">
                        <a href="<?php echo $v_arr_riesgos['path_informe_score'];?>" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a>
                    </p>

<?php
        }
?>

                </div>
            </div>

            <!--++++ bloque 2 buro -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 450px;">
                    <label for="score_riesgo_justi">JUSTIFICACION</label>
                    <textarea name="score_riesgo_justi" id="score_riesgo_justi" cols="70" rows="5" class="formulario_control"><?php echo $v_arr_riesgos['desc_riesgoscore'];?></textarea>
                    <input type='hidden' name="score_riesgo_justi_old" id="score_riesgo_justi_old" value="<?=$v_arr_riesgos['desc_riesgoscore']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="btn_buro" style="color:#fff;">ACCION</label>
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarRiesgoBuro()" id="btn_buro">
                        <i class="fa-regular fa-floppy-disk"></i> Grabar Calificación BURO
                    </button>
                </div>
            </div>

            <!--++++ Riesgo OP - Variables dimension pagador-->
            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 10px;width: 100%; float: left;padding: 5px;background-color: var(--color-oro);">
                VARIABLES DIMENSION PAGADOR
            </div>

            <!--++++ Riesgo OP - Variables dimension pagador-->

            <!--++++ historial de pagos pagador-->

<?php
        $varr_historial_pago = $obj_mae->promedio_pagos_op($_GET['id']);    // historia_pagos, dias_promedio, calificacion

        if ($varr_historial_pago['historia_pagos'] > 0) $v_con_historia = 'SI';
        else $v_con_historia = 'NO';
?>

            <!--++++++ bloque cumplimiento de pagos -->
            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 10px;width: 100%;float: left;">VARIABLE CUMPLIMIENTO DE PAGO</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="dias_prom_pago">DIAS PROM PAGO</label>
                    <input type="text" name="dias_prom_pago" id="dias_prom_pago" value="<?=$varr_historial_pago['dias_promedio']?>" style="text-align: right;" class="formulario_control" onchange="diasPromedio()">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="historia_pago">CON HISTORIA</label>
                    <input type="text" name="historia_pago" id="historia_pago" value="<?=$v_con_historia?>" style="text-align: center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="calificacion_pago">VALOR VARIABLE</label>
                    <input type="text" name="calificacion_pago" id="calificacion_pago" value="<?=$varr_historial_pago['calificacion']?>" style="text-align: right;" class="formulario_control" readonly>
                </div>
            </div>

            <!--++++++ bloque PERFIL DE EMPRESA -->
            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 15px;width: 100%;float: left;">VARIABLE PERFIL EMPRESA</div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="tamanho">TAMAÑO</label>
                    <select name="tamanho" id="tamanho" class="formulario_control" onchange="calculaTamanho()">

<?php
        //++++ inicio del select
        if ($arr_empresa['tamanoid'] == 0)
            echo '      <option value="0" selected>-- Tamaño --</option>';
        else
            echo '      <option value="0">-- Tamaño --</option>';

        //++++ recorrido del arreglo de tamanhos
        for ($i=0; $i<count($arr_ttamano); $i++){
            if ($arr_ttamano[$i]['id'] == $arr_empresa['tamanoid'])
                echo '  <option value="'.$arr_ttamano[$i]['id'].'" selected>'.$arr_ttamano[$i]['nombre'].'</option>';
            else
                echo '  <option value="'.$arr_ttamano[$i]['id'].'">'.$arr_ttamano[$i]['nombre'].'</option>';
        }
?>

                    </select>
                </div>

<?php
        $varr_rango_tamanho = $obj_mae->get_risk_parametros(0,'TAMANHO OP');
        $v_rango = '';
        $v_calificacion_tamanho = 0;

        for ($j = 0; $j < count($varr_rango_tamanho); $j++){
            if ($varr_rango_tamanho[$j]['valor_num'] == $arr_empresa['tamanoid']){
                $v_rango = '[$'.number_format($varr_rango_tamanho[$j]['valor_minimo'],2,'.',',').' - $'.number_format($varr_rango_tamanho[$j]['valor_maximo'],2,'.',',').']';
                $v_calificacion_tamanho = $varr_rango_tamanho[$j]['calificacion'];
                break;
            }
        }
?>

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="rango_tamanho">RANGO</label>
                    <input type="text" name="rango_tamanho" id="rango_tamanho" value="<?=$v_rango?>" style="text-align:center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="calificacion_tamanho">CALIFICACION</label>
                    <input type="text" name="calificacion_tamanho" id="calificacion_tamanho" value="<?=$v_calificacion_tamanho?>" style="text-align:right;" class="formulario_control" readonly>
                </div>

<?php
        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        if (is_null($arr_empresa['f_ini_actividad'])){
            $v_anhos_operando = 0;
            $v_factor_operando = 0.5;
        } else {
            $vf_inicio_actividades = new DateTime($arr_empresa['f_ini_actividad']);
            $vf_hoy = new DateTime();

            $diff_actividad = $vf_hoy->diff($vf_inicio_actividades);
            $v_anhos_operando = (int) $diff_actividad->y;

            //++++ parametros de risgo
            $varr_factor_operando = $obj_mae->get_risk_parametros(0,'ANTIGUEDAD OP');

            for ($j = 0; $j < count($varr_factor_operando); $j++){
                if ($varr_factor_operando[$j]['valor_minimo'] <= $v_anhos_operando && $varr_factor_operando[$j]['valor_maximo'] >= $v_anhos_operando){
                    $v_factor_operando = $varr_factor_operando[$j]['calificacion'];
                    break;
                }
            }
        }

        $v_valor_perfil = $v_factor_operando * $v_calificacion_tamanho;
?>

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="anhos_operando">AÑOS OPERANDO</label>
                    <input type="text" name="anhos_operando" id="anhos_operando" value="<?=$v_anhos_operando?>" style="text-align:right;" class="formulario_control" onchange="anhosOperando()">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="factor_anhos_operando">FACTOR</label>
                    <input type="text" name="factor_anhos_operando" id="factor_anhos_operando" value="<?=$v_factor_operando?>" style="text-align:right;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="calificacion_perfil">VALOR VARIABLE</label>
                    <input type="text" name="calificacion_perfil" id="calificacion_perfil" value="<?=$v_valor_perfil?>" style="text-align:right;" class="formulario_control" readonly>
                </div>
            </div>

            <!--++++++ bloque MACRO ECONOMICO -->
            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 15px;width: 100%;float: left;">VARIABLE MACROECONOMICA</div>

<?php
        $varr_variables_macro = $obj_mae->get_risk_variables(0,6,0);
        $v_variables_count = 0;     // contador de variables por linea
        $varr_impacto_macro = $obj_mae->get_risk_parametros(0,'IMPACTO MACRO');
        $varr_nivel_macro = $obj_mae->get_risk_parametros(0,'NIVEL MACRO');
        $varr_variables_empresa = $obj_mae->get_riesgo_empresa_variable($_GET['id']);       // variables macro de la empresa
        $v_grupo_variables = '';

        // genero un arreglo solo con el id de la variable de la empresa
        $varr_variables_id = array_column($varr_variables_empresa, 'variable_id');
        $v_tipo_impacto = "'impacto'";
        $v_tipo_nivel = "'nivel'";

        for ($i = 0; $i < count($varr_variables_macro); $i++){
            // recorrido de las variables macro
            $v_grupo_variables .= $varr_variables_macro[$i]['variable_id'].'-';
            $v_variable_id_param = "'".$varr_variables_macro[$i]['variable_id']."'";

            if ($v_variables_count == 0){
                echo '
            <div class="contenedor_formulario_column">';
            }

            // busco el valor de la variable de la empresa
            $indice = array_search($varr_variables_macro[$i]['variable_id'], $varr_variables_id);

            // comprobar si se encontro
            if ($indice !== false){
                $v_impacto_emrpesa = $varr_variables_empresa[$indice]['impacto'];   // es el id
                $v_nivel_empresa = $varr_variables_empresa[$indice]['nivel'];       // es el id
                $v_impacto_empresa_valor = $varr_variables_empresa[$indice]['impacto_valor'];
                $v_nivel_empresa_valor = $varr_variables_empresa[$indice]['nivel_valor'];
            } else {
                $v_impacto_empresa = 19;
                $v_nivel_empresa = 29;
                $v_impacto_empresa_valor = 0;
                $v_nivel_empresa_valor = 0;
            }

            $v_califica_macro = ($v_impacto_empresa_valor / 10) * (100 - $v_nivel_empresa_valor);

            echo '
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="'.$varr_variables_macro[$i]['variable_id'].'">VARIABLE</label>
                    <input type="text" name="'.$varr_variables_macro[$i]['variable_id'].'" id="'.$varr_variables_macro[$i]['variable_id'].'" value="'.$varr_variables_macro[$i]['nombre'].'" style="text-align:center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="impactomacro-'.$varr_variables_macro[$i]['variable_id'].'">IMPACTO</label>
                    <select name="impactomacro-'.$varr_variables_macro[$i]['variable_id'].'" id="impactomacro-'.$varr_variables_macro[$i]['variable_id'].'" class="formulario_control" onchange="variableMacro('.$v_tipo_impacto.','.$v_variable_id_param.')">';

            for ($j = 0; $j < count($varr_impacto_macro); $j++){
                if ($varr_impacto_macro[$j]['parametro_id'] == $v_impacto_empresa)
                    echo '
                        <option value="'.$varr_impacto_macro[$j]['parametro_id'].'-'.$varr_impacto_macro[$j]['valor_num'].'" selected>'.$varr_impacto_macro[$j]['nombre'].'</option>';
                else echo '
                        <option value="'.$varr_impacto_macro[$j]['parametro_id'].'-'.$varr_impacto_macro[$j]['valor_num'].'">'.$varr_impacto_macro[$j]['nombre'].'</option>';
            }

            echo '
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="nivelmacro-'.$varr_variables_macro[$i]['variable_id'].'">NIVEL</label>
                    <select name="nivelmacro-'.$varr_variables_macro[$i]['variable_id'].'" id="nivelmacro-'.$varr_variables_macro[$i]['variable_id'].'" class="formulario_control" onchange="variableMacro('.$v_tipo_nivel.','.$v_variable_id_param.')">';

            for ($j = 0; $j < count($varr_nivel_macro); $j++){
                if ($varr_nivel_macro[$j]['parametro_id'] == $v_nivel_empresa)
                    echo '
                        <option value="'.$varr_nivel_macro[$j]['parametro_id'].'-'.$varr_nivel_macro[$j]['valor_num'].'" selected>'.$varr_nivel_macro[$j]['nombre'].'</option>';
                else echo '
                        <option value="'.$varr_nivel_macro[$j]['parametro_id'].'-'.$varr_nivel_macro[$j]['valor_num'].'">'.$varr_nivel_macro[$j]['nombre'].'</option>';
            }

            echo '  </select>
                    <input type="hidden" name="calificacion_macro-'.$varr_variables_macro[$i]['variable_id'].'" id="calificacion_macro-'.$varr_variables_macro[$i]['variable_id'].'" value="'.$v_califica_macro.'">
                </div>';

            if ($v_variables_count == 1 || $i == (count($varr_variables_macro) - 1)){
                echo '
            </div>';
            }

            $v_variables_count ++;

            if ($v_variables_count == 2) $v_variables_count = 0;
        }

        echo '  <input type="hidden" name="grupo_variables" id="grupo_variables" value="'.$v_grupo_variables.'">
                <input type="hidden" name="q_variables_macro" id="q_variables_macro" value="'.count($varr_variables_macro).'">';
?>

            <!--++++ Riesgo OP - Resumen de variables -->
            <div style="font-weight: bold;color:#000;font-size: 12px;margin-top: 10px;width: 100%; float: left;padding: 5px;background-color: var(--color-oro);">
                NIVEL DE RIESGO FACTUREATE
            </div>

            <!--++++ control de vencimiento del riesgo factureate -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="f_evaluacion">FECHA RIESGO</label>
                    <input type="text" name="f_evaluacion" id="f_evaluacion" class="formulario_control" value="<?=$f_riesgo?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="dias_xvencer">DIAS X VENCER</label>
                    <input type="text" name="dias_xvencer" id="dias_xvencer" class="formulario_control" style="text-align:right;" value="<?=$v_dias_xvencer_factu?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="mensaje_factu">NOTA</label>
                    <input type="text" name="mensaje_factu" id="mensaje_factu" class="formulario_control" value="<?=$mensaje_vencimiento?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="puntaje_factureate">PUNTAJE</label>
                    <input type="text" name="puntaje_factureate" id="puntaje_factureate" value="<?=number_format($v_arr_riesgos['score_factureate'],2,'.',',')?>" style="text-align: right;" class="formulario_control" readonly>
                    <input type="hidden" name="puntaje_factureate_old" id="puntaje_factureate_old" value="<?=$v_arr_riesgos['score_factureate']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="calificacion_factureate">CALIFICACION</label>
                    <input type="text" name="calificacion_factureate" id="calificacion_factureate" value="<?=$v_arr_riesgos['calificacion']?>" style="text-align: center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="nom_calificacion_factureate">NOMBRE CALIFICACION</label>
                    <input type="text" name="nom_calificacion_factureate" id="nom_calificacion_factureate" value="<?=$v_arr_riesgos['calificacion_nombre']?>" style="text-align: center;" class="formulario_control" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 20px;">
                    <label for="logo_calificacion" style="color: #fff;">LOGO</label>
                    <p id="logo_calificacion"></p>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="informe_score_file">AGREGAR INFORME</label>
                    <input type="file" name="informe_factu_file" id="informe_factu_file" class="formulario_control" style="background: #fff;">
                    <input type="hidden" name="path_factu" value="<?=$v_path_informe_factu?>">
                </div>

                <div class="formulario_grupo_row" style="width: 30px;" id="pdf_factu">

<?php
        if ($v_path_informe_factu != ''){
            echo '  <label>PDF</label>
                    <label><a href="'.$v_path_informe_factu.'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>';
        }
?>

                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label style="color:#fff;" for="btn_calculo">ACCION</label>
                    <button id="btn_calculo" style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="calculaRiesgoF()">
                        <i class="fa-regular fa-floppy-disk"></i> Calcular Riesgo
                    </button>
                </div>
            </div>

            <!--++++ bloque de resumen de variables -->
<?php
        $varr_resumen_riesgos = $obj_mae->get_resumen_variables($v_arr_riesgos['riesgo_id']);
        $v_indice = 0;
        $v_cadena_tipos = '';
        $v_descripcion = '';

        for ($i = 0; $i < count($varr_resumen_riesgos); $i++){
            if (is_null($varr_resumen_riesgos[$i]['riesgo_empresa_desc'])) $v_descripcion = '';
            else $v_descripcion = $varr_resumen_riesgos[$i]['riesgo_empresa_desc'];

            if ($v_indice == 0) echo '
            <div class="contenedor_formulario_column">';

            // resumen variable ciclo 1
            echo '
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="resumen-'.$varr_resumen_riesgos[$i]['tipo_id'].'">'.$varr_resumen_riesgos[$i]['tipo_nombre'].'</label>
                    <textarea class="formulario_control" name="resumen-'.$varr_resumen_riesgos[$i]['tipo_id'].'" id="resumen-'.$varr_resumen_riesgos[$i]['tipo_id'].'" cols="40" rows="3">'.$v_descripcion.'</textarea>
                </div>';

            if ($v_indice == 2 || $i == (count($varr_resumen_riesgos) - 1)){
                echo '
            </div>';
                $v_indice = 0;
            } else $v_indice ++;

            $v_cadena_tipos .= $varr_resumen_riesgos[$i]['tipo_id'].'-';
        }
?>

            <input type="hidden" name="cadena_tipos" id="cadena_tipos" value="<?=$v_cadena_tipos?>">

            <!--++++ bloque de justificacion -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 450px;">
                    <label for="factureate_riesgo_justi">JUSTIFICACION</label>
                    <textarea class="formulario_control" name="factureate_riesgo_justi" id="factureate_riesgo_justi" cols="70" rows="3"><?php echo $v_arr_riesgos['desc_riesgofact'];?></textarea>
                    <input type='hidden' name="factureate_riesgo_justi_old" value="<?=$v_arr_riesgos['desc_riesgofact']?>">
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label style="color:#fff;">ACCION</label>
                    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarRiesgoFactureate()">
                        <i class="fa-regular fa-floppy-disk"></i> Grabar Riesgo Factureate
                    </button>
                </div>
            </div>
            
<?php
    }
?>

            <!-- BOTONERA -->
<?php
    echo '  <div class="contenedor_formulario_column">';

            if ($_SESSION['user']['perfilid'] == 12 || $_SESSION['user']['perfilid'] == 13){       //LEGAL
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

            if ($_SESSION['user']['perfilid'] == 11 || $_SESSION['user']['perfilid'] == 6){   // COO, ANALISTA OP
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

        </div>
    </div>
    </form>

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
            let procede = 'SI';

            //++++ validacion de buro
            const emp_riesgoid = document.getElementById("emp_riesgoid").value;
            const puntaje_buro = document.getElementById("puntaje_buro").value;
            const puntaje_buro_old = document.getElementById("puntaje_buro_old").value;

            if (puntaje_buro > 0){
                //++++ hay cambios en los riesgos del buro
                if (puntaje_buro !== puntaje_buro_old){
                    alert("Idenficamos cambios en los riesgos de Buro, debe guardar los riesgos del Buro antes de guardar los riesgos Factureate");
                    procede = 'NO';
                }
            }

            //++++ variable de cumplimiento de pago
            const historia_pago = document.getElementById("historia_pago").value;
            const calificacion_pago = document.getElementById("calificacion_pago").value;
            const dias_prom_pago = document.getElementById("dias_prom_pago").value;

            //++++ variable perfil empresa
            const anhos_operando = document.getElementById("anhos_operando").value;
            const calificacion_perfil = document.getElementById("calificacion_perfil").value;
            const calificacion_tamanho = document.getElementById("calificacion_tamanho").value;
            const tamanho = document.getElementById("tamanho").value;

            //++++ variables macro
            const grupo_variables = document.getElementById("grupo_variables").value;   // cadena con las variables macro
            const variables = grupo_variables.split('-').filter(Boolean).map(Number);   // arreglo con las variables macro
            const q_variables_macro = document.getElementById("q_variables_macro").value;
            const factor_macro = 1 / Number(q_variables_macro);
            let cadena_datos_macro = '';
            let puntaje_macro = 0;
            let puntaje_macro_total = 0;

            variables.forEach(function(variable) {
                cadena_datos_macro = cadena_datos_macro + 'variable'+variable+'-'+document.getElementById("impactomacro-"+variable).value+'-'+document.getElementById("nivelmacro-"+variable).value+'-';
                puntaje_macro = document.getElementById("calificacion_macro-"+variable).value;
                puntaje_macro_total = Number(puntaje_macro_total) + (Number(puntaje_macro) * Number(factor_macro));
            });

            puntaje_macro_total = 100 - puntaje_macro_total;

            //++++ llamo al calculo de riesgo
            calculaRiesgoF();

            //++++ datos del calulo del riesgo factureate
            const puntaje_factureate = document.getElementById("puntaje_factureate").value;
            const puntaje_factureate_old = document.getElementById("puntaje_factureate_old").value;
            const informe_factureate = document.getElementById('informe_factu_file');
            const msg_factu = document.getElementById('factureate_riesgo_justi').value;

            //++++ validacion de informacion ingresada
            if (informe_factureate.value == ""){
                alert("Debe cargar el informe de analisis de Riesgo de Factureate");
                procede = "NO";
            }

            if (procede == 'SI' && msg_factu == "") {
                alert("Debe ingresar un texto de justificacion del nivel de riesgo Factureate");
                procede = "NO";
            }

            //++++ validacion de resumen macro
            const cadena_tipos = document.getElementById("cadena_tipos").value;   // cadena con los resumenes
            const arr_tipos = cadena_tipos.split('-').filter(Boolean).map(Number);   // arreglo con los tipos
            let resumen_riesgo = '';
            let resultado_resumen = '';

            arr_tipos.forEach(function(varTipo) {
                resumen_riesgo = document.getElementById("resumen-"+varTipo).value;
                
                if (resumen_riesgo == '') resultado_resumen = 'fallo';
            });

            if (resultado_resumen == 'fallo'){
                procede = 'NO';
                alert("Debe ingresar todos los resumenes de riesgo");
            }

            if (procede == "SI"){
                let formData = new FormData();

                const fileInforme = informe_factureate.files[0];
                const v_ruc = $('#ruc').val();
                const v_feval = $('#f_evaluacion').val();
                const v_empresa_id = $('#empresaid').val();
                const v_dias = $('#dias_xvencer').val();
                const v_riesgo_sec_id = document.getElementById("riesgo_id").value;
                const v_nombre_emp = $('#nombre_empresa').val();

                formData.append('origen', 'factureate')

                //datos de la variable de cumplimiento de pagos
                formData.append('historia_pago', historia_pago)
                formData.append('calificacion_pago', calificacion_pago)
                formData.append('dias_prom_pago', dias_prom_pago)

                //datos de perfil de empresa
                formData.append('tamanho', tamanho)
                formData.append('anhos_operando', anhos_operando)
                formData.append('calificacion_perfil', calificacion_perfil)

                //datos macro
                formData.append('cadena_datos_macro', cadena_datos_macro)
                formData.append('puntaje_macro_total', puntaje_macro_total)

                //datos del riesgo factureate
                formData.append('informe_factu_file', fileInforme)
                formData.append('ruc', v_ruc)
                formData.append('factureate_riesgo_justi', msg_factu)
                formData.append('f_evaluacion', v_feval)
                formData.append('empresaid', v_empresa_id)
                formData.append('dias_xvencer', v_dias)
                formData.append('riesgo_sec_id', v_riesgo_sec_id)
                formData.append('cadena_tipos', cadena_tipos)
                formData.append('puntaje_factureate', puntaje_factureate)
                formData.append('puntaje_factureate_old', puntaje_factureate_old)
                formData.append('nombre_empresa', v_nombre_emp)

                arr_tipos.forEach(function(varTipo) {
                    resumen_riesgo = document.getElementById("resumen-"+varTipo).value;
                    formData.append('resumen-'+varTipo, resumen_riesgo)
                });

                fetch("update_riesgo_proceso.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.respuesta == '0') alert('Ocurrio un error con los informes adjuntados');
                    else {
                        alert('La evaluación de riesgos fue registrada');
                        var content_pdf = document.getElementById("pdf_factu");

                        content_pdf.innerHTML = data.respuesta;

                        if (v_riesgo_id == 0){
                            var obj_riesgo_id = document.getElementById('riesgo_id');
                            obj_riesgo_id.value = data.resultado;
                        }
                    }
                })
                .catch(err => console.log(err))
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
                            //refresh_page(v_empresa_id);
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
                            //refresh_page(v_empresa_id);
                        }
                    });
                }
            }
        }

        function cerrar(){
            var v_previo = $('#previo').val();
            location.href = v_previo;
        }

        function guardarRiesgoBuro(){
            let conforme = "SI";
            const buro = document.getElementById('emp_riesgoid');
            const puntaje_buro = document.getElementById("puntaje_buro");
            const archivo = document.getElementById('informe_score_file');
            const nivel_buro = document.getElementById('nivel_score_riesgoid');
            const msg_buro = document.getElementById('score_riesgo_justi');
            
            if (buro.value == "0"){ 
                alert ("Debe seleccionar un Buro de credito");
                conforme = "NO";
            }

            if (conforme == "SI" && puntaje_buro.value == 0){
                alert("Debe ingresar el puntaje asignado en el informe del Buro");
                conforme = "NO";
            }

            if (conforme == "SI" && archivo.value == ""){
                alert("Debe cargar el informe del Score de Credito");
                conforme = "NO";
            } 

            if (conforme == "SI" && msg_buro.value == ""){ 
                alert("Debe ingresar un texto de justificacion del nivel de riesgo asignado");
                conforme = "NO";
            }

            if (conforme == "SI"){
                const v_ruc = $('#ruc').val();
                const v_nivel_riesgo = $('#nivel_score_riesgoid').val();
                const v_nivel_riesgo_old = $('#nivel_score_riesgoid_old').val();
                const v_fevaluacion = $('#f_evaluacion_score').val();
                const v_riesgo_id = $('#riesgo_id').val();
                const v_justi = $('#score_riesgo_justi').val();
                const v_emp_riesgo = $('#emp_riesgoid').val();
                const v_dias_xvencer = $('#dias_xvencer_score').val();
                const v_empresaid = $('#empresaid').val();
                const v_nombre_emp = $('#nombre_empresa').val();
                const puntaje_buro = document.getElementById("puntaje_buro").value;
                const puntaje_conv = document.getElementById("puntaje_conv").value;

                let formData = new FormData();

                formData.append('origen', 'score')
                formData.append('ruc', v_ruc)
                formData.append('nivel_score_riesgoid', v_nivel_riesgo)
                formData.append('nivel_score_riesgoid_old', v_nivel_riesgo_old)
                formData.append('f_evaluacion_score', v_fevaluacion)
                formData.append('riesgo_id', v_riesgo_id)
                formData.append('score_riesgo_justi', v_justi)
                formData.append('emp_riesgoid', v_emp_riesgo)
                formData.append('dias_xvencer_score', v_dias_xvencer)
                formData.append('empresaid', v_empresaid)
                formData.append('nombre_empresa', v_nombre_emp)
                formData.append('puntaje_buro', puntaje_buro)
                formData.append('puntaje_conv', puntaje_conv)

                const fileInforme = archivo.files[0];
                formData.append('informe_score_file', fileInforme)
                    
                fetch("update_riesgo_proceso.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.respuesta == '0') alert('Ocurrio un error con los informes adjuntados');
                    else {
                        alert('La evaluación de riesgos fue registrada');
                        let content_pdf = document.getElementById("pdf_buro");

                        content_pdf.innerHTML = data.respuesta;

                        if (v_riesgo_id == 0){
                            let obj_riesgo_id = document.getElementById('riesgo_id');
                            obj_riesgo_id.value = data.resultado;
                        }
                    }
                })
                .catch(err => console.log(err))
            }
        }

        //++++ funtion de formato a los numeros
        function formatearNumero(valor) {
            if (!valor) return "";

            let tienePuntoFinal = valor.endsWith(".");

            let partes = valor.split(".");
            let entero = partes[0];
            let decimal = partes[1] || "";

            // Formatear miles SOLO en la parte entera
            entero = entero.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            if (tienePuntoFinal) {
                return entero + ".";
            }

            return decimal ? `${entero}.${decimal}` : entero;
        }

        //++++ funcion generica para formatear numeros
        function formateaInput(input_formato, input_sinformato){
            const input = document.getElementById(input_formato);
            const input_real = document.getElementById(input_sinformato);

            const teclasPermitidas = [
                "Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"
            ];

            // GUARDAR POSICION DEL CURSOS
            let valor_original = input.value;
            let cursor = input.selectionStart;

            // Contar cuántas comas había antes del cursor
            let antesCursor = valor_original.slice(0, cursor);
            let comasAntes = (antesCursor.match(/,/g) || []).length;

            // Limpiar valor
            let limpio = valor_original.replace(/,/g, "");
            limpio = limpio.replace(/[^0-9.]/g, "");

            // Evitar múltiples puntos
            let partes = limpio.split(".");
            if (partes.length > 2) {
                limpio = partes[0] + "." + partes[1];
            }

            // Limitar decimales
            partes = limpio.split(".");
            if (partes[1]) {
                limpio = partes[0] + "." + partes[1].slice(0, 2);
            }

            // Guardar valor real
            input_real.value = limpio;

            // Formatear
            let formateado = formatearNumero(limpio);

            // Recalcular cursor
            let nuevoAntesCursor = formateado.slice(0, cursor);
            let comasDespues = (nuevoAntesCursor.match(/,/g) || []).length;

            let nuevaPos = cursor + (comasDespues - comasAntes);

            input.value = formateado;
            input.setSelectionRange(nuevaPos, nuevaPos);

            // RECALCULO DE TOTALES
            //calculatotales(0);
        }
        //++++ manejo de formato del puntaje buro
        document.getElementById("puntaje_buro_view").addEventListener("input", function (e) {
            formateaInput("puntaje_buro_view", "puntaje_buro");
        });

        function puntajeBuro(){
            var buro = document.getElementById("emp_riesgoid").value;
            var puntaje_buro = document.getElementById("puntaje_buro").value;

            if (buro > 0 && puntaje_buro > 0){
                var puntaje_conv = document.getElementById("puntaje_conv");
                var puntaje_conv_view = document.getElementById("puntaje_conv_view");
                var riesgo_buro = document.getElementById("riesgo_buro");
                var nivel_score_riesgoid = document.getElementById("nivel_score_riesgoid");

                var formaData = new FormData();

                formaData.append('buro', buro)
                formaData.append('puntaje_buro', puntaje_buro)
                formaData.append('tipo', 'buro')

                fetch("empresa_xriesgo_modal_ajax.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    puntaje_conv.value = data.puntaje_conv;
                    puntaje_conv_view.value = data.puntaje_conv_view;
                    riesgo_buro.value = data.calificacion;
                    nivel_score_riesgoid.value = data.riesgo_id;
                })
                .catch(err => console.log(err))
            } else {
                if (buro == 0) alert("Debe seleccionar la empresa Buro");
            }
        }

        function diasPromedio(){
            var dias_promedio = document.getElementById("dias_prom_pago").value;
            var historia = document.getElementById("historia_pago").value;
            var op_id = document.getElementById("empresaid").value;
            var calificacion_pago = document.getElementById("calificacion_pago");
            //historia = 'SI';
            if (historia == 'SI'){
                var formaData = new FormData();

                formaData.append('dias_promedio', dias_promedio)
                formaData.append('historia_pago', 1)
                formaData.append('op_id', op_id)
                formaData.append('tipo', 'cumplimiento_pago')

                fetch("empresa_xriesgo_modal_ajax.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    calificacion_pago.value = data.calificacion;
                })
                .catch(err => console.log(err))
            }
        }

        function calculaTamanho(){
            var tamanho_id = document.getElementById("tamanho").value;
            var rango_tamanho = document.getElementById("rango_tamanho");
            var calificacion_tamanho = document.getElementById("calificacion_tamanho");
            var factor_anhos_operando = document.getElementById("factor_anhos_operando").value;
            var calificacion_perfil = document.getElementById("calificacion_perfil");
            var calificacion_perfil_value;

            var formaData = new FormData();
            formaData.append('tamanho_id', tamanho_id)
            formaData.append('tipo', 'calcula_tamanho')

            fetch("empresa_xriesgo_modal_ajax.php", {
                method: "POST",
                body: formaData
            })
            .then(response => response.json())
            .then(data => {
                rango_tamanho.value = data.rango;
                calificacion_tamanho.value = data.calificacion_tamanho;
                calificacion_perfil_value = Number(data.calificacion_tamanho) * Number(factor_anhos_operando);
                calificacion_perfil.value = calificacion_perfil_value.toFixed(2);
            })
            .catch(err => console.log(err))
        }

        function anhosOperando(){
            var anhos_operando = document.getElementById("anhos_operando").value;
            var factor_anhos_operando = document.getElementById("factor_anhos_operando");
            var calificacion_tamanho = document.getElementById("calificacion_tamanho").value;
            var calificacion_perfil = document.getElementById("calificacion_perfil");
            var calificacion_perfil_value;

            var formaData = new FormData();
            formaData.append('anhos', anhos_operando)
            formaData.append('tipo', 'calcula_operando')

            fetch("empresa_xriesgo_modal_ajax.php", {
                method: "POST",
                body: formaData
            })
            .then(response => response.json())
            .then(data => {
                factor_anhos_operando.value = data.factor;
                calificacion_perfil_value = Number(calificacion_tamanho) * Number(data.factor);
                calificacion_perfil.value = calificacion_perfil_value.toFixed(2);
            })
            .catch(err => console.log(err))
        }

        function calculaRiesgoF(){
            // cumplimiento pago
            const historia_pago = document.getElementById("historia_pago").value;
            const calificacion_pago = document.getElementById("calificacion_pago").value;

            // perfil empresa
            const calificacion_perfil = document.getElementById("calificacion_perfil").value;

            // buro
            const emp_riesgoid = document.getElementById("emp_riesgoid").value;
            const puntaje_buro = document.getElementById("puntaje_buro").value;
            const puntaje_buro_conv = document.getElementById("puntaje_conv").value;

            // macroeconomico
            const grupo_variables = document.getElementById("grupo_variables").value;
            const variables = grupo_variables.split('-').filter(Boolean).map(Number);
            let puntaje_macro_total = 0;
            let puntaje_macro;
            const q_variables_macro = document.getElementById("q_variables_macro").value;
            const factor_macro = 1 / Number(q_variables_macro);

            variables.forEach(function(variable) {
                puntaje_macro = document.getElementById("calificacion_macro-"+variable).value;
                puntaje_macro_total = Number(puntaje_macro_total) + (Number(puntaje_macro) * Number(factor_macro));
            });

            puntaje_macro_total = 100 - puntaje_macro_total;

            // variables de la calificacion factureate
            let puntaje_f =document.getElementById("puntaje_factureate");
            let calificacion_f = document.getElementById("calificacion_factureate");
            let nom_calificacion_f = document.getElementById("nom_calificacion_factureate");
            let logo_calificacion = document.getElementById("logo_calificacion");

            const empresa_id = document.getElementById("empresaid").value;

            // calculo de riesgo op
            let formaData = new FormData();
            formaData.append('historia_pago', historia_pago)
            formaData.append('calificacion_pago', calificacion_pago)
            formaData.append('calificacion_perfil', calificacion_perfil)
            formaData.append('emp_riesgoid', emp_riesgoid)
            formaData.append('puntaje_buro', puntaje_buro)
            formaData.append('puntaje_buro_conv', puntaje_buro_conv)
            formaData.append('puntaje_macro', puntaje_macro_total)
            formaData.append('empresa_id', empresa_id)
            formaData.append('tipo', 'calcula_riesgo_op')

            fetch("empresa_xriesgo_modal_ajax.php", {
                method: "POST",
                body: formaData
            })
            .then(response => response.json())
            .then(data => {
                puntaje_f.value = data.puntaje_f;
                calificacion_f.value = data.calificacion_f;
                nom_calificacion_f.value = data.nom_calificacion_f;
                logo_calificacion.innerHTML = data.logo_calificacion;
            })
            .catch(err => console.log(err))
        }

        function variableMacro(p_tipo, variable_id){
            const impacto_cadena = document.getElementById("impactomacro-"+variable_id).value;
            const nivel_cadena = document.getElementById("nivelmacro-"+variable_id).value;

            const valores_impacto = impacto_cadena.split("-");
            const valores_nivel = nivel_cadena.split("-");

            const impacto = valores_impacto[1];
            const nivel = valores_nivel[1];

            let calificacion = (Number(impacto) / 10) * (100 - Number(nivel));
            let calificacion_macro = document.getElementById("calificacion_macro-"+variable_id);
            calificacion_macro.value = calificacion;
        }
    </script>

</BODY>
</HTML>