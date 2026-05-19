<?
class inversiones{
    function get_inversion_xusuario($usuarioid,$tipo, $empresaid, $p_rowini, $p_rows){
        $csub = new db_param_trans;
        $csub->connect();
        $arr_inversiones = array();
        
        $idqry = $csub->query("select * from SUB_SUBASTAS_XINVERSIONISTA(".$usuarioid.",'".$tipo."',".$empresaid.",".$p_rowini.",".$p_rows.")");
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();
        
        for($i = 0; $i < $csub->nrows(); $i ++){
            $arr_inversiones[$i] = array('subasta_id'=>$obj->r_subasta_id,'e_subasta_id'=>$obj->r_e_subasta_id,
                                    'e_subasta' => $obj->r_e_subasta, 'porciento_financia' => $obj->r_porciento_financia,
                                    'monto_financia' => $obj->r_monto_financia, 'monto_remanente' => $obj->r_monto_remanente,
                                    'transferencia_path' => $obj->r_transferenciapath, 'factura_id' => $obj->r_facturaid,
                                    'factura_nro' => $obj->r_factura_nro, 'f_vencimiento' => $obj->r_fvencimiento,
                                    'cliente_id' => $obj->r_cliente_id, 'moneda_id' => $obj->r_moneda_id,
                                    'moneda' => $obj->r_moneda_nombre, 'cliente_nro' => $obj->r_cliente_nro,
                                    'cliente' => $obj->r_cliente_nombre, 'tia_final' => $obj->r_tia_final,
                                    'porciento_inversion' => $obj->r_representacion, 'monto_inversion' => $obj->r_monto_propuesta,
                                    'propuesta_id' => $obj->r_propuesta_id, 'e_propuesta' => $obj->r_e_propuesta,
                                    'fondo_disponible' => $obj->r_fondo_disponible, 'e_propuesta_id' => $obj->r_e_propuesta_id,
                                    'ganancia' => $obj->r_ganancia, 'riesgo_id' => $obj->r_riesgo_id,
                                    'riesgo_desc' => $obj->r_riesgo_desc, 'riesgo_nombre' => $obj->r_riesgo_nombre,
                                    'calificacion' => $obj->r_calificacion, 'e_financia' => $obj->r_e_financia,
                                    'e_financia_id' => $obj->r_e_financia_id
                                );
            $obj = $csub->next_record();
        }

        $csub->close();
        return $arr_inversiones;
    }
    function get_inversiones_xusuario($usuarioid,$tipo, $empresaid, $p_rowini, $p_rows){
        $csub = new db_param_trans;
        $csub->connect();
        $arr_inversiones = array();
        
        $idqry = $csub->query("select * from INV_INVERSIONES_XINVERSIONISTA(".$usuarioid.",'".$tipo."',".$empresaid.",".$p_rowini.",".$p_rows.")");
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();
        
        for($i = 0; $i < $csub->nrows(); $i ++){
            $arr_inversiones[$i] = array('subasta_id'=>$obj->r_subasta_id,'e_subasta_id'=>$obj->r_e_subasta_id,
                                    'e_subasta' => $obj->r_e_subasta, 'porciento_financia' => $obj->r_porciento_financia,
                                    'monto_financia' => $obj->r_monto_financia, 'monto_remanente' => $obj->r_monto_remanente,
                                    'transferencia_path' => $obj->r_transferenciapath, 'factura_id' => $obj->r_facturaid,
                                    'factura_nro' => $obj->r_factura_nro, 'f_vencimiento' => $obj->r_fvencimiento,
                                    'cliente_id' => $obj->r_cliente_id, 'moneda_id' => $obj->r_moneda_id,
                                    'moneda' => $obj->r_moneda_nombre, 'cliente_nro' => $obj->r_cliente_nro,
                                    'cliente' => $obj->r_cliente_nombre, 'tia_final' => $obj->r_tia_final,
                                    'porciento_inversion' => $obj->r_representacion, 'monto_inversion' => $obj->r_monto_propuesta,
                                    'propuesta_id' => $obj->r_propuesta_id, 'e_propuesta' => $obj->r_e_propuesta,
                                    'fondo_disponible' => $obj->r_fondo_disponible, 'e_propuesta_id' => $obj->r_e_propuesta_id,
                                    'ganancia' => $obj->r_ganancia, 'riesgo_id' => $obj->r_riesgo_id,
                                    'riesgo_desc' => $obj->r_riesgo_desc, 'riesgo_nombre' => $obj->r_riesgo_nombre,
                                    'calificacion' => $obj->r_calificacion, 'e_financia' => $obj->r_e_financia,
                                    'e_financia_id' => $obj->r_e_financia_id, 'ganancia' => $obj->r_ganancia,
                                    'factura_fvencimiento' => $obj->r_factura_fvencimiento, 'factura_fpago' => $obj->r_factura_fpago, 'factura_diff_pago' => $obj->r_factura_diff_pago
                                );
            $obj = $csub->next_record();
        }

        $csub->close();
        return $arr_inversiones;
    }
    function get_propuesta($propuesta_id){
        $conn_t = new db_param_trans;
        $conn_t->connect();
        
        $idqry = $conn_t->query("select * from INV_GET_PROPUESTA(".$propuesta_id.")");
        if (!$idqry) echo pg_last_error($conn_t->Link_ID);
        $obj = $conn_t->next_record();
        
        $arr_propuesta = array('subasta_id'=>$obj->r_subasta_id,'usuario_id'=>$obj->r_usuario_id,
                                'f_creacion' => $obj->r_f_creacion, 'e_propuesta_id' => $obj->r_e_propuesta_id,
                                'e_propuesta' => $obj->r_e_propuesta, 'moneda_id' => $obj->r_moneda_id,
                                'moneda' => $obj->r_moneda, 'monto' => $obj->r_monto,
                                'porciento_financia' => $obj->r_porciento_financia, 'tia' => $obj->r_tia,
                                'fondo_disponible' => $obj->r_fondo_disponible, 'propuesta_tia'=>$obj->r_propuesta_tia, 'cliente_nombre'=>$obj->r_cliente_nombre,
                                'ganancia'=>$obj->r_ganancia, 'fecha_vencimiento'=>$obj->r_fecha_vencimiento);
        
        $conn_t->close();
        return $arr_propuesta;
    }
    function get_inversion_xusuario_pendiente($usuarioid, $empresaid, $moneda_id){
        $csub = new db_param_trans;
        $csub->connect();
        $arr_inversiones = array();
        
        $idqry = $csub->query("select * from INV_PROPUESTA_PEND_XINVERSIONISTA(".$usuarioid.",'".$moneda_id."',".$empresaid.")");
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();
        
        for($i = 0; $i < $csub->nrows(); $i ++){
            $arr_inversiones[$i] = array('propuesta_id'=>$obj->r_propuesta_id,'subasta_id'=>$obj->r_subasta_id,
                                    'f_creacion' => $obj->r_f_creacion, 'monto' => $obj->r_monto,
                                    'monto_comprometido' => $obj->r_comprometido, 'factura' => $obj->r_factura,
                                    'pagador' => $obj->r_pagador
                                );
            $obj = $csub->next_record();
        }

        $csub->close();
        return $arr_inversiones;
    }
    function get_count_inversiones($usuarioid,$tipo, $empresaid){
        $conn = new db_param_trans;
        $conn->connect();
        $idqry = $conn->query("select INV_INVERSIONES_COUNT(".$usuarioid.",'".$tipo."',".$empresaid.") as contador");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $resultado = $obj->contador;
        $conn->close();
        return $resultado;
    }
    function get_montos_inversion($usuario_id, $tipo, $empresa_id){
        $conn = new db_param_trans;
        $conn->connect();
        $v_arr = array();

        $idqry = $conn->query("select * from INV_INVERSIONES_MONTOS(".$usuario_id.",'".$tipo."',".$empresa_id.")");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        for ($i = 0; $i < $conn->nrows(); $i++){
            $v_arr[$i] = array('moneda_id'=>$obj->moneda_id, 'moneda_nom'=>$obj->moneda_nom, 'monto'=>$obj->monto, 'simbolo'=>$obj->moneda_simbolo);
            $obj = $conn->next_record();
        }
        
        $conn->close();
        return $v_arr;
    }
    function get_detalle_inversion($p_propuesta_id, $p_factura_id){
        $conn = new db_param_trans;
        $conn->connect();
        $conn_inv = new db_param_trans;
        $conn_inv->connect();
        $v_arr_inversion = array();

        $idqry = $conn->query("select factura.numero, empresa.nombre, factura.monedaid, tipos.nombre as moneda, factura.fvencimiento, 
                                    coalesce(factura.f_confirmacion,factura.fvencimiento) as f_confirmacion_factura 
                                from factura, empresa, tipos
                                where factura.id = ".$p_factura_id." and empresa.id = factura.clienteid and tipos.id = factura.monedaid");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $v_arr_inversion['factura_numero'] = $obj->numero;
        $v_arr_inversion['cliente'] = $obj->nombre;
        $v_arr_inversion['moneda_id'] = $obj->monedaid;
        $v_arr_inversion['moneda'] = $obj->moneda;
        $v_arr_inversion['f_vencimiento'] = $obj->fvencimiento;
        $v_arr_inversion['f_pago_factura'] = $obj->f_confirmacion_factura;

        $idqry = $conn_inv->query("select subasta.estado as e_subasta_id, estados_sub.nombre as e_subasta, financiamiento.estado as e_finan_id, estados_fin.nombre as e_finan,
                                financiamiento.fregistro, registro_financiamiento.dias_original, registro_financiamiento.monto_ganancia, registro_financiamiento.monto_inversion,
                                financiamiento.fpago 
                                from propuestas, estados as estados_sub, 
                                    (((subasta left outer join financiamiento on financiamiento.subastaid = subasta.id) left outer join estados as estados_fin on 
                                    estados_fin.id = financiamiento.estado) left outer join registro_financiamiento on registro_financiamiento.financiamientoid = financiamiento.id and 
                                    registro_financiamiento.propuestaid = ".$p_propuesta_id.") 
                                where propuestas.id = ".$p_propuesta_id." and subasta.id = propuestas.subastaid and estados_sub.id = subasta.estado");
        if (!$idqry) echo pg_last_error($conn_inv->Link_ID);
        $obj_inv = $conn_inv->next_record();

        $v_arr_inversion['e_subasta_id'] = $obj_inv->e_subasta_id;
        $v_arr_inversion['e_subasta'] = $obj_inv->e_subasta;
        $v_arr_inversion['e_finan_id'] = $obj_inv->e_finan_id;
        $v_arr_inversion['e_finan'] = $obj_inv->e_finan;
        $v_arr_inversion['fregistro'] = $obj_inv->fregistro;
        $v_arr_inversion['dias_inversion'] = $obj_inv->dias_original;
        $v_arr_inversion['monto_ganancia'] = $obj_inv->monto_ganancia;
        $v_arr_inversion['monto_inversion'] = $obj_inv->monto_inversion; $v_arr_inversion['f_pago'] = $obj_inv->fpago;

        $conn->close();
        $conn_inv->close();

        return $v_arr_inversion;
    }
    function get_inversiones_liquidadas($usuario_id, $empresa_id, $p_finicio){
        $conn = new db_param_trans;
        $conn->connect();
        $v_arr = array();

        $idqry = $conn->query("select * from INV_INVERSIONES_LIQUIDADAS_PERIODO(".$usuario_id.",".$empresa_id.",'".$p_finicio."')");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        for ($i = 0; $i < $conn->nrows(); $i++){
            $v_arr[$i] = array('propuesta_id'=>$obj->propuesta_id, 'factura_numero'=>$obj->factura_numero, 'pagador'=>$obj->pagador, 'f_inversion'=>$obj->f_inversion,
                            'moneda_id'=>$obj->moneda_id, 'moneda_nom'=>$obj->moneda_nom, 'monto_inversion'=>$obj->monto_inversion, 'monto_ganancia'=>$obj->monto_ganancia,
                            'tia'=>$obj->tia, 'dias_inversion'=>$obj->dias_inversion, 'f_pago'=>$obj->f_pago, 'factura_id'=>$obj->factura_id);
            $obj = $conn->next_record();
        }
        
        $conn->close();
        return $v_arr;
    }
    function get_pendientes_deposito($usuario_id){
        $conn = new db_param_trans;
        $conn->connect();
        $v_arr = array();
        
        $idqry = $conn->query("select * from INV_PENDIENTES_DEPOSITO(".$usuario_id.")");
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        for ($i = 0; $i < $conn->nrows(); $i++){
            $v_arr[$i] = array('factura_id'=>$obj->r_factura_id, 'pagador'=>$obj->r_pagador, 'factura_nro'=>$obj->r_factura_nro, 'factura_fvencimiento'=>$obj->r_factura_fvencimiento,
                            'monto_inversion'=>$obj->r_monto_inversion, 'tia'=>$obj->r_tia, 'fondo_pendiente'=>$obj->r_fondo_pendiente, 'moneda_id'=>$obj->r_moneda_id,
                            'moneda'=>$obj->r_moneda, 'fpropuesta'=>$obj->r_fpropuesta, 'subasta_ffin'=>$obj->r_subasta_ffin, 'subasta_hfin'=>$obj->r_subasta_hfin,
                            'fpropuesta_formato'=>$obj->r_fpropuesta_formato, 'subasta_id'=>$obj->r_subasta_id, 'propuesta_id'=>$obj->r_propuesta_id);
            $obj = $conn->next_record();
        }
        
        $conn->close();
        return $v_arr;
    }

    function get_propuestas_xinversor($p_tipo_acceso, $p_usuario_id, $p_empresa_id, $p_rowini, $p_numrows){
        $conn = new db_param_trans; $conn->connect();
        
        if ($p_tipo_acceso == 'COUNT'){
            $idqry = $conn->query("select INV_PROPUESTAS_ACTIVAS_COUNT(".$p_usuario_id.",".$p_empresa_id.") as contador");
            if (!$idqry) echo pg_last_error($conn->Link_ID); $obj = $conn->next_record(); $v_arr = $obj->contador;
        } else{
            $idqry = $conn->query("select * from INV_PROPUESTAS_ACTIVAS(".$p_usuario_id.",".$p_empresa_id.",".$p_numrows.",".$p_rowini.")");

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_arr = array();
            
            for ($i = 0; $i < $conn->nrows(); $i++){
                $v_arr[$i] = array('propuesta_id'=>$obj->r_propuesta_id, 'subasta_id'=>$obj->r_subasta_id, 'propuesta_fcreacion'=>$obj->r_propuesta_fcreacion, 'monto'=>$obj->r_monto,
                                    'representacion'=>$obj->r_representacion, 'tia'=>$obj->r_tia, 'factura_id'=>$obj->r_factura_id, 'emisor_id'=>$obj->r_emisor_id, 
                                    'cliente_id'=>$obj->r_cliente_id, 'cliente_nombre'=>$obj->r_cliente_nombre, 'riesgo_id'=>$obj->r_riesgo_id, 'riesgo_nombre'=>$obj->r_riesgo_nombre,
                                    'riesgo_calificacion'=>$obj->r_riesgo_calificacion, 'riesgo_descripcion'=>$obj->r_riesgo_descripcion, 'color_fondo'=>$obj->r_riesgo_color_fondo,
                                    'color_letra'=>$obj->r_riesgo_color_letra, 'moneda_id'=>$obj->r_moneda_id, 'moneda_nombre'=>$obj->r_moneda_nombre, 'estado_id'=>$obj->r_estado_id,
                                    'simbolo_moneda'=>$obj->r_simbolo_moneda);
                $obj = $conn->next_record();
            }
        }

        $conn->close();
        return $v_arr;
    }

    function get_propuestas_pre_xinversor($p_tipo_acceso, $p_usuario_id, $p_empresa_id, $p_rowini, $p_numrows){
        $conn = new db_param_trans; $conn->connect();
        
        if ($p_tipo_acceso == 'COUNT'){
            $idqry = $conn->query("select INV_PROPUESTAS_PRE_ACTIVAS_COUNT(".$p_usuario_id.",".$p_empresa_id.") as contador");
            if (!$idqry) echo pg_last_error($conn->Link_ID); $obj = $conn->next_record(); $v_arr = $obj->contador;
        } else{
            $idqry = $conn->query("select * from INV_PROPUESTAS_PRE_ACTIVAS(".$p_usuario_id.",".$p_empresa_id.",".$p_numrows.",".$p_rowini.")");

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_arr = array();
            
            for ($i = 0; $i < $conn->nrows(); $i++){
                $v_arr[$i] = array('propuesta_id'=>$obj->r_propuesta_id, 'subasta_id'=>$obj->r_subasta_id, 'propuesta_fcreacion'=>$obj->r_propuesta_fcreacion, 'monto'=>$obj->r_monto,
                                    'representacion'=>$obj->r_representacion, 'tia'=>$obj->r_tia, 'factura_id'=>$obj->r_factura_id, 'emisor_id'=>$obj->r_emisor_id, 
                                    'cliente_id'=>$obj->r_cliente_id, 'cliente_nombre'=>$obj->r_cliente_nombre, 'riesgo_id'=>$obj->r_riesgo_id, 'riesgo_nombre'=>$obj->r_riesgo_nombre,
                                    'riesgo_calificacion'=>$obj->r_riesgo_calificacion, 'riesgo_descripcion'=>$obj->r_riesgo_descripcion, 'color_fondo'=>$obj->r_riesgo_color_fondo,
                                    'color_letra'=>$obj->r_riesgo_color_letra, 'moneda_id'=>$obj->r_moneda_id, 'moneda_nombre'=>$obj->r_moneda_nombre, 'estado_id'=>$obj->r_estado_id,
                                    'simbolo_moneda'=>$obj->r_simbolo_moneda);
                $obj = $conn->next_record();
            }
        }

        $conn->close();
        return $v_arr;
    }

    function anular_propuesta($p_propuesta_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update propuesta set estado = 0 where id = ".$p_propuesta_id);
        if (!$idqry) echo pg_last_error($conn->Link_ID); 
        $conn->next_record();

        $conn->close();
    }

    function get_perfil_inversor($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        $idqry = $conn2->query("select count(1) as contador from perfil_inversion where id_usuario = ".$p_inversor_id." and estado_id = 1");
        if (!$idqry) echo pg_last_error($conn2->Link_ID); 
        $obj2 = $conn2->next_record();

        if ($obj2->contador > 0){
            $idqry = $conn->query("select monto_minimo_inst, monto_maximo_inst, notificacion_correo, notificacion_msg, invierte_auto, notificacion_email_oportunidades, 
                                notificacion_msg_oportunidades, notificacion_cada_oportunidad, tea_automatica 
                            from perfil_inversion 
                            where id_usuario = ".$p_inversor_id);
            if (!$idqry) echo pg_last_error($conn->Link_ID); 
            $obj = $conn->next_record();

            $varr_perfil_inversor = array('monto_min' => $obj->monto_minimo_inst, 'monto_max' => $obj->monto_maximo_inst, 'notifica_email' => $obj->notificacion_correo, 
                                        'notifica_msg' => $obj->notificacion_msg, 'invierte_auto' => $obj->invierte_auto, 'notifica_email_oport' => $obj->notificacion_email_oportunidades, 
                                        'notifica_msg_oport' => $obj->notificacion_msg_oportunidades, 'notifica_cada_oport' => $obj->notificacion_cada_oportunidad, 'count' => $obj2->contador, 'tea_automatica' => $obj->tea_automatica);
        } else $varr_perfil_inversor['count'] = 0;

        $conn->close(); $conn2->close();
        return $varr_perfil_inversor;
    }
    function get_perfil_inversor_detalle($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select perfil_inversion_variable.variable_id, tipos.nombre, perfil_inversion_variable.variable_valor_id, perfil_inversion_variable.monto_minimo,
                                perfil_inversion_variable.monto_maximo 
                            from perfil_inversion_variable, tipos 
                            where perfil_inversion_variable.perfil_inv_id = ".$p_inversor_id." and tipos.id = perfil_inversion_variable.variable_id and perfil_inversion_variable.estado_id = 1 
                            order by tipos.nombre");
        if (!$idqry) echo pg_last_error($conn->Link_ID); 
        $obj = $conn->next_record();
        $varr_perfil_inversor = array();

        for ($i = 0; $i < $conn->nrows(); $i++){
            $varr_perfil_inversor[$i] = array('variable_id' => $obj->variable_id, 'variable_nombre' => $obj->nombre, 'variable_detalle_id' => $obj->variable_valor_id,
                                            'monto_minimo' => $obj->monto_minimo, 'monto_maximo' => $obj->monto_maximo);
        }

        $conn->close();
        return $varr_perfil_inversor;
    }

    function activa_perfil_inversor($p_datos){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $conn3 = new db_param_trans; $conn3->connect();

        $idqry = $conn->query("select INV_ACTIVA_PERFIL_INVERSOR(".$p_datos[0]['inversor_id'].",".$p_datos[0]['activa_perfil'].",".$p_datos[0]['automatico'].",
                                    ".$p_datos[0]['email_legal'].",".$p_datos[0]['msg_legal'].",".$p_datos[0]['email_opor'].",
                                    ".$p_datos[0]['msg_opor'].",".$p_datos[0]['cada_opor'].",".$p_datos[0]['monto_minimo'].",
                                    ".$p_datos[0]['monto_maximo'].",".$_SESSION['user']['usuarioid'].",'".$_SERVER['REMOTE_ADDR']."',".$p_datos[0]['tea_automatica'].") as activacion");
        if (!$idqry) echo pg_last_error($conn->Link_ID); 
        $obj = $conn->next_record();
        
        if ($obj->activacion == 1){
            $idqry = $conn3->query("select INV_ANULA_PERFIL_VARIABLE(".$p_datos[0]['inversor_id'].",'".$_SERVER['REMOTE_ADDR']."',".$_SESSION['user']['usuarioid'].") as anulacion");
            if (!$idqry) echo pg_last_error($conn3->Link_ID); 
            $obj = $conn3->next_record();

            for ($i=1; $i<count($p_datos); $i++){
                $idqry = $conn2->query("select INV_REGISTRA_PERFIL_INVERSOR_DETALLE(".$p_datos[0]['inversor_id'].",".$p_datos[$i]['variable'].",
                                    ".$p_datos[$i]['variable_detalle'].",'".$_SERVER['REMOTE_ADDR']."',".$_SESSION['user']['usuarioid'].") as activa_detalle");
                if (!$idqry) echo pg_last_error($conn2->Link_ID);
            }
            
            $obj = $conn2->next_record();
        }

        $conn->close(); $conn2->close(); $conn3->close();
    }

    function desactivar_perfil_inversor($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select INV_DESACTIVA_PERFIL_INVERSOR(".$p_inversor_id.",".$_SESSION['user']['usuarioid'].",'".$_SERVER['REMOTE_ADDR']."') as activacion");
        if (!$idqry) echo pg_last_error($conn->Link_ID); 
        $obj = $conn->next_record();
        $resultado = $obj->activacion;

        $conn->close();
        return $resultado;
    }

    function get_inversiones($p_tipo, $p_row_ini, $p_num_rows, $p_filtros, $p_order){
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo == 'COUNT'){
            $v_sql = "select count(1) as contador from financiamiento where 1 = 1";

            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID); 
            $obj = $conn->next_record();

            $varr_result = $obj->contador;
        } else {
            $v_sql = "select    financiamiento.id, financiamiento.facturaid, factura.numero, factura.clienteid, pagador.nombre as cliente_nombre, financiamiento.fpago, financiamiento.monedaid, 
                                tmoneda.dato1 as moneda_simbol, factura.total, financiamiento.monto_financiado, financiamiento.fpago_efectivo, financiamiento.monto_remanente_e,
                                financiamiento.ganancia_e, financiamiento.comision_factureate_e, emisor.nombre as emisor_nombre, financiamiento.fregistro,
                                coalesce(FINAN_GET_TASA_FINANCIAMIENTO(financiamiento.subastaid),0) as tasa_compuesta,
                                coalesce(FINAN_GET_INVERSORES_FINANCIAMIENTO(financiamiento.subastaid),'') as inversores  
                    from        financiamiento, factura, empresa as pagador, tipos as tmoneda, empresa as emisor 
                    where       factura.id = financiamiento.facturaid and pagador.id = factura.clienteid and tmoneda.id = financiamiento.monedaid and 
                                emisor.id = factura.emisorid";

            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;
            if ($p_order != '') $v_sql .= " order by ".$p_order;
            if ($p_num_rows > 0) $v_sql .= " limit ".$p_num_rows." offset ".$p_row_ini;

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID); 
            $obj = $conn->next_record();

            $varr_result = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $varr_result[$i] = array(   'finan_id' => $obj->id,                       'factura_id' => $obj->facturaid,            'factura_numero' => $obj->numero,
                                            'cliente_id' => $obj->clienteid,              'cliente_nombre' => $obj->cliente_nombre,   'f_vencimiento' => $obj->fpago,
                                            'moneda_id' => $obj->monedaid,                'moneda_simbol' => $obj->moneda_simbol,     'monto_factura' => $obj->total,
                                            'monto_financiado' => $obj->monto_financiado, 'fpago_efectivo' => $obj->fpago_efectivo,   'monto_remanente' => $obj->monto_remanente_e,
                                            'ganancia' => $obj->ganancia_e,               'comision_factureate' => $obj->comision_factureate_e,
                                            'emisor_nombre' => $obj->emisor_nombre,       'f_registro' => $obj->fregistro,            'tasa_compuesta' => $obj->tasa_compuesta,
                                            'inversores' => $obj->inversores);
                $obj = $conn->next_record();
            }
        }

        $conn->close();
        return $varr_result;
    }

    function get_inversores_perfil_match($p_arr){
        $conn = new db_param_trans; $conn->connect();
        $conn_base = new db_param_trans; $conn_base->connect();
        $conn_risk = new db_param_trans; $conn_risk->connect();
        $conn_risk_user = new db_param_trans; $conn_risk_user->connect();
        $conn_sector = new db_param_trans; $conn_sector->connect();
        $conn_sector_user = new db_param_trans; $conn_sector_user->connect();
        $conn_time = new db_param_trans; $conn_time->connect();
        $conn_time_user = new db_param_trans; $conn_time_user->connect();

        $varr_inversores = array();
        $v_indice = 0;
        $v_inversor_cadena = '';

        //---- seleccion de todos los inversores con perfil activo
        $v_sql = "  select count(1) as contador from perfil_inversion 
                    where estado_id > 0 and monto_minimo_inst <= ".$p_arr['monto']." and 
                        (monto_maximo_inst = 0 or (monto_maximo_inst > 0 and monto_maximo_inst >= ".$p_arr['monto']."))";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID); 
        $obj = $conn->next_record();

        if ($obj->contador > 0){
            //---- obtengo todos los inversores con perfil que califican en montos
            $v_sql_base = " select id_usuario from perfil_inversion 
                            where estado_id > 0 and monto_minimo_inst <= ".$p_arr['monto']." and 
                                (monto_maximo_inst = 0 or (monto_maximo_inst > 0 and monto_maximo_inst >= ".$p_arr['monto']."))";

            $idqry = $conn_base->query($v_sql_base);
            if (!$idqry) echo pg_last_error($conn_base->Link_ID); 
            $obj_base = $conn_base->next_record();
            $v_inversor_cadena = '';

            //---- arreglo principal con todos los inversores encontrados
            for($i = 0; $i < $conn_base->nrows(); $i ++){
                if ($i > 0) $v_inversor_cadena .= ',';

                $varr_inversores[$i] = array('inversor_id' => $obj_base->id_usuario, 'estado_id' => 1);
                $v_inversor_cadena .= $obj_base->id_usuario;
                $obj_base = $conn_base->next_record();
            }

            $v_indice = $i;
        }

        //---- analisis del perfil de riesgo variable 103
        if ($v_inversor_cadena == '') $v_inversor_cadena = '0';

        $v_sql_risk = " select distinct perfil_inv_id from perfil_inversion_variable  
                        where estado_id > 0 and perfil_inv_id in (".$v_inversor_cadena.") and  variable_id = 103";

        $idqry = $conn_risk_user->query($v_sql_risk);
        if (!$idqry) echo pg_last_error($conn_risk_user->Link_ID); 
        $obj_risk_user = $conn_risk_user->next_record();
        $varr_risk_user = array();
        $v_cadena_riskuser = '';

        for($i = 0; $i < $conn_risk_user->nrows(); $i ++){
            if ($i > 0) $v_cadena_riskuser .= ',';

            $varr_risk_user[$i] = array('inversor_id' => $obj_risk_user->perfil_inv_id, 'estado_id' => 0);
            $v_cadena_riskuser .= $obj_risk_user->perfil_inv_id;
            $obj_risk_user = $conn_risk_user->next_record();
        }

        if ($v_cadena_riskuser == '') $v_cadena_riskuser = '0';

        $v_sql_risk = " select distinct perfil_inv_id from perfil_inversion_variable  
                        where estado_id > 0 and perfil_inv_id in (".$v_cadena_riskuser.") and  variable_id = 103 and 
                            ((".$p_arr['riesgo_id']." = 7 and ".$p_arr['riesgo_id']." = variable_valor_id) or 
                            (".$p_arr['riesgo_id']." <> 7 and ".$p_arr['riesgo_id']." >= variable_valor_id))";

        $idqry = $conn_risk->query($v_sql_risk);
        if (!$idqry) echo pg_last_error($conn_risk->Link_ID); 
        $obj_risk = $conn_risk->next_record();
        
        for($i = 0; $i < $conn_risk->nrows(); $i ++){
            for ($j = 0; $j < count($varr_risk_user); $j++){
                if ($varr_risk_user[$j]['inversor_id'] == $obj_risk->perfil_inv_id){
                    $varr_risk_user[$j]['estado_id'] = 1;
                    break;
                }
            }

            $obj_risk = $conn_risk->next_record();
        }

        //---- ya se tienen todos los inversores con perfil que cumplen con riesgos
        for ($i = 0; $i < count($varr_inversores); $i++){
            for ($j = 0; $j < count($varr_risk_user); $j++){
                if ($varr_inversores[$i]['inversor_id'] == $varr_risk_user[$j]['inversor_id']){
                    if ($varr_risk_user[$j]['estado_id'] == 0) $varr_inversores[$i]['estado_id'] = 0;
                    break;
                }
            }
        } 

        //---- analisis de sector economico, variable 104
        $v_inversor_cadena = '';

        for ($i = 0; $i < count($varr_inversores); $i++){
            if ($varr_inversores[$i]['estado_id'] == 1){
                if ($v_inversor_cadena != '') $v_inversor_cadena .= ',';
                $v_inversor_cadena .= $varr_inversores[$i]['inversor_id'];
            }
        }

        if ($v_inversor_cadena == '') $v_inversor_cadena = '0';

        $v_sql_sector = "   select distinct perfil_inv_id from perfil_inversion_variable  
                            where estado_id > 0 and perfil_inv_id in (".$v_inversor_cadena.") and  variable_id = 104";

        $idqry = $conn_sector_user->query($v_sql_sector);
        if (!$idqry) echo pg_last_error($conn_sector_user->Link_ID); 
        $obj_sector = $conn_sector_user->next_record();
        $varr_sector_user = array();
        $v_cadena_sector = '';
        
        for($i = 0; $i < $conn_sector_user->nrows(); $i ++){
            if ($i > 0) $v_cadena_sector .= ',';

            $varr_sector_user[$i] = array('inversor_id' => $obj_sector->perfil_inv_id, 'estado_id' => 0);
            $v_cadena_sector .= $obj_sector->perfil_inv_id;

            $obj_sector = $conn_sector_user->next_record();
        }

        if ($v_cadena_sector == '') $v_cadena_sector = '0';

        $v_sql_sector = "   select distinct perfil_inv_id from perfil_inversion_variable  
                            where estado_id > 0 and perfil_inv_id in (".$v_cadena_sector.") and  variable_id = 104 and 
                                variable_valor_id = ".$p_arr['sector_id'];

        $idqry = $conn_sector->query($v_sql_sector);
        if (!$idqry) echo pg_last_error($conn_sector->Link_ID); 
        $obj_sector = $conn_sector->next_record();

        for($i = 0; $i < $conn_sector->nrows(); $i ++){
            for ($j = 0; $j < count($varr_sector_user); $j++){
                if ($obj_sector->perfil_inv_id == $varr_sector_user[$j]['inversor_id']) $varr_sector_user[$j]['estado_id'] = 1;
                break;
            }
        }

        //---- arreglo de inversores filtrado
        for ($i = 0; $i < count($varr_inversores); $i++){
            if ($varr_inversores[$i]['estado_id'] == 1){
                for ($j = 0; $j < count($varr_sector_user); $j++){
                    if ($varr_inversores[$i]['inversor_id'] == $varr_sector_user[$j]['inversor_id']){
                        if ($varr_sector_user[$j]['estado_id'] == 0) $varr_inversores[$i]['estado_id'] = 0;
                        break;
                    }
                }
            }
        }

        //---- analisis de tiempo - variable 105 // tipos 106 30-60, 107 60-80, 108 80-100, 109 100-120 110 120-mas
        if ($p_arr['plazo'] >= 120) $v_tiempos = '106,107,108,109,110';
        if ($p_arr['plazo'] >= 100 && $p_arr['plazo'] < 120) $v_tiempos = '106,107,108,109';
        if ($p_arr['plazo'] >= 80 && $p_arr['plazo'] < 100) $v_tiempos = '106,107,108';
        if ($p_arr['plazo'] >= 60 && $p_arr['plazo'] < 80) $v_tiempos = '106,107';
        if ($p_arr['plazo'] >= 30 && $p_arr['plazo'] < 60) $v_tiempos = '106';
        else $v_tiempos = '0';

        $v_inversor_cadena = '';

        for ($i = 0; $i < count($varr_inversores); $i++){
            if ($varr_inversores[$i]['estado_id'] == 1){
                if ($v_inversor_cadena != '') $v_inversor_cadena .= ',';
                $v_inversor_cadena .= $varr_inversores[$i]['inversor_id'];
            }
        }

        if ($v_inversor_cadena == '') $v_inversor_cadena = '0';

        $v_sql_time = "   select distinct perfil_inv_id from perfil_inversion_variable 
                            where estado_id > 0 and variable_id = 105 and perfil_inv_id in (".$v_inversor_cadena.")";

        $idqry = $conn_time_user->query($v_sql_time);
        if (!$idqry) echo pg_last_error($conn_time_user->Link_ID); 
        $obj_time = $conn_time_user->next_record();
        $varr_time = array();
        $v_inversor_time = '';
        
        for($i = 0; $i < $conn_time_user->nrows(); $i ++){
            if ($v_inversor_time != '') $v_inversor_time .= ',';
            $varr_time[$i] = array('inversor_id' => $obj_time->perfil_inv_id, 'estado' => 0);
            $v_inversor_time .= $obj_time->perfil_inv_id;
            $obj_time = $conn_time_user->next_record();
        }

        if (count($varr_time) > 0){
            $v_sql_time = "   select distinct perfil_inv_id from perfil_inversion_variable 
                            where estado_id > 0 and variable_id = 105 and variable_valor_id in (".$v_tiempos.") and perfil_inv_id in (".$v_inversor_time.")";

            $idqry = $conn_time->query($v_sql_time);
            if (!$idqry) echo pg_last_error($conn_time->Link_ID); 
            $obj_time = $conn_time->next_record();

            //---- busco todos los que no cumplen para deshabilitarlos
            for($i = 0; $i < $conn_time->nrows(); $i ++){
                for ($j = 0; $j < count($varr_time); $j++){
                    if ($obj_time->perfil_inv_id == $varr_time[$j]['inversor_id']){
                        $varr_time[$j]['estado_id'] = 1;
                        break;
                    }
                }
            }

            //---- marco los que no cumplen en elarreglo principal
            for ($i = 0; $i < count($varr_inversores); $i++){
                if ($varr_inversores[$i]['estado_id'] == 1){
                    for ($j = 0; $j < count($varr_time); $j++){
                        if ($varr_inversores[$i]['inversor_id'] == $varr_time[$j]['inversor_id']){
                            $varr_inversores[$i]['estado_id'] = $varr_time[$j]['estado_id'];
                            break;
                        }
                    }
                }
            }
        }

        $conn->close(); $conn_base->close(); $conn_risk->close(); $conn_risk_user->close(); $conn_sector->close(); $conn_sector_user->close();
        $conn_time->close(); $conn_time_user->close();

        return $varr_inversores;
    }

    function genera_acceso_inversion($p_factura_id){
        $conn = new db_param; $conn->connect();
        $vobj_mae_class = new maestros;

        $idqry = $conn->query("select certificado_seguridad_factura(".$p_factura_id.") as certificado");
        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();
        $varr_param = $vobj_mae_class->get_parametro_detalle(53);
        $v_acceso = $varr_param['valorchar'].'/acceso_rapido_inversion.php?tk='.$obj->certificado.'&fid='.$p_factura_id;

        $conn->close();

        return $v_acceso;
    }
}
?>