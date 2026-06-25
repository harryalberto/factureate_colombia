<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");

/*#######################################################################
------ LOGICA NO VISIBLE 
#######################################################################*/
$obj_mae = new maestros;
$obj_mail = new mail_util;
$obj_seg = new seguridad;

date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy = date('d-m-Y');

if ($_POST['accion'] == 'aprobar_legal_op'){
    $v_carpeta = '../archivos/empresa_'.$_POST['ruc'].'/legal';
    $v_archivo = $v_carpeta.'/'.$_FILES['informe_legal']['name'];
    
    if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
    move_uploaded_file($_FILES['informe_legal']['tmp_name'], $v_archivo);

    $varr_datos = array('empresa_id'=>$_POST['empresaid'], 'nombre_repre'=>$_POST['nombre_repre'], 'email_repre'=>$_POST['email_repre'], 'tdoc_repre_id'=>$_POST['tdoc_repre'],
                        'nrodoc_repre'=>$_POST['nrodoc_repre'], 'telefono_repre'=>$_POST['telefono_repre'], 'informe_legal'=>$v_archivo, 'tempresa'=>'OP');
    $obj_mae->actualiza_emisor($varr_datos,'LEGAL');

    echo 'Se aprobo legalmente el obligado al pago!!';
} elseif ($_POST['accion'] == 'aprobar'){
    $arr_datos = array('accion' => 'aprobar', 'empresaid' => $_POST['empresaid']);

    if ($_POST['t_empresaid'] == 48 || $_POST['t_empresaid'] == 50 || $_POST['t_empresaid'] == 51 || $_POST['t_empresaid'] == 52){
    // empresa obligada al pago que debe tener calificacion de riesgos
        $con_cambios = 0;
        if ($_POST['emp_riesgoid'] != $_POST['emp_riesgoid_old']) $con_cambios = 1;
        if ($_POST['nivel_score_riesgoid'] != $_POST['nivel_score_riesgoid_old']) $con_cambios = 1;
        if ($_POST['score_riesgo_justi'] != $_POST['score_riesgo_justi_old']) $con_cambios = 1;
        if ($_POST['nivel_factureate_riesgoid'] != $_POST['nivel_factureate_riesgoid_old']) $con_cambios = 1;
        if ($_POST['factureate_riesgo_justi'] != $_POST['factureate_riesgo_justi_old']) $con_cambios = 1;
        if (isset($_FILES['informe_factu_file']) && $_FILES['informe_factu_file']['name'] != ''){
            $con_cambios = 1;
            $path_factu = '../archivos/riesgos_emp_'.$_POST['ruc'].'/'.$_FILES['informe_factu_file']['name'];
            move_uploaded_file($_FILES['informe_factu_file']['tmp_name'],  $path_factu);
        } else $path_factu = $_POST['path_factu'];
        if (isset($_FILES['informe_score_file']) && $_FILES['informe_score_file']['name'] != ''){
            $con_cambios = 1;
            $path_score = '../archivos/riesgos_emp_'.$_POST['ruc'].'/'.$_FILES['informe_score_file']['name'];
            move_uploaded_file($_FILES['informe_score_file']['tmp_name'],  $path_score);
        } else $path_score = $_POST['path_score'];

        if ($con_cambios == 1){     // hay cambios en los riesgos
            $arr_input = array('empresaid' => $_POST['empresaid'], 'scoreid' => $_POST['emp_riesgoid'],
                                'nivel_scoreid' => $_POST['nivel_score_riesgoid'], 'justi_nivel_score' => $_POST['score_riesgo_justi'],
                                'nivel_factuid' => $_POST['nivel_factureate_riesgoid'], 'justi_nivel_factu' => $_POST['factureate_riesgo_justi'],
                                'path_score' => $path_score, 'path_factu' => $path_factu);
            $obj_mae->registra_riesgos($arr_input);
        }
        // actualizo los datos de la empresa
        $arr_input_emp = array('empresaid' => $_POST['empresaid'], 'direccion' => $_POST['direccion'],
                                'tamanoid' => $_POST['tamanoid'], 'sectorid' => $_POST['sectorid'],
                                'actividad' => $_POST['actividad']);
        $obj_mae->actualiza_emisor($arr_input_emp,'info_empresa');
        $arr_email = array('perfilid' => 9, 'nombre_salida' => 'FACTUREATE Registro',
                        'subject' => 'Se dio de alta una nueva empresa',
                        'body' => 'La empresa '.$_POST['nombre_empresa'].' con DOC '.$_POST['ruc'].' a sido dado de alta.<br><br>FACTUREATE PERU');
        $mensaje = 'La empresa fue aprobada  ...';
    } else{
    // EMISOR 
        if (!is_dir('../pdf/empresa_'.$_POST['ruc'])) mkdir('../pdf/empresa_'.$_POST['ruc'], 0777, true);

        $v_carpeta = '../pdf/empresa_'.$_POST['ruc'].'/legal';
        $v_archivo = $v_carpeta.'/'.$v_hoy.'_'.$_FILES['informe_legal']['name'];
        //$v_archivo_link = 'https://factureate.com/plataforma-rd/pdf/empresa_'.$_POST['ruc'].'/legal/'.$_FILES['informe_legal']['name'];

        if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
        move_uploaded_file($_FILES['informe_legal']['tmp_name'], $v_archivo);

        $varr_datos = array('informe_legal'=>$v_archivo, 'tipo_empresa'=>$_POST['t_empresaid'], 'empresa_id'=>$_POST['empresaid'], 'valida_url_contrato'=>$_POST['valida_contrato'],
                            'url_contrato'=>$_POST['url_contrato']);

        $obj_mae->aprueba_empresa($varr_datos);
        echo 'ok';
    }

    //$obj_mae->gestiona_empresa($arr_datos);     // cambia el estado
    // correo a los gerentes de operaciones
    //$obj_mail->enviar_correo_xperfil($arr_email);    
    $redireccion = '<script>
                        setTimeout(function(){location.href = "'.$_POST['previo'].'";},1000);
                    </script>';
} elseif ($_POST['accion'] == 'grabar' || $_POST['accion'] == 'grabaraprobar'){
    $v_arr_datos = array('identificacion'=>$_POST['id_empresa'], 'nombre'=>$_POST['nombre_empresa'], 'direccion'=>$_POST['direccion'],
                        'sectorid'=>$_POST['sectorid'], 'tamanoid'=>$_POST['tamanoid'], 'actividad'=>$_POST['actividad'],
                        'nombre_repre'=>'', 'email_repre'=>'', 'tipodoc_repre'=>0, 'nrodoc_repre'=>'', 'pathdoc_repre'=>'',
                        'pathvigencia_repre'=>'', 'nombre_contacto'=>$_POST['nombre_contacto'], 'email_contacto'=>$_POST['email_contacto'],
                        'tipodoc_contacto'=>$_POST['tdoc_contacto'], 'nrodoc_contacto'=>$_POST['nrodoc_contacto'], 'telefono_contacto'=>$_POST['telefono_contacto'],
                        'tipo_empresa'=>$_POST['tipo_empresa_id']);
    $v_empresa_id = $obj_mae->registro_empresa($v_arr_datos);
    
    if ($v_empresa_id > 0){
        if ($_POST['accion'] == 'grabaraprobar'){
            $v_arr_datos['empresaid'] = $v_empresa_id;
            $v_arr_datos['accion'] = 'aprobar';
            $v_arr_datos['motivo'] = '';
            $obj_mae->gestiona_empresa($v_arr_datos);   // aprobacion de la empresa
            $arr_email = array('perfilid' => 9, 'nombre_salida' => 'FACTUREATE Registro', 'subject' => 'Se dio de alta una nueva empresa',
                            'body' => 'La empresa '.$_POST['nombre_empresa'].' con DOC '.$_POST['id_empresa'].' a sido dado de alta.<br><br>FACTUREATE OPERACIONES COLOMBIA');
            $mensaje = 'La empresa fue aprobada  ...';
            $obj_mail->enviar_correo_xperfil($arr_email);    
            $redireccion = '<script>
                                setTimeout(function(){location.href = "'.$_POST['previo'].'";},1000);
                            </script>';
        } else {
            if ($_POST['tipo_empresa_id'] == 48){
            // OBLIGADO AL PAGO
                $varr_correo = array('notificaid' => 28, 'datos_body' => '<br><br>NOMBRE: '.$_POST['nombre_empresa'].'<br>ID EMPRESA: '.$_POST['id_empresa'].'<br><br>FACTUREATE');

                $obj_mail->enviar_correo_xnotificacion($varr_correo);
            }
        }
    }

    echo $v_empresa_id;
} elseif ($_POST['accion'] == 'reg_contrato'){  // REGISTRO DEL CONTRATO DE VINCULACION
    $v_carpeta = '../pdf/empresa_'.$_POST['ruc'].'/legal';
    $v_archivo = $v_carpeta.'/'.$v_hoy.'_'.$_FILES['file_contrato_vinculacion']['name'];
    //$v_archivo_link = 'https://factureate.com/plataforma-rd/pdf/empresa_'.$_POST['ruc'].'/legal/'.$_FILES['file_contrato_vinculacion']['name'];

    if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

    move_uploaded_file($_FILES['file_contrato_vinculacion']['tmp_name'], $v_archivo);

    $v_arr_datos = array('empresa_id'=>$_POST['empresaid'], 'link_contrato' => $v_archivo);

    $obj_mae->registra_contrato_vinculacion($v_arr_datos);
    echo 'ok';
} elseif ($_POST['accion'] == 'update'){
    if ($_POST['t_empresaid'] == 48){   // OBLIGADO AL PAGO
        $varr_datos = array('direccion'=>$_POST['direccion'], 'tamano_id'=>$_POST['tamanoid'], 'paginaweb'=>$_POST['paginaweb'], 'actividad_id'=>$_POST['sectorid'],
                            'actividad'=>$_POST['actividad'], 'contacto_nom'=>$_POST['nombre_contacto'], 'contacto_mail'=>$_POST['email_contacto'],
                            'contactodoc_id'=>$_POST['tdoc_contacto'], 'contactodoc'=>$_POST['nrodoc_contacto'], 'contacto_telf'=>$_POST['telefono_contacto'],
                            'empresa_id'=>$_POST['empresaid']);

        $varr_permisos = $obj_seg->get_permisos($_SESSION['user']['perfilid']);

        if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'EMP-UPD-OP')){ // COO, ANALISTA OP
            $obj_mae->update_obligado_pago($varr_datos);
        }
    }

    if ($_POST['estado_id'] != 3) $obj_mae->calcula_estado_empresa($_POST['empresaid']);
    
    $obj_mail->enviar_notificacion_externo(24);     // NOTIFICACION EXTERNA DE RECHAZOS
    $obj_mail->enviar_multicorreo_interno(25);      // NOTIFICACION INTERNA DE RECHAZOS
    $obj_mail->enviar_multicorreo_interno(26);      // NOTIFICACION INTERNA DE ACEPTACION

    echo 'Informacion operativa guardada';
}
//#######################################################################
?>
