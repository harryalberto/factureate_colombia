<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

class mail_util{
    function decrip($text){
        $longitud = strlen($text);
        $cod = '';
        $dec = '';
   
        for ($i = 0; $i < $longitud; $i++){
            $pos = ($longitud - $i) * -1;
            $caracter = substr($text,$pos,1);
   
            if ($caracter == '-'){
                $valor = $cod - 454;
                $dec .= chr($valor);
                $cod = '';
            }
            else $cod .= $caracter;
        }
   
        return $dec;
    }
    function enviar_correo($arr_mail){
        if ($arr_mail['mail_salida'] == 'pymes@factureate.com') $dato = $this->decrip("502-511-518-541-569-574-503-504-566-562-");
        if ($arr_mail['mail_salida'] == 'operaciones@factureate.com') $dato = $this->decrip("538-555-568-564-508-503-502-518-552-572-");

        $mail = new PHPMailer(true);

        try{
            $mail->IsSMTP();
            $mail->Host = "mail.brdkairos.com"; // A RELLENAR. Aqu� pondremos el SMTP a utilizar. Por ej. mail.midominio.com
            $mail->SMTPAuth = true;
            $mail->Username = $arr_mail['mail_salida']; // A RELLENAR. Email de la cuenta de correo. ej.info@midominio.com La cuenta de correo debe ser creada previamente.
            $mail->Password = $dato; // A RELLENAR. Aqui pondremos la contrase�a de la cuenta de correo
             $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(
                $arr_mail['mail_salida'],
                'Factureate Notificaciones'
            );
            $mail->AddAddress($arr_mail['mail_destino']); // Esta es la direcci�n a donde enviamos
            $mail->IsHTML(true); // El correo se env�a como HTML
            $mail->Subject = $arr_mail['subject']; // Este es el titulo del email.
            $mail->Body = $arr_mail['body']; // Mensaje a enviar.
            $mail->Send(); // Env�a el correo.
        } catch (Exception $e) {
            echo 'Error: ' . $mail->ErrorInfo;
        }
    }

    function enviar_correo_attach($arr_mail){
        if ($arr_mail['mail_salida'] == 'pymes@factureate.com') $dato = $this->decrip("502-511-518-541-569-574-503-504-566-562-");
        if ($arr_mail['mail_salida'] == 'operaciones@factureate.com') $dato = $this->decrip("538-555-568-564-508-503-502-518-552-572-");

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "mail.brdkairos.com"; // A RELLENAR. Aqu� pondremos el SMTP a utilizar. Por ej. mail.midominio.com
        $mail->Username = $arr_mail['mail_salida']; // A RELLENAR. Email de la cuenta de correo. ej.info@midominio.com La cuenta de correo debe ser creada previamente.
        $mail->Password = $dato; // A RELLENAR. Aqui pondremos la contrase�a de la cuenta de correo
        $mail->Port = 587; // Puerto de conexi�n al servidor de envio.(587)465
        $mail->From = $arr_mail['mail_salida']; // A RELLENARDesde donde enviamos (Para mostrar). Puede ser el mismo que el email creado previamente.
        $mail->FromName = $arr_mail['nombre_salida']; //A RELLENAR Nombre a mostrar del remitente.

        $mail->AddAttachment($arr_mail['root_archivo'], $arr_mail['nombre_archivo']);

        $mail->AddAddress($arr_mail['mail_destino']); // Esta es la direcci�n a donde enviamos
        $mail->IsHTML(true); // El correo se env�a como HTML
        $mail->Subject = $arr_mail['subject']; // Este es el titulo del email.
        $mail->Body = $arr_mail['body']; // Mensaje a enviar.
        $exito = $mail->Send(); // Env�a el correo.
        $mail->ClearAllRecipients( ); // clear all
    }

