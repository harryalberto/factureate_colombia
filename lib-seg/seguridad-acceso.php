<?
class seguridad{
    function encrypta($cadena){
        $longitud = strlen($cadena);
        $encrypt = '';

        for ($i = 0; $i < $longitud; $i++){
            $pos = ($longitud - $i) * -1;
            $caracter = substr($cadena,$pos,1);
            $valor = ord($caracter);
            $aux = $valor;
            
            $valor = $valor + 43;
            $valor = $valor * 2;
            $valor = $valor + 49;
            $valor = $valor - 64;
            
            $encrypt .= $valor.'-';
        }

        return $encrypt;
    }
    function decrip($text){
        $longitud = strlen($text);
        $cod = '';
        $dec = '';
     
        for ($i = 0; $i < $longitud; $i++){
            $pos = ($longitud - $i) * -1;
            $caracter = substr($text,$pos,1);
          
            if ($caracter == '-'){
               $valor = (($cod + 64 - 49) / 2) - 43;
               $dec .= chr($valor);
               $cod = '';
            }
            else $cod .= $caracter;
        }

        return $dec;
    }
    function valida_user ($user,$pass){
        $conn = new db_param;
        $conn->connect();

        $passencrypt = $this->encrypta($pass);
        //$passencrypt = $pass;
        $idqry = $conn->query("select count(1) as contador from usuarios 
                                where identificacion = '".$user."' and password = '".$passencrypt."' and estado = 1");
        $obj = $conn->next_record();

        if ($obj->contador > 0){
            //existe el usuario, ahora se llamará a la creacion del certificado
            $idqry = $conn->query("select id from usuarios 
                                where identificacion = '".$user."' and password = '".$passencrypt."' and estado = 1");
            $obj = $conn->next_record();
            $usuarioid = $obj->id;
            $idqry = $conn->query("select certificado_seguridad('".$usuarioid."') as certificado");
            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();
            $usuario = array("encontrado"=>1,"id"=>$usuarioid,"certificado"=>$this->encrypta($obj->certificado));
        } else $usuario = array("encontrado"=>0);

        $conn->close();
        return $usuario;
    }
    function valida_certificado($usuarioid,$certificado){
        $conn = new db_param;
        $conn->connect();

        $conn->query("select certificado_valida(".$usuarioid.",'".$certificado."') as resultado");
        $obj = $conn->next_record();
        $resultado = $obj->resultado;

        $conn->close();
        return $resultado;
    }
    function inicia_acceso($usuarioid,$certificado){
        $conn = new db_param; $conn->connect();
        $conn2 = new db_param; $conn2->connect();
        $conn3 = new db_param_trans; $conn3->connect();
        $v_procede = 1;
        /*---- datos del usuario ----*/
        $idqry = $conn->query("select usuarios.nombre, usuarios.apellido, usuarios.tipousuario, perfil_usuario.tipo,usuarios.perfilid,usuarios.empresaid,
                                    usuarios.email  , perfil_usuario.pagina_inicio 
                    from usuarios,perfil_usuario 
                    where usuarios.id = ".$usuarioid." and perfil_usuario.id = usuarios.perfilid");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $_SESSION['user'] = array('usuarioid'=>$usuarioid,'nombre'=>$obj->nombre,
                                    'apellido'=>$obj->apellido,'certificado'=>$certificado,
                                    'perfiltipo'=>$obj->tipo,'tipousuario'=>$obj->tipousuario,
                                    'empresaid'=>$obj->empresaid, 'email' => $obj->email,
                                    'perfilid' => $obj->perfilid);
        
        $v_pagina_acceso = $obj->pagina_inicio;
        //################# MENU
        //---------- VERIFICA A LOS INVERSORES
        
        if ($_SESSION['user']['tipousuario'] == 4 || $_SESSION['user']['tipousuario'] == 6){
        // SI ES INVERSOR
            if ($_SESSION['user']['empresaid'] == 0) $v_inversor_id = $_SESSION['user']['usuarioid'];
            else $v_inversor_id = $_SESSION['user']['empresaid'];

            $idqry = $conn3->query("select estado from inversionista where inversor_id = ".$v_inversor_id);
            if (!$idqry) echo pg_last_error($conn3->Link_ID);
            $obj3 = $conn3->next_record();

            if ($obj3->estado == 9 || $obj3->estado == 10 || $obj3->estado == 63 ) $v_procede = 0;
        }

        //@@@@ VERIFICA EMPRESAS EMISORAS
        if ($_SESSION['user']['tipousuario'] == 3) {
            $idqry = $conn3->query("select estado from empresa where id = ".$obj->empresaid);
            if (!$idqry) echo pg_last_error($conn3->Link_ID);
            $obj3 = $conn3->next_record();

            if ($obj3->estado == 4 || $obj3->estado == 5) $v_procede = 0;
        }

        if ($v_procede > 0){
            $_SESSION['menu'] = array();
            
            if ($_SESSION['user']['tipousuario'] == 4 && ($obj3->estado == 6 || $obj3->estado == 7 || $obj3->estado == 8)) {   // INVERSOR REGISTRADO EN VALIDACION
                $v_sql = "select distinct perfil_permiso.menuid, menu.nombre, menu.pagina,menu.orden 
                        from perfil_permiso, menu 
                        where perfil_permiso.perfilid = ".$obj->perfilid." and menu.id = perfil_permiso.menuid and menu.estado = 1 and menu.nivel = 1 and perfil_permiso.menuid = 44
                        order by menu.orden";

                $v_pagina_acceso = 'inversionistas/perfil_inversor.php';
            } elseif ($_SESSION['user']['tipousuario'] == 3 && ($obj3->estado == 1 || $obj3->estado == 2)) {  // EMISOR REGISTRADO Y EN VALIDACION
                $v_sql = "select distinct perfil_permiso.menuid, menu.nombre, menu.pagina,menu.orden 
                        from perfil_permiso, menu 
                        where perfil_permiso.perfilid = ".$obj->perfilid." and menu.id = perfil_permiso.menuid and menu.estado = 1 and menu.nivel = 1 and perfil_permiso.menuid = 14 
                        order by menu.orden";
                $v_pagina_acceso = 'emisores/registra_emisor.php';
            } else {
                $v_sql = "  select distinct perfil_permiso.menuid, menu.nombre, menu.pagina,menu.orden 
                        from perfil_permiso, menu 
                        where perfil_permiso.perfilid = ".$obj->perfilid." and menu.id = perfil_permiso.menuid and menu.estado = 1 and menu.nivel = 1 
                        order by menu.orden";
            }

            $idqry = $conn2->query($v_sql);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj = $conn2->next_record();
            
            for($i = 0; $i < $conn2->nrows(); $i ++){
                $_SESSION['menu'][$i] = array('menuid'=>$obj->menuid,'menu'=>$obj->nombre,
                                        'pagina'=>$obj->pagina);
                $obj = $conn2->next_record();
            }
        }

        //$conn->close(); $conn2->close(); $conn3->close();

        return $v_pagina_acceso;
    }
    function get_datos_usuario($usuarioid){
        $conn = new db_param;
        $conn->connect();
        /*---- datos del usuario ----*/
        $idqry = $conn->query(" select  usuarios.nombre, usuarios.apellido, usuarios.tipousuario, perfil_usuario.tipo,usuarios.perfilid,usuarios.empresaid, 
                                        usuarios.email, usuarios.identificacion, usuarios.tipodoc, tipos.nombre as tipodoc_nombre, usuarios.telefono,
                                        usuarios.fiduciaria_id 
                                from usuarios,perfil_usuario, tipos 
                                where usuarios.id = ".$usuarioid." and perfil_usuario.id = usuarios.perfilid and tipos.id = usuarios.tipodoc");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $resultado = array('usuarioid'=>$usuarioid,                   'nombre'=>$obj->nombre,
                            'apellido'=>$obj->apellido,               'certificado'=>$certificado,
                            'perfiltipo'=>$obj->tipo,                 'tipousuario'=>$obj->tipousuario,
                            'empresaid'=>$obj->empresaid,             'email' => $obj->email,
                            'identificacion' => $obj->identificacion, 'tipodoc'=>$obj->tipodoc,         'tipodoc_nombre'=>$obj->tipodoc_nombre, 
                            'telefono'=>$obj->telefono,               'perfilid' => $obj->perfilid,     'fiduciaria_id' => $obj->fiduciaria_id 
                        );

        $conn->close();
        return $resultado;
    }
    function get_datos_usuario_xempresa($p_empresa_id){
        $conn = new db_param;
        $conn->connect();
        /*---- datos del usuario ----*/
        $idqry = $conn->query("select usuarios.nombre, usuarios.apellido, usuarios.tipousuario, perfil_usuario.tipo,usuarios.perfilid,usuarios.empresaid, 
                                    usuarios.email, usuarios.identificacion 
                    from usuarios,perfil_usuario 
                    where usuarios.empresaid = ".$p_empresa_id." and perfil_usuario.id = usuarios.perfilid");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $resultado = array('usuarioid'=>$usuarioid,'nombre'=>$obj->nombre,
                            'apellido'=>$obj->apellido,'certificado'=>$certificado,
                            'perfiltipo'=>$obj->tipo,'tipousuario'=>$obj->tipousuario,
                            'empresaid'=>$obj->empresaid, 'email' => $obj->email,
                            'identificacion' => $obj->identificacion
                        );

        $conn->close();
        return $resultado;
    }
    function crear_usuario($arr_datos){
        $conn = new db_param; $conn->connect();
        $conn_exe = new db_param; $conn_exe->connect();
        $conn_trans = new db_param_trans; $conn_trans->connect();
        
        $identificacion = $arr_datos['identificacion'];
        $v_pass = $this->genera_pass();
        //$passencrypt = $this->encrypta($arr_datos['password']);
        $passencrypt = $this->encrypta($v_pass);

        if (isset($arr_datos['fiduciaria_id'])){
            $v_sql = "  select SEG_CREA_USUARIO_V2('".$identificacion."','".$passencrypt."','".$arr_datos['email']."',1,'".$arr_datos['nombre']."','".$arr_datos['apellido']."',
                                                ".$arr_datos['tipodoc'].",".$arr_datos['tipousuario'].",".$arr_datos['perfilid'].",".$arr_datos['empresaid'].",
                                                '".$arr_datos['telefono']."',".$arr_datos['fiduciaria_id'].",'".$arr_datos['url']."') as usuarioid";
        } else {
            $v_sql = "  select SEG_CREA_USUARIO('".$identificacion."','".$passencrypt."','".$arr_datos['email']."',1,'".$arr_datos['nombre']."','".$arr_datos['apellido']."',
                                                ".$arr_datos['tipodoc'].",".$arr_datos['tipousuario'].",".$arr_datos['perfilid'].",".$arr_datos['empresaid'].") as usuarioid";
        }

        $idqry = $conn_exe->query($v_sql);
        if (!$idqry) echo pg_last_error($conn_exe->Link_ID);
        $obj = $conn_exe->next_record();

        $resultado = array('usuarioid' => $obj->usuarioid, 'identificacion' => $identificacion, 'password' => $v_pass);

        //==== se iguala el secuencial de empresas con usuarios
        $idqry = $conn_trans->query("select nextval('sempresa') as secuencial");
        if (!$idqry) echo pg_last_error($conn_trans->Link_ID);
        $conn_trans->next_record();

        $conn->close(); $conn_exe->close();
        return $resultado;
    }
    function get_submenu($menuid, $perfilid){
        $conn = new db_param;
        $conn->connect();
        $resultado = array();

        $idqry = $conn->query("select distinct perfil_permiso.menuid, menu.nombre, menu.pagina, menu.orden 
                                from perfil_permiso, menu 
                                where perfil_permiso.perfilid = ".$perfilid." and menu.id = perfil_permiso.menuid and 
                                    menu.estado = 1 and menu.nivel = 2 and menu.padreid = ".$menuid." 
                                order by menu.orden");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $resultado[$i] = array('manuid' => $obj->menuid, 'nombre' => $obj->nombre,
                            'pagina' => $obj->pagina, 'orden' => $obj->orden
                            );
            $obj = $conn->next_record();
        }

