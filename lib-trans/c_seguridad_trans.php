<?php
class seguridad_trans{
    function valida_existe_empresa($identificacion){
        $conector = new db_param_trans; $conector->connect();
        // COUNT SI EXISTE
        $idqry = $conector->query("select SEG_EXISTE_EMPRESA_IDENTIFICACION ('".$identificacion."') as contador");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        $contador = $obj->contador;

        //$conector->close();
        return $contador;
    }

    function ws_registra_inversor($parr_datos){
        $conector = new db_param_trans; $conector->connect();
        $vobj_seg_ws = new seguridad;
        $vobj_mae_ws = new maestros;
        $vobj_mail_ws = new mail_util;

        $varr_zona = $vobj_mae_ws->get_parametro_detalle(21);
        date_default_timezone_set($varr_zona['valorchar']);
        $v_hoy = date('Y-m-d');

        $v_nombre_completo = $parr_datos['nombre'].' '.$parr_datos['apellido'];
        
        $idqry = $conector->query("select count(1) as contador from inversionista where nombre = '".$v_nombre_completo."' or identificacion = '".$parr_datos['nro_doc']."'");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        if ($obj->contador == 0){
            //--------- CREACION DEL USUARIO
            if ($parr_datos['tipo_persona'] == 85) $v_empresa_id = 0; else $v_empresa_id = -1;
            $varr_usuario = array('identificacion' => $parr_datos['nro_doc'], 'password' => '0000', 'email' => $parr_datos['email'], 'nombre' => $parr_datos['nombre'],
                        'apellido' => $parr_datos['apellido'], 'tipodoc' => $parr_datos['tipo_doc'], 'tipousuario' => 4, 'perfilid' => 2, 'empresaid' => $v_empresa_id);

            $varr_usuario_result = $vobj_seg_ws->crear_usuario($varr_usuario);

            //---------- CREACION DEL INVERSOR
            if ($parr_datos['tipo_persona'] == 86) $v_inversor_id = $v_empresa_id; else $v_inversor_id = $varr_usuario_result['usuarioid'];

            if ($varr_usuario_result['usuarioid'] != -200){
                //@@@@ PATH DESTINO DE ARCHIVO DE CEDULA
                $v_nombre_inversor = $parr_datos['nombre'].'_'.$parr_datos['a_paterno'].'_'.$parr_datos['a_materno'];
                $v_nombre_inversor_final = str_replace(" ", "_", $v_nombre_inversor);

                $v_carpeta_destino = $_SERVER['DOCUMENT_ROOT'].'/archivos/INV_'.$v_nombre_inversor_final.'_'.$parr_datos['nro_doc'];
                if (!is_dir($v_carpeta_destino)) mkdir($v_carpeta_destino, 0777, true);

                $v_carpeta_vincula = $v_carpeta_destino.'/vinculacion';
                if (!is_dir($v_carpeta_vincula)) mkdir($v_carpeta_vincula, 0777, true);

                $file_path_cedula = '';

                //@@@@ REGISTRO DE INVERSOR
                $varr_inversor = array('nombre' => $parr_datos['nombre'], 'apellido' => $parr_datos['apellido'], 'email' => $parr_datos['email'], 'telefono' => $parr_datos['telefono'],
                            'tipo_doc' => $parr_datos['tipo_doc'], 'nro_doc' => $parr_datos['nro_doc'], 'direccion' => $parr_datos['direccion'], 'tipo_persona' => $parr_datos['tipo_persona'],
                            'documento' => $file_path_cedula, 'inversor_id' => $v_inversor_id, 'tipo_registro' => 112);
                $vobj_mae_ws->registra_inversor($varr_inversor);
                $varr_link = $vobj_mae_ws->get_parametro_detalle(53);
                $varr_docuprovee = $vobj_mae_ws->get_parametro_detalle(78);

                if ($varr_docuprovee['valornum'] == 1) $v_provee_docdig = 'de nuestro proveedor de contratos digitales '.$varr_docuprovee['valorchar'];
                else $v_provee_docdig = '';

                /*$varr_mail = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'Factureate', 'mail_destino' => $parr_datos['email'], 
                        'subject' => '[FACTUREATE] Tu registro como inversor esta en proceso',
                        'body' => 'Hola '.$parr_datos['nombre'].' '.$parr_datos['apellido'].', nos complace saludarte y agradecer tu interes en invertir con nosotros, tus datos fueron registrados, los siguientes son los pasos a seguir para terminar vuestra vinculacion con FACTUREATE:
                            <br><br>1. Accede a la plataforma Factureate y completa la informacion de registro.
                            <br>2. Nuestra area legal realizara el debido analisis para que puedas invertir con nosotros.
                            <br>3. Una vez aprobada tu informacion recibiras un correo '.$v_provee_docdig.' con el contrato de vinculalcion, donde se explica la forma de invertir con nosotros.
                            <br>4. Una vez firmado el contrato de vinculacion digitalmente, desde ese momento podras invertir y experimentar el crecimiento de tus inversiones.
                            <br><br>Acceso a la plataforma:
                            <br>Usuario: '.$parr_datos['nro_doc'].'
                            <br>Password: '.$varr_usuario_result['password'].'
                            <br>Plataforma: <a href="'.$varr_link['valorchar'].'" target="_blank">Acceso Plataforma</a>
                            <br><br>(*) Tildes omitidas intencionalmente
                            <br><br>==============================
                            <br><img src="cid:logo_factureate" width="100">',
                        'firma' => '../img/logo.png',
                        'firma_nombre' => 'logo_factureate');*/

                //------ respuesta del registro
                $output = array('id' => 1, 'mensaje' => 'Inversor creado');
                $output['mail_salida'] = 'operaciones@factureate.com';
                $output['nombre_salida'] = 'Factureate';
                $output['mail_destino'] = $parr_datos['email'];
                $output['subject'] = '[FACTUREATE] Tu registro como inversor esta en proceso';
                $output['body'] = 'Hola '.$parr_datos['nombre'].' '.$parr_datos['apellido'].', nos complace saludarte y agradecer tu interes en invertir con nosotros,
                                tus datos fueron registrados, los siguientes son los pasos a seguir para terminar vuestra vinculacion con FACTUREATE:
                            <br><br>1. Accede a la plataforma Factureate y completa la informacion de registro.
                            <br>2. Nuestra area legal realizara el debido analisis para que puedas invertir con nosotros.
                            <br>3. Una vez aprobada tu informacion recibiras un correo '.$v_provee_docdig.' con el contrato de vinculalcion, donde se explica la forma de invertir con nosotros.
                            <br>4. Una vez firmado el contrato de vinculacion digitalmente, desde ese momento podras invertir y experimentar el crecimiento de tus inversiones.
                            <br><br>Acceso a la plataforma:
                            <br>Usuario: '.$parr_datos['nro_doc'].'
                            <br>Password: '.$varr_usuario_result['password'].'
                            <br>Plataforma: <a href="'.$varr_link['valorchar'].'" target="_blank">Acceso Plataforma</a>
                            <br><br>(*) Tildes omitidas intencionalmente<br><br>
                            <img src="cid:logo_factureate" width="100">';
                $output['firma'] = '../images/logo.png';
                $output['firma_nombre'] = 'logo_factureate';

                $v_retorno = $output;
            } else $v_retorno = array('id' => 0);

        } else $v_retorno = array('id' => 0);

        return $v_retorno;
    }
}
?>