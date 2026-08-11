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
require("../lib-xlsx/SimpleXLSXGen.php");
use Shuchkin\SimpleXLSXGen;

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
    //==== aprobacion legal
    $arr_datos = array('accion' => 'aprobar', 'empresaid' => $_POST['empresaid']);

    if ($_POST['t_empresaid'] == 48 || $_POST['t_empresaid'] == 50 || $_POST['t_empresaid'] == 51 || $_POST['t_empresaid'] == 52){
        // 48 = OP, 50 = EMISOR y OP,  51 = INVERSOR OP, 52 = EMISOR INVERSOR OP
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
        $v_carpeta = '../pdf/EMP_'.$_POST['nombre_empresa'].'_'.$_POST['ruc'];

        if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
        
        $v_carpeta .= '/legal';
        $v_archivo = $v_carpeta.'/'.$v_hoy.'_'.$_FILES['informe_legal']['name'];
        
        if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
        move_uploaded_file($_FILES['informe_legal']['tmp_name'], $v_archivo);

        $varr_datos = array('informe_legal'=>$v_archivo, 'tipo_empresa'=>$_POST['t_empresaid'], 'empresa_id'=>$_POST['empresaid'], 'valida_url_contrato'=>$_POST['valida_contrato'],
                            'url_contrato'=>$_POST['url_contrato']);

        $obj_mae->aprueba_empresa($varr_datos);

        //=== VALIDAR PROCESO DE ENVIO DE CONTRATO
        $varr_param_contratodigi = $obj_mae->get_parametro_detalle(84);
        $varr_empresa = $obj_mae->get_datos_emisor_full($_POST['empresaid']);

        if ($varr_param_contratodigi['valornum'] == 0){
            // ENVIO DEL CORREO A LA EMPRESA
            $v_link = $_POST['url_contrato'];

            $arr_mail_user = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                    'mail_destino' => $varr_empresa['email_contacto'],
                                    'subject' => 'VINCULACION CON FACTUREATE',
                                    'body' => 'Su solicitud de vinculacion para vender sus facturas en FACTUREATE ha sido aprobada.<br><br>
                                                Empresa: '.$varr_empresa['nombre'].'<br>
                                                NIT: '.$varr_empresa['identificacion'].'<br><br>
                                                Lo siguiente que debe hacer es revisar y firmar el contrato de vinculacion mediante el siguiente link donde no debe ingresar 
                                                ninguna informacion confidencial solo firmar biometricamente, le recomendamos leer el documento antes de firmar en el siguiente link.<br>
                                                <a href="'.$v_link.'" target="_blank">CONTRATO CON FACTUREATE</a><br><br>
                                                FACTUREATE');
        
            $obj_mail->enviar_correo($arr_mail_user);
        }
        $rpta = 'paso aprobacion/';
        //=== GESTION DE PROVEEDOR DE ENDOSO
        $varr_param_proveendoso = $obj_mae->get_parametro_detalle(85);

        if ($varr_param_proveendoso['valornum'] == 1){
            //==== proveedor de endoso es parte del proceso
            $varr_param_mailatm = $obj_mae->get_parametro_detalle(86);
            $rpta .= 'entro proveedor/';
            if ($varr_param_mailatm['valornum'] == 1){
                //=== genero el correo que se debe enviar al proveedor, enviar desde operacion_radian_col@
                //===== genero del excel que se debe enviar al proveedor
                $varr_param_factu = $obj_mae->get_parametro_detalle(66);
                $varr_param_factu_docu = $obj_mae->get_parametro_detalle(67);
                $varr_param_factu_repre = $obj_mae->get_parametro_detalle(87);
                $varr_param_factu_tdocrepre = $obj_mae->get_parametro_detalle(88);
                $varr_param_factu_nrorepre = $obj_mae->get_parametro_detalle(89);
                $varr_param_factu_mailrepre = $obj_mae->get_parametro_detalle(90);

                $rpta .= 'entro xls';
                $xlsx_vinculacion_data = [
                    ['Nombre / Razón Social Mandante', 'Tipo documento de identidad Mandante', 'Número de documento de identidad Mandante', 'Domicilio Mandante', 
                    'Nombre Representante Legal Mandante', 'Tipo documento de identidad Representante Legal Mandante','Número documento de identidad Representante Legal Mandante',
                    'Telefono Mandante','Dirección Mandante', 'E-mail Representante Legal Mandante', 
                    'Nombre / Razón Social Factor','Tipo documento de identidad Factor','Número de documento de identidad Factor','Nombre Representante Legal Factor',
                    'Tipo documento de identidad Representante Legal Factor','Número documento de identidad Representante Factor','E-mail Representante Legal Factor'],
                    [$_POST['nombre_empresa'], 'NIT', $_POST['ruc'], $varr_empresa['direccion'], 
                    $varr_empresa['nombre_repre'], $varr_empresa['doc_repre'], $varr_empresa['nrodoc_repre'],
                    $varr_empresa['telf_contacto'], $varr_empresa['direccion'], $varr_empresa['email_repre'], 
                    $varr_param_factu['valorchar'], 'NIT', $varr_param_factu_docu['valorchar'], $varr_param_factu_repre['valorchar'],
                    $varr_param_factu_tdocrepre['valorchar'], $varr_param_factu_nrorepre['valorchar'], $varr_param_factu_mailrepre['valorchar']]
                ];

                $xlsx_vinculacion = SimpleXLSXGen::fromArray($xlsx_vinculacion_data);
                // guardado en el servidor
                $v_carpeta = '../pdf/EMP_'.$_POST['nombre_empresa'].'_'.$_POST['ruc'].'/vinculacion';
                if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
                $v_archivo_xlsx = $v_carpeta.'/base de solicitud.xlsx';

                $xlsx_vinculacion->saveAs($v_archivo_xlsx);
                //===== fin de generacion del xlsx
                $arr_mail_proveedor = array(
                                    'mail_salida' => 'operacion_radian_col@factureate.com', 
                                    'nombre_salida' => 'FACTUREATE',
                                    'mail_destino' => $varr_param_mailatm['valorchar'],
                                    'subject' => 'Solicitud de creacion de mandato',
                                    'body' => 'Sollicitud de creacion de mandato para nuestro cliente, cuyos datos son los siguiente.<br><br>
                                                Empresa: '.$varr_empresa['nombre'].'<br>
                                                NIT: '.$varr_empresa['identificacion'].'<br><br>
                                                * Tildes omitidas itencionalmente<br><br>
                                                FACTUREATE',
                                    'attach_nombre1' => 'Certificado de Camara de Comercio.pdf',
                                    'attach1' => $varr_empresa['vigencia_path'],
                                    'attach_nombre2' => 'Base de solicitud.xlsx',
                                    'attach2' => $v_archivo_xlsx);
        
                $obj_mail->enviar_correo_attach($arr_mail_proveedor);
            }
        }

        //echo 'okaprobacionlegal';
        echo $rpta;
    }

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
    $v_carpeta = '../pdf/EMP_'.$_POST['nombre_empresa'].'_'.$_POST['ruc'].'/legal';
    $v_archivo = $v_carpeta.'/'.$v_hoy.'_'.$_FILES['file_contrato_vinculacion']['name'];

    if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
    move_uploaded_file($_FILES['file_contrato_vinculacion']['tmp_name'], $v_archivo);

    if ($_POST['contrato_provee'] == 1){
        $v_archivo_provee = $v_carpeta.'/'.$_FILES['file_contrato_provee_endoso']['name'];
        move_uploaded_file($_FILES['file_contrato_provee_endoso']['tmp_name'], $v_archivo_provee);
    }

    $v_arr_datos = array('empresa_id'=>$_POST['empresaid'], 'link_contrato' => $v_archivo, 'link_contrato_provee' => $v_archivo_provee);

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