        $conn->close();
        return $resultado;
    }

    function upd_datos_usuario($p_datos){
        $conn = new db_param; $conn->connect();

        if ($p_datos['estado_inversor_id'] == 8){
            $idqry = $conn->query("update usuarios set email = '".$p_datos['email_persona']."', telefono = '".$p_datos['telefono_persona']."' where id = ".$p_datos['inversor_id']);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
        } elseif ($p_datos['estado_inversor_id'] == 6){
            // REGISTRADO
            $v_sql = "  update usuarios
                        set tipodoc = ".$p_datos['tipo_documento'].", email = '".$p_datos['email_persona']."', telefono = '".$p_datos['telefono_persona']."' 
                        where id = ".$p_datos['inversor_id'];

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $conn->next_record();
        }

        $conn->close();
        return 1;
    }

    function genera_pass(){
        $v_caracter = 'L';
        $v_pass = '';

        for($i = 0; $i < 6; $i ++){
            if ($v_caracter == 'L'){
                $v_pass .= rand(1,9);
                $v_caracter = 'N';
            } else{
                $v_asc = rand(65,90);
                $v_pass .= chr($v_asc);
                $v_caracter = 'L';
            }
        }

        return $v_pass;
    }

    function cambia_pass_atm($p_usuario_id){
        $conn = new db_param; $conn->connect();

        $v_pass = $this->genera_pass();
        $v_pass_enc = $this->encrypta($v_pass);

        $idqry = $conn->query("update usuarios set password = '".$v_pass_enc."' where id = ".$p_usuario_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        return $v_pass;
    }

    function proceso_olvide_pass($p_documento){
        $conn = new db_param; $conn->connect();
        $conn2 = new db_param; $conn2->connect();

        $idqry = $conn2->query("select count(1) as contador from usuarios where identificacion = '".$p_documento."' and estado = 1");
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();

        if ($obj2->contador > 0){
            $idqry = $conn->query("select id, nombre, apellido, email from usuarios where identificacion = '".$p_documento."' and estado = 1");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            $v_nombre = $obj->nombre.' '.$obj->apellido;
            $varr_result = array('id' => $obj->id, 'nombre' => $v_nombre, 'email' => $obj->email, 'sec' => 1);
        } else {
            $varr_result = array('sec' => 0);
        }

        $conn->close(); $conn2->close();

        return $varr_result;
    }

    function cambiar_password_usuario($p_documento,$p_password, $p_newpassword){
        $conn = new db_param; $conn->connect();
        $conn2 = new db_param; $conn2->connect();
        $conn3 = new db_param; $conn3->connect();

        $v_pass_enc = $this->encrypta($p_password);

        $idqry = $conn2->query("select count(1) as contador from usuarios where identificacion = '".$p_documento."' and estado = 1 and password = '".$v_pass_enc."'");
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();

        if ($obj2->contador > 0){
            $idqry = $conn->query("select id from usuarios where identificacion = '".$p_documento."'");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            $v_id = $obj->id;
            $v_newpass_enc = $this->encrypta($p_newpassword);

            $idqry = $conn3->query("update usuarios set password = '".$v_newpass_enc."' where identificacion = '".$p_documento."'");
            if (!$idqry) echo pg_last_error($conn3->Link_ID);
            $conn3->next_record();

            $varr_result = array('id' => $v_id, 'sec' => 1);
        } else {
            $varr_result = array('sec' => 0);
        }

        $conn->close(); $conn2->close(); $conn3->close();

        return $varr_result;
    }

    function get_usuarios($p_tipo, $p_row_ini, $p_rows_num, $p_filtros, $p_order){
        $conn = new db_param; $conn->connect();

        if ($p_tipo == 'COUNT'){
            $v_sql = "  select  count(1) as contador 
                        from    usuarios, tipos as tdocumento, tipos as tusuario, perfil_usuario 
                        where   usuarios.estado > 0 and tdocumento.id = usuarios.tipodoc and tusuario.id = usuarios.tipousuario and perfil_usuario.id = usuarios.perfilid";

            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            $varr_result = $obj->contador;
        } else {
            $v_sql = "  select  usuarios.id,usuarios.identificacion, usuarios.email, usuarios.nombre, usuarios.apellido, usuarios.tipodoc, tdocumento.nombre as tipodoc_nombre,
                                usuarios.tipousuario, tusuario.nombre as tipousuario_nombre, usuarios.perfilid, perfil_usuario.nombre as perfil_nombre, usuarios.empresaid,
                                usuarios.telefono, usuarios.fiduciaria_id 
                        from    usuarios, tipos as tdocumento, tipos as tusuario, perfil_usuario 
                        where   usuarios.estado > 0 and tdocumento.id = usuarios.tipodoc and tusuario.id = usuarios.tipousuario and perfil_usuario.id = usuarios.perfilid";

            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;
            if ($p_order != '') $v_sql .= " order by ".$p_order;
            if ($p_rows_num > 0) $v_sql .= " limit ".$p_rows_num." offset ".$p_row_ini;

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            $varr_result = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $varr_result[$i] = array(   'usuario_id' => $obj->id,                   'identificacion' => $obj->identificacion,   'email' => $obj->email,
                                            'nombre' => $obj->nombre,                   'apellido' => $obj->apellido,               'tipodoc' => $obj->tipodoc,
                                            'tipodoc_nombre' => $obj->tipodoc_nombre,   'tipousuario' => $obj->tipousuario,         'tipousuario_nombre' => $obj->tipousuario_nombre,
                                            'perfil_id' => $obj->perfilid,              'perfil_nombre' => $obj->perfil_nombre,     'empresa_id' => $obj->empresaid,
                                            'telefono' => $obj->telefono,               'fiduciaria_id' => $obj->fiduciaria_id);

                $obj = $conn->next_record();
            }
        }

        $conn->close();

        return $varr_result;
    }

    function actualiza_datos_usuario($parr_datos){
        $conn = new db_param; $conn->connect();

        $v_sql = "update usuarios set ";
        $v_coma = '';

        if (isset($parr_datos['email']) && $parr_datos['email'] != ''){
            $v_sql .= " email = '".$parr_datos['email']."'"; $v_coma = ",";
        }
        if (isset($parr_datos['perfil_id']) && $parr_datos['perfil_id'] != ''){
            $v_sql .= $v_coma." perfilid = '".$parr_datos['perfil_id']."'"; $v_coma = ",";
        }

        $v_sql .= " where id = ".$parr_datos['usuario_id'];

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();

        $conn->close();
    }

    function get_permisos($p_perfil_id){
        $conn = new db_param; $conn->connect();

        $v_sql = "  select perfil_permiso.permisoid, permisos.codigo
                    from perfil_permiso, permisos 
                    where perfil_permiso.perfilid = ".$p_perfil_id." and permisos.id = perfil_permiso.permisoid";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_resultado = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_resultado[$i] = array('permiso_id' => $obj->permisoid, 'codigo' => $obj->codigo);
            $obj = $conn->next_record();
        }

        $conn->close();

        return $varr_resultado;
    }

    function valida_acceso_express($p_usuario_id, $p_token, $p_fid){
        $conn = new db_param; $conn->connect();

        $v_sql = "  select  count(1) as contador
                    from    certificado 
                    where   usuarioid = 0 and codigo = '".$p_token."' and factura_id = ".$p_fid;

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $v_resultado = $obj->contador;

        $conn->close();

        return $v_resultado;
    }

    //==== nueva funcion de inicializacion de sesion al usuario
    function inicia_session_usuario($usuarioid){
        $conn = new db_param; $conn->connect();
        $conn2 = new db_param; $conn2->connect();
        $conn3 = new db_param_trans; $conn3->connect();

        $v_procede = 1;
        
        //---- datos del usuario ----
        $v_sql = "  select  usuarios.nombre, usuarios.apellido, usuarios.tipousuario, perfil_usuario.tipo,usuarios.perfilid,
                            usuarios.empresaid,usuarios.email,perfil_usuario.pagina_inicio 
                    from    usuarios,perfil_usuario 
                    where   usuarios.id = ".$usuarioid." and perfil_usuario.id = usuarios.perfilid";
        
        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $_SESSION['user'] = array(  'usuarioid'=>$usuarioid,        'nombre'=>$obj->nombre,     'apellido'=>$obj->apellido,
                                    'perfiltipo'=>$obj->tipo,       'tipousuario'=>$obj->tipousuario,
                                    'empresaid'=>$obj->empresaid,   'email' => $obj->email,     'perfilid' => $obj->perfilid);
        
        $v_pagina_acceso = $obj->pagina_inicio;

        //==== VERIFICA ESTADO SI ES INVERSOR A LOS INVERSORES
        
        if ($_SESSION['user']['tipousuario'] == 4 || $_SESSION['user']['tipousuario'] == 6){
        // SI ES INVERSOR
            if ($_SESSION['user']['empresaid'] == 0) $v_inversor_id = $_SESSION['user']['usuarioid'];
            else $v_inversor_id = $_SESSION['user']['empresaid'];

            $idqry = $conn3->query("select estado from inversionista where inversor_id = ".$v_inversor_id);
            if (!$idqry) echo pg_last_error($conn3->Link_ID);
            $obj3 = $conn3->next_record();

            if ($obj3->estado == 9 || $obj3->estado == 10 || $obj3->estado == 63 ) $v_procede = 0;
        }

        //==== VERIFICA EMPRESAS EMISORAS

        if ($_SESSION['user']['tipousuario'] == 3) {
            $idqry = $conn3->query("select estado from empresa where id = ".$obj->empresaid);
            if (!$idqry) echo pg_last_error($conn3->Link_ID);
            $obj3 = $conn3->next_record();

            if ($obj3->estado == 4 || $obj3->estado == 5) $v_procede = 0;
        }

        //==== ACCESO DEL MENU

        if ($v_procede > 0){
            $_SESSION['menu'] = array();
            
            if ($_SESSION['user']['tipousuario'] == 4 && ($obj3->estado == 6 || $obj3->estado == 7 || $obj3->estado == 8)) {   // INVERSOR REGISTRADO EN VALIDACION
                $v_sql = "  select  distinct perfil_permiso.menuid, menu.nombre, menu.pagina,menu.orden 
                            from    perfil_permiso, menu 
                            where   perfil_permiso.perfilid = ".$obj->perfilid." and menu.id = perfil_permiso.menuid and menu.estado = 1 and 
                                    menu.nivel = 1 and perfil_permiso.menuid = 44
                            order by menu.orden";

                $v_pagina_acceso = 'inversionistas/perfil_inversor.php';
            } elseif ($_SESSION['user']['tipousuario'] == 3 && ($obj3->estado == 1 || $obj3->estado == 2)) {  // EMISOR REGISTRADO Y EN VALIDACION
                $v_sql = "  select  distinct perfil_permiso.menuid, menu.nombre, menu.pagina,menu.orden 
                            from    perfil_permiso, menu 
                            where   perfil_permiso.perfilid = ".$obj->perfilid." and menu.id = perfil_permiso.menuid and menu.estado = 1 and 
                                    menu.nivel = 1 and perfil_permiso.menuid = 14 
                            order by menu.orden";

                $v_pagina_acceso = 'emisores/registra_emisor.php';
            } else {
                $v_sql = "  select  distinct perfil_permiso.menuid, menu.nombre, menu.pagina,menu.orden 
                            from    perfil_permiso, menu 
                            where   perfil_permiso.perfilid = ".$obj->perfilid." and menu.id = perfil_permiso.menuid and menu.estado = 1 and 
                                    menu.nivel = 1 
                            order by menu.orden";
            }

            $idqry = $conn2->query($v_sql);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj = $conn2->next_record();
            
            for($i = 0; $i < $conn2->nrows(); $i ++){
                $_SESSION['menu'][$i] = array('menuid'=>$obj->menuid,'menu'=>$obj->nombre, 'pagina'=>$obj->pagina);
                $obj = $conn2->next_record();
            }
        }

        $conn->close(); $conn2->close(); $conn3->close();

        return $v_pagina_acceso;
    }
}
?>