    function enviar_correo_xperfil($arr_datos){
        // correo de operaciones
        $conn = new db_param;
        $conn->connect();
        $arr_mail = array();

        $mail_salida = 'operaciones@factureate.com';
        $dato = $this->decrip("538-555-568-564-508-503-502-518-552-572-");
        $v_sql = "  select  usuarios.id as usuarioid, usuarios.email 
                    from    usuarios 
                    where   usuarios.perfilid = ".$arr_datos['perfilid'];
        /*$idqry = $conn->query("select perfil_usuario_notificaciones.usuarioid, usuarios.email from perfil_usuario_notificaciones, usuarios 
                                where perfil_usuario_notificaciones.perfilid = ".$arr_datos['perfilid']." and perfil_usuario_notificaciones.estado = 1 and 
                                    usuarios.id = perfil_usuario_notificaciones.usuarioid");*/
        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_mail['mail_salida'] = $mail_salida;
            $arr_mail['nombre_salida'] = $arr_datos['nombre_salida'];
            $arr_mail['mail_destino'] = $obj->email;
            $arr_mail['subject'] = $arr_datos['subject'];
            $arr_mail['body'] = $arr_datos['body'];
            $this->enviar_correo($arr_mail);
            $obj = $conn->next_record();
        }

        $conn->close();
    }
    function enviar_multicorreo_xperfil($arr_datos, $id_perfil){
        $conn = new db_param;
        $conn->connect();
        $arr_mail = array();

        $mail_salida = 'operaciones@factureate.com';
        $dato = $this->decrip("538-555-568-564-508-503-502-518-552-572-");
        $idqry = $conn->query("select perfil_usuario_notificaciones.usuarioid, usuarios.email from perfil_usuario_notificaciones, usuarios 
                                where perfil_usuario_notificaciones.perfilid = ".$id_perfil." and perfil_usuario_notificaciones.estado = 1 and 
                                    usuarios.id = perfil_usuario_notificaciones.usuarioid");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){    // bucle de todos los usuario del perfil
            $arr_mail['mail_salida'] = $mail_salida;
            $arr_mail['mail_destino'] = $obj->email;

            for ($j=0; $j<count($arr_datos); $j++){
                $arr_mail['nombre_salida'] = $arr_datos[$j]['nombre_salida'];
                $arr_mail['subject'] = $arr_datos[$j]['subject'];
                $arr_mail['body'] = $arr_datos[$j]['body'];

                $this->enviar_correo($arr_mail);
            }

            $obj = $conn->next_record();
        }

        $conn->close();
    }
    function enviar_correo_xnotificacion($arr_input){
        $conn = new db_param;
        $conn_perfil = new db_param;
        $conn->connect();
        $conn_perfil->connect();

        $idqry = $conn->query("select * from SEG_VALIDA_NOTIFICACION(".$arr_input['notificaid'].")");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($obj->existe > 0){
            $arr_datos = array('nombre_salida' => 'FACTUREATE', 'subject' => $obj->p_subject,
                                'body' => $obj->p_body.'<br><br>'.$arr_input['datos_body']);

            $idqry = $conn_perfil->query("select perfilid from notificacion_perfil where notificacionid = ".$arr_input['notificaid']." and estado > 0");
            if (!$idqry) echo pg_last_error($conn_perfil->Link_ID);
            $obj_perfil = $conn_perfil->next_record();

            for($i = 0; $i < $conn_perfil->nrows(); $i ++){
                $arr_datos['perfilid'] = $obj_perfil->perfilid;
                $this->enviar_correo_xperfil($arr_datos);
                $obj_perfil = $conn_perfil->next_record();
            }
        }

        //$conn->close();
        //$conn_perfil->close();
    }
    function enviar_multicorreo_interno($id_notificacion){
        $conn = new db_param;
        $conn_perfil = new db_param;
        $conn_noti = new db_param_trans;
        $conn_delete = new db_param_trans;
        $conn->connect();
        $conn_perfil->connect();
        $conn_noti->connect();
        $conn_delete->connect();

        $v_borrar = '';

        $idqry = $conn->query("select * from SEG_VALIDA_NOTIFICACION(".$id_notificacion.")"); //compensado completamente
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($obj->existe > 0){
            // notificaciones generadas
            $id_noti = $conn_noti->query("select id, contenido from genera_notificacion where notificacionid = ".$id_notificacion);
            if (!$id_noti) echo pg_last_error($conn_noti->Link_ID);
            $obj_noti = $conn_noti->next_record();

            for($x = 0; $x < $conn_noti->nrows(); $x++){
                $arr_datos[$x] = array('nombre_salida' => 'FACTUREATE', 'subject' => $obj->p_subject, 'body' => $obj->p_body.'<br><br>'.$obj_noti->contenido);
                if ($x > 0) $v_borrar .= ','.$obj_noti->id;
                else $v_borrar = $obj_noti->id;
                $obj_noti = $conn_noti->next_record();
            }

            $conn_delete->query("delete from genera_notificacion where id in (".$v_borrar.")");
        
            $idqry = $conn_perfil->query("select perfilid from notificacion_perfil where notificacionid = ".$id_notificacion." and estado > 0");
            if (!$idqry) echo pg_last_error($conn_perfil->Link_ID);
            $obj_perfil = $conn_perfil->next_record();

            for($i = 0; $i < $conn_perfil->nrows(); $i ++){
                $this->enviar_multicorreo_xperfil($arr_datos,$obj_perfil->perfilid);
                $obj_perfil = $conn_perfil->next_record();
            }
        }

        /*$conn->close();
        $conn_perfil->close();
        $conn_noti->close();
        $conn_delete->close();*/
    }
    function enviar_multicorreo_externo($id_notificacion, $id_usuario, $p_mail){
        $conn_noti = new db_param_trans;
        $conn_delete = new db_param_trans;
        $conn_noti->connect();
        $conn_delete->connect();

        $v_borrar = '';

        // notificaciones generadas
        $id_noti = $conn_noti->query("select id, contenido from genera_notificacion where notificacionid = ".$id_notificacion." and usuarioid = ".$id_usuario);
        if (!$id_noti) echo pg_last_error($conn_noti->Link_ID);
        $obj_noti = $conn_noti->next_record();
        
        for($x = 0; $x < $conn_noti->nrows(); $x++){
            $arr_mail['mail_salida'] = 'operaciones@factureate.com';
            $arr_mail['nombre_salida'] = 'Operaciones factureate';
            $arr_mail['mail_destino'] = $p_mail;
            $arr_mail['subject'] = 'Notificacion Factureate';
            $arr_mail['body'] = $obj_noti->contenido;
            if ($x > 0) $v_borrar .= ','.$obj_noti->id;
            else $v_borrar = $obj_noti->id;

            $this->enviar_correo($arr_mail);
            $obj_noti = $conn_noti->next_record();
        }

        $conn_delete->query("delete from genera_notificacion where id in (".$v_borrar.")");
        
        /*$conn_noti->close();
        $conn_delete->close();*/
    }
    function get_mail_usuario($p_usuario_id){
        $conn_user = new db_param;
        $conn_user->connect();
        
        $id_qry = $conn_user->query("select email from usuarios where id = ".$p_usuario_id);
        if (!$id_qry) echo pg_last_error($conn_user->Link_ID);
        $obj_user = $conn_user->next_record();
        
        $v_mail = $obj_user->email;
        
        //$conn_user->close();
        return $v_mail;
    }
    function enviar_notificacion_externo($p_notificacion_id){
        $conn_noti = new db_param_trans;
        $conn_delete = new db_param_trans;
        $conn_noti_base = new db_param;
        
        $conn_noti->connect();
        $conn_noti_base->connect();
        $conn_delete->connect();
        $v_borrar = 0;
        // notificaciones generadas
        $id_noti = $conn_noti->query("select id, usuarioid, contenido from genera_notificacion where notificacionid = ".$p_notificacion_id);
        if (!$id_noti) echo pg_last_error($conn_noti->Link_ID);
        $obj_noti = $conn_noti->next_record();

        $id_notib = $conn_noti_base->query("select subject, body from notificaciones where id = ".$p_notificacion_id);
        if (!$id_notib) echo pg_last_error($conn_noti_base->Link_ID);
        $obj_noti_base = $conn_noti_base->next_record();
        
        for($x = 0; $x < $conn_noti->nrows(); $x++){
            $v_mail = $this->get_mail_usuario($obj_noti->usuarioid);
            $arr_mail['mail_salida'] = 'operaciones@factureate.com';
            $arr_mail['nombre_salida'] = 'Operaciones factureate';
            $arr_mail['mail_destino'] = $v_mail;
            $arr_mail['subject'] = $obj_noti_base->subject;
            $arr_mail['body'] = $obj_noti_base->body.'<br>'.$obj_noti->contenido;
            if ($x > 0) $v_borrar .= ','.$obj_noti->id;
            else $v_borrar = $obj_noti->id;

            $this->enviar_correo($arr_mail);
            $obj_noti = $conn_noti->next_record();
        }

        $conn_delete->query("delete from genera_notificacion where id in (".$v_borrar.")");
        
        /*$conn_noti->close();
        $conn_noti_base->close();
        $conn_delete->close();*/
    }
}
?>