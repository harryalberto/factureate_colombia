<?php
class maestros{
    function get_datos_emisor($emisorid){
        $conn = new db_param_trans;
        $conn->connect();

        $conn->query("select * from get_datos_emisor(".$emisorid.")");
        $obj = $conn->next_record();
        $arremisor = array('identificacion'=>$obj->identificacion, 'nombre'=>$obj->nombre,
                            'direccion'=>$obj->direccion, 'ubi1nombre'=>$obj->ubi1nombre,
                            'ubi2nombre'=>$obj->ubi2nombre, 'ubi3nombre'=>$obj->ubi3nombre,
                            'estado'=>$obj->estado);

        //$conn->close();
        return $arremisor;
    }
    function get_tipos($tipobase){
        $conn = new db_param_trans;
        $conn->connect();
        $arrtipo = array();

        $conn->query("select * from get_tipos('".$tipobase."')");
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arrtipo[$i] = array('id'=>$obj->tipoid, 'nombre'=>$obj->nombre,
                            'dato1'=>$obj->dato1);
            $obj = $conn->next_record();
        }
        
        //$conn->close();
        return $arrtipo;
    }

    function get_tipos_seg($tipobase){
        $conn = new db_param; $conn->connect();
        $arrtipo = array();

        $conn->query("select id, nombre from tipos where tipo_base = '".$tipobase."' and estado = 1");
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arrtipo[$i] = array('id'=>$obj->id, 'nombre'=>$obj->nombre);
            $obj = $conn->next_record();
        }
        
        //$conn->close();
        return $arrtipo;
    }

    function get_tipos_xnom($tipobase){
        $conn = new db_param_trans;
        $conn->connect();
        $arrtipo = array();

        $conn->query("select id as tipoid, nombre, dato1 from tipos where tipo_base = '".$tipobase."' and estado = 1 order by nombre");
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arrtipo[$i] = array('id'=>$obj->tipoid, 'nombre'=>$obj->nombre,
                            'dato1'=>$obj->dato1);
            $obj = $conn->next_record();
        }
        
        //$conn->close();
        return $arrtipo;
    }

    function get_nombre_empresa_xdoc($identificacion){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select count(1) as contador from empresa where identificacion = '".trim($identificacion)."'");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        if ($obj->contador > 0){
            $conn2 = new db_param_trans; $conn2->connect();

            $idqry = $conn2->query("select nombre from empresa where identificacion = '".trim($identificacion)."' limit 1");
            if (!$idqry) echo 'error2, '.pg_last_error($conn2->Link_ID);
            $obj = $conn2->next_record();
            $empresa = $obj->nombre;
            //$conn2->close();
        } else $empresa = '';

        //$conn->close();

        return $empresa;
    }
    function get_parametros(){
        $conn = new db_param_trans;
        $conn->connect();
        $parametros = array();

        $conn->query("select id, nombre, valor_num, valor_char from parametros where estado = 1");
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $parametros[$obj->nombre]['valornum'] = $obj->valor_num;
            $parametros[$obj->nombre]['valorchar'] = $obj->valor_char;

            $obj = $conn->next_record();
        }
        
        //$conn->close();
        return $parametros;
    }
    function get_parametro_detalle($p_id){
        $conn = new db_param_trans;
        $conn->connect();

        $conn->query("select id, nombre, valor_num, valor_char from parametros where estado = 1 and id = ".$p_id);
        $obj = $conn->next_record();

        $parametros = array('valornum'=>$obj->valor_num, 'valorchar'=>$obj->valor_char);
        
        //$conn->close();
        return $parametros;
    }
    function registro_express_cliente($identificacion,$nombre){
        $conn = new db_param_trans; $conn->connect();
        
        $idqry = $conn->query("select emp_crea_empresa_express('".$identificacion."','".$nombre."') as empresaid");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $empresaid = $obj->empresaid;

        //SE CREA LA CARPETA DE LA EMRPESA Y DE LOS RIESGOS POR SER OP
        if (!is_dir('../archivos/empresa_'.$identificacion)) mkdir('../archivos/empresa_'.$identificacion, 0777, true);
        if (!is_dir('../archivos/empresa_'.$identificacion.'/evaluacion_riesgo')) mkdir('../archivos/empresa_'.$identificacion.'/evaluacion_riesgo', 0777, true);
        //mkdir('../archivos/riesgos_emp_'.$identificacion);
        
        //$conn->close();
        return $empresaid;
    }

    function registro_express_cliente_arr($parr_datos){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $idqry = $conn->query("select emp_crea_empresa_express('".$parr_datos['identificacion']."','".$parr_datos['nombre']."') as empresaid");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $empresaid = $obj->empresaid;

        //INCLUSION DE DATOS ADICIONALES
        $v_sql = "update empresa set emailcontacto = '".$parr_datos['cliente_correo']."', direccion = '".$parr_datos['cliente_direccion']."',
                                    nombrecontacto = '".$parr_datos['cliente_contacto']."' where id = ".$empresaid;

        $idqry = $conn2->query($v_sql);
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $conn2->next_record();

        //SE CREA LA CARPETA DE LA EMRPESA Y DE LOS RIESGOS POR SER OP
        if (!is_dir('../archivos/empresa_'.$parr_datos['identificacion'])) mkdir('../archivos/empresa_'.$parr_datos['identificacion'], 0777, true);
        if (!is_dir('../archivos/empresa_'.$parr_datos['identificacion'].'/evaluacion_riesgo')) mkdir('../archivos/empresa_'.$parr_datos['identificacion'].'/evaluacion_riesgo', 0777, true);

        return $empresaid;
    }

    function valida_factura_existencia($fnumero){
        $conn = new db_param_trans;
        $conn->connect();
        
        $idqry = $conn->query("select count(1) as contador from factura where numero = '".$fnumero."'");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $resultado = $obj->contador;
        
        //$conn->close();
        return $resultado;
    }
    function gestiona_notificacion($usuarioid){
        //aqui se desarrolla
    }
    function get_estados($base){
        $conn = new db_param_trans;
        $conn->connect();
        $arr = array();

        $conn->query("select * from get_estados('".$base."')");
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr[$i] = array('id'=>$obj->estadoid, 'nombre'=>$obj->nombre);
            $obj = $conn->next_record();
        }
        
        ///$conn->close();
        return $arr;
    }
    function get_estado_cliente($clienteid){
        $conn = new db_param_trans;
        $conn->connect();

        $conn->query("select empresa.estado, estados.nombre from estados,empresa 
                    where empresa.id = '".$clienteid."' and estados.id = empresa.estado");
        $obj = $conn->next_record();
        $estado = array('estadoid' => $obj->estado, 'estado' => $obj->nombre);

        //$conn->close();
        return $estado;
    }
    function get_triesgopagador(){
        $conn = new db_param_trans;
        $conn->connect();
        $arrtipo = array();

        $conn->query("select * from get_tipo_riesgo_pagador()");
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arrtipo[$i] = array('id'=>$obj->tipoid, 'nombre'=>$obj->nombre,
                            'calificacion'=>$obj->calificacion);
            $obj = $conn->next_record();
        }
        
        //$conn->close();
        return $arrtipo;
    }
    function get_datos_obpag($empresaid){
        $conn = new db_param_trans;
        $conn->connect();

        $conn->query("select * from GET_DATOS_OBPAGO(".$empresaid.")");
        $obj = $conn->next_record();
        $arrobpago = array('identificacion'=>$obj->identificacion, 'nombre'=>$obj->nombre,
                            'sectoreconomico'=>$obj->sectoreconomico, 'actividad'=>$obj->actividad,
                            'paginaweb'=>$obj->paginaweb, 'finicio'=>$obj->finicio
                            );

        //$conn->close();
        return $arrobpago;
    }
    function get_riesgo_obpago($empresaid){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $idqry = $conn2->query("select count(1) as contador from riesgo_empresa where empresaid = ".$empresaid." and estado = 1");
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();

        if ($obj2->contador > 0){
            $idqry = $conn->query("select * from GET_RIESGO_OBPAGO(".$empresaid.")");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            
            $arrobpagoriesgo = array(   'count' => 1,                                   'riesgo_scoreid'=>$obj->riesgoscoreid,          'desc_riesgoscore'=>$obj->desc_riesgo_score,
                                        'empresa_scoreid'=>$obj->empresa_scoreid,       'riesgo_factid'=>$obj->riesgofactid,
                                        'desc_riesgofact'=>$obj->desc_riesgo_factureate,'nrfact'=>$obj->nrfact,
                                        'crfact' => $obj->crfact,                       'nrscore' => $obj->nrscore,
                                        'crscore' => $obj->crscore,                     'nombrescore' => $obj->nombrescore,
                                        'path_informe_score' => $obj->path_informe_score,'path_informe_factu' => $obj->path_informe_factu,
                                        'f_evaluacion' => $obj->f_evaluacion,           'f_evalua_score' => $obj->f_evalua_score,
                                        'riesgo_id' => $obj->riesgo_id,                 'color_riesgo_fact' => $obj->color_riesgo_fact, 'colorfuente_riesgo_fact' => $obj->colorfuente_riesgo_fact,
                                        'color_riesgo_score' => $obj->color_riesgo_score,'colorfuente_riesgo_score' => $obj->colorfuente_riesgo_score);
        } else $arrobpagoriesgo = array('count' => 0,
                                        'color_riesgo_score' => 'de3d4c',               'colorfuente_riesgo_score' => 'fff',
                                        'crscore' => '---',                             'nrscore' => 'PENDIENTE',
                                        'color_riesgo_fact' => 'de3d4c',                'colorfuente_riesgo_fact' => 'fff',
                                        'crfact' => '---',                              'nrfact' => 'PENDIENTE',
                                        'riesgo_factid' => 0,                           'desc_riesgofact' => '',
                                        'riesgo_scoreid' => 0,                          'desc_riesgoscore' => '');

        //$conn->close();
        return $arrobpagoriesgo;
    }

    function get_riesgo_comercial($p_obp_id, $p_emisor_id){
        //$conn->close();
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select * from GET_RIESGO_COMERCIAL(".$p_obp_id.",".$p_emisor_id.")");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $arr_riesgo_comercial = array('riesgo_id'=>$obj->riesgoid, 'desc_riesgo'=>$obj->desc_riesgo,
                            'calificacion'=>$obj->calificacion, 'nombre_riesgo'=>$obj->nombre_riesgo);

        //$conn->close();
        return $arr_riesgo_comercial;
    }
    function get_historianeg_obpago($empresaid){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select * from GET_HISTORIANEG_OBPAGO(".$empresaid.")");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $arrhistoria = array('noperaciones'=>$obj->noperaciones, 'enproceso'=>$obj->enproceso,
                            'pagadaontime'=>$obj->pagadaontime, 'pagadadelay'=>$obj->pagadadelay,
                            'cobranza'=>$obj->cobranza, 'montofinanciadosol'=>$obj->montofinanciadosol,
                            'montofinanciadodol' => $obj->montofinanciadodol, 'montofinanciadoeur' => $obj->montofinanciadoeur,
                            'montocobranzasol' => $obj->montocobranzasol, 'montocobranzadol' => $obj->montocobranzadol,
                            'montocobranzaeur' => $obj->montocobranzaeur
                            );

        //$conn->close();
        return $arrhistoria;
    }
    function registro_empresa($arr_datos){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $conn_seg = new db_param; $conn_seg->connect();
        
        $idqry = $conn->query("select emp_crea_empresa('".$arr_datos['identificacion']."','".$arr_datos['nombre']."','".$arr_datos['direccion']."',
                                ".$arr_datos['sectorid'].",".$arr_datos['tamanoid'].",'".$arr_datos['actividad']."','".$arr_datos['nombre_representante']."',
                                '".$arr_datos['email_repre']."',".$arr_datos['tipodoc_repre'].",'".$arr_datos['nrodoc_repre']."',
                                '','','".$arr_datos['nombre_contacto']."',
                                '".$arr_datos['email_contacto']."',".$arr_datos['tipodoc_contacto'].",'".$arr_datos['nrodoc_contacto']."',
                                '".$arr_datos['telefono_contacto']."') as empresaid");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $empresaid = $obj->empresaid;

        if ($arr_datos['tipo_empresa'] != 46){
            $conn2->query("update empresa set t_empresa = ".$arr_datos['tipo_empresa'].", estado = 2 where id = ".$empresaid);
            $conn2->next_record();
        }

        //==== obtencion del secuencial de usuario para igualar con la de la empresa
        $idqry = $conn_seg->query("select nextval('s_usuario') as secuencial");

        if (!$idqry) echo pg_last_error($conn_seg->Link_ID);
        $conn_seg->next_record();
        
        //$conn->close(); $conn2->close(); $conn_seg->close();

        return $empresaid;
    }
    function update_obligado_pago($parr_datos){
        $conn = new db_param_trans; $conn->connect();

        $v_modifica = '';
        $v_indice = 0;

        if ($parr_datos['direccion'] != ''){$v_modifica .= " direccion = '".$parr_datos['direccion']."'"; $v_indice ++;}
        if ($parr_datos['tamano_id'] != 0){if($v_indice > 0) $v_modifica .= " ,tamanhoid = ".$parr_datos['tamano_id']; else $v_modifica .= " tamanhoid = ".$parr_datos['tamano_id']; $v_indice ++;}
        if ($parr_datos['paginaweb'] != ''){if($v_indice > 0) $v_modifica .= " ,paginaweb = '".$parr_datos['paginaweb']."'"; else $v_modifica .= " paginaweb = '".$parr_datos['paginaweb']."'"; $v_indice ++;}
        if ($parr_datos['actividad_id'] != 0){if($v_indice > 0) $v_modifica .= " ,sectoreconomicoid = ".$parr_datos['actividad_id']; else $v_modifica .= " sectoreconomicoid = ".$parr_datos['actividad_id']; $v_indice ++;}
        if ($parr_datos['actividad'] != ''){if($v_indice > 0) $v_modifica .= " ,actividad = '".$parr_datos['actividad']."'"; else $v_modifica .= " actividad = '".$parr_datos['actividad']."'"; $v_indice ++;}
        if ($parr_datos['contacto_nom'] != ''){if($v_indice > 0) $v_modifica .= " ,nombrecontacto = '".$parr_datos['contacto_nom']."'"; else $v_modifica .= " nombrecontacto = '".$parr_datos['contacto_nom']."'"; $v_indice ++;}
        if ($parr_datos['contacto_mail'] != ''){if($v_indice > 0) $v_modifica .= " ,emailcontacto = '".$parr_datos['contacto_mail']."'"; else $v_modifica .= " emailcontacto = '".$parr_datos['contacto_mail']."'"; $v_indice ++;}
        if ($parr_datos['contactodoc_id'] != 0){if($v_indice > 0) $v_modifica .= " ,tipodoccontacto = ".$parr_datos['contactodoc_id']; else $v_modifica .= " tipodoccontacto = ".$parr_datos['contactodoc_id']; $v_indice ++;}
        if ($parr_datos['contactodoc'] != ''){if($v_indice > 0) $v_modifica .= " ,identificacioncontacto = '".$parr_datos['contactodoc']."'"; else $v_modifica .= " identificacioncontacto = '".$parr_datos['contactodoc']."'"; $v_indice ++;}
        if ($parr_datos['contacto_telf'] != ''){if($v_indice > 0) $v_modifica .= " ,telefonocontacto = '".$parr_datos['contacto_telf']."'"; else $v_modifica .= " telefonocontacto = '".$parr_datos['contacto_telf']."'"; $v_indice ++;}

        $idqry = $conn->query("update empresa set ".$v_modifica." where id = ".$parr_datos['empresa_id']);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
        return 1;
    }
    function get_datos_emisor_full($emisorid){
        $conn = new db_param_trans;
        $conn->connect();
        
        $conn->query("select * from GET_DATOS_EMISOR_FULL(".$emisorid.")");
        $obj = $conn->next_record();
        
        $nombre_repre = $obj->nombre_repre.' '.$obj->apellido_repre;
        $arremisor = array('identificacion'=>$obj->identificacion, 'nombre'=>$obj->nombre,
                            'direccion'=>$obj->direccion, 'ubi1nombre'=>$obj->ubi1nombre,
                            'ubi2nombre'=>$obj->ubi2nombre, 'ubi3nombre'=>$obj->ubi3nombre,
                            'estado'=>$obj->estado, 'nombre_repre' => $nombre_repre,
                            'nrodoc_repre' => $obj->nrodoc_repre, 'email_repre' => $obj->email_repre,
                            'tdoc_repre' => $obj->tdoc_repre, 'doc_repre' => $obj->doc_repre,
                            'sectorid' => $obj->sectorid, 'sector' => $obj->sector,
                            'actividad' => $obj->actividad, 'tamanoid' => $obj->tamanoid,
                            'tamano' => $obj->tamano, 'docrepre_path' => $obj->docrepre_path,
                            'vigencia_path' => $obj->vigencia_path, 'nombre_contacto' => $obj->nombre_contacto,
                            'email_contacto' => $obj->email_contacto, 'tdoc_contacto' => $obj->tdoc_contacto,
                            'doc_contacto' => $obj->doc_contacto, 'nrodoc_contacto' => $obj->nrodoc_contacto,
                            'telf_contacto' => $obj->telf_contacto, 'estado_nombre' => $obj->r_estado_nombre
                        );
        
        //$conn->close();
        return $arremisor;
    }
    function actualiza_emisor($arr_datos, $tipo){
        $conn = new db_param_trans; $conn->connect();
        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fecha_hoy = date('Y-m-d');

        if ($tipo == 'full'){
            $idqry = $conn->query("select EMP_UPD_EMISOR_FULL(".$arr_datos['emisorid'].",'".$arr_datos['nombre']."','".$arr_datos['ruc']."',
                                            '".$arr_datos['direccion']."',".$arr_datos['tamanoid'].",".$arr_datos['sectorid'].",
                                            '".$arr_datos['actividad']."','".$arr_datos['nombre_repre']."','".$arr_datos['email_repre']."',
                                            ".$arr_datos['tdoc_repre'].",'".$arr_datos['nrodoc_repre']."','".$arr_datos['docrepre_path']."',
                                            '".$arr_datos['docrepre_file']."','".$arr_datos['podrepre_file']."','".$arr_datos['podrepre_path']."',
                                            '".$arr_datos['nombre_contacto']."','".$arr_datos['email_contacto']."',".$arr_datos['tdoc_contacto'].",
                                            '".$arr_datos['nrodoc_contacto']."','".$arr_datos['telefono_contacto']."',".$_SESSION['user']['usuarioid'].") as resultado");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
        } elseif ($tipo == 'info_empresa'){
            $idqry = $conn->query("select EMP_UPD_EMPRESA_INFO(".$arr_datos['empresaid'].",'".$arr_datos['direccion']."',".$arr_datos['tamanoid'].",
                                            ".$arr_datos['sectorid'].",'".$arr_datos['actividad']."',".$_SESSION['user']['usuarioid'].") as resultado");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
        } elseif ($tipo == 'LEGAL'){
            if ($arr_datos['tempresa'] == 'OP'){
                $idqry = $conn->query("update empresa set nombrerepre = '".$arr_datos['nombre_repre']."', emailrepre = '".$arr_datos['email_repre']."', 
                                            tipodocrepre = ".$arr_datos['tdoc_repre_id'].", identificacionrepre = '".$arr_datos['nrodoc_repre']."', telefonorepre = '".$arr_datos['telefono_repre']."',
                                            informe_legal_path = '".$arr_datos['informe_legal']."', f_informe_legal = '".$v_fecha_hoy."',
                                            u_informe_legal = ".$_SESSION['user']['usuarioid'].", estado_legal = 1 
                                        where id = ".$arr_datos['empresa_id']);

                if (!$idqry) echo pg_last_error($conn->Link_ID);
                $this->calcula_estado_empresa($arr_datos['empresa_id']);
            }
        }

        //$conn->close();
    }
    function enviar_registro_empresa($empresaid){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select EMP_ENVIAR_REGISTRO(".$empresaid.",".$_SESSION['user']['usuarioid'].") as resultado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        
        //$conn->close();
    }
    function get_empresas_xestado($estado, $rows, $rowini, $tipo, $t_empresa){
        $conn = new db_param_trans;
        $conn->connect();

        if ($tipo == 'count'){
            $idqry = $conn->query("select EMP_EMPRESAS_XESTADO_COUNT(".$estado.", ".$t_empresa.") as contador");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $resultado = $obj->contador;
        } else{
            $idqry = $conn->query("select * from EMP_EMPRESAS_XESTADO(".$estado.", ".$rowini.", ".$rows.", ".$t_empresa.")");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $resultado = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $resultado[$i] = array('empresaid' => $obj->empresaid, 'nrodoc' => $obj->nrodoc,
                                        'empresa' => $obj->empresa, 'f_registro' => $obj->f_registro,
                                        'paginaweb' => $obj->paginaweb, 'f_modifica' => $obj->f_modifica,
                                        'u_modifica_id' => $obj->u_modifica_id, 'f_envio' => $obj->f_envio,
                                        'u_envio_id' => $obj->u_envio_id, 'f_aprobacion' => $obj->f_aprobacion,
                                        'u_aprobacion_id' => $obj->u_aprobacion_id, 't_empresa' => $obj->t_empresa,
                                        'pendiente_riesgo' => $obj->pendiente_riesgo, 't_empresa_nombre' => $obj->t_empresa_nombre
                                    );
                $obj = $conn->next_record();
            }
        }
        
        //$conn->close();
        return $resultado;
    }

    function get_empresas ($p_tipo_consulta, $p_rows, $p_rowini, $p_filtros, $p_order){
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo_consulta == 'COUNT'){
            $v_qry = "  select  count(1) as contador
                        from    empresa, tipos
                        where   tipos.id = empresa.t_empresa and ".$p_filtros;

            $idqry = $conn->query($v_qry);

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $resultado = $obj->contador;
        } else {
            $v_qry = "  select  empresa.id as empresa_id, empresa.identificacion, empresa.nombre as empresa_nom, empresa.fregistro, empresa.paginaweb, empresa.f_modifica, 
                                empresa.u_modifica_id, empresa.f_envio, empresa.u_envio_id, empresa.f_aprobacion, empresa.u_aprobacion_id, 
                                empresa.t_empresa, empresa.pendiente_riesgo, tipos.nombre as t_empresa_nombre, empresa.emailcontacto, empresa.estado, 
                                estados.nombre as estado_nom, coalesce(tipo_riesgo_empresa.nombre,'VACIO') as categoria_nom, tipo_riesgo_empresa.calificacion,
                                empresa.telefonocontacto, coalesce(empresa.contrato_path,'VACIO') as contrato, 
                                coalesce(empresa.f_envio_contrato,'2000-01-01') as envio_contrato
                        from    ((empresa left outer join riesgo_empresa on riesgo_empresa.empresaid = empresa.id and riesgo_empresa.estado = 1) left outer join tipo_riesgo_empresa on tipo_riesgo_empresa.id = riesgo_empresa.riesgoid), tipos, estados 
                        where   tipos.id = empresa.t_empresa and estados.id = empresa.estado and ".$p_filtros." 
                        order by ".$p_order." 
                        limit ".$p_rows." offset ".$p_rowini;

            $idqry = $conn->query($v_qry);

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $resultado = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $resultado[$i] = array( 'empresa_id' => $obj->empresa_id,           'nrodoc' => $obj->identificacion,
                                        'empresa_nom' => $obj->empresa_nom,         'email' => $obj->emailcontacto,
                                        'telefono' => $obj->telefonocontacto,       'estado_nom' => $obj->estado_nom,
                                        'tipo_empresa_nom' => $obj->t_empresa_nombre,   'categoria' => $obj->categoria_nom,
                                        'calificacion' => $obj->calificacion,       'envio_contrato' => $obj->envio_contrato,
                                        'contrato' => $obj->contrato,                       'estado' => $obj->estado,
                                        'tipo_empresa' => $obj->t_empresa
                                    );
                $obj = $conn->next_record();
            }
        }

        //$conn->close();
        return $resultado;
    }

    function get_datos_empresa($p_empresa_id){
        $conn = new db_param_trans;
        $conn->connect();

        $conn->query("select * from EMP_DATOS_EMPRESA(".$p_empresa_id.")");
        $obj = $conn->next_record();

        $nombre_repre = $obj->nombre_repre.' '.$obj->apellido_repre;

        $arremisor = array('identificacion'=>$obj->identificacion, 'nombre'=>$obj->nombre,
                            'direccion'=>$obj->direccion, 'ubi1nombre'=>$obj->ubi1nombre,
                            'ubi2nombre'=>$obj->ubi2nombre, 'ubi3nombre'=>$obj->ubi3nombre,
                            'estado'=>$obj->estado, 'nombre_repre' => $nombre_repre,
                            'nrodoc_repre' => $obj->nrodoc_repre, 'email_repre' => $obj->email_repre,
                            'tdoc_repre' => $obj->tdoc_repre, 'doc_repre' => $obj->doc_repre,
                            'sectorid' => $obj->sectorid, 'sector' => $obj->sector,
                            'actividad' => $obj->actividad, 'tamanoid' => $obj->tamanoid,
                            'tamano' => $obj->tamano, 'docrepre_path' => $obj->docrepre_path,
                            'vigencia_path' => $obj->vigencia_path, 'nombre_contacto' => $obj->nombre_contacto,
                            'email_contacto' => $obj->email_contacto, 'tdoc_contacto' => $obj->tdoc_contacto,
                            'doc_contacto' => $obj->doc_contacto, 'nrodoc_contacto' => $obj->nrodoc_contacto,
                            'telf_contacto' => $obj->telf_contacto, 'f_registro' => $obj->f_registro,
                            'paginaweb' => $obj->paginaweb, 'f_ini_actividad' => $obj->f_ini_actividad,
                            'e_empresa_id' => $obj->e_empresa_id, 'e_empresa' => $obj->e_empresa,
                            'f_aprobacion' => $obj->f_aprobacion, 't_empresaid' => $obj->t_empresaid,
                            'pendiente_riesgo' => $obj->pendiente_riesgo, 't_empresa' => $obj->t_empresa,
                            'evaluacion_accionistas' => $obj->evaluacion_accionistas, 'informe_legal_path' => $obj->informe_legal_path,
                            'f_informe_legal' => $obj->f_informe_legal, 'contrato_path' => $obj->contrato_path, 'f_firma_contrato' => $obj->f_firma_contrato,
                            'estado_legal' => $obj->r_estado_legal, 'inversor_natural' => $obj->r_inversor_natural);
        
        //$conn->close();
        return $arremisor;
    }
    function get_datos_empresa_full($empresaid){
        $conn = new db_param_trans;
        $conn->connect();
        // no esta funcionando para OP
        $conn->query("select * from EMP_DATOS_EMPRESA_FULL(".$empresaid.")");
        $obj = $conn->next_record();
        $nombre_repre = $obj->nombre_repre.' '.$obj->apellido_repre;
        $arremisor = array('identificacion'=>$obj->identificacion, 'nombre'=>$obj->nombre,
                            'direccion'=>$obj->direccion, 'ubi1nombre'=>$obj->ubi1nombre,
                            'ubi2nombre'=>$obj->ubi2nombre, 'ubi3nombre'=>$obj->ubi3nombre,
                            'estado'=>$obj->estado, 'nombre_repre' => $nombre_repre,
                            'nrodoc_repre' => $obj->nrodoc_repre, 'email_repre' => $obj->email_repre,
                            'tdoc_repre' => $obj->tdoc_repre, 'doc_repre' => $obj->doc_repre,
                            'sectorid' => $obj->sectorid, 'sector' => $obj->sector,
                            'actividad' => $obj->actividad, 'tamanoid' => $obj->tamanoid,
                            'tamano' => $obj->tamano, 'docrepre_path' => $obj->docrepre_path,
                            'vigencia_path' => $obj->vigencia_path, 'nombre_contacto' => $obj->nombre_contacto,
                            'email_contacto' => $obj->email_contacto, 'tdoc_contacto' => $obj->tdoc_contacto,
                            'doc_contacto' => $obj->doc_contacto, 'nrodoc_contacto' => $obj->nrodoc_contacto,
                            'telf_contacto' => $obj->telf_contacto, 'f_registro' => $obj->f_registro,
                            'paginaweb' => $obj->paginaweb, 'f_ini_actividad' => $obj->f_ini_actividad,
                            'e_empresa_id' => $obj->e_empresa_id, 'e_empresa' => $obj->e_empresa,
                            'f_aprobacion' => $obj->f_aprobacion, 't_empresaid' => $obj->t_empresaid,
                            'pendiente_riesgo' => $obj->pendiente_riesgo, 't_empresa' => $obj->t_empresa,
                            'f_evaluacion_riesgo' => $obj->f_evaluacion_riesgo, 'r_factureateid' => $obj->r_factureateid,
                            'r_scoreid' => $obj->r_scoreid, 'r_score_desc' => $obj->r_score_desc,
                            'r_factureate_desc' => $obj->r_factureate_desc, 'empresa_scoreid' => $obj->empresa_scoreid,
                            'nombre_riesgo_fact' => $obj->nombre_riesgo_fact, 'califica_fact' => $obj->califica_fact,
                            'nombre_riesgo_score' => $obj->nombre_riesgo_score, 'califica_score' => $obj->califica_score,
                            'empresa_score' => $obj->empresa_score, 'u_envio_id' => $obj->u_envio_id,
                            'u_aprobacion_id' => $obj->u_aprobacion_id, 'path_informe_factu' => $obj->path_informe_factu,
                            'path_informe_score' => $obj->path_informe_score
                        );
        
        //$conn->close();
        return $arremisor;
    }
    function gestiona_empresa ($arr_input){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select EMP_GESTIONA_EMPRESA(".$arr_input['empresaid'].",'".$arr_input['accion']."','".$arr_input['motivo']."',
                                                ".$_SESSION['user']['usuarioid'].") as resultado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }
    function empresas_riesgo(){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select id, nombre from empresa_score_riesgos where estado > 0");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $arr_empresas = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_empresas[$i] = array('id' => $obj->id, 'nombre' => $obj->nombre);
            $obj = $conn->next_record();
        }

        //$conn->close();
        return $arr_empresas;
    }
    function niveles_riesgo(){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select id, nombre, calificacion, descripcion, color from tipo_riesgo_empresa where estado > 0");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $arr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_result[$i] = array('id' => $obj->id, 'nombre' => $obj->nombre, 'calificacion' => $obj->calificacion,
                                    'descripcion' => $obj->descripcion, 'color' => $obj->color);
            $obj = $conn->next_record();
        }

        //$conn->close();
        return $arr_result;
    }
    function registra_riesgos($arr_input){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select EMP_REGISTRA_RIESGOS(".$arr_input['empresaid'].",".$_SESSION['user']['usuarioid'].", ".$arr_input['scoreid'].",
                                                            ".$arr_input['nivel_scoreid'].",'".$arr_input['justi_nivel_score']."', ".$arr_input['nivel_factuid'].",
                                                            '".$arr_input['justi_nivel_factu']."','".$arr_input['path_score']."',
                                                            '".$arr_input['path_factu']."') as contador");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_resultado = $obj->contador;
        
        //$conn->close();

        return $v_resultado;
    }
    function update_riesgos($v_arr_param){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $conn3 = new db_param_trans; $conn3->connect();
        $conn4 = new db_param_trans; $conn4->connect();
        $conn_risk = new db_param_trans; $conn_risk->connect();
        
        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fecha_hoy = date('Y-m-d');

        if ($v_arr_param['tipo_riesgo'] == 'score'){ 
            if ($v_arr_param['id'] == 0){   // CUANDO NO TIENE NINGUNA EVALUACION REGISTRADA
                $v_arr_aux = array('empresaid'=>$v_arr_param['empresa_id'], 'scoreid'=>$v_arr_param['empresa_score_id'], 'nivel_scoreid'=>$v_arr_param['riesgo_id'],
                                    'justi_nivel_score'=>$v_arr_param['descripcion'], 'nivel_factuid'=>0, 'justi_nivel_factu'=>'', 'path_score'=>$v_arr_param['informe_riesgo'],
                                    'path_factu'=>'');
                $v_riesgo_id = $this->registra_riesgos($v_arr_aux);
            } else {
                $v_tipo_riesgo = 66;    //RIESGO SCORE
                //if ($v_arr_param['dias_xvencer'] < 0) $v_estado = 42;   // CADUCADO
                //else $v_estado = 1;     // ACTIVO
                $v_estado = 1;     // ACTIVO

                if ($v_arr_param['id'] > 0){
                    $idqry = $conn2->query("select EMP_UPDATE_RIESGOS(".$v_arr_param['id'].",".$_SESSION['user']['usuarioid'].", ".$v_tipo_riesgo.") as contador");
                    if (!$idqry) echo pg_last_error($conn2->Link_ID);
                    $conn2->next_record();

                    $idqry = $conn->query("update riesgo_empresa set f_evalua_score = '".$v_fecha_hoy."', riesgoscoreid = ".$v_arr_param['riesgo_id'].", 
                                            desc_riesgo_score = '".$v_arr_param['descripcion']."', empresa_scoreid = ".$v_arr_param['empresa_score_id'].",
                                            informe_score = '".$v_arr_param['informe_riesgo']."', usuarioid = ".$_SESSION['user']['usuarioid'].", estado = ".$v_estado." 
                                        where id = ".$v_arr_param['id']);
                    if (!$idqry) echo pg_last_error($conn->Link_ID);
                    $conn->next_record();
                }

                $v_riesgo_id = $v_arr_param['riesgo_id'];
            }
        } else{
            //==== riesgo factureate

            if ($v_arr_param['id'] == 0){
                //==== primer ingreso de riesgo de la empresa

                $v_arr_aux = array('empresaid'=>$v_arr_param['empresa_id'], 'scoreid'=>0, 'nivel_scoreid'=>0,
                                    'justi_nivel_score'=>'', 'nivel_factuid'=>$v_arr_param['riesgo_id'], 'justi_nivel_factu'=>$v_arr_param['descripcion'], 
                                    'path_score'=>'', 'path_factu'=>$v_arr_param['informe_riesgo']);
                $v_riesgo_id = $this->registra_riesgos($v_arr_aux);
                $v_estado = 1;
            } else {
                $v_tipo_riesgo = 67;    // RIESGO FACTUREATE
                $v_estado = 1;

                $idqry = $conn2->query("select EMP_UPDATE_RIESGOS(".$v_arr_param['id'].",".$_SESSION['user']['usuarioid'].", ".$v_tipo_riesgo.") as contador");
                if (!$idqry) echo pg_last_error($conn2->Link_ID);
                $conn2->next_record();

                $idqry = $conn->query("update riesgo_empresa set fevaluacion = '".$v_fecha_hoy."', riesgoid = ".$v_arr_param['riesgo_id'].", 
                                        desc_riesgo_factureate = '".$v_arr_param['descripcion']."', 
                                        informe_factureate = '".$v_arr_param['informe_riesgo']."', usuarioid = ".$_SESSION['user']['usuarioid'].", estado = ".$v_estado." 
                                    where id = ".$v_arr_param['id']);
                if (!$idqry) echo pg_last_error($conn->Link_ID);
                $conn->next_record();

                $v_riesgo_id = $v_arr_param['riesgo_id'];
            }

            if ($v_estado == 1) $this->actualiza_estado_riesgo_empresa($v_arr_param['empresa_id']);
            else{ 
                $v_query = "update empresa set pendiente_riesgo = 'SI' where id = ".$v_arr_param['empresa_id'];
                $idqry = $conn4->query($v_query);

                if (!$idqry) echo pg_last_error($conn4->Link_ID);
                $conn4->next_record();
            }
        
            $this->calcula_estado_empresa($v_arr_param['empresa_id']);    
        }

        //==== se coloca el riesgo a las facturas enviadas que no tienen riesgo

        $v_sql = "  update  factura set riesgofacturaid = ".$v_riesgo_id." 
                    where   clienteid = ".$v_arr_param['empresa_id']." and estado = 12 and coalesce(riesgofacturaid,0) = 0";

        $idqry = $conn_risk->query($v_sql);
        if (!$idqry) echo pg_last_error($conn_risk->Link_ID);
        $conn_risk->next_record();

        //==== calculo del riesgo factureate
        
        $this->calcula_riesgo_factureate($v_riesgo_id);

        //$conn->close(); $conn2->close(); $conn3->close(); $conn4->close();

        return $v_riesgo_id;
    }

    function actualiza_estado_riesgo_empresa ($p_empresa_id){
        $conn = new db_param_trans; $conn->connect();

        $v_qry = "select EMP_UPDATE_RIESGO_EMPRESA(".$p_empresa_id.") as result";
        $idqry = $conn->query($v_qry);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $resultado = $obj->result;

        //$conn->close();
        return $resultado;
    }

    function calcula_estado_empresa($p_empresa_id){
        $conn = new db_param_trans; $conn->connect();
        
        $idqry = $conn->query("select EMP_VERIFICA_ESTADO_EMPRESA(".$p_empresa_id.") as result");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $resultado = $obj->result;

        //$conn->close();
        return $resultado;
    }
    function calcula_riesgo_factureate($p_riesgoemp_id){
        
        $v_fecha_hoy = date('d-m-Y');

        
    }

    function dias_vigencia_riesgos($parametro){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select nombre, valor_num from parametros where id = ".$parametro);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $retorno = array('valor' => $obj->valor_num, 'descripcion' => $obj->nombre);
        
        //$conn->close();
        return $retorno;
    }
    function kpi_op_xvalidar(){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select count(1) as contador from empresa where estado = 2");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $retorno['count'] = $obj->contador;
        
        //$conn->close();
        return $retorno;
    }
    function kpi_op_riesgo_xvencer(){
        $conn = new db_param_trans;
        $conn2 = new db_param_trans;
        $conn3 = new db_param_trans;
        $conn->connect();
        $conn2->connect();
        $conn3->connect();

        $idqry = $conn->query("select valor_num from parametros where id = 6");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_max_dias = $obj->valor_num;
        $idqry = $conn2->query("select valor_num from parametros where id = 18");
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();
        $v_preventivo = $obj2->valor_num;
        $v_dias_transcurridos = $v_max_dias - $v_preventivo;
        $idqry = $conn3->query("select count(1) as contador from riesgo_empresa where riesgo_empresa.estado = 1 and (current_date - riesgo_empresa.fevaluacion) < ".$v_max_dias." and (current_date - riesgo_empresa.fevaluacion) >= ".$v_dias_transcurridos);
        if (!$idqry) echo pg_last_error($conn3->Link_ID);
        $obj3 = $conn3->next_record();
        $retorno['count'] = $obj3->contador;
        $retorno['maximo'] = $v_preventivo;
        
        //$conn->close();
        //$conn2->close();
        //$conn3->close();

        return $retorno;
    }
    function kpi_op_riesgo_vencido(){
        $conn = new db_param_trans;
        $conn3 = new db_param_trans;
        $conn->connect();
        $conn3->connect();

        $idqry = $conn->query("select valor_num from parametros where id = 6");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_max_dias = $obj->valor_num;
        $idqry = $conn3->query("select count(1) as contador from riesgo_empresa where riesgo_empresa.estado = 1 and (current_date - riesgo_empresa.fevaluacion) >= ".$v_max_dias);
        if (!$idqry) echo pg_last_error($conn3->Link_ID);
        $obj3 = $conn3->next_record();
        $retorno['count'] = $obj3->contador;
        $retorno['maximo'] = $v_max_dias;
        
        //$conn->close();
        //$conn3->close();

        return $retorno;
    }
    function get_menu_acceso (){
        $conn = new db_param;
        $conn->connect();
        $arr_result = array();

        $idqry = $conn->query("select menu.id, menu.nombre as nombre_visual, menu.orden, menu.pagina, acceso.nombre as nombre_back, acceso.codigo, acceso.id as acceso_id
                                from menu, acceso
                                where menu.estado = 1 and acceso.menuid = menu.id and acceso.estado = 1");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_result[$i] = array('id' => $obj->id, 'nombre_visual' => $obj->nombre_visual, 'orden' => $obj->orden,
                                    'pagina' => $obj->pagina, 'nombre_back' => $obj->nombre_back, 'codigo'=>$obj->codigo,
                                    'acceso_id'=>$obj->acceso_id);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $arr_result;
    }
    function insert_menu ($p_nombre_back, $p_nombre_visual, $p_codigo, $p_orden, $p_pagina){
        $conn = new db_param;
        $conn2 = new db_param;
        $conn3 = new db_param;
        
        $conn->connect();
        $conn2->connect();
        $conn3->connect();
        
        $idqry = $conn->query("select max(menu.id) as max_menu, max(acceso.id) as max_acceso from menu, acceso");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $v_menu_id = $obj->max_menu + 1;
        $v_acceso_id = $obj->max_acceso + 1;

        $idqry = $conn2->query("insert into menu(id, nombre, orden, estado, nivel, padreid, pagina) values(".$v_menu_id.",'".$p_nombre_visual."',".$p_orden.",1,1,0,'".$p_pagina."')");
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();

        $idqry = $conn3->query("insert into acceso(id, nombre, estado, codigo, menuid) values(".$v_acceso_id.",'".$p_nombre_back."',1,'".$p_codigo."',".$v_menu_id.")");
        if (!$idqry) echo pg_last_error($conn3->Link_ID);
        $obj3 = $conn3->next_record();
        
        /*$conn->close();
        $conn2->close();
        $conn3->close();*/

        return 1;
    }
    function get_menu_acceso_row ($p_menu_id){
        $conn = new db_param;
        $conn->connect();
        
        $idqry = $conn->query("select menu.id, menu.nombre as nombre_visual, menu.orden, menu.pagina, acceso.nombre as nombre_back, acceso.codigo, acceso.id as acceso_id
                                from menu, acceso
                                where menu.estado = 1 and acceso.menuid = menu.id and acceso.estado = 1 and menu.id = ".$p_menu_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $arr_result = array('id' => $obj->id, 'nombre_visual' => $obj->nombre_visual, 'orden' => $obj->orden,
                            'pagina' => $obj->pagina, 'nombre_back' => $obj->nombre_back, 'codigo'=>$obj->codigo,'acceso_id'=>$obj->acceso_id);

        //$conn->close();

        return $arr_result;
    }
    function update_menu ($p_nombre_back, $p_nombre_visual, $p_codigo, $p_orden, $p_pagina, $p_menu_id, $p_acceso_id){
        $conn = new db_param;
        $conn2 = new db_param;
        
        $conn->connect();
        $conn2->connect();
        
        $idqry = $conn->query("update menu set nombre = '".$p_nombre_visual."', orden = ".$p_orden.", pagina = '".$p_pagina."' where id = ".$p_menu_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $idqry = $conn2->query("update acceso set nombre = '".$p_nombre_back."', codigo = '".$p_codigo."' where id = ".$p_acceso_id);
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();

        /*$conn->close();
        $conn2->close();*/
        
        return 1;
    }
    function get_perfiles (){
        $conn = new db_param;
        $conn->connect();
        $arr_result = array();
        
        $idqry = $conn->query("select perfil_usuario.id, perfil_usuario.nombre, perfil_usuario.descripcion, perfil_usuario.tipo as tipo_id, tipos.nombre as tipo
                                from perfil_usuario, tipos
                                where perfil_usuario.tipo = tipos.id and perfil_usuario.estado = 1
                                order by perfil_usuario.tipo");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_result[$i] = array('perfil_id' => $obj->id, 'nombre' => $obj->nombre, 'descripcion' => $obj->descripcion,
                                    'tipo_id' => $obj->tipo_id, 'tipo' => $obj->tipo);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $arr_result;
    }
    function get_accesos ($p_perfil_id, $p_tipo){
        $conn = new db_param;
        $conn->connect();
        $arr_result = array();
        
        if ($p_tipo == 'ASIGNADO'){
            $idqry = $conn->query("select perfil_permiso.menuid, perfil_permiso.accesoid, menu.nombre, menu.pagina, acceso.nombre as nombre_visual
                                    from perfil_permiso, menu, acceso
                                    where perfil_permiso.menuid = menu.id and perfil_permiso.accesoid = acceso.id and acceso.menuid = menu.id and 
                                        perfil_permiso.perfilid = ".$p_perfil_id);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $arr_result[$i] = array('menu_id' => $obj->menuid, 'acceso_id' => $obj->accesoid, 'nombre' => $obj->nombre, 'pagina' => $obj->pagina,
                                        'nombre_visual' => $obj->nombre_visual);
                $obj = $conn->next_record();
            }
        } elseif ($p_tipo == 'NASIGNADO'){
            $idqry = $conn->query("select menu.id, menu.nombre, menu.pagina, acceso.id as acceso_id, acceso.nombre as nombre_visual, menu.referencia
                                    from menu, acceso
                                    where menu.estado = 1 and acceso.menuid = menu.id and acceso.estado = 1 and menu.id not in (select perfil_permiso.menuid
                                        from perfil_permiso where perfil_permiso.perfilid = ".$p_perfil_id.")");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $arr_result[$i] = array('menu_id' => $obj->id, 'nombre' => $obj->nombre, 'pagina' => $obj->pagina, 'acceso_id' => $obj->acceso_id,
                                        'nombre_visual' => $obj->nombre_visual, 'referencia'=> $obj->referencia);
                $obj = $conn->next_record();
            }
        }

        //$conn->close();

        return $arr_result;
    }
    function update_accesos ($p_tipo, $p_datos, $p_perfil_id){
        $conn = new db_param;
        
        $conn->connect();
        
        if ($p_tipo == 'DELETE'){
            for ($i=0; $i<count($p_datos); $i++){
                $idqry = $conn->query("delete from perfil_permiso where perfilid = ".$p_perfil_id." and menuid = ".$p_datos[$i]['menu_id']);
                if (!$idqry) echo pg_last_error($conn->Link_ID);
            }
        } elseif ($p_tipo == 'INSERT'){
            for ($i=0; $i<count($p_datos); $i++){
                $idqry = $conn->query("insert into perfil_permiso(perfilid, menuid, accesoid, permisoid) values(".$p_perfil_id.",".$p_datos[$i]['menu_id'].",".$p_datos[$i]['acceso_id'].",0)");
                if (!$idqry) echo pg_last_error($conn->Link_ID);
            }
        }
        $obj = $conn->next_record();

        //$conn->close();
        
        return 1;
    }
    function registra_notificacion_inversionistas($p_finan_id, $p_notifica_id, $p_comp_body){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select MAIL_NOTIFICA_INVERSIONISTA(".$p_finan_id.",".$p_notifica_id.",'".$p_comp_body."') as contador");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        //$conn->close();
    }
    function validar_cliente ($p_arr_cliente){
        $v_nombre = $this->get_nombre_empresa_xdoc($p_arr_cliente['cliente_nro']);
        return $v_nombre;
    }
    function calcula_ganancia($p_finicio, $p_ffin, $p_tia, $p_inversion){
        $v_tmes = pow(($p_tia+1),(1/12)) - 1;
        $v_tdia = pow(($v_tmes+1),(1/30)) - 1;
        $v_dt_finicio = new DateTime($p_finicio);
        $v_dt_ffin = new DateTime($p_ffin);
        $v_diferencia = $v_dt_finicio->diff($v_dt_ffin);
        $v_dias = $v_diferencia->days;
        $v_ganancia = $v_dias * $v_tdia * $p_inversion;

        return $v_ganancia;
    }
    function calcula_dif_fechas($p_finicio, $p_hinicio, $p_ffin, $p_hfin){
        $v_dt_finicio = new DateTime($p_finicio.' '.$p_hinicio);
        $v_dt_ffin = new DateTime($p_ffin.' '.$p_hfin);
        $v_dif = $v_dt_finicio->diff($v_dt_ffin);
        $v_arr_result = array('dias'=>$v_dif->d, 'horas'=>$v_dif->h, 'desc'=>$v_dif->d.' dias, '.$v_dif->h.' horas');

        return $v_arr_result;
    }
    function get_accionistas ($p_empresa_id){
        $conn = new db_param_trans;
        $conn->connect();
        $arr_result = array();
        
        $idqry = $conn->query("select empresa_accionistas.id, empresa_accionistas.nombre, empresa_accionistas.tipodoc_id, 
                                    empresa_accionistas.nro_documento, tipos.nombre as tipodoc
                                from empresa_accionistas, tipos
                                where empresa_accionistas.empresa_id = ".$p_empresa_id." and tipos.id = empresa_accionistas.tipodoc_id");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_result[$i] = array('accionista_id' => $obj->id, 'accionista_nombre' => $obj->nombre, 'tipodoc_id' => $obj->tipodoc_id, 'nro_documento' => $obj->nro_documento,
                                    'tipodoc' => $obj->tipodoc);
            $obj = $conn->next_record();
        }
        
        //$conn->close();

        return $arr_result;
    }
    function guarda_accionistas($parr_accionistas){
        $conn = new db_param_trans;
        $conn->connect();

        for ($i=0; $i<count($parr_accionistas); $i++){
            $idqry = $conn->query("select EMP_REGISTRA_ACCIONISTA('".$parr_accionistas[$i]['nombre']."',".$parr_accionistas[$i]['tipodoc'].",
                                    '".$parr_accionistas[$i]['nro_doc']."',".$parr_accionistas[$i]['empresa_id'].")");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
        }

        $conn->next_record();
        $this->evalua_accionistas($parr_accionistas[0]['empresa_id']);

        //$conn->close();

        return 1;
    }
    function evalua_accionistas($p_empresa_id){
        $conn = new db_param_trans;
        $conn->connect();

        $idqry = $conn->query("select EMP_EVALUA_ACCIONISTAS(".$p_empresa_id.",".$_SESSION['user']['usuarioid'].") as resultado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        
        $v_obj = $conn->next_record();
        $v_resultado = $v_obj->resultado;

        //$conn->close();

        return $v_resultado;
    }
    function aprueba_empresa($parr_datos){
        $conn = new db_param_trans;
        $obj_mailing = new mail_util;

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $conn->connect();
        $v_fecha_hoy = date('Y-m-d');
        // EMPRESA QUEDA COMO PRE APROBADA
        if ($parr_datos['valida_url_contrato'] == 'SI') $v_sql_contrato = ",link_firma_contrato = '".$parr_datos['url_contrato']."', f_envio_contrato = '".$v_fecha_hoy."'";
        else $v_sql_contrato = '';

        $idqry = $conn->query("update empresa set estado = 50, informe_legal_path = '".$parr_datos['informe_legal']."', f_informe_legal = '".$v_fecha_hoy."', 
                                u_informe_legal = ".$_SESSION['user']['usuarioid'].$v_sql_contrato.", f_aprobacion = '".$v_fecha_hoy."', u_aprobacion_id = ".$_SESSION['user']['usuarioid']." 
                                where id = ".$parr_datos['empresa_id']);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        
        $v_obj = $conn->next_record();
        // PREPARACION DE CONTRATOS PARA LA FIRMA
        //.......
        // ENVIO DEL CORREO A LA EMPRESA
        if ($parr_datos['valida_url_contrato'] == 'SI') $v_link = $parr_datos['url_contrato'];
        else $v_link = 'el de la integracion con el suplidor';

        $varr_empresa = $this->get_datos_emisor_full($parr_datos['empresa_id']);
        $arr_mail_user = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                'mail_destino' => $varr_empresa['email_contacto'],
                                'subject' => 'VINCULACION CON FACTUREATE',
                                'body' => 'Su solicitud de vinculacion para vender sus facturas en FACTUREATE ha sido aprobada.<br><br>
                                            Empresa: '.$varr_empresa['nombre'].'<br>
                                            RNC: '.$varr_empresa['identificacion'].'<br><br>
                                            Lo siguiente que debe hacer es revisar y firmar el contrato de vinculacion mediante el siguiente link donde no debe ingresar 
                                            ninguna informacion confidencial solo firmar biometricamente, le recomendamos leer el documento antes de firmar en el siguiente link.<br>
                                            <a href="'.$v_link.'" target="_blank">CONTRATO CON FACTUREATE</a><br><br>
                                            FACTUREATE');
        
        $obj_mailing->enviar_correo($arr_mail_user);
        
        //$conn->close();

        return 1;
    }
    function registra_contrato_vinculacion($parr_datos){
        $conn = new db_param_trans;
        $obj_mailing = new mail_util;

        $conn->connect();
        $v_fecha_hoy = date('Y-m-d');
        
        $idqry = $conn->query("update empresa set estado = 3, contrato_path = '".$parr_datos['link_contrato']."', f_firma_contrato = '".$v_fecha_hoy."'
                                where id = ".$parr_datos['empresa_id']);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();
        // ENVIO DEL CORREO A LA EMPRESA
        $v_link = 'https://factureate-webapp-cadjdpb8ccb9emg6.eastus-01.azurewebsites.net/';
        $varr_empresa = $this->get_datos_emisor_full($parr_datos['empresa_id']);
        $arr_mail_user = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                'mail_destino' => $varr_empresa['email_contacto'],
                                'subject' => 'VINCULACION CON FACTUREATE',
                                'body' => 'EN HORA BUENA !!!!!!. Ya puedes iniciar a registrar tus facturas y obtener financiamiento.<br><br>
                                            Empresa: '.$varr_empresa['nombre'].'<br>
                                            RNC: '.$varr_empresa['identificacion'].'<br><br>
                                            Ya puedes ingresar a la <a href="'.$v_link.'" target="_blank">plataforma FACTUREARE</a> haciendo click <a href="'.$v_link.'" target="_blank">AQUI</a> y registrar tus facturas para obtener el financiamiento que necesitas.<br><br>
                                            usuario: [DOCUMENTO DE IDENTIFICACION]
                                            password:[FUE ENVIADO EN UN CORREO ANTERIOR A ESTE] <br><br>
                                            <b>[IMPORTANTE]</b> Nunca compartas tu acceso a la plataforma FACTUREATE porque eres el unico responsable de las facturas que venderas.<br><br>
                                            * Tildes omitidas intencionalmente
                                            <br><br>============
                                            <br>FACTUREATE');
        
        $obj_mailing->enviar_correo($arr_mail_user);
        
        //$conn->close();

        return 1;
    }

    function notifica_inversores($parr_datos){
        return 1;
    }

    function get_datos_inversor($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "  select  inversionista.nombre, inversionista.apellido, inversionista.email, inversionista.telefono, inversionista.id, tipos.nombre as categoria, 
                            tipos.dato_num as comision, inversionista.documentopath, inversionista.estado, coalesce(inversionista.estado_anulacion_id,0) as estado_anulacion_id,
                            inversionista.contrato_path, estados.nombre as estado_anulacion_nombre, inversionista.ocupacion_id,
                            inversionista.ec_nomina_path, inversionista.servicios_path, inversionista.kwc, inversionista.movimientos_bancarios_path,
                            inversionista.estados_financieros_path, inversionista.kyc_path, inversionista.actividad_descripcion, inversionista.registro_mercantil_path,
                            inversionista.declaracion_impuestos_path, inversionista.tipo_registro, inversionista.tipodoc, inversionista.identificacion,
                            inversionista.direccion, inversionista.tipo_inversor, inversionista.informe_plaft, inversionista.contrato_enviado,
                            inversionista.estado_fiducia, inversionista.pep, ocupacion.nombre as ocupacion_nombre 
                    from ((inversionista left outer join estados on inversionista.estado_anulacion_id = estados.id) left outer join tipos as ocupacion on ocupacion.id = inversionista.ocupacion_id), tipos  
                    where inversionista.inversor_id = ".$p_inversor_id." and tipos.id = inversionista.categoria_id";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $varr_result = array(   'inversor_nombre' => $obj->nombre,      'inversor_apellido' => $obj->apellido,      'inversor_email' => $obj->email, 
                                'inversor_telefono' => $obj->telefono,  'inversor_sec' => $obj->id,                 'categoria' => $obj->categoria, 
                                'comision' => $obj->comision,           'documento'=>$obj->documentopath,           'estado_id' => $obj->estado, 
                                'contrato' => $obj->contrato_path,      'estado_anulacion_id' => $obj->estado_anulacion_id, 
                                'estado_anulacion_nombre' => $obj->estado_anulacion_nombre,                         'ocupacion_id' => $obj->ocupacion_id,
                                'telefono' => $obj->telefono,           'ec_laboral_path' => $obj->ec_nomina_path,  'f_servicios_path' => $obj->servicios_path,
                                'kyc_path' => $obj->kyc_path,           'kwc' => $obj->kwc,                         'movimientos_banco_path' => $obj->movimientos_bancarios_path,
                                'eeff_path' => $obj->estados_financieros_path,                                      'actividad_descripcion' => $obj->actividad_descripcion,
                                'registro_mercantil_path' => $obj->registro_mercantil_path,                         'declaracion_impuestos_path' => $obj->declaracion_impuestos_path,
                                'tipo_registro' => $obj->tipo_registro, 'tipodoc' => $obj->tipodoc,                 'identificacion' => $obj->identificacion,
                                'direccion' => $obj->direccion,         'tipo_inversor' => $obj->tipo_inversor,     'informe_plaft' => $obj->informe_plaft,
                                'contrato_enviado' => $obj->contrato_enviado,                                       'estado_fiducia' => $obj->estado_fiducia,
                                'pep' => $obj->pep,                     'ocupacion_nombre' => $obj->ocupacion_nombre);

        //$conn->close();

        return $varr_result;
    }

    function kpi_clo (){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select * from KPI_CLO_v2()");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array('qinversor_sineval_alert' => $obj->r_qinversor_sineval_alert, 'qinversor_sineval_max' => $obj->r_qinversor_sineval_max, 
            'qinversor_sin_plaft' => $obj->r_qinversor_sinplaft, 'qinversor_plaft_alert' => $obj->r_qinversor_plaft_alert, 'qinversor_sineval' => $obj->r_qinversor_sineval,
            'qinversor_plaft_vencido' => $obj->r_qinversor_plaft_vencido, 'qcontratos_xenviar' => $obj->r_qcontratos_xenviar,
            'qcontratos_xenviar_alert' => $obj->r_qcontratos_xenviar_alert, 'qcontratos_sinfirma' => $obj->r_qcontratos_sinfirma,
            'qcontratos_sinfirma_alert' => $obj->r_qcontratos_sinfirma_alert, 'qoperaciones_sin_endoso' => $obj->r_operaciones_sinendoso,
            'qempresas_sineval' => $obj->r_qempresas_sineval_legal, 'qempresas_sineval_alert' => $obj->r_qempresas_sineval_legal_alert,
            'qempresas_conflicto'=>$obj->r_qempresas_conflicto_alert);

        //$conn->close();

        return $varr_result;
    }

    function kpi_fiducia(){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select * from KPI_FIDUCIA()");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $varr_result = array('dias_depuracion' => $obj->r_dias_depuracion,              'qdepuracion_pendiente_alert' => $obj->r_qdepuracion_pendiente_alert, 
                             'qdepuracion_pendiente' => $obj->r_qdepuracion_pendiente,  'dias_dd' => $obj->r_dias_dd, 
                             'qdd_pendiente_alert' => $obj->r_qdd_pendiente_alert,      'qdd_pendiente' => $obj->r_qdd_pendiente);

        //$conn->close();

        return $varr_result;
    }

    function ordenes_transferencia($p_tipo, $p_estado, $p_rowini, $p_numrows){
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo == 'COUNT'){
            $idqry = $conn->query("select count(1) as resultado 
                                from orden_transferencia, tipos, tipos as tipo_moneda
                                where orden_transferencia.estado_id = ".$p_estado." and tipos.id = orden_transferencia.destinatario_tipo and 
                                    tipo_moneda.id = orden_transferencia.moneda_id");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $varr_result = $obj->resultado;
        } else {
            $idqry = $conn->query(" select  orden_transferencia.id, orden_transferencia.destinatario_tipo, tipos.nombre as destinatario_tipo_nombre, 
                                            orden_transferencia.cuenta_bancaria, orden_transferencia.nombre_destinatario, orden_transferencia.fecha, 
                                            orden_transferencia.operacion_id, orden_transferencia.moneda_id, orden_transferencia.monto, tipo_moneda.nombre as moneda,
                                            orden_transferencia.destinatario_id 
                                    from    orden_transferencia, tipos, tipos as tipo_moneda
                                    where   orden_transferencia.estado_id = ".$p_estado." and tipos.id = orden_transferencia.destinatario_tipo and 
                                            tipo_moneda.id = orden_transferencia.moneda_id
                                    limit ".$p_numrows." OFFSET ".$p_rowini);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $varr_result = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $varr_result[$i] = array('ot_id' => $obj->id, 'destino_tipo_id' => $obj->destinatario_tipo, 'destino_tipo' => $obj->destinatario_tipo_nombre,
                                'cuenta_banco' => $obj->cuenta_bancaria, 'destino_nombre' => $obj->nombre_destinatario, 'fecha_orden' => $obj->fecha,
                                'operacion_id' => $obj->operacion_id, 'moneda_id' => $obj->moneda_id, 'monto' => $obj->monto, 'moneda' => $obj->moneda,
                                'destinatario_id' => $obj->destinatario_id);

                $obj = $conn->next_record();
            }
        }

        //$conn->close();
        return $varr_result;
    }

    function get_orden_transferencia($p_ot){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $idqry = $conn->query(" select  orden_transferencia.destinatario_tipo, tipos.nombre as destinatario_tipo_nombre, 
                                        orden_transferencia.cuenta_bancaria, orden_transferencia.nombre_destinatario, orden_transferencia.fecha, 
                                        orden_transferencia.operacion_id, orden_transferencia.moneda_id, orden_transferencia.monto, tipo_moneda.nombre as moneda,
                                        orden_transferencia.destinatario_id, orden_transferencia.motivo_id, motivo.nombre as motivo_nombre, subasta.id as subasta_id 
                                from    orden_transferencia, tipos, tipos as tipo_moneda, tipos as motivo, subasta
                                where   orden_transferencia.id = ".$p_ot." and tipos.id = orden_transferencia.destinatario_tipo and 
                                        tipo_moneda.id = orden_transferencia.moneda_id and motivo.id = orden_transferencia.motivo_id and subasta.facturaid = orden_transferencia.operacion_id");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array(   'destino_tipo_id' => $obj->destinatario_tipo,   'destino_tipo' => $obj->destinatario_tipo_nombre,
                                'cuenta_banco' => $obj->cuenta_bancaria,        'destino_nombre' => $obj->nombre_destinatario,      'fecha_orden' => $obj->fecha,
                                'operacion_id' => $obj->operacion_id,           'moneda_id' => $obj->moneda_id,                     'monto' => $obj->monto,         'moneda' => $obj->moneda,
                                'destinatario_id' => $obj->destinatario_id,     'motivo_id' => $obj->motivo_id,                     'motivo' => $obj->motivo_nombre,
                                'subasta_id' => $obj->subasta_id);
        
        if ($obj->destinatario_tipo == 73){     // VENDEDOR O EMISOR
            $qry = "select  empresa.emailcontacto, empresa_cuenta_banco.tcuenta_id as tipo_cuenta_banco, empresa_cuenta_banco.banco_id, tipos.nombre as tcuenta_nombre, 
                            bancos.nombre_banco, empresa.identificacion, empresa_cuenta_banco.nro_cuenta 
                    from    empresa, empresa_cuenta_banco, tipos, bancos 
                    where   empresa.id = ".$obj->destinatario_id." and empresa_cuenta_banco.empresa_id = empresa.id and empresa_cuenta_banco.moneda_id = ".$obj->moneda_id." and 
                            empresa_cuenta_banco.estado_id = 68 and tipos.id = empresa_cuenta_banco.tcuenta_id and bancos.id = empresa_cuenta_banco.banco_id";
            $idqry = $conn2->query($qry);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj = $conn2->next_record();

            $varr_result['email_contacto'] = $obj->emailcontacto; $varr_result['tcuenta_id'] = $obj->tipo_cuenta_banco; $varr_result['banco_id'] = $obj->banco_id;
            $varr_result['tcuenta'] = $obj->tcuenta_nombre; $varr_result['banco'] = $obj->nombre_banco; $varr_result['identificacion'] = $obj->identificacion;
            $varr_result['cuenta_banco'] = $obj->nro_cuenta;
        }

        /*$conn->close();
        $conn2->close();*/
        return $varr_result;
    }
    function transferir_fondos_exterior($parr_datos){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select FINAN_REGISTRA_TRANSFERENCIA(".$parr_datos['motivo_id'].",".$parr_datos['moneda_id'].",".$parr_datos['monto'].",".$parr_datos['destinatario_id'].",
                                ".$_SESSION['user']['usuarioid'].",".$parr_datos['operacion_id'].",".$parr_datos['ot_id'].") as resultado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
        return 1;
    }

    function get_empresas_xestadoriesgo($p_estado_1, $p_estado_2, $p_estado_3, $p_estado_4, $p_rows, $p_rowini, $p_tipo_consulta){
        // estado_1 = pendientes, estado_2 = vencidos, estado_3 = x_vencer, estado_4 = activos
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo_consulta == 'count'){
            $idqry = $conn->query("select EMP_EMPRESAS_XESTADORIESGO_COUNT(".$p_estado_1.",".$p_estado_2.", ".$p_estado_3.", ".$p_estado_4.") as contador");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $resultado = $obj->contador;
        } else{
            $idqry = $conn->query("select * from EMP_EMPRESAS_XESTADORIESGO(".$p_estado_1.",".$p_estado_2.", ".$p_estado_3.", ".$p_estado_4.", ".$p_rowini.", ".$p_rows.")");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $resultado = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $resultado[$i] = array('empresa_id' => $obj->r_empresa_id, 'nrodoc' => $obj->r_nrodoc,
                                        'empresa' => $obj->r_empresa, 'f_registro' => $obj->r_fregistro,
                                        'paginaweb' => $obj->r_paginaweb, 'f_modifica' => $obj->r_fmodifica,
                                        'u_modifica_id' => $obj->r_u_modifica_id, 'f_envio' => $obj->r_fenvio,
                                        'u_envio_id' => $obj->r_u_envio_id, 'f_aprobacion' => $obj->r_faprobacion,
                                        'u_aprobacion_id' => $obj->r_u_aprobacion_id, 'tempresa_id' => $obj->r_tempresa_id,
                                        'pendiente_riesgo' => $obj->r_pendiente_riesgo, 'tempresa' => $obj->r_tempresa,
                                        'dias_alert' => $obj->r_dias_alert, 'estado' => $obj->r_estado
                                    );
                                    
                $obj = $conn->next_record();
            }
        }
        
        //$conn->close();
        return $resultado;
    }

    function get_zona_horaria(){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select valor_char from parametros where id = 21");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_zona = $obj->valor_char;

        //$conn->close();
        return $v_zona;
    }

    function registra_orden_transferencia($p_factura_id,$p_monto,$p_montivo_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select FINAN_REGISTRA_OT(".$p_factura_id.",".$p_monto.",".$p_montivo_id.") as resultado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
        return 1;
    }
    function termina_orden_transferencia($p_orden_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update orden_transferencia set estado_id = 48 where id = ".$p_orden_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
        return 1;
    }

    function get_bancos(){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select id,nombre_banco from bancos where estado_id = 1");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('banco_id'=>$obj->id, 'banco_nombre'=>$obj->nombre_banco);
            $obj = $conn->next_record();
        }

        //$conn->close();
        return $varr_result;
    }

    function get_variables_valor($p_variable, $p_parametro){
        $conn = new db_param_trans; $conn->connect();

        if ($p_parametro == 0){
            if ($p_variable == 'RIESGO')
                $idqry = $conn->query("select id, CONCAT(nombre,' ',calificacion) as nombre_variable, nivel from tipo_riesgo_empresa where estado = 1 order by nivel desc");
            if ($p_variable == 'SECTOR')
                $idqry = $conn->query("select id, nombre as nombre_variable from tipos where tipo_base = 'SECTORECO' and estado = 1 order by nombre");
            if ($p_variable == 'TIEMPO')
                $idqry = $conn->query("select id, nombre as nombre_variable from tipos where tipo_base = 'VARIABLE TIEMPO' and estado = 1 order by id");
        } else{
            if ($p_variable == 'RIESGO')
                $idqry = $conn->query("select perfil_inversion_variable.variable_valor_id as id, tipos.nombre as nombre_variable from perfil_inversion_variable, tipos where perfil_inversion_variable.estado_id = 1 and perfil_inversion_variable.perfil_inv_id = ".$p_parametro." and perfil_inversion_variable.variable_id = 103 and perfil_inversion_variable.variable_valor_id = tipos.id");
            if ($p_variable == 'SECTOR')
                $idqry = $conn->query("select perfil_inversion_variable.variable_valor_id as id, tipos.nombre as nombre_variable from perfil_inversion_variable, tipos where perfil_inversion_variable.estado_id = 1 and perfil_inversion_variable.perfil_inv_id = ".$p_parametro." and perfil_inversion_variable.variable_id = 104 and perfil_inversion_variable.variable_valor_id = tipos.id");
            if ($p_variable == 'TIEMPO')
                $idqry = $conn->query("select perfil_inversion_variable.variable_valor_id as id, tipos.nombre as nombre_variable from perfil_inversion_variable, tipos where perfil_inversion_variable.estado_id = 1 and perfil_inversion_variable.perfil_inv_id = ".$p_parametro." and perfil_inversion_variable.variable_id = 105 and perfil_inversion_variable.variable_valor_id = tipos.id");
        }

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('id'=>$obj->id, 'nombre'=>$obj->nombre_variable);
            $obj = $conn->next_record();
        }

        //$conn->close();
        return $varr_result;
    }

    function upd_datos_inversor($p_datos){
        $conn = new db_param_trans; $conn->connect();

        if ($p_datos['estado_inversor_id'] == 8){       // INVERSOR ACTIVO
            $idqry = $conn->query("update inversionista set email = '".$p_datos['email_persona']."', telefono = '".$p_datos['telefono_persona']."' where inversor_id = ".$p_datos['inversor_id']);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
        } elseif ($p_datos['estado_inversor_id'] == 6){
            // REGISTRADO PREVIO AL ENVIO
            $v_sql = "update inversionista set tipodoc = ".$p_datos['tipo_documento'].", identificacion = '".$p_datos['nro_documento']."', email = '".$p_datos['email_persona']."', telefono = '".$p_datos['telefono_persona']."', ocupacion_id = ".$p_datos['condicion_laboral'];

            if (isset($p_datos['formulario_kyc'])) $v_sql .= ", kyc_path = '".$p_datos['formulario_kyc']."', kwc = 1";
            if (isset($p_datos['servicios_path'])) $v_sql .= ", servicios_path = '".$p_datos['servicios_path']."'";
            if (isset($p_datos['nomina_path'])) $v_sql .= ", ec_nomina_path = '".$p_datos['nomina_path']."'";
            if (isset($p_datos['impuestos_path'])) $v_sql .= ", declaracion_impuestos_path = '".$p_datos['impuestos_path']."'";
            if (isset($p_datos['bancos_path'])) $v_sql .= ", movimientos_bancarios_path = '".$p_datos['bancos_path']."'";
            if ($p_datos['actividad'] != "") $v_sql .= ", actividad_descripcion = '".$p_datos['actividad']."'";
            if (isset($p_datos['eeff_path'])) $v_sql .= ", estados_financieros_path = '".$p_datos['eeff_path']."'";
            if (isset($p_datos['regmercantil_path'])) $v_sql .= ", registro_mercantil_path = '".$p_datos['regmercantil_path']."'";
            if (isset($p_datos['docidentidad_path'])) $v_sql .= ", documentopath = '".$p_datos['docidentidad_path']."'";

            $v_sql .= " where inversor_id = ".$p_datos['inversionista_id'];

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $conn->next_record();
        }

        //$conn->close();
        return 1;
    }

    function get_brokers($p_usuario_id, $p_empresa_id){
        $conn = new db_param; $conn->connect();

        $v_sql = "  select  usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.tipodoc, usuarios.identificacion,
                            usuarios.estado_activo as estado, CASE usuarios.tipodoc WHEN 1 THEN 'CEDULA' WHEN 2 THEN 'CE' WHEN 3 THEN 'PASAPORTE' END AS nombre_doc,
                            estados.nombre as nombre_estado, usuarios.estado_activo 
                    from    usuarios, estados 
                    where   usuarios.empresaid = ".$p_empresa_id." and usuarios.estado > 0 and usuarios.estado_activo > 0 and 
                            usuarios.id <> usuarios.empresaid and estados.id = usuarios.estado_activo and usuarios.empresaid > 0";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();
        
        for($i = 0; $i < $conn->nrows(); $i ++){
            $v_nombre = $obj->nombre.' '.$obj->apellido;
            $varr_result[$i] = array('id'=>$obj->id, 'nombre'=>$v_nombre, 'tipodoc' => $obj->nombre_doc, 'identificacion' => $obj->identificacion,
                                    'estado' => $obj->nombre_estado, 'estado_id' => $obj->estado_activo);

            $obj = $conn->next_record();
        }
    
        //$conn->close();
        return $varr_result;
    }

    function registra_solicitud_anulacion_inversor($p_inversor_id, $p_motivo){
        $conn = new db_param_trans; $conn->connect();
        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fecha_hoy = date('Y-m-d');

        $idqry = $conn->query("update inversionista set fecha_solicita_anulacion = '".$v_fecha_hoy."', motivo_anulacion = '".$p_motivo."', 
                                estado_anulacion_id = 55 where inversor_id = ".$p_inversor_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function anula_solicitud_anulacion($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update inversionista set estado_anulacion_id = 0 where inversor_id = ".$p_inversor_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function get_usuarios_xempresa($p_empresa_id){
        $conn = new db_param; $conn->connect();

        $idqry = $conn->query("select usuarios.id, usuarios.email, usuarios.nombre || ' ' || usuarios.apellido as usuario_nombre, usuarios.tipodoc, tipos.nombre as tdoc_nombre, 
                                usuarios.fapertura, usuarios.telefono, usuarios.identificacion, usuarios.perfilid 
                            from usuarios, tipos 
                            where usuarios.estado = 1 and tipos.id = usuarios.tipodoc and usuarios.empresaid = ".$p_empresa_id);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('usuario_id' => $obj->id, 'email' => $obj->email, 'nombre' => $obj->usuario_nombre, 'tipodoc' => $obj->tipodoc, 'tipodoc_nombre' => $obj->tdoc_nombre,
                                        'fapertura' => $obj->fapertura, 'telefono' => $obj->telefono, 'nro_doc' => $obj->identificacion, 'perfil_id' => $obj->perfilid);

            $obj = $conn->next_record();
        }

        //$conn->close();
        return $varr_result;
    }

    function verifica_existencia_empresa($p_nombre, $p_identificacion){
        $conn = new db_param_trans; $conn->connect();
        
        // VERIFICO SI EXISTE EL NOMBRE O IDENTIFICACION
        $idqry = $conn->query("select EMP_VERIFICA_EXISTENCIA('".$p_nombre."','".$p_identificacion."') as retorno");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_retorno = $obj->retorno;

        //$conn->close();

        return $v_retorno;
    }

    function get_intervalo_comision_emisor(){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        
        $idqry = $conn->query("select valor_num from parametros where id = 32");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_nivel_minimo = $obj->valor_num;

        $idqry = $conn2->query("select comision_factureate_porc from tipo_riesgo_empresa where nivel in (7,".$v_nivel_minimo.")");

        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();
        $varr_comisiones = array();

        for($i = 0; $i < $conn2->nrows(); $i ++){
            $varr_comisiones[$i] = array('comision' => $obj2->comision_factureate_porc);
            $obj2 = $conn2->next_record();
        }

        //$conn->close(); $conn2->close();

        return $varr_comisiones;
    }

    function actualizar_emisor_v2($parr_datos){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = 'update empresa set ';
        $v_cambios = 0;

        if ($parr_datos['direccion'] != $parr_datos['direccion_old']){
            $v_sql .= "direccion = '".$parr_datos['direccion']."' "; $v_cambios ++;
        }
        if ($parr_datos['tamanoid'] != $parr_datos['tamanoid_old']){
            if ($v_cambios > 0) $v_sql .= ", tamanhoid = ".$parr_datos['tamanoid'];
            else $v_sql .= "tamanhoid = ".$parr_datos['tamanoid'];
            $v_cambios ++;
        }
        if ($parr_datos['sectorid'] != $parr_datos['sectorid_old']){
            if ($v_cambios > 0) $v_sql .= ", sectoreconomicoid = ".$parr_datos['sectorid'];
            else $v_sql .= "sectoreconomicoid = ".$parr_datos['sectorid'];
            $v_cambios ++;
        }
        if ($parr_datos['actividad'] != $parr_datos['actividad_old']){
            if ($v_cambios > 0) $v_sql .= ", actividad = '".$parr_datos['actividad']."'";
            else $v_sql .= "actividad = '".$parr_datos['actividad']."'";
            $v_cambios ++;
        }
        if ($parr_datos['nombre_repre'] != $parr_datos['nombre_repre_old']){
            if ($v_cambios > 0) $v_sql .= ", nombrerepre = '".$parr_datos['nombre_repre']."'";
            else $v_sql .= "nombrerepre = '".$parr_datos['nombre_repre']."'";
            $v_cambios ++;
        }
        if ($parr_datos['email_repre'] != $parr_datos['email_repre_old']){
            if ($v_cambios > 0) $v_sql .= ", emailrepre = '".$parr_datos['email_repre']."'";
            else $v_sql .= "emailrepre = '".$parr_datos['email_repre']."'";
            $v_cambios ++;
        }
        if ($parr_datos['tdoc_repre'] != $parr_datos['tdoc_repre_old']){
            if ($v_cambios > 0) $v_sql .= ", tipodocrepre = ".$parr_datos['tdoc_repre'];
            else $v_sql .= "tipodocrepre = ".$parr_datos['tdoc_repre'];
            $v_cambios ++;
        }
        if ($parr_datos['nrodoc_repre'] != $parr_datos['nrodoc_repre_old']){
            if ($v_cambios > 0) $v_sql .= ", identificacionrepre = '".$parr_datos['nrodoc_repre']."'";
            else $v_sql .= "identificacionrepre = '".$parr_datos['nrodoc_repre']."'";
            $v_cambios ++;
        }
        if ($parr_datos['docrepre_file'] != ''){
            if ($v_cambios > 0) $v_sql .= ", identificacionreprepath = '".$parr_datos['docrepre_file']."'";
            else $v_sql .= "identificacionreprepath = '".$parr_datos['docrepre_file']."'";
            $v_cambios ++;
        }
        if ($parr_datos['podrepre_file'] != ''){
            if ($v_cambios > 0) $v_sql .= ", ficharucpath = '".$parr_datos['podrepre_file']."'";
            else $v_sql .= "ficharucpath = '".$parr_datos['podrepre_file']."'";
            $v_cambios ++;
        }
        if ($parr_datos['nombre_contacto'] != $parr_datos['nombre_contacto_old']){
            if ($v_cambios > 0) $v_sql .= ", nombrecontacto = '".$parr_datos['nombre_contacto']."'";
            else $v_sql .= "nombre_contacto = '".$parr_datos['nombre_contacto']."'";
            $v_cambios ++;
        }
        if ($parr_datos['email_contacto'] != $parr_datos['email_contacto_old']){
            if ($v_cambios > 0) $v_sql .= ", emailcontacto = '".$parr_datos['email_contacto']."'";
            else $v_sql .= "emailcontacto = '".$parr_datos['email_contacto']."'";
            $v_cambios ++;
        }
        if ($parr_datos['tdoc_contacto'] != $parr_datos['tdoc_contacto_old']){
            if ($v_cambios > 0) $v_sql .= ", tipodoccontacto = ".$parr_datos['tdoc_contacto'];
            else $v_sql .= "tipodoccontacto = ".$parr_datos['tdoc_contacto'];
            $v_cambios ++;
        }
        if ($parr_datos['nrodoc_contacto'] != $parr_datos['nrodoc_contacto_old']){
            if ($v_cambios > 0) $v_sql .= ", identificacioncontacto = '".$parr_datos['nrodoc_contacto']."'";
            else $v_sql .= "identificacioncontacto = '".$parr_datos['nrodoc_contacto']."'";
            $v_cambios ++;
        }
        if ($parr_datos['telefono_contacto'] != $parr_datos['telefono_contacto_old']){
            if ($v_cambios > 0) $v_sql .= ", telefonocontacto = '".$parr_datos['telefono_contacto']."'";
            else $v_sql .= "telefonocontacto = '".$parr_datos['telefono_contacto']."'";
            $v_cambios ++;
        }

        if ($v_cambios > 0){
            $v_sql .= " where id = ".$parr_datos['emisorid'];
        }

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function get_inversores($p_tipo, $p_rowini, $p_rowcount, $parr_filtros){
        $conn = new db_param_trans; $conn->connect();
        
        $v_where = '';

        if ($p_tipo == 'COUNT'){
            for ($i=0; $i<count($parr_filtros); $i++){
                if ($v_where != '') $v_where .= 'and ';

                if ($parr_filtros[$i]['filtro'] == 'ESTADO') $v_where .= 'estado in '.$parr_filtros[$i]['valor'].' ';
            }

            $idqry = $conn->query("select count(1) as contador from inversionista where ".$v_where);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            $v_resultado = $obj->contador;
        } else {
            for ($i=0; $i<count($parr_filtros); $i++){
                $v_where .= 'and ';

                if ($parr_filtros[$i]['filtro'] == 'ESTADO') $v_where .= 'inversionista.estado in '.$parr_filtros[$i]['valor'].' ';
            }

            $idqry = $conn->query("select inversionista.nombre, inversionista.apellido, inversionista.email, inversionista.telefono, inversionista.estado, estados.nombre as estado_nombre,
                                    inversionista.inversor_id, inversionista.tipo_inversor, tipos.nombre as tipo_inversor_nombre, inversionista.categoria_id, 
                                    categoria.nombre as categoria_nombre, inversionista.identificacion 
                                from inversionista, estados, tipos, tipos as categoria 
                                where estados.id = inversionista.estado and tipos.id = inversionista.tipo_inversor and categoria.id = inversionista.categoria_id ".$v_where." 
                                order by inversionista.nombre limit ".$p_rowcount." offset ".$p_rowini);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_resultado = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $v_resultado[$i] = array('nombre' => $obj->nombre.' '.$obj->apellido, 'email' => $obj->email, 'telefono' => $obj->telefono, 'estado_id' => $obj->estado,
                                    'estado_nombre' => $obj->estado_nombre, 'inversor_id' => $obj->inversor_id, 'tipo_inversor' => $obj->tipo_inversor, 
                                    'tipo_inversor_nombre' => $obj->tipo_inversor_nombre, 'categoria_id' => $obj->categoria_id, 'categoria_nombre' => $obj->categoria_nombre, 
                                    'identificacion' => $obj->identificacion);

                $obj = $conn->next_record();
            }
        }

        //$conn->close();

        return $v_resultado;
    }

    function get_inversores_v2($p_tipo, $p_rowini, $p_rowcount, $parr_filtros, $p_order){
        $conn = new db_param_trans; $conn->connect();
        
        if ($p_tipo == 'COUNT'){
            $idqry = $conn->query("select count(1) as contador from inversionista where ".$parr_filtros);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            $v_resultado = $obj->contador;
        } else {
            if ($parr_filtros != '') $v_where = ' and '.$parr_filtros;
            else $v_where = '';

            if ($p_order != '') $v_order = 'order by '.$p_order;
            else $v_order = '';

            $idqry = $conn->query("select inversionista.nombre, inversionista.apellido, inversionista.email, inversionista.telefono, inversionista.estado, 
                                    estados.nombre as estado_nombre,
                                    inversionista.inversor_id, inversionista.tipo_inversor, tipos.nombre as tipo_inversor_nombre, inversionista.categoria_id, 
                                    categoria.nombre as categoria_nombre, inversionista.identificacion, inversionista.contrato_enviado, inversionista.estado_fiducia,
                                    tipo_documento.nombre as tipo_documento_nom, estados_fidu.nombre as estado_fiducia_nom, 
                                    CTA_PENDIENTE_BANCO(inversionista.inversor_id) as cuenta_xaprob 
                                from inversionista, estados, tipos, tipos as categoria, tipos as tipo_documento, estados as estados_fidu 
                                where estados.id = inversionista.estado and tipos.id = inversionista.tipo_inversor and categoria.id = inversionista.categoria_id and 
                                    tipo_documento.id = inversionista.tipodoc and estados_fidu.id = inversionista.estado_fiducia ".$v_where." 
                                 ".$v_order." limit ".$p_rowcount." offset ".$p_rowini);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_resultado = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $v_resultado[$i] = array('nombre' => $obj->nombre.' '.$obj->apellido, 'email' => $obj->email, 'telefono' => $obj->telefono, 'estado_id' => $obj->estado,
                                    'estado_nombre' => $obj->estado_nombre, 'inversor_id' => $obj->inversor_id, 'tipo_inversor' => $obj->tipo_inversor, 
                                    'tipo_inversor_nombre' => $obj->tipo_inversor_nombre, 'categoria_id' => $obj->categoria_id, 'categoria_nombre' => $obj->categoria_nombre, 
                                    'identificacion' => $obj->identificacion, 'contrato_enviado' => $obj->contrato_enviado, 'apellido' => $obj->apellido, 'nombre2' => $obj->nombre,
                                    'tipo_documento_nom' => $obj->tipo_documento_nom, 'estado_fiducia_nom' => $obj->estado_fiducia_nom, 'cuenta_xaprob' => $obj->cuenta_xaprob);

                $obj = $conn->next_record();
            }
        }

        //$conn->close();

        return $v_resultado;
    }

    function registra_inversor($parr_datos){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select GN_REGISTRA_INVERSOR('".$parr_datos['nombre']."', '".$parr_datos['apellido']."', '".$parr_datos['email']."', '".$parr_datos['telefono']."',".$parr_datos['tipo_doc'].", '".$parr_datos['nro_doc']."', '".$parr_datos['direccion']."', ".$parr_datos['tipo_persona'].",'".$parr_datos['documento']."', ".$parr_datos['inversor_id'].", ".$parr_datos['tipo_registro'].") as resultado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function get_inversor_detalle($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select inversionista.nombre, inversionista.apellido, inversionista.email, inversionista.tipodoc, inversionista.identificacion, inversionista.direccion, inversionista.telefono, inversionista.documentopath, inversionista.estado, coalesce(inversionista.contrato_path,'') as contrato_path, coalesce(inversionista.informe_plaft,'') as informe_plaft, inversionista.id, inversionista.tipo_inversor, inversionista.categoria_id, tipos.nombre as tipo_documento_nom, estados.nombre as estado_nom, tcategoria.nombre as categoria_nom, inversionista.tipo_registro, inversionista.contrato_enviado, inversionista.f_aprueba_factureate, inversionista.estado_fiducia, inversionista.informe_depuracion_path 
            from inversionista, tipos, estados, tipos as tcategoria 
            where inversionista.inversor_id = ".$p_inversor_id." and tipos.id = inversionista.tipodoc and estados.id = inversionista.estado and tcategoria.id = inversionista.categoria_id");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array('nombre' => $obj->nombre,              'apellido' => $obj->apellido,       'email' => $obj->email,         'tipodoc' => $obj->tipodoc, 
                            'identificacion' => $obj->identificacion,                                   'direccion' => $obj->direccion, 'telefono' => $obj->telefono, 
                            'documento_path' => $obj->documentopath,'estado' => $obj->estado,           'contrato_path' => $obj->contrato_path, 
                            'informe_plaft' => $obj->informe_plaft, 'sec_id' => $obj->id,               'tipo_inversor' => $obj->tipo_inversor, 
                            'categoria_id' => $obj->categoria_id,   'tipo_documento_nom' => $obj->tipo_documento_nom,                   'estado_nom' => $obj->estado_nom, 
                            'categoria_nom' => $obj->categoria_nom, 'tipo_registro' => $obj->tipo_registro,                             
                            'contrato_enviado' => $obj->contrato_enviado,                               'f_aprueba_factureate' => $obj->f_aprueba_factureate, 
                            'estado_fiducia' => $obj->estado_fiducia,                                   'informe_depuracion_path' => $obj->informe_depuracion_path);

        //$conn->close();
        return $varr_result;
    }

    function upd_inversor($p_datos){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "update inversionista set nombre = '".$p_datos['nombre']."', apellido = '".$p_datos['apellido']."', email = '".$p_datos['email']."', direccion = '".$p_datos['direccion']."', telefono = '".$p_datos['telefono']."'";

        if ($p_datos['documento'] != '') $v_sql .= ", documentopath = '".$p_datos['documento']."'";

        $v_sql .= " where inversor_id = ".$p_datos['inversor_id'];

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function aprobar_inversor($p_inversor_id, $p_plaft){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_fhoy = date('Y-m-d');
        $v_hhoy = date('H:i:s');
        $varr_fideicomiso = $this->get_parametro_detalle(60);

        if ($varr_fideicomiso['valornum'] == 0){
            $idqry = $conn->query("update inversionista 
                                set estado = 58, informe_plaft = '".$p_plaft."', f_plaft = '".$v_fhoy."', h_plaft = '".$v_hhoy."', contrato_enviado = 1
                                where inversor_id = ".$p_inversor_id);
            if (!$idqry) echo pg_last_error($conn->Link_ID); $obj = $conn->next_record();

            // CREAR SALDOS Y CUENTA OMNIBUS
            $idqry = $conn2->query("select INV_CREAR_SALDOS_INVERSOR(".$p_inversor_id.")");
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $conn2->next_record();
        } else {
            //------- CUANDO EL FIDEICOMISO FORMA PARTE DEL MODELO
            $idqry = $conn->query("update inversionista 
                                set estado = 8, f_aprueba_factureate = '".$v_fhoy."', h_aprueba_factureate = '".$v_hhoy."' 
                                where inversor_id = ".$p_inversor_id);
            
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $conn->next_record();
        }

        /*$conn->close();
        $conn2->close();*/

        return 1;
    }

    function aprobar_depuracion_inversor($p_accion, $p_inversor_id, $p_informe_path, $p_tipo_persona){
        $conn = new db_param_trans; $conn->connect();
        $conn_seg = new db_param; $conn_seg->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_fhoy = date('Y-m-d');
        $v_hhoy = date('H:i:s');

        if ($p_accion == 'ok'){
            $idqry = $conn->query(" update  inversionista set estado_fiducia = 60, informe_depuracion_path = '".$p_informe_path."', f_aprueba_final = '".$v_fhoy."', 
                                            h_aprueba_final = '".$v_hhoy."' 
                                    where inversor_id = ".$p_inversor_id);
            if (!$idqry) echo pg_last_error($conn->Link_ID); $conn->next_record();

            $varr_contrato = $this->get_parametro_detalle(28);

            //==== CREACION DEL CONTRATO AUTOMATICO
            if ($varr_contrato['valornum'] == 1){
                //..
                $v_retorno = 'link de contrato';
            } else {
                $v_retorno = 'CONTRATO MANUAL';
            }
        } else {
            $idqry = $conn->query("update inversionista set estado_fiducia = 60, estado = 63, informe_depuracion_path = '".$p_informe_path."' where inversor_id = ".$p_inversor_id);
            if (!$idqry) echo pg_last_error($conn->Link_ID); $conn->next_record();

            //---- ANULO EL USUARIO RELACIONADO
            if ($p_tipo_persona == 85){
                $idqry = $conn_seg->query("update usuarios set estado = 0 where id = ".$p_inversor_id);
                if (!$idqry) echo pg_last_error($conn_seg->Link_ID); $conn_seg->next_record();
            } else{
                $idqry = $conn_seg->query("update usuarios set estado = 0 where empresaid = ".$p_inversor_id);
                if (!$idqry) echo pg_last_error($conn_seg->Link_ID); $conn_seg->next_record();
            }

            $v_retorno = 'RECHAZADO';
        }

        return $v_retorno;
    }

    function guardar_contrato_inversor($p_inversor_id, $p_contrato){
        $conn = new db_param_trans; $conn->connect();
        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_fhoy = date('Y-m-d');
        $v_hhoy = date('H:i:s');

        $idqry = $conn->query("update inversionista set contrato_path = '".$p_contrato."', estado = 64 where inversor_id = ".$p_inversor_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function get_comision_fact_emisor($p_emisor_id, $p_cliente_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select CALCULA_TASACOMISION_FACTUREATE_EMI(".$p_emisor_id.",".$p_cliente_id.") as tasa_comision");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $v_retorno = $obj->tasa_comision;

        //$conn->close();
        return $v_retorno;
    }

    function get_porc_adelanto_emisor($p_emisor_id, $p_cliente_id){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $idqry = $conn->query("select CALCULA_NIVEL_RIESGO_FACTURA(".$p_emisor_id.",".$p_cliente_id.") as nivel_riesgo");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $v_nivel_riesgo = $obj->nivel_riesgo;

        if ($v_nivel_riesgo > 0){
            $idqry = $conn2->query("select porc_adelanto from riesgo_factura_param where nivel_riesgo = ".$v_nivel_riesgo);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj2 = $conn2->next_record();
            $v_resultado = $obj2->porc_adelanto;
        } else{
            $idqry = $conn2->query("select valor_num from parametros where id = 4");
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj2 = $conn2->next_record();
            $v_resultado = $obj2->valor_num;
        }

        //$conn->close(); $conn2->close();
        return $v_resultado;
    }

    function valida_parametros_factura($p_emisor_id, $p_cliente_doc){
        $conn = new db_param_trans; $conn->connect();
        $conn_count = new db_param_trans; $conn_count->connect();

        //==== verifico que no sea un cliente nuevo que aun no esta registrado

        $idqry = $conn_count->query("select count(1) as contador from empresa where identificacion = '".$p_cliente_doc."'");
        if (!$idqry) echo pg_last_error($conn_count->Link_ID);
        $obj = $conn_count->next_record();
        
        if ($obj->contador > 0){
            $idqry = $conn->query("select id from empresa where identificacion = '".$p_cliente_doc."'");
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_cliente_id = $obj->id;
        } else $v_cliente_id = 0;

        $v_porc_comision = $this->get_comision_fact_emisor($p_emisor_id, $v_cliente_id);
        $v_porc_adelanto = $this->get_porc_adelanto_emisor($p_emisor_id, $v_cliente_id);
        $v_result = $v_porc_comision.'+'.$v_porc_adelanto;

        //$conn->close(); $conn_count->close();
        
        return $v_result;
    }

    function valida_correo_op($p_op_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select coalesce(emailcontacto,'') as email from empresa where id = ".$p_op_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($obj->email != '') $v_result = 1;
        else $v_result = 0;

        //$conn->close();
        return $v_result;
    }

    function kpi_graph_distribucion_empresa_tamano(){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select empresa.tamanhoid, tipos.nombre, count(empresa.tamanhoid) as cantidad
                                from empresa, tipos 
                                where empresa.t_empresa = 46 and empresa.estado = 3 and tipos.id = empresa.tamanhoid
                                group by empresa.tamanhoid, tipos.nombre");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('tamanho_id' => $obj->tamanhoid, 'tamanho_nombre' => $obj->nombre, 'cantidad' => $obj->cantidad);

            $obj = $conn->next_record();
        }

        //$conn->close();
        return $varr_result;
    }

    function kpi_graph_tipo_inversor(){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select tipos.id, tipos.nombre, count(tipos.id) as cantidad
                                from tipos, inversionista
                                where tipos.tipo_base = 'TIPO_INVERSOR' and tipos.id = inversionista.tipo_inversor and inversionista.estado = 8
                                group by tipos.id, tipos.nombre");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('tipo_id' => $obj->id, 'tipo_nombre' => $obj->nombre, 'cantidad' => $obj->cantidad);

            $obj = $conn->next_record();
        }

        //$conn->close();
        return $varr_result;
    }

    function kpi_graph_plaft(){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select * from kpi_plaft()");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
            $varr_result = array('pep_activo_inver' => $obj->r_pep_activo_inver, 'pep_inactivo_inver' => $obj->r_pep_inactivo_inver, 
                'pep_activo_acc' => $obj->r_pep_activo_acc, 'pep_inactivo_acc' => $obj->r_pep_inactivo_acc, 'ofac_activo_inver' => $obj->r_ofac_activo_inver,
                'ofac_inactivo_inver' => $obj->r_ofac_inactivo_inver, 'ofac_activo_acc' => $obj->r_ofac_activo_acc, 'ofac_inactivo_acc' => $obj->r_ofac_inactivo_acc,
                'onu_activo_inver' => $obj->r_onu_activo_inver, 'onu_inactivo_inver' => $obj->r_onu_inactivo_inver, 'onu_activo_acc' => $obj->r_onu_activo_acc,
                'onu_inactivo_acc' => $obj->r_onu_inactivo_acc, 'cautela_activo_inver' => $obj->r_cautela_activo_inver, 'cautela_inactivo_inver' => $obj->r_cautela_inactivo_inver,
                'cautela_activo_acc' => $obj->r_cautela_activo_acc, 'cautela_inactivo_acc' => $obj->r_cautela_inactivo_acc);

        //$conn->close();
        return $varr_result;
    }

    function kpi_coo (){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select * from KPI_COO()");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array('qfacturas_ensolicitud' => $obj->r_qfacturas_ensolicitud, 'qfacturas_ensolicitud_alert' => $obj->r_qfacturas_ensolicitud_alert,
                            'qsubastas_activas' => $obj->r_qsubastas_activas, 'qsubastas_activas_alert' => $obj->r_qsubastas_activas_alert,
                            'qsubastas_sin_propuestas' => $obj->r_qsubastas_sin_propuestas, 'qsubastas_sin_minimo' => $obj->r_qsubastas_sin_minimo,
                            'qsubastas_encomp' => $obj->r_qsubastas_encomp, 'qsubastas_encomp_alert' => $obj->r_qsubastas_encomp_alert,
                            'qfinan_xvencer' => $obj->r_qfinan_xvencer, 'qfinan_vencido' => $obj->r_qfinan_vencido, 'qfinan_vencehoy' => $obj->r_qfinan_vencehoy,
                            'qempresas_validacionop' => $obj->r_qempresas_validacionop);

        //$conn->close();

        return $varr_result;
    }

    function kpi_graph_facturas_reg_tiempo(){
        $conn = new db_param_trans; $conn->connect();
        $v_18meses = 18*30;

        $idqry = $conn->query("select to_char(fregistro,'TMMonth')||' '||EXTRACT(YEAR from fregistro) as mes, EXTRACT(YEAR from fregistro)*EXTRACT(MONTH from fregistro) as mesanno, count(id) as cantidad from factura where estado <> 0 and fregistro >= (current_date - INTERVAL '".$v_18meses." day') group by to_char(fregistro,'TMMonth')||' '||EXTRACT(YEAR from fregistro),EXTRACT(YEAR from fregistro)*EXTRACT(MONTH from fregistro) order by EXTRACT(YEAR from fregistro)*EXTRACT(MONTH from fregistro)");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('mes' => $obj->mes, 'cantidad' => $obj->cantidad);

            $obj = $conn->next_record();
        }

        //$conn->close();
        return $varr_result;
    }

    function kpi_graph_facturas_subasta_tiempo(){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $v_12meses = 12*30;

        $idqry = $conn->query("select to_char(fregistro,'TMMonth')||' '||EXTRACT(YEAR from fregistro) as mes, EXTRACT(YEAR from fregistro)*EXTRACT(MONTH from fregistro) as mesanno, count(id) as cantidad from factura where estado <> 0 and fregistro >= (current_date - INTERVAL '".$v_12meses." day') group by to_char(fregistro,'TMMonth')||' '||EXTRACT(YEAR from fregistro),EXTRACT(YEAR from fregistro)*EXTRACT(MONTH from fregistro) order by EXTRACT(YEAR from fregistro)*EXTRACT(MONTH from fregistro)");

        $idqry2 = $conn2->query("select to_char(fcreacion,'TMMonth')||' '||EXTRACT(YEAR from fcreacion) as mes, EXTRACT(YEAR from fcreacion)*EXTRACT(MONTH from fcreacion) as mesanno, count(id) as cantidad from subasta where estado <> 0 and fcreacion >= (current_date - INTERVAL '".$v_12meses." day') group by to_char(fcreacion,'TMMonth')||' '||EXTRACT(YEAR from fcreacion),EXTRACT(YEAR from fcreacion)*EXTRACT(MONTH from fcreacion) order by EXTRACT(YEAR from fcreacion)*EXTRACT(MONTH from fcreacion)");

        if (!$idqry2) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();
        $varr_subasta = array();

        for($j = 0; $j < $conn2->nrows(); $j ++){
            $varr_subasta[$j] = array('mes' => $obj2->mes, 'cantidad' => $obj2->cantidad, 'mesanno' => $obj2->mesanno);

            $obj2 = $conn2->next_record();
        }

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('mes' => $obj->mes, 'registrados' => $obj->cantidad);
            $encontrado = 0;

            for ($z = 0; $z < count($varr_subasta); $z++){
                if ($varr_subasta[$z]['mesanno'] == $obj->mesanno){
                    $varr_result[$i]['subastados'] = $varr_subasta[$z]['cantidad'];
                    $encontrado = 1;
                    break;
                }
            }

            if ($encontrado == 0) $varr_result[$i]['subastados'] = 0;

            $obj = $conn->next_record();
        }

        //$conn->close(); $conn2->close();
        return $varr_result;
    }

    function get_solicitudes_vinculacion($p_tipo, $p_rowini, $p_rowcount, $parr_filtros, $p_order){
        // SOLICITUDES DE VINCULACION PARA LA FIDUCIARIA
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo == 'COUNT'){
            $idqry = $conn->query("select count(1) as contador from inversionista where estado = 8 and estado_fiducia = 59");

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_result = $obj->contador;
        } else {
            $v_qry = "select inversionista.nombre, inversionista.apellido, inversionista.email, inversionista.tipodoc, tipos.nombre as tipodoc_nom,
                        inversionista.identificacion, inversionista.telefono, inversionista.inversor_id, inversionista.tipo_inversor, tipo_inv.nombre as tipoinv_nom,
                        inversionista.tipo_registro, tipo_reg.nombre as tiporeg_nom, inversionista.f_aprueba_factureate, inversionista.h_aprueba_factureate 
                    from inversionista, tipos, tipos as tipo_inv, tipos as tipo_reg 
                    where inversionista.estado = 8 and tipos.id = inversionista.tipodoc and tipo_inv.id = inversionista.tipo_inversor and 
                        tipo_reg.id = inversionista.tipo_registro and inversionista.estado_fiducia = 59";

            if ($p_order != '') $v_qry .= ' order by '.$p_order;
            if ($p_rowcount > 0) $v_qry .= ' limit '.$p_rowcount.' offset '.$p_rowini;
            
            $idqry = $conn->query($v_qry);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_result = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $v_result[$i] = array('nombre' => $obj->nombre, 'apellido' => $obj->apellido, 'email' => $obj->email, 'tipodoc_id' => $obj->tipodoc, 'tipodoc' => $obj->tipodoc_nom,
                                    'identificacion' => $obj->identificacion, 'telefono' => $obj->telefono, 'inversor_id' => $obj->inversor_id, 'tinversor_id' => $obj->tipo_inversor,
                                    'tinversor' => $obj->tipoinv_nom, 'tregistro_id' => $obj->tipo_registro, 'tregistro' => $obj->tiporeg_nom, 
                                    'f_aprueba_factureate' => $obj->f_aprueba_factureate, 'h_aprueba_factureate' => $obj->h_aprueba_factureate);

                $obj = $conn->next_record();
            }
        }

        //$conn->close();

        return $v_result;
    }

    function envia_contrato_vinculacion_inversor($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update inversionista set estado = 58, contrato_enviado = 1 where inversor_id = ".$p_inversor_id);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function dd_inversor($p_inversor_id, $p_informe_path){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update inversionista set estado_fiducia = 62, informe_plaft = '".$p_informe_path."' where inversor_id = ".$p_inversor_id);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        //$conn->close();
    }

    function kpi_graph_adelantos_mes(){
        $conn = new db_param_trans; $conn->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_mes = date('m');
        $v_anho = date('Y');
        $v_fhoy = date('Y-m-d');

        $v_inicial = strtotime($v_anho.'-'.$v_mes.'-01'); $v_inicial = date('Y-m-d', $v_inicial);
        $v_manhana = strtotime('+1 day', strtotime($v_fhoy)); $v_manhana = date('Y-m-d', $v_manhana);

        $v_sql = "select    fregistro, monedaid, sum(monto) as monto from movimientos_cuenta 
                  where     estado = 1 and tipo_operacionid = 96 and fregistro >= '".$v_inicial."' and fregistro < '".$v_manhana."' 
                  group by  fregistro, monedaid
                  order by  fregistro, monedaid";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('f_registro' => $obj->fregistro,   'moneda_id' => $obj->monedaid,  'monto' => $obj->monto);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $varr_result;
    }

    function kpi_graph_ingresos_mes(){
        $conn = new db_param_trans; $conn->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_mes = date('m');
        $v_anho = date('Y');
        $v_fhoy = date('Y-m-d');

        $v_inicial = strtotime($v_anho.'-'.$v_mes.'-01'); $v_inicial = date('Y-m-d', $v_inicial);
        $v_manhana = strtotime('+1 day', strtotime($v_fhoy)); $v_manhana = date('Y-m-d', $v_manhana);

        $v_sql = "select    fregistro, monedaid, sum(monto) as monto from movimientos_cuenta 
                  where     estado = 1 and tipo_operacionid in (56,72) and fregistro >= '".$v_inicial."' and fregistro < '".$v_manhana."' 
                  group by  fregistro, monedaid
                  order by  fregistro, monedaid";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('f_registro' => $obj->fregistro,   'moneda_id' => $obj->monedaid,  'monto' => $obj->monto);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $varr_result;
    }

    function kpi_graph_inversiones_mes(){
        $conn = new db_param_trans; $conn->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_mes = date('m');
        $v_anho = date('Y');
        $v_fhoy = date('Y-m-d');

        $v_inicial = strtotime($v_anho.'-'.$v_mes.'-01'); $v_inicial = date('Y-m-d', $v_inicial);
        $v_manhana = strtotime('+1 day', strtotime($v_fhoy)); $v_manhana = date('Y-m-d', $v_manhana);

        $v_sql = "select    fregistro, count(id) as qinversiones from financiamiento 
                  where     estado = 27 and fregistro >= '".$v_inicial."' and fregistro < '".$v_manhana."' 
                  group by  fregistro
                  order by  fregistro";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('f_registro' => $obj->fregistro,   'qinversiones' => $obj->qinversiones);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $varr_result;
    }

    function kpi_graph_liquidaciones_mes(){
        $conn = new db_param_trans; $conn->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_mes = date('m');
        $v_anho = date('Y');
        $v_fhoy = date('Y-m-d');

        $v_inicial = strtotime($v_anho.'-'.$v_mes.'-01'); $v_inicial = date('Y-m-d', $v_inicial);
        $v_manhana = strtotime('+1 day', strtotime($v_fhoy)); $v_manhana = date('Y-m-d', $v_manhana);

        $v_sql = "select    fpago_efectivo, count(id) as qliquidaciones from financiamiento 
                  where     estado = 37 and fpago_efectivo >= '".$v_inicial."' and fpago_efectivo < '".$v_manhana."' 
                  group by  fpago_efectivo
                  order by  fpago_efectivo";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('f_liquidacion' => $obj->fpago_efectivo,   'qliquidaciones' => $obj->qliquidaciones);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $varr_result;
    }

    function get_fiduciarias(){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "  select id, razon_social from fiduciaria";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('fidu_id' => $obj->id,   'fidu_nombre' => $obj->razon_social);
            $obj = $conn->next_record();
        }

        //$conn->close();

        return $varr_result;
    }

    function registra_path_documentos_empresa($parr_path){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "update empresa set ";
        $i = 0;

        if ($parr_path['registro_mercantil'] != ''){
            $v_sql .= " ficharucpath = '".$parr_path['registro_mercantil']."'";
            $i ++;
        }

        if ($parr_path['documento_repre'] != ''){
            if ($i > 0) $v_sql .= ',';
            else $i++;
            $v_sql .= "identificacionreprepath = '".$parr_path['documento_repre']."'";
        }

        if ($parr_path['poderes_empresa'] != ''){
            if ($i > 0) $v_sql .= ",";
            else $i ++;
            $v_sql .= "vigenciapoderespath = '".$parr_path['poderes_empresa']."'";
        }

        $v_sql .= " where id = ".$parr_path['empresa_id'];

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();

        //$conn->close();
    }

    function envia_inversor($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_fecha = date('Y-m-d');
        $v_hora = date('h:i:s');

        $v_sql = "update inversionista set estado = 7, f_envio = '".$v_fecha."', h_envio = '".$v_hora."' where inversor_id = ".$p_inversor_id;

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();

        //$conn->close();
        return $v_sql;
    }

    function busca_arreglo_bidi($parr, $column, $valor){
        //busqueda en arreglo bidimensional
        $v_encontrado = false;

        for ($i = 0; $i < count($parr); $i++){
            if ($parr[$i][$column] == $valor){
                $v_encontrado = true;
                break;
            }
        }

        return $v_encontrado;
    }

    //====== DICIEMBRE 2025 / 11-12-2025

    function valida_cuenta_banco_empresa ($p_empresa_id, $p_moneda_id){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $v_sql = "  select  count(1) as contador 
                    from    empresa_cuenta_banco 
                    where   empresa_id = ".$p_empresa_id." and moneda_id = ".$p_moneda_id." and estado_id > 0";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $v_obj_count = $conn->next_record();

        if ($v_obj_count->contador > 0){
            $v_sql = "  select  count(1) as contador2
                        from    empresa_cuenta_banco 
                        where   empresa_id = ".$p_empresa_id." and moneda_id = ".$p_moneda_id." and estado_id = 68";

            $idqry = $conn2->query($v_sql);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $v_obj_count2 = $conn2->next_record();

            if ($v_obj_count2->contador2 > 0) $v_resultado = 1;
            else $v_resultado = -2;
        } else $v_resultado = -1;

        //$conn->close();

        return $v_resultado;
    }

    function aprobar_inversor_factureate($p_inversor_id, $p_plaft, $p_parametro){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        $v_fhoy = date('Y-m-d');
        $v_hhoy = date('H:i:s');
        //$varr_fideicomiso = $this->get_parametro_detalle(60);

        if ($p_parametro == 0){
            $idqry = $conn->query("update inversionista 
                                set estado = 58, informe_plaft = '".$p_plaft."', f_plaft = '".$v_fhoy."', h_plaft = '".$v_hhoy."', contrato_enviado = 1
                                where inversor_id = ".$p_inversor_id);
            if (!$idqry) echo pg_last_error($conn->Link_ID); $obj = $conn->next_record();

            // CREAR SALDOS Y CUENTA OMNIBUS
            $idqry = $conn2->query("select INV_CREAR_SALDOS_INVERSOR(".$p_inversor_id.")");
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $conn2->next_record();
        } else {
            //------- CUANDO EL FIDEICOMISO FORMA PARTE DEL MODELO
            $idqry = $conn->query("update inversionista 
                                set estado = 8, f_aprueba_factureate = '".$v_fhoy."', h_aprueba_factureate = '".$v_hhoy."' 
                                where inversor_id = ".$p_inversor_id);
            
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $conn->next_record();
        }

        //$conn->close();
        //$conn2->close();

        return 1;
    }

    function get_detalle_nivel_riesgo($p_nivel){
        $conn = new db_param_trans; $conn->connect();

        $v_qry = "  select nombre, calificacion, nivel from tipo_riesgo_empresa where id = ".$p_nivel;

        $idqry = $conn->query($v_qry);
        if (!$idqry) echo pg_last_error($conn->Link_ID); $obj = $conn->next_record();

        $varr_result = array('nombre' => $obj->nombre, 'calificacion' => $obj->calificacion, 'nivel' => $obj->nivel);

        //$conn->close();

        return $varr_result;
    }

    function get_empresas_riesgo_pend($p_rows, $p_rowini, $p_tipo_consulta){
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo_consulta == 'count'){
            $idqry = $conn->query("select EMP_EMPRESAS_RIESGO_PEND_COUNT() as contador");

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            
            $resultado = $obj->contador;
        } else{
            $idqry = $conn->query("select * from EMP_EMPRESAS_RIESGO_PEND(".$p_rowini.", ".$p_rows.")");

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            
            $resultado = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $resultado[$i] = array('empresa_id' => $obj->r_empresa_id, 'nrodoc' => $obj->r_nrodoc,
                                        'empresa' => $obj->r_empresa, 'f_registro' => $obj->r_fregistro,
                                        'paginaweb' => $obj->r_paginaweb, 'f_modifica' => $obj->r_fmodifica,
                                        'u_modifica_id' => $obj->r_u_modifica_id, 'f_envio' => $obj->r_fenvio,
                                        'u_envio_id' => $obj->r_u_envio_id, 'f_aprobacion' => $obj->r_faprobacion,
                                        'u_aprobacion_id' => $obj->r_u_aprobacion_id, 'tempresa_id' => $obj->r_tempresa_id,
                                        'pendiente_riesgo' => $obj->r_pendiente_riesgo, 'tempresa' => $obj->r_tempresa,
                                        'dias_alert' => $obj->r_dias_alert, 'estado' => $obj->r_estado
                                    );
                                    
                $obj = $conn->next_record();
            }
        }
        
        //$conn->close();
        
        return $resultado;
    }

    function registra_interes_contacto($parr){
        //--- registro de los datos del usuario que solicita que lo llamen por la pagina web
        //--- nombre, email, telefono, tipo doc, documento, razon social, rnc, tipo interes
        $conn = new db_param_trans; $conn->connect();
        $conn_fecha = new db_param_trans; $conn_fecha->connect();
        $conn_registro = new db_param_trans; $conn_registro->connect();

        //--- obtencion de la secuencia 
        $v_sql_sec = "select nextval('s_interes_contacto') as secuencia";
        $idqry = $conn->query($v_sql_sec);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_secuencia = $obj->secuencia;

        //--- obtencion de la fecha y hora
        $v_sql_zona = "select valor_char from parametros where id = 21";
        $idqry = $conn_fecha->query($v_sql_zona);

        if (!$idqry) echo pg_last_error($conn_fecha->Link_ID);
        $obj = $conn_fecha->next_record();
        $v_zona = $obj->valor_char;

        date_default_timezone_set($v_zona);

        $v_fhoy = date('Y-m-d');
        $v_hhoy = date('H:i:s');

        //--- registro del interes
        $v_sql = "  insert into interes_contacto (id,nombre,email,telefono,t_documento,documento,razon_social,identificacion,t_interes,fecha,hora,ip) 
                    values (".$v_secuencia.",'".$parr['nombre']."','".$parr['email']."','".$parr['telefono']."',".$parr['t_documento'].",'".$parr['documento']."',
                            '".$parr['razon_social']."','".$parr['identificacion']."',".$parr['t_interes'].",'".$v_fhoy."','".$v_hhoy."','".$_SERVER['REMOTE_ADDR']."')";
        $idqry = $conn_registro->query($v_sql);

        if (!$idqry) echo pg_last_error($conn_registro->Link_ID);
        $obj = $conn_registro->next_record();

        //$conn->close(); $conn_fecha->close();   $conn_registro->close();

        return 1;
    }

    function registra_mensaje_web($parr){
        //--- registro mensaje web
        //--- nombre, email, telefono, tipo doc, documento, mensaje
        $conn = new db_param_trans; $conn->connect();
        $conn_fecha = new db_param_trans; $conn_fecha->connect();
        $conn_registro = new db_param_trans; $conn_registro->connect();

        //--- obtencion de la secuencia 
        $v_sql_sec = "select nextval('s_interes_mensaje') as secuencia";
        $idqry = $conn->query($v_sql_sec);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_secuencia = $obj->secuencia;

        //--- obtencion de la fecha y hora
        $v_sql_zona = "select valor_char from parametros where id = 21";
        $idqry = $conn_fecha->query($v_sql_zona);

        if (!$idqry) echo pg_last_error($conn_fecha->Link_ID);
        $obj = $conn_fecha->next_record();
        $v_zona = $obj->valor_char;

        date_default_timezone_set($v_zona);

        $v_fhoy = date('Y-m-d');
        $v_hhoy = date('H:i:s');

        //--- registro del interes
        $v_sql = "  insert into interes_mensaje_web (id,nombre,email,telefono,t_documento,documento,mensaje,fecha,hora,ip) 
                    values (".$v_secuencia.",'".$parr['nombre']."','".$parr['email']."','".$parr['telefono']."',".$parr['t_documento'].",'".$parr['documento']."',
                            '".$parr['mensaje_web']."','".$v_fhoy."','".$v_hhoy."','".$_SERVER['REMOTE_ADDR']."')";
        $idqry = $conn_registro->query($v_sql);

        if (!$idqry) echo pg_last_error($conn_registro->Link_ID);
        $obj = $conn_registro->next_record();

        //$conn->close(); $conn_fecha->close();   $conn_registro->close();

        return 1;
    }

    function valida_usuario_inversor($p_inversor){
        $conn = new db_param_trans; $conn->connect();
        $conn_seg = new db_param; $conn_seg->connect();

        $v_sql = "select tipo_inversor from inversionista where inversor_id = ".$p_inversor;

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_usuario_inversor = array();

        if ($obj->tipo_inversor == 85){
            //---- persona natural
            $varr_usuario_inversor[0] = array('usuario_id' => $p_inversor, 'tipo_inversor' => 85);
        } else{
            //---- empresa
            $v_sql = "select id from usuarios where estado = 1 and empresaid = ".$p_inversor;

            $idqry = $conn_seg->query($v_sql);
            if (!$idqry) echo pg_last_error($conn_seg->Link_ID);
            $obj = $conn_seg->next_record();

            for($i = 0; $i < $conn_seg->nrows(); $i ++){
                $varr_usuario_inversor[$i] = array('usuario_id' => $obj->id, 'tipo_inversor' => 86);
                $obj = $conn_seg->next_record();
            }
        }

        //$conn->close(); $conn_seg->close();

        return $varr_usuario_inversor;
    }

    function analiza_nuevo_broker($parr_datos){
        $conn_user = new db_param; $conn_user->connect();
        $conn_user2 = new db_param; $conn_user2->connect();

        $varr_resultado = array();

        $v_sql = "select count(1) as contador from usuarios where estado > 0 and identificacion = '".$parr_datos['nrodoc']."' and empresaid = ".$parr_datos['inversor_id'];

        $idqry = $conn_user->query($v_sql);
        if (!$idqry) echo pg_last_error($conn_user->Link_ID);
        $obj_user = $conn_user->next_record();

        if ($obj_user->contador > 0){
            $varr_resultado['resultado'] = -1;
        } else {
            $v_sql = "select count(1) as contador from usuarios where estado > 0 and identificacion = '".$parr_datos['nrodoc']."'";

            $idqry = $conn_user2->query($v_sql);
            if (!$idqry) echo pg_last_error($conn_user2->Link_ID);
            $obj_user = $conn_user2->next_record();

            $varr_resultado['resultado'] = 1;

            if ($obj_user->contador > 0) $varr_resultado['broker_user'] = $parr_datos['nrodoc'].'-'.$obj_user->contador;
            else $varr_resultado['broker_user'] = $parr_datos['nrodoc'];
        }

        //$conn_user->close(); $conn_user2->close();

        return $varr_resultado;
    }

    function convierte_inversor_empresa($arr_datos){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $conn_seg = new db_param; $conn_seg->connect();
        
        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fechahoy = date('Y-m-d');

        $v_sql = "  insert into empresa(id, identificacion, nombre, direccion, nombrerepre, tipodocrepre, identificacionrepre, sectoreconomicoid, actividad, 
                                        identificacionreprepath, tamanhoid, emailrepre, vigenciapoderespath, nombrecontacto, emailcontacto, tipodoccontacto, 
                                        identificacioncontacto, telefonocontacto, estado, fregistro, t_empresa, inversor_natural)
                    values (".$arr_datos['inversor_id'].", '".$arr_datos['identificacion']."', '".$arr_datos['nombre']."', '".$arr_datos['direccion']."', 
                            '".$arr_datos['nombre']."', ".$arr_datos['tipodoc_repre'].", '".$arr_datos['nrodoc_repre']."', 0, '',
                            '', 0, '".$arr_datos['email_repre']."', '', '".$arr_datos['nombre']."', '".$arr_datos['email_contacto']."', ".$arr_datos['tipodoc_contacto'].",
                            '".$arr_datos['nrodoc_contacto']."', '".$arr_datos['telefono_contacto']."', 3, '".$v_fechahoy."', 47, 1);";
        
        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();

        $v_sql = "update usuarios set empresaid = ".$arr_datos['inversor_id']." where id = ".$arr_datos['inversor_id'];

        $idqry = $conn_seg->query($v_sql);
        if (!$idqry) echo pg_last_error($conn_seg->Link_ID);
        $conn_seg->next_record();

        //==== actualizo el tipo de persona de inversor
        $v_sql = "update inversionista set tipo_inversor = 86 where inversor_id = ".$arr_datos['inversor_id'];

        $idqry = $conn2->query($v_sql);
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $conn2->next_record();
        
        //$conn->close(); $conn_seg->close();

        return $arr_datos['inversor_id'];
    }

    function eliminar_broker($p_broker_id, $p_broker_doc, $p_inversor_id){
        $conn_seg = new db_param; $conn_seg->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fechahoy = date('Y-m-d');

        $v_sql = "  update usuarios set estado = 0, estado_activo = 0, fdesactivacion = '".$v_fechahoy."', usuarioid_desactiva = ".$p_inversor_id." where id = ".$p_broker_id;

        $idqry = $conn_seg->query($v_sql);
        if (!$idqry) echo pg_last_error($conn_seg->Link_ID);
        $conn_seg->next_record();

        //$conn_seg->close();
    }

    function get_brokers_list($p_tipo, $p_rows, $p_rowini, $p_filtros, $p_order){
        $conn = new db_param; $conn->connect();

        if ($p_tipo == 'COUNT') {
            $v_sql = "  select  count(1) as contador 
                        from    usuarios, estados 
                        where   usuarios.estado > 0 and usuarios.id <> usuarios.empresaid and estados.id = usuarios.estado_activo and 
                                usuarios.empresaid > 0 and usuarios.perfilid in (3,5)";

            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;
        } else {
            $v_sql = "  select  usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.tipodoc, usuarios.identificacion,
                                usuarios.estado_activo as estado, CASE usuarios.tipodoc WHEN 1 THEN 'CEDULA' WHEN 2 THEN 'CE' WHEN 3 THEN 'PASAPORTE' END AS nombre_doc,
                                estados.nombre as nombre_estado, usuarios.email 
                        from    usuarios, estados 
                        where   usuarios.estado > 0 and usuarios.id <> usuarios.empresaid and estados.id = usuarios.estado_activo and 
                                usuarios.empresaid > 0 and usuarios.perfilid in (3,5)";

            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;
            if ($p_order != '') $v_sql .= ' order by '.$p_order;
            if ($p_rows > 0) $v_sql .= ' limit '.$p_rows.' offset '.$p_rowini;
        }

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($p_tipo == 'COUNT') $varr_result = $obj->contador;
        else {
            $varr_result = array();
            
            for($i = 0; $i < $conn->nrows(); $i ++){
                $v_nombre = $obj->nombre.' '.$obj->apellido;
                $varr_result[$i] = array('id'=>$obj->id, 'nombre'=>$v_nombre, 'tipodoc' => $obj->nombre_doc, 'identificacion' => $obj->identificacion,
                                        'estado' => $obj->nombre_estado, 'email' => $obj->email);

                $obj = $conn->next_record();
            }
        }
    
        //$conn->close();
        
        return $varr_result;
    }

    function get_broker_detalle($p_broker_id){
        $conn = new db_param; $conn->connect();

        $v_sql = "  select  usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.tipodoc, usuarios.identificacion,
                            usuarios.estado_activo as estado, CASE usuarios.tipodoc WHEN 1 THEN 'CEDULA' WHEN 2 THEN 'CE' WHEN 3 THEN 'PASAPORTE' END AS nombre_doc,
                            estados.nombre as nombre_estado, usuarios.email, usuarios.empresaid 
                    from    usuarios, estados 
                    where   usuarios.id = ".$p_broker_id." and estados.id = usuarios.estado_activo";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array('nombre' => $obj->nombre, 'apellido' => $obj->apellido, 'tipodoc_id' => $obj->tipodoc, 'identificacion' => $obj->identificacion,
                            'estado_activo_id' => $obj->estado, 'tipodoc' => $obj->nombre_doc, 'estado_activo' => $obj->nombre_estado, 'email' => $obj->email,
                            'empresa_id' => $obj->empresaid);

        //$conn->close();
        
        return $varr_result;
    }

    function get_q_brokeraje($p_documento){
        $conn = new db_param; $conn->connect();

        $v_sql = "  select  count(1) as contador from usuarios 
                    where   identificacion = '".$p_documento."' and estado > 0 and estado_activo = 1";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $resultado = $obj->contador;

        //$conn->close();

        return $resultado;
    }

    function aprobar_broker($p_broker_id, $p_informe_path){
        $conn = new db_param; $conn->connect();

        $v_sql = "  update usuarios set estado_activo = 1, informe_broker = '".$p_informe_path."' where id = ".$p_broker_id;

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();

        //$conn->close();

        return 1;
    }

    function rechazar_broker($p_broker_id, $p_informe_path){
        $conn = new db_param; $conn->connect();

        $v_sql = "  update usuarios set estado = 0, estado_activo = 0, informe_broker = '".$p_informe_path."' where id = ".$p_broker_id;

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $conn->next_record();

        //$conn->close();

        return 1;
    }

    function get_tipos_documentos_elec($p_cadena){
        if (strpos($p_cadena, "-") !== false) {
            $arr = explode("-", $p_cadena);
        } else {
            $arr = array($p_cadena);
        }

        return $arr;
    }

    function valida_emrpesa_id($parr_datos){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        if (isset($parr_datos['nombre'])){
            $qry = "select count(1) as contador from empresa where nombre = LTRIM(RTRIM('".$parr_datos['nombre']."')) and estado > 0";

            $idqry = $conn->query($qry);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            if ($obj->contador > 0){
                $qry = "select id from empresa where nombre = LTRIM(RTRIM('".$parr_datos['nombre']."')) and estado > 0";

                $idqry = $conn2->query($qry);
                if (!$idqry) echo pg_last_error($conn2->Link_ID);
                $obj = $conn2->next_record();

                $v_id = $obj->id;
            } else $v_id = 0;
        } elseif (isset($parr_datos['documento'])){
            $qry = "select count(1) as contador from empresa where identificacion = LTRIM(RTRIM('".$parr_datos['documento']."')) and estado > 0";

            $idqry = $conn->query($qry);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();

            if ($obj->contador > 0){
                $qry = "select id from empresa where identificacion = LTRIM(RTRIM('".$parr_datos['documento']."')) and estado > 0";

                $idqry = $conn2->query($qry);
                if (!$idqry) echo pg_last_error($conn2->Link_ID);
                $obj = $conn2->next_record();

                $v_id = $obj->id;
            } else $v_id = 0;
        } else $v_id = 0;

        //$conn->close(); $conn2->close();

        return $v_id;
    }

    function get_datos_cuenta_emisor($p_empresa_id, $p_moneda_id){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $v_qry = "select count(1) as contador from empresa_cuenta_banco where empresa_id = ".$p_empresa_id." and moneda_id = ".$p_moneda_id." and estado_id = 68";

        $idqry = $conn->query($v_qry);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        if ($obj->contador > 0){
            $v_qry = "  select  empresa_cuenta_banco.id, empresa_cuenta_banco.banco_id, bancos.nombre_banco, empresa_cuenta_banco.nro_cuenta, empresa_cuenta_banco.tcuenta_id,
                                tipos.nombre as tcuenta_nombre
                        from    empresa_cuenta_banco, bancos, tipos
                        where   empresa_cuenta_banco.empresa_id = ".$p_empresa_id." and empresa_cuenta_banco.moneda_id = ".$p_moneda_id." and empresa_cuenta_banco.estado_id = 68 and
                                bancos.id = empresa_cuenta_banco.banco_id and tipos.id = empresa_cuenta_banco.tcuenta_id
                        order by empresa_cuenta_banco.id";

            $idqry = $conn2->query($v_qry);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj2 = $conn2->next_record();

            for($i = 0; $i < $conn2->nrows(); $i ++){
                $varr_result[$i] = array(   'msg' => 'CON DATOS', 'banco_id' => $obj2->banco_id, 'banco_nombre' => $obj2->nombre_banco, 'nro_cuenta' => $obj2->nro_cuenta,
                                            'tcuenta_id' => $obj2->tcuenta_id, 'tcuenta_nombre' => $obj2->tcuenta_nombre);

                $obj2 = $conn2->next_record();
            }
        } else {
            $varr_result[0] = array('msg' => 'VACIO');
        }

        return $varr_result;
    }
}
?>