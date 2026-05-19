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
$arr_empresa = $obj_mae->get_datos_emisor_full($_SESSION['user']['empresaid']);
date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy = date('Y-m-d');

if ($_POST['accion'] == 'grabar'){
    if (isset($_FILES['file_dni']) && $_FILES['file_dni']['name'] != ''){ //coloco el archivo de la solicitud en el servidor
        $carpeta_dni_path = '../archivos/empresa_'.$arr_empresa['identificacion'].'/legal';

        if (!is_dir($carpeta_dni_path)) mkdir($carpeta_dni_path, 0777, true);

        $file_dni_path = $carpeta_dni_path.'/doc_repre_'.$v_hoy.'_'.$_FILES['file_dni']['name'];
        move_uploaded_file($_FILES['file_dni']['tmp_name'],  $file_dni_path);
    } else $file_dni_path = '';

    if (isset($_FILES['file_poderes']) && $_FILES['file_poderes']['name'] != ''){ //coloco el archivo de la solicitud en el servidor
        $carpeta_pod_path = '../archivos/'.'empresa_'.$arr_empresa['identificacion'].'/legal';

        if (!is_dir($carpeta_pod_path)) mkdir($carpeta_pod_path, 0777, true);

        $file_pod_path = $carpeta_pod_path.'/doc_poderes_'.$v_hoy.'_'.$_FILES['file_poderes']['name'];
        move_uploaded_file($_FILES['file_poderes']['tmp_name'],  $file_pod_path);
    } else $file_pod_path = '';

    $arr_nuevos_datos = array('emisorid' => $_SESSION['user']['empresaid'], 'direccion' => $_POST['direccion'],'tamanoid' => $_POST['tamanoid'], 
                            'sectorid' => $_POST['sectorid'],'actividad' => $_POST['actividad'], 'nombre_repre' => $_POST['nombre_repre'],
                            'email_repre' => $_POST['email_repre'], 'tdoc_repre' => $_POST['tdoc_repre'],'nrodoc_repre' => $_POST['nrodoc_repre'], 
                            'docrepre_path' => $_POST['docrepre_path'],'docrepre_file' => $file_dni_path, 'podrepre_file' => $file_pod_path,
                            'podrepre_path' => $_POST['poderes_path'], 'nombre_contacto' => $_POST['nombre_contacto'],'email_contacto' => $_POST['email_contacto'], 
                            'tdoc_contacto' => $_POST['tdoc_contacto'],'nrodoc_contacto' => $_POST['nrodoc_contacto'], 'telefono_contacto' => $_POST['telefono_contacto'],
                            'direccion_old' => $_POST['direccion_old'],'tamanoid_old' => $_POST['tamanoid_old'], 'sectorid_old' => $_POST['sectorid_old'],
                            'actividad_old' => $_POST['actividad_old'], 'nombre_repre_old' => $_POST['nombre_repre_old'],'email_repre_old' => $_POST['email_repre_old'], 
                            'tdoc_repre_old' => $_POST['tdoc_repre_old'],'nrodoc_repre_old' => $_POST['nrodoc_repre_old'], 'nombre_contacto_old' => $_POST['nombre_contacto_old'],
                            'email_contacto_old' => $_POST['email_contacto_old'], 'tdoc_contacto_old' => $_POST['tdoc_contacto_old'],
                            'nrodoc_contacto_old' => $_POST['nrodoc_contacto_old'], 'telefono_contacto_old' => $_POST['telefono_contacto_old']);
        
        //$obj_mae->actualiza_emisor($arr_nuevos_datos,'full');
    $obj_mae->actualizar_emisor_v2($arr_nuevos_datos);

    if ($_POST['estado_id'] == 3){
        if ($_POST['cambios_repre'] > 0 || $_POST['cambios_contacto'] > 0){
            $varr_correo = array('notificaid'=>27, 'datos_body'=>'EMISOR ID: '.$_SESSION['user']['empresaid'].'<br>EMISOR: '.$_POST['nombre_empresa']);
            $obj_mail->enviar_correo_xnotificacion($varr_correo);
        }
    }

    echo '  <script>
                alert("Los datos del emisor fueron actualizados");
            </script>';
} elseif ($_POST['accion'] == 'enviar'){
    $obj_mae->enviar_registro_empresa($_SESSION['user']['empresaid']);
    // correo a los analistas
    $arr_email = array('perfilid' => 8, 'nombre_salida' => 'FACTUREATE Registro',
                        'subject' => 'Envio de solicitud de registro de emisor',
                        'body' => 'El emisor '.$_POST['nombre_empresa'].' con DOC '.$_POST['ruc'].' a enviado su solicitud para ser evaluada.<br><br>FACTUREATE RD');
    $obj_mail->enviar_correo_xperfil($arr_email);
    // correo al usuario
    $arr_mail_user = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'FACTUREATE',
                            'mail_destino' => $_SESSION['user']['email'],
                            'subject' => 'Solicitud de admisi(o)n de nuevo Emisor',
                            'body' => 'Su solicitud de registro como Emisor de FACTUREATE ha sido enviada, en breve terminaremos el analisis de la informacion y le enviaremos un correo para que pueda iniciar a solicitar financiamiento.<br><br>
                                        Empresa: '.$_POST['nombre_empresa'].'<br>
                                        RNC: '.$_POST['ruc'].'<br><br>
                                        ============
                                        <br>FACTUREATE');
    $obj_mail->enviar_correo($arr_mail_user);
}
/*--------------------------------------------------------*/
?>
