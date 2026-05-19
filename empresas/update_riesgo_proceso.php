<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$obj_mail = new mail_util;
$obj_seg = new seguridad;

$v_con_cambios = 0;
date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy_esp = date('Y-m-d');
$output = [];

if ($_POST['origen'] == 'score'){
    if (isset($_FILES['informe_score_file']) && $_FILES['informe_score_file']['name'] != ''){
        $v_con_cambios = 1;
        $v_path_carpeta = '../archivos/empresa_'.$_POST['ruc'].'/evaluacion_riesgo';

        if (!is_dir($v_path_carpeta)) mkdir($v_path_carpeta, 0777, true);   //verifica existencia de carpeta

        $v_path = '../archivos/empresa_'.$_POST['ruc'].'/evaluacion_riesgo/'.$v_hoy_esp.'_buro_'.$_FILES['informe_score_file']['name'];
        move_uploaded_file($_FILES['informe_score_file']['tmp_name'],  $v_path);

        $v_arr_riesgos = array('riesgo_id'=>$_POST['nivel_score_riesgoid'], 'f_evaluacion'=>$_POST['f_evaluacion_score'], 'id'=>$_POST['riesgo_id'],
                                'descripcion'=>$_POST['score_riesgo_justi'],'empresa_score_id'=>$_POST['emp_riesgoid'], 'informe_riesgo'=>$v_path,
                                'tipo_riesgo'=>$_POST['origen'], 'dias_xvencer'=>$_POST['dias_xvencer_score'], 'empresa_id'=>$_POST['empresaid']);

        $v_subject = 'Se actualizo el riesgo del Score de credito de la empresa '.$_POST['nombre_empresa'];
        $v_body = 'Se actualizo el riesgo del BURO de credito de la empresa '.$_POST['nombre_empresa'].' con ID '.$_POST['ruc'];

        /*$v_resultado = '<label>PDF</label>
                    <label><a href="'.$v_path.'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>';*/
        $output['respuesta'] = '<label>PDF</label>
                    <label><a href="'.$v_path.'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>';
    } else { 
        $v_con_cambios = 0;
        //$v_resultado = "0";
        $output['respuesta'] = '0';
    }
} else{
    if (isset($_FILES['informe_factu_file']) && $_FILES['informe_factu_file']['name'] != ''){
        $v_con_cambios = 1;
        $v_path_carpeta = '../archivos/empresa_'.$_POST['ruc'].'/evaluacion_riesgo';

        if (!is_dir($v_path_carpeta)) mkdir($v_path_carpeta, 0777, true);   //verifica existencia de carpeta

        $v_path = $v_path_carpeta.'/'.'_'.$_FILES['informe_factu_file']['name'];
        move_uploaded_file($_FILES['informe_factu_file']['tmp_name'],  $v_path);

        $v_arr_riesgos = array('riesgo_id'=>$_POST['nivel_factureate_riesgoid'], 'f_evaluacion'=>$_POST['f_evaluacion'], 'id'=>$_POST['riesgo_id'],
                                'descripcion'=>$_POST['factureate_riesgo_justi'],'empresa_score_id'=>0, 'informe_riesgo'=>$v_path,
                                'tipo_riesgo'=>$_POST['origen'], 'dias_xvencer'=>$_POST['dias_xvencer'], 'empresa_id'=>$_POST['empresaid']);

        $v_subject = 'Se actualizo el riesgo Factureate de la empresa '.$_POST['nombre_empresa'];
        $v_body = 'Se actualizo el riesgo Factureate de la empresa '.$_POST['nombre_empresa'].' con ID '.$_POST['ruc'];
        
        $output['respuesta'] = '<label>PDF</label>
                    <label><a href="'.$v_path.'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>';
    } else {
        $v_con_cambios = 0;
        //$v_resultado = "0";
        $output['respuesta'] = '0';
    }
}

if ($v_con_cambios == 1){
    $v_riesgo_id = $obj_mae->update_riesgos($v_arr_riesgos);

    $arr_email = array('perfilid' => 9, 'nombre_salida' => 'FACTUREATE Riesgos',
                        'subject' => $v_subject,
                        'body' => $v_body.'<br><br>Operaciones - FACTUREATE');
    
    /*=============================================
    ==== envio de correo al personal de factureate
    ===============================================*/
    $obj_mail->enviar_correo_xperfil($arr_email);

    $obj_mail->enviar_notificacion_externo(24);     // NOTIFICACION EXTERNA DE RECHAZOS
    $obj_mail->enviar_multicorreo_interno(25);      // NOTIFICACION INTERNA DE RECHAZOS
    $obj_mail->enviar_multicorreo_interno(26);      // NOTIFICACION INTERNA DE ACEPTACION

    //==== cambio de calificacion de BURO
    if ($v_arr_riesgos['tipo_riesgo'] == 'score'){
        if ($_POST['nivel_score_riesgoid'] != $_POST['nivel_score_riesgoid_old']){
            $varr_nivel = $vobj_mae->get_detalle_nivel_riesgo($_POST['nivel_score_riesgoid']);
            $varr_nivel_old = $vobj_mae->get_detalle_nivel_riesgo($_POST['nivel_score_riesgoid_old']);

            $varr_notimail = array( 'notificaid' => 41, 
                                    'datos_body' => '<br>Empresa: '.$_POST['nombre_empresa'].'<br>Calificacion inicial: '.$varr_nivel['nombre'].' ['.$varr_nivel['calificacion'].']<br>Calificacion final: '.$varr_nivel_old['nombre'].' ['.$varr_nivel_old['calificacion'].']<br><br>Factureate App');
            $obj_mail->enviar_correo_xnotificacion($varr_notimail);       // envio de notificacion por cambio de calificacion de BURO
        }
    } else {
        if (($_POST['nivel_factureate_riesgoid'] != $_POST['nivel_factureate_riesgoid_old']) && $_POST['nivel_factureate_riesgoid_old'] > 0){
            $varr_nivel = $vobj_mae->get_detalle_nivel_riesgo($_POST['nivel_factureate_riesgoid']);
            $varr_nivel_old = $vobj_mae->get_detalle_nivel_riesgo($_POST['nivel_factureate_riesgoid_old']);

            $varr_notimail = array( 'notificaid' => 42, 
                                    'datos_body' => '<br>Empresa: '.$_POST['nombre_empresa'].'<br>Calificacion inicial: '.$varr_nivel['nombre'].' ['.$varr_nivel['calificacion'].']<br>Calificacion final: '.$varr_nivel_old['nombre'].' ['.$varr_nivel_old['calificacion'].']<br><br>Factureate App');
            $obj_mail->enviar_correo_xnotificacion($varr_notimail);       // envio de notificacion por cambio de calificacion de BURO
        }
    }
    
    $output['resultado'] = $v_riesgo_id;
} else $output['resultado'] = 0;

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>