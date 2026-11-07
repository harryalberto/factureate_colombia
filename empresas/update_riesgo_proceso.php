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

        //++++ validacion de la carpeta
        $v_path_carpeta = '../archivos/empresa_'.$_POST['ruc'];
        if (!is_dir($v_path_carpeta)) mkdir($v_path_carpeta, 0777, true);   //verifica existencia de carpeta
        $v_path_carpeta = '../archivos/empresa_'.$_POST['ruc'].'/evaluacion_riesgo';
        if (!is_dir($v_path_carpeta)) mkdir($v_path_carpeta, 0777, true);   //verifica existencia de carpeta

        //++++ guardado del archivo de informe
        $v_path = '../archivos/empresa_'.$_POST['ruc'].'/evaluacion_riesgo/'.$v_hoy_esp.'_buro_'.$_FILES['informe_score_file']['name'];
        move_uploaded_file($_FILES['informe_score_file']['tmp_name'],  $v_path);

        //++++ arreglo que se usara para el guardado
        $v_arr_riesgos = array( 'riesgo_id'=>$_POST['nivel_score_riesgoid'],    'f_evaluacion'=>$_POST['f_evaluacion_score'],       'id'=>$_POST['riesgo_id'],
                                'descripcion'=>$_POST['score_riesgo_justi'],    'empresa_score_id'=>$_POST['emp_riesgoid'],         'informe_riesgo'=>$v_path,
                                'tipo_riesgo'=>$_POST['origen'],                'dias_xvencer'=>$_POST['dias_xvencer_score'],       'empresa_id'=>$_POST['empresaid'],
                                'puntaje_buro' => $_POST['puntaje_buro'],       'puntaje_conv' => $_POST['puntaje_conv']
                            );

        //++++ datos del correo a enviar
        $v_body = 'Se actualizo el riesgo del BURO de credito de la empresa '.$_POST['nombre_empresa'].' con ID '.$_POST['ruc'];

        $arr_email_notificacion = array('notificaid' => 47,
                                        'datos_body' => '<br>Empresa: '.$_POST['nombre_empresa'].' <br> ID: '.$_POST['ruc']);

        $output['respuesta'] = '<label>PDF</label>
                    <label><a href="'.$v_path.'" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></label>';
    } else { 
        $v_con_cambios = 0;
        $output['respuesta'] = '0';
    }
} else{
    //++++ actualizacion de los riesgos factureate
    if (isset($_FILES['informe_factu_file']) && $_FILES['informe_factu_file']['name'] != ''){
        // el informe esta adjunto quiere decir para paso la comprobacion
        $v_con_cambios = 1;

        // guardo el informe de factureate
        $v_path_carpeta = '../archivos/empresa_'.$_POST['ruc'].'/evaluacion_riesgo';

        if (!is_dir($v_path_carpeta)) mkdir($v_path_carpeta, 0777, true);   //verifica existencia de carpeta

        $v_path = $v_path_carpeta.'/'.'_'.$_FILES['informe_factu_file']['name'];
        move_uploaded_file($_FILES['informe_factu_file']['tmp_name'],  $v_path);

        // guardo la variable de cumplimiento de pago
        $varr_cumplimiento_pago = array(    'historia_pago' => $_POST['historia_pago'],         'calificacion_pago' => $_POST['calificacion_pago'],
                                            'dias_promedio_pago' => $_POST['dias_prom_pago'],   'tipo_variable' => 'CUMPLIMIENTO PAGO',
                                            'empresa_id' => $_POST['empresaid'],                'puntaje_cumplimiento_pago' => $_POST['calificacion_pago']
                                        );

        $obj_mae->guarda_riesgos_variables_op($varr_cumplimiento_pago);

        // guardo la variable perfil empresa
        $varr_perfil = array(   'tipo_variable' => 'PERFIL EMPRESA',            'calificacion_perfil' => $_POST['calificacion_perfil'],
                                'anhos_operando' => $_POST['anhos_operando'],   'tamanho' => $_POST['tamanho'],
                                'empresa_id' => $_POST['empresaid']
                            );

        $obj_mae->guarda_riesgos_variables_op($varr_perfil);

        // guardo las variables macro economicas
        $varr_macro = array( 'tipo_variable' => 'MACRO',                             'cadena_macro' => $_POST['cadena_datos_macro'],
                            'puntaje_macro_total' => $_POST['puntaje_macro_total'], 'empresa_id' => $_POST['empresaid']
                        );

        $obj_mae->guarda_riesgos_variables_op($varr_macro);

        //++++ genero el arreglo para guardar los datos del riesgo factureate
        $v_arr_riesgos = array( 'id' => $_POST['riesgo_sec_id'],                    'f_evaluacion'=>$_POST['f_evaluacion'],
                                'descripcion'=>$_POST['factureate_riesgo_justi'],   'empresa_score_id'=>0,                              'informe_riesgo'=>$v_path,
                                'tipo_riesgo'=>$_POST['origen'],                    'dias_xvencer'=>$_POST['dias_xvencer'],             'empresa_id'=>$_POST['empresaid'],
                                'puntaje_factureate' => $_POST['puntaje_factureate']
                            );

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
    //++++ registro la evaluacion de riesgo
    $v_riesgo_id = $obj_mae->update_riesgos($v_arr_riesgos);    // devuelve el id secuencial

    //++++ registro el resumen de riesgos factureate
    if ($_POST['origen'] == 'factureate'){
        // resumenes
        $v_cadena_resumen = $_POST['cadena_tipos'];
        $v_cadena_resumen = rtrim($v_cadena_resumen, '-');
        $varr_resumen = explode('-', $v_cadena_resumen);

        foreach ($varr_resumen as $dato){
            $obj_mae->guarda_resumen_riesgos($v_riesgo_id, $dato, $_POST['resumen-'.$dato]);
        }
    }

    //+++ envio de correo al personal de factureate
    //$obj_mail->enviar_correo_xnotificacion($arr_email_notificacion);
    
    $output['resultado'] = $v_riesgo_id;
} else $output['resultado'] = 0;

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>