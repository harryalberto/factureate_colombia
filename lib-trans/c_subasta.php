<?
class subasta{
    function get_subastas_disponibles($rows,$rowini,$tipo){
        $conector = new db_param_trans;
        $conector->connect();
                
        if ($tipo == 'select'){
            $arrsubastas = array();
            $idqry = $conector->query("select * from sub_subastas_dispo(".$rowini.",".$rows.")");

            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
        
            for($i = 0; $i < $conector->nrows(); $i ++){
                $arrsubastas[$i] = array('subastaid'=>$obj->subastaid,'estadoid'=>$obj->subestadoid,
                                    'facturaid' => $obj->facturaid, 'cliente' => $obj->faccliente,
                                    'total' => $obj->factotal, 'monedaid' => $obj->facmonedaid,
                                    'moneda' => $obj->facmoneda, 'fvencimiento' => $obj->facfvencimiento,
                                    'riesgoid' => $obj->riesgoid, 'riesgo' => $obj->riesgo,
                                    'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                    'qpropuestas' => $obj->qpropuestas, 'montofin' => $obj->submontofin
                                );
                $obj = $conector->next_record();
            }
        } else{
            $idqry = $conector->query("select sub_subastas_dispo_count() as contador");

            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
            $arrsubastas = $obj->contador;
        }

        $conector->close();
        return $arrsubastas;
    }
    function get_propuesta_xinversionista($subastaid,$usuarioid, $empresaid){
        $conector = new db_param_trans;
        $conector->connect();
        $idqry = $conector->query("select sub_propuesta_xusuario(".$subastaid.",".$usuarioid.",".$empresaid.") as contador");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        $resultado = $obj->contador;

        $conector->close();
        return $resultado;
    }
    function get_subasta($subastaid){
        $conector = new db_param_trans;
        $conector->connect();
                
        $idqry = $conector->query("select * from sub_datos_subasta(".$subastaid.")");

        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        
        $arrsubasta = array('estadoid'=>$obj->subestadoid,
                                    'facturaid' => $obj->facturaid, 'cliente' => $obj->faccliente,
                                    'clienteid' => $obj->facclienteid,
                                    'total' => $obj->factotal, 'monedaid' => $obj->facmonedaid,
                                    'moneda' => $obj->facmoneda, 'fvencimiento' => $obj->facfvencimiento,
                                    'riesgoid' => $obj->riesgoid, 'riesgo' => $obj->riesgo,
                                    'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                    'facnumero' => $obj->facnumero, 'montofin' => $obj->submontofin,
                                    'riesgoscore' => $obj->riesgoscore, 'calificacionscore' => $obj->calificacionscore,
                                    'colorscore' => $obj->colorscore, 'tipofinancia' => $obj->tipofinancia,
                                    'emisor' => $obj->emisor, 'emisorid' => $obj->emisorid,
                                    'emisordoc' => $obj->emisordoc, 'clientedoc' => $obj->clientedoc,
                                    'tipofinanciamientonom' => $obj->tipofinanciamientonom, 'estadosubastaid' => $obj->estadosubastaid, 
                                    'estadosubasta' => $obj->estadosubasta, 'grupowinid' => $obj->grupowinid,
                                    'transferenciapath' => $obj->transferenciapath, 'fondospath' => $obj->fondospath,
                                    'f_subasta' => $obj->f_subasta, 'h_subasta' => $obj->h_subasta,
                                    'estado_compensa_id' => $obj->estado_compensa_id, 'estado_compensa' => $obj->estado_compensa,
                                    'ref_envio_contrato' => $obj->ref_envio_contrato, 'path_contrato' => $obj->path_contrato,
                                    'riesgo_factura_id' => $obj->riesgo_factura_id, 'riesgo_factura_califica' => $obj->riesgo_factura_califica,
                                    'riesgo_factura_nombre' => $obj->riesgo_factura_nombre, 'riesgo_factura_color' => $obj->riesgo_factura_color,
                                    'riesgo_factura_color_fuente' => $obj->riesgo_factura_color_fuente, 'emisor_correo' => $obj->emisor_correo,
                                    'emisor_telefono' => $obj->emisor_telefono, 'factura_femision' => $obj->factura_femision,
                                    'repre_emisor_nombre' => $obj->repre_emisor_nombre, 'repre_emisor_tipodoc_id' => $obj->repre_emisor_tipodoc_id,
                                    'repre_emisor_nrodoc' => $obj->repre_emisor_nrodoc, 'repre_emisor_tipodoc' => $obj->repre_emisor_tipodoc, 'monto_remanente' => $obj->monto_remanente,
                                    'simbolo_moneda'=>$obj->r_simbolo_moneda, 'condesc_maximo' => $obj->r_condesc_maximo, 'desc_maximo' => $obj->r_desc_maximo
                            );
                
        $conector->close();
        return $arrsubasta;
    }
    function get_subasta_posiciones($subastaid){
        $conector = new db_param_trans;
        $conector->connect();
        $arrsubasta = array();
                
        $idqry = $conector->query("select * from sub_datos_subasta_posiciones(".$subastaid.")");

        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        for($i = 0; $i < $conector->nrows(); $i ++){
            $arrsubasta[$i] = array('grupo'=>$obj->subgrupo, 'posicion' => $obj->subposicion,
                                    'posicion_porc' => $obj->subposicion_porc, 'propuestaid' => $obj->propuestaid,
                                    'estadoid' => $obj->estadoid, 'estado' => $obj->estado,
                                    'turno' => $obj->turno, 'tia' => $obj->tia,
                                    'turnofinal' => $obj->turnofinal, 'tiafinal' => $obj->tiafinal,
                                    'grupofinal' => $obj->grupofinal, 'fondo_disponible' => $obj->fondo_disponible
                            );
            $obj = $conector->next_record();
        }
                
        $conector->close();
        return $arrsubasta;
    }
    function genera_propuesta($arrpropuesta){
        $conector = new db_param_trans;
        $conector->connect();

        $idqry = $conector->query("select sub_genera_propuesta(".$arrpropuesta['subastaid'].",".$_SESSION['user']['usuarioid'].",".$arrpropuesta['monto'].",".$arrpropuesta['representacion'].",".$arrpropuesta['propuestaid'].",".$arrpropuesta['tia'].",".$_SESSION['user']['empresaid'].") as propuestaid");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        $propuestaid = $obj->propuestaid;
        
        $conector->close();
        return $propuestaid;
    }
    function get_posicion($subastaid, $usuarioid, $empresaid){
        $conector = new db_param_trans;
        $conector->connect();
                
        $idqry = $conector->query("select * from sub_datos_posicion(".$subastaid.",".$usuarioid.",".$empresaid.")");
        
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        for($i = 0; $i < $conector->nrows(); $i ++){
            $arrposicion = array('id'=>$obj->propuestaid, 'monto' => $obj->monto,
                                    'representacion' => $obj->repre, 'tia' => $obj->tia,
                                    'creador_usuarioid' => $obj->creador_usuarioid, 'estado_id'=>$obj->r_estado_id
                            );
        }
                
        $conector->close();
        return $arrposicion;
    }
    function anula_propuesta($propuestaid){
        $conector = new db_param_trans;
        $conector->connect();
        
        $idqry = $conector->query("select sub_anula_propuesta(".$propuestaid.") as propuestaid");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        $propuestaid = $obj->propuestaid;
        
        $conector->close();
        return $propuestaid;
    }
    function get_subastas_xestado($estadoid,$rows,$rowini,$tipo){
        $csub = new db_param_trans;
        $csub->connect();
                
        if ($tipo == 'select'){
            $arrsubastas = array();
            $idqry = $csub->query("select * from SUB_SUBASTAS_XESTADO(".$estadoid.",".$rowini.",".$rows.")");

            if (!$idqry) echo pg_last_error($csub->Link_ID);
            $obj = $csub->next_record();
        
            for($i = 0; $i < $csub->nrows(); $i ++){
                $arrsubastas[$i] = array('subastaid'=>$obj->subastaid,'estadoid'=>$obj->subestadoid,
                                        'facturaid' => $obj->facturaid, 'cliente' => $obj->faccliente,
                                        'total' => $obj->factotal, 'monedaid' => $obj->facmonedaid,
                                        'moneda' => $obj->facmoneda, 'fvencimiento' => $obj->facfvencimiento,
                                        'riesgoid' => $obj->riesgoid, 'riesgo' => $obj->riesgo,
                                        'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                        'qpropuestas' => $obj->qpropuestas, 'montofin' => $obj->submontofin,
                                        'facnumero' => $obj->facnumero, 'fsubasta_creacion'=> $obj->fsubasta_creacion,
                                        'hsubasta_creacion' => $obj->hsubasta_creacion, 'fsubasta_compensado' => $obj->fsubasta_compensado,
                                        'hsubasta_compensado'=> $obj->hsubasta_compensado, 'emisor_id' => $obj->emisor_id, 'emisor_nombre' => $obj->emisor_nombre,
                                        'estado_compensacion' => $obj->estado_compensacion, 'estado_compensacion_nombre' => $obj->estado_compensacion_nombre
                                    );
                $obj = $csub->next_record();
            }
        } else{
            $idqry = $csub->query("select SUB_SUBASTAS_XESTADO_COUNT(".$estadoid.") as contador");

            if (!$idqry) echo pg_last_error($csub->Link_ID);
            $obj = $csub->next_record();
            $arrsubastas = $obj->contador;
        }

        $csub->close();
        return $arrsubastas;
    }
    function get_subastas_xestado_varios($p_estados,$rows,$rowini,$tipo){
        $csub = new db_param_trans;
        $csub->connect();
                
        if ($tipo == 'select'){
            $arrsubastas = array();
            $idqry = $csub->query("select * from SUB_SUBASTAS_XESTADO_VARIOS(".$p_estados.",".$rowini.",".$rows.")");

            if (!$idqry) echo pg_last_error($csub->Link_ID);
            $obj = $csub->next_record();
        
            for($i = 0; $i < $csub->nrows(); $i ++){
                $arrsubastas[$i] = array('subastaid'=>$obj->subastaid,'estadoid'=>$obj->subestadoid,
                                        'facturaid' => $obj->facturaid, 'cliente' => $obj->faccliente,
                                        'total' => $obj->factotal, 'monedaid' => $obj->facmonedaid,
                                        'moneda' => $obj->facmoneda, 'fvencimiento' => $obj->facfvencimiento,
                                        'riesgoid' => $obj->riesgoid, 'riesgo' => $obj->riesgo,
                                        'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                        'qpropuestas' => $obj->qpropuestas, 'montofin' => $obj->submontofin,
                                        'facnumero' => $obj->facnumero, 'fsubasta_creacion'=> $obj->fsubasta_creacion,
                                        'hsubasta_creacion' => $obj->hsubasta_creacion, 'subasta_estado' => $obj->subasta_estado
                                    );
                $obj = $csub->next_record();
            }
        } else{
            $idqry = $csub->query("select SUB_SUBASTAS_XESTADO_VARIOS_COUNT(".$p_estados.") as contador");

            if (!$idqry) echo pg_last_error($csub->Link_ID);
            $obj = $csub->next_record();
            $arrsubastas = $obj->contador;
        }

        $csub->close();
        return $arrsubastas;
    }

    function get_subastas_v2($tipo, $rowini, $rows, $filtro, $order){
        $conn = new db_param_trans;
        $conn->connect();

        if ($tipo == 'COUNT'){
            $v_qry = "select count(1) as contador from subasta where ".$filtro;

            $idqry = $conn->query($v_qry);

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $arrsubastas = $obj->contador;
        } else{
            $v_qry = "  select  subasta.id, subasta.estado, subasta.facturaid, empresa.nombre as cliente,factura.total, factura.monedaid, 
                                tipos.nombre as monedanom, factura.fvencimiento, tipo_riesgo_empresa.id as riesgoid, 
                                tipo_riesgo_empresa.nombre as riesgonom, tipo_riesgo_empresa.calificacion, tipo_riesgo_empresa.color,
                                subasta.monto_financia, factura.numero, subasta.fcreacion, subasta.hcreacion, estados.nombre as estado_sub
                        from    subasta, factura, empresa, tipos, riesgo_empresa, tipo_riesgo_empresa, estados
                        where   ".$filtro." and factura.id = subasta.facturaid and empresa.id = factura.clienteid and 
                                tipos.id = factura.monedaid and riesgo_empresa.empresaid = factura.clienteid and 
                                riesgo_empresa.estado = 1 and tipo_riesgo_empresa.id = riesgo_empresa.riesgoid and 
                                estados.id = subasta.estado
                        order by ".$order." 
                        limit ".$rows." offset ".$rowini;

            $idqry = $conn->query($v_qry);

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $arrsubastas = array();
        
            for($i = 0; $i < $conn->nrows(); $i ++){
                $arrsubastas[$i] = array(   'subastaid'=>$obj->id,                'estadoid'=>$obj->estado,
                                            'facturaid' => $obj->facturaid,       'cliente' => $obj->cliente,
                                            'total' => $obj->total,               'monedaid' => $obj->monedaid,
                                            'moneda' => $obj->monedanom,          'fvencimiento' => $obj->fvencimiento,
                                            'riesgoid' => $obj->riesgoid,         'riesgo' => $obj->riesgonom,
                                            'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                            'montofin' => $obj->monto_financia,   'facnumero' => $obj->numero, 
                                            'fsubasta_creacion'=> $obj->fcreacion,'hsubasta_creacion' => $obj->hcreacion, 
                                            'subasta_estado' => $obj->estado_sub
                                    );
                $obj = $conn->next_record();
            }
        }

        $conn->close();
        return $arrsubastas;
    }

    function liquidar_subasta($p_arr_subasta){
        $csub = new db_param_trans; $csub->connect();
        $conn = new db_param_trans; $conn->connect();
        // guardo los datos de la transferencia de fondos
        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fhoy = date('Y-m-d');
        $v_hhoy = time();
        $v_hhoy_format = date("H:i:s", $v_hhoy);
        $idqry = $conn->query("update subasta set f_transferencia = '".$v_fhoy."', h_transferencia = '".$v_hhoy_format."', fondospath = '".$p_arr_subasta['path']."', estado_compensacion = 41 
                                    where id = ".$p_arr_subasta['subasta_id']);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj2 = $conn->next_record();
        // liquidacion de la subasta
        $idqry = $csub->query("select SUB_LIQUIDA_SUBASTA(".$p_arr_subasta['subasta_id'].") as resultado");
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();
        $resultado = $obj->resultado;

        $csub->close();
        $conn->close();

        return $resultado;
    }
    function get_subastas_inversion_xusuario($usuarioid,$tipo, $empresaid){
        $csub = new db_param_trans;
        $csub->connect();
        $arr_inversiones = array();
        
        $idqry = $csub->query("select * from SUB_SUBASTAS_XINVERSIONISTA(".$usuarioid.",'".$tipo."',".$empresaid.")");
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
                                    'e_propuesta_id' => $obj->r_e_propuesta_id, 'e_propuesta' => $obj->r_e_propuesta,
                                    'fondo_disponible' => $obj->r_fondo_disponible, 'riesgo_id' => $obj->r_riesgo_id,
                                    'riesgo_desc' => $obj->r_riesgo_desc, 'riesgo_nombre' => $obj->r_riesgo_nombre,
                                    'calificacion' => $obj->r_calificacion, 'propuesta_id' => $obj->r_propuesta_id
                                );
            $obj = $csub->next_record();
        }

        $csub->close();
        return $arr_inversiones;
    }
    function get_inversionistas_xsubasta($p_subasta_id){
        $csub = new db_param_trans;
        $csub->connect();
        $arr_inversionistas = array();

        $arr_subasta = $this->get_subasta($p_subasta_id);
        $idqry = $csub->query("select id, monto, tia, usuarioid from propuestas where estado > 0 and grupo_final = ".$arr_subasta['grupowinid']." and subastaid = ".$p_subasta_id);
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();

        for($i = 0; $i < $csub->nrows(); $i ++){
            $arr_inversionistas[$i] = array('propuesta_id'=>$obj->id, 'monto'=>$obj->monto, 'tia'=>$obj->tia, 'inversionista_id'=>$obj->usuarioid);
            $obj = $csub->next_record();
        }

        $csub->close();
        return $arr_inversionistas;
    }
    function termina_subasta ($p_subasta_id){
        $csub = new db_param_trans;
        $csub->connect();

        $idqry = $csub->query("select sub_procesa_propuestas(".$p_subasta_id.") as resultado");
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();

        $csub->close();
        return 1;
    }

    function anular_subasta($p_subasta_id){
        $conector = new db_param_trans;     $conector->connect();
        
        $v_qry = "select sub_anula_subasta(".$p_subasta_id.") as rpta_anulacion";
        $idqry = $conector->query($v_qry);

        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        $conector->close();

        return $obj->rpta_anulacion;
    }

    function get_subastas_disponibles_user($rows,$rowini,$tipo,$p_user){
        $conector = new db_param_trans;
        $conector->connect();
                
        if ($tipo == 'select'){
            $arrsubastas = array();
            $idqry = $conector->query("select * from sub_subastas_dispo_user(".$rowini.",".$rows.",".$p_user.")");

            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
        
            for($i = 0; $i < $conector->nrows(); $i ++){
                $arrsubastas[$i] = array('subastaid'=>$obj->subastaid,'estadoid'=>$obj->subestadoid,
                                    'facturaid' => $obj->facturaid, 'cliente' => $obj->faccliente,
                                    'total' => $obj->factotal, 'monedaid' => $obj->facmonedaid,
                                    'moneda' => $obj->facmoneda, 'fvencimiento' => $obj->facfvencimiento,
                                    'riesgoid' => $obj->riesgoid, 'riesgo' => $obj->riesgo,
                                    'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                    'qpropuestas' => $obj->qpropuestas, 'montofin' => $obj->submontofin,
                                    'color_fuente' => $obj->color_fuente
                                );
                $obj = $conector->next_record();
            }
        } else{
            $idqry = $conector->query("select sub_subastas_dispo_user_count(".$p_user.") as contador");

            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
            $arrsubastas = $obj->contador;
        }

        $conector->close();
        return $arrsubastas;
    }
    function get_subastas_disponibles_riesgo_sector($rows,$rowini,$tipo,$p_sector, $p_riesgo){
        $conector = new db_param_trans;
        $conector->connect();
                
        if ($tipo == 'select'){
            $arrsubastas = array();
            $idqry = $conector->query("select * from sub_subastas_dispo_riesgosector(".$rowini.",".$rows.",".$p_riesgo.",".$p_sector.")");

            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
        
            for($i = 0; $i < $conector->nrows(); $i ++){
                $arrsubastas[$i] = array('subastaid'=>$obj->subastaid,'estadoid'=>$obj->subestadoid,
                                    'facturaid' => $obj->facturaid, 'cliente' => $obj->faccliente,
                                    'total' => $obj->factotal, 'monedaid' => $obj->facmonedaid,
                                    'moneda' => $obj->facmoneda, 'fvencimiento' => $obj->facfvencimiento,
                                    'riesgoid' => $obj->riesgoid, 'riesgo' => $obj->riesgo,
                                    'calificacion' => $obj->calificacion, 'color' => $obj->color,
                                    'qpropuestas' => $obj->qpropuestas, 'montofin' => $obj->submontofin
                                );
                $obj = $conector->next_record();
            }
        } else{
            $idqry = $conector->query("select sub_subastas_dispo_riesgosector_count(".$p_riesgo.",".$p_sector.") as contador");

            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
            $arrsubastas = $obj->contador;
        }

        $conector->close();
        return $arrsubastas;
    }
    function subastas_indicadores($p_tipo){
        $conector = new db_param_trans;
        $conector2 = new db_param_trans;
        $conector->connect();
        $conector2->connect();
        date_default_timezone_set($_SESSION['user']['zona_horaria']);

        if ($p_tipo == 'NOACTIVAS'){
            $idqry = $conector->query("select count(1) as contador from subasta where estado = 23");
            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
            $arr_result['count'] = $obj->contador;
            $idqry = $conector2->query("SELECT max(current_date - subasta.fcreacion) as maximo, parametros.valor_num from subasta, parametros where parametros.id = 14 and subasta.estado = 23 group by parametros.valor_num");
            if (!$idqry) echo pg_last_error($conector2->Link_ID);
            $obj2 = $conector2->next_record();
            $arr_result['maximo'] = $obj2->maximo;
            $arr_result['parametro'] = $obj2->valor_num;
        } elseif ($p_tipo == 'ACTIVAS'){
            $idqry = $conector->query("select count(1) as contador from subasta where estado = 24");
            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
            $arr_result['count'] = $obj->contador;
            $idqry = $conector2->query("SELECT max(current_date - subasta.ffin) as maximo, parametros.valor_num from subasta, parametros where parametros.id = 3 and subasta.estado = 24 group by parametros.valor_num");
            if (!$idqry) echo pg_last_error($conector2->Link_ID);
            $obj2 = $conector2->next_record();
            $arr_result['maximo'] = $obj2->maximo;
            $arr_result['parametro'] = $obj2->valor_num;
        } elseif ($p_tipo == 'COMPENSACION'){
            $idqry = $conector->query("select count(1) as contador from subasta where estado = 25");
            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
            $arr_result['count'] = $obj->contador;
            $idqry = $conector2->query("SELECT max(current_date - subasta.ffin) as maximo, parametros.valor_num from subasta, parametros where parametros.id = 16 and subasta.estado = 25 group by parametros.valor_num");
            if (!$idqry) echo pg_last_error($conector2->Link_ID);
            $obj2 = $conector2->next_record();
            $arr_result['maximo'] = $obj2->maximo;
            $arr_result['parametro'] = $obj2->valor_num;
        } elseif ($p_tipo == 'COMPENSADA'){
            $idqry = $conector->query("select * from KPI_SUB_COMPENSADA()");
            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();

            $arr_result = array('count' => $obj->r_cantidad_compensada, 'count_alert' => $obj->r_cantidad_alerta);
        } elseif ($p_tipo == 'ENCOMPENSACION'){
            $idqry = $conector->query("select * from KPI_SUB_ENCOMPENSACION()");
            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();

            $arr_result = array('count' => $obj->r_cantidad_encompensacion, 'count_alert' => $obj->r_cantidad_alerta, 'monto_alerta' => $obj->r_monto_alerta);
        }

        $conector->close();
        $conector2->close();
        return $arr_result;
    }
    function ordena_transferencia_financiamiento_autm($p_subasta_id, $p_factura_id, $p_path){
        $conector = new db_param_trans;
        $conector->connect();
        // actualiza el estado de la subasta
        $v_fhoy = date('Y-m-d');
        $v_hhoy = time();
        $v_hhoy_format = date("H:i:s", $v_hhoy);
        $idqry = $conector->query("update subasta set estado_compensacion = 41, f_envio_contrato = '".$v_fhoy."', h_envio_contrato = '".$v_hhoy_format."', transferenciapath = '".$p_path."' 
                                    where id = ".$p_subasta_id);
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        // genera el archivo para el banco y lo transfiere al FTP        
        

        $conector->close();
        return 1;
    }
    function ordena_transferencia_automatica($p_arreglo){
        $conector = new db_param_trans; $conector->connect();
        //###### realiza la orden de transferencia al banco
        //######@@@@@@ SE GENERA EL ARCHIVO DE TRANSFERENCIA AL BANCO
        //######@@@@@@ SE DEPOSITA EL ARCHIVO EN EL FTP DEL BANCO
        //###### verifica el tipo de transferencia que se esta realizando
        if ($p_arreglo['tipo'] == 'FINANCIA'){
            //###### registra los datos de la transferencia de fondos
            //$idqry = $conector->query("select SUB_REGISTRA_TRANSFONDOS(".$p_arreglo['subasta_id'].",'".$v_path_transferencia."')"); //aqui se incluye la liquidacion de la subasta
            //if (!$idqry) echo pg_last_error($conector->Link_ID);
            //$obj = $conector->next_record();
        }

        $conector->close();
        return 1;
    }
    function verifica_transferencia_automatica($parr_datos){
        $conector = new db_param_trans;
        $conector->connect();
        //@@@@@@@@@@@@@ SE VERIFICA EL FTP O API PARA VERIFICAR SI LA TRANSFERENCIA SE REALIZO OK
        if ($v_rpta == 1){
            $idqry = $conector->query("select SUB_REGISTRA_TRANSFONDOS(".$p_arreglo['subasta_id'].",'".$v_path_transferencia."')"); //aqui se incluye la liquidacion de la subasta
            if (!$idqry) echo pg_last_error($conector->Link_ID);
            $obj = $conector->next_record();
        }

        return 1;
    }
    function envia_contrato ($p_subasta_id, $p_referencia, $parr_datos){
        $csub = new db_param_trans;
        $obj_mailing = new mail_util;

        $csub->connect();

        $idqry = $csub->query("select SUB_ENVIA_CONTRATO(".$p_subasta_id.",'".$p_referencia."') as resultado");
        if (!$idqry) echo pg_last_error($csub->Link_ID);
        $obj = $csub->next_record();
        //######## ENVIO CORREO AL EMISOR PARA LA FIRMA
        $varr_subasta = $this->get_subasta($p_subasta_id);
        $arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE',
                                'mail_destino' => $parr_datos['emisor_correo'],
                                'subject' => 'CONTRATO DE VENTA DE SU FACTURA',
                                'body' => 'Esta todo listo para transferirle el adelanto de su factura, para recibir el adelanto debe firmar electronicamente el contrato en el siguiente link:<br><br>
                                            Empresa: '.$parr_datos['emisor_nombre'].'<br>
                                            DOC: '.$parr_datos['emisor_identificacion'].'<br>
                                            Cliente: '.$parr_datos['cliente'].'<br>
                                            Factura: '.$varr_subasta['facnumero'].'<br>
                                            ID Operaci&oacute;n: '.$varr_subasta['facturaid'].'<br>
                                            Monto Factura: '.number_format($varr_subasta['total'],2,'.',',').' '.$varr_subasta['moneda'].'<br>
                                            Monto Adelanto: '.number_format($varr_subasta['montofin'],2,'.',',').' '.$varr_subasta['moneda'].'<br>
                                            Posible Remanente: '.number_format($varr_subasta['monto_remanente'],2,'.',',').' '.$varr_subasta['moneda'].'<br><br>
                                            Lo siguiente que debe hacer es revisar y firmar el contrato de cesi&oacute;n mediante el siguiente link donde no debe ingresar 
                                            ninguna informaci&oacute;n confidencial solo firmar biometricamente, le recomendamos leer el documento antes de firmar en el siguiente link.<br>
                                            Todo seguimiento que desee hacer a la operación debe considerar el ID OPERACION.<br>
                                            Link de contrato: <a href="'.$p_referencia.'">CONTRATO DE CESION</a><br><br>
                                            FACTUREATE');
        
        $obj_mailing->enviar_correo($arr_mail_user);

        $csub->close();
        return 1;
    }
    function recibe_contrato_firmado($p_subasta_id, $p_path){
        $conector = new db_param_trans;
        $conector->connect();
        
        $idqry = $conector->query("select SUB_REGISTRA_CONTRATO(".$p_subasta_id.",'".$p_path."') as resultado");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        //###### transferencia de fondos al vendedor ######
        //$p_arreglo = array("tipo"=>"FINANCIA", "subasta_id"=>$p_subasta_id);
        //$this->ordena_transferencia_automatica($p_arreglo);

        $conector->close();
        return 1;
    }
    function registra_endoso($p_subasta_id, $p_path){
        $conector = new db_param_trans;
        $conector->connect();
        
        $idqry = $conector->query("select SUB_REGISTRA_ENDOSO(".$p_subasta_id.",'".$p_path."') as resultado");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        //###### transferencia de fondos al vendedor ######
        //$p_arreglo = array("tipo"=>"FINANCIA", "subasta_id"=>$p_subasta_id);
        //$this->ordena_transferencia_automatica($p_arreglo);

        $conector->close();
        return 1;
    }

    function envia_contrado_endoso_emisor($parr_datos){
        $v_link = '';
        //#####@@@@ CONSUMIR API DE PROVEEDOR PARA GENERAR CONTRATO, SE DEVUELVE v_link EL LINK DEL CONTRATO QUE SE LE ENVIA AL EMISOR
        $this->envia_contrato($parr_datos['subasta_id'], $v_link);
        
        return 1;
    }
    function extender_subasta($p_subasta_id, $p_dias){
        //######### EXTENSION DEL TIEMPO DE LA SUBASTA
        $conector = new db_param_trans; $conector->connect();
        $idqry = $conector->query("update subasta set ffin = ffin + CAST('".$p_dias." days' AS INTERVAL) where id = ".$p_subasta_id);
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        $conector->close();
        return 1;
    }

    function monto_propuestas_subasta($p_user_id, $p_empresa_id, $p_moneda_id){
        $conector = new db_param_trans; $conector->connect();

        if ($_SESSION['user']['perfiltipo'] == 11 || $_SESSION['user']['perfiltipo'] == 10)     // EXTERNO A FACTUREATE
            if ($_SESSION['user']['empresaid'] > 0)
                $idqry = $conector->query("select COALESCE(sum(propuestas.monto),0) as total from propuestas, subasta, factura 
                                        where propuestas.empresaid = ".$_SESSION['user']['empresaid']." and propuestas.estado = 1 and subasta.id = propuestas.subastaid and 
                                            subasta.estado = 24 and factura.id = subasta.facturaid and factura.monedaid = ".$p_moneda_id);
            else
                $idqry = $conector->query("select COALESCE(sum(propuestas.monto),0) as total from propuestas, subasta, factura where propuestas.usuarioid = ".$_SESSION['user']['usuarioid']." and 
                                        propuestas.estado = 1 and subasta.id = propuestas.subastaid and subasta.estado = 24 and 
                                        factura.id = subasta.facturaid and factura.monedaid = ".$p_moneda_id);
        else
            if ($p_empresa_id > 0)
                $idqry = $conector->query("select COALESCE(sum(propuestas.monto),0) as total from propuestas, subasta 
                                            where propuestas.empresaid = ".$p_empresa_id." and propuestas.estado = 1 and subasta.id = propuestas.subastaid and subasta.estado = 24 and 
                                            factura.id = subasta.facturaid and factura.monedaid = ".$p_moneda_id);
            else
                $idqry = $conector->query("select COALESCE(sum(propuestas.monto),0) as total from propuestas, subasta where propuestas.usuarioid = ".$p_user_id." and 
                                            propuestas.estado = 1 and subasta.id = propuestas.subastaid and subasta.estado = 24 and 
                                            factura.id = subasta.facturaid and factura.monedaid = ".$p_moneda_id);

        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        $v_retorno = $obj->total;

        $conector->close();
        return $v_retorno;
    }

    function get_confirmaciones($p_empresa_id){
        $conector = new db_param_trans; $conector->connect();
        $conector2 = new db_param; $conector2->connect();

        $idqry = $conector2->query("select id from usuarios where empresaid = ".$p_empresa_id." and estado = 1");
        if (!$idqry) echo pg_last_error($conector2->Link_ID);
        $obj2 = $conector2->next_record();
        $v_usuarios = '(';

        for($i = 0; $i < $conector2->nrows(); $i ++){
            if ($i == 0) $v_usuarios .= $obj2->id;
            else $v_usuarios .= ','.$obj2->id;

            $obj2 = $conector2->next_record();
        }

        $v_usuarios .= ')';

        $idqry = $conector->query("select confirmacion_subasta.id, confirmacion_subasta.subastaid, confirmacion_subasta.facturaid, confirmacion_subasta.fregistro,
                                    confirmacion_subasta.hregistro, confirmacion_subasta.monto, factura.numero, factura.monedaid, empresa.nombre as cliente_nombre,
                                    tipos.nombre as moneda_nombre, tipos.dato1 as moneda_simbolo, factura.fvencimiento, factura.total 
                                from confirmacion_subasta, factura, empresa, tipos 
                                where confirmacion_subasta.estado = 32 and confirmacion_subasta.usuarioid in ".$v_usuarios." and 
                                    confirmacion_subasta.tipo = 26 and factura.id = confirmacion_subasta.facturaid and empresa.id = factura.clienteid and tipos.id = factura.monedaid 
                                order by confirmacion_subasta.fregistro");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
        $varr_retorno = array();

        for($i = 0; $i < $conector->nrows(); $i ++){
            $varr_retorno[$i] = array('confirmacion_id' => $obj->id, 'subasta_id' => $obj->subastaid, 'factura_id' => $obj->facturaid, 'fecha_registro' => $obj->fregistro,
                                        'hora_registro' => $obj->hregistro, 'monto' => $obj->monto, 'factura_numero' => $obj->numero, 'moneda_id' => $obj->monedaid, 
                                        'cliente_nombre' => $obj->cliente_nombre, 'moneda_nombre' => $obj->moneda_nombre, 'moneda_simbolo' => $obj->moneda_simbolo, 
                                        'fecha_vencimiento' => $obj->fvencimiento, 'monto_factura' => $obj->total);
            $obj = $conector->next_record();
        }

        $conector->close(); $conector2->close();
        return $varr_retorno;
    }

    function confirmacion_emisor($p_confirma_id){
        $conector = new db_param_trans; $conector->connect();

        $idqry = $conector->query("select SUB_PROCESA_CONFIRMACION_EMISOR(".$p_confirma_id.") as resultado");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
    }

    function rechaza_finan_emisor($p_confirma_id){
        $conector = new db_param_trans; $conector->connect();

        $idqry = $conector->query("update confirmacion_subasta set estado = 0 where id = ".$p_confirma_id);
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();
    }

    function get_inversores_win_subasta($p_subasta_id){
        $conector = new db_param_trans; $conector->connect();

        $idqry = $conector->query("select * from SUB_GET_INVERSORES_WIN(".$p_subasta_id.")");
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        $varr_result = array();

        for($i = 0; $i < $conector->nrows(); $i ++){
            $varr_result[$i] = array('inversor_id' => $obj->r_inversor_id,  'inversor_nombre' => $obj->r_inversor_nombre,
                                    'monto' => $obj->r_monto,               'porc_representacion' => $obj->r_representacion,
                                    'tia_final' => $obj->r_tia_final);

            $obj = $conector->next_record();
        }

        $conector->close();
        return $varr_result;
    }

    function get_subastas_inversor($p_tipo, $rows,$rowini, $p_filtros, $p_order, $p_inversor_id){
        $conector = new db_param_trans; $conector->connect();
        $consulta = new db_param_trans; $consulta->connect();
        $conn_risk = new db_param_trans; $conn_risk->connect();
        $conn_sector = new db_param_trans; $conn_sector->connect();
        $conn_time = new db_param_trans; $conn_time->connect();

        date_default_timezone_set($_SESSION['user']['zona_horaria']);
        $v_fhoy = date('Y-m-d');

        $v_sql = "select count(1) as contador from perfil_inversion where id_usuario = ".$p_inversor_id." and estado_id = 1";

        $idqry = $conector->query($v_sql);
        if (!$idqry) echo pg_last_error($conector->Link_ID);
        $obj = $conector->next_record();

        if ($obj->contador > 0){
            //---- el inversor cuenta con perfil
            //------ analisis de variable riesgos
            $v_sql_risk = " select  variable_valor_id from perfil_inversion_variable 
                            where   estado_id = 1 and variable_id = 103 and perfil_inv_id = ".$p_inversor_id;

            $idqry = $conn_risk->query($v_sql_risk);
            if (!$idqry) echo pg_last_error($conn_risk->Link_ID);

            $v_sql_risk = "";

            if ($conn_risk->nrows() > 0){
                //---- tiene variables de riesgo
                $obj_risk = $conn_risk->next_record();

                for($i = 0; $i < $conn_risk->nrows(); $i ++){
                    if ($v_sql_risk != '') $v_sql_risk .= ',';

                    $v_sql_risk .= $obj_risk->variable_valor_id;
                    $obj_risk = $conn_risk->next_record();
                }

                $v_sql_risk = ' and factura.riesgofacturaid in ('.$v_sql_risk.') ';
            }

            //------ analisis de variable de sector
            $v_sql_sector = "   select  variable_valor_id from perfil_inversion_variable 
                                where   estado_id = 1 and variable_id = 104 and perfil_inv_id = ".$p_inversor_id;

            $idqry = $conn_sector->query($v_sql_sector);
            if (!$idqry) echo pg_last_error($conn_sector->Link_ID);

            $v_sql_sector = "";

            if ($conn_sector->nrows() > 0){
                //---- tiene variables de sector
                $obj_sector = $conn_sector->next_record();

                for($i = 0; $i < $conn_sector->nrows(); $i ++){
                    if ($v_sql_sector != '') $v_sql_sector .= ',';

                    $v_sql_sector .= $obj_sector->variable_valor_id;
                    $obj_sector = $conn_sector->next_record();
                }

                $v_sql_sector = ' and empresa.sectoreconomicoid in ('.$v_sql_sector.') ';
            }

            //------ analisis de variable tiempo
            $v_sql_time = "     select  variable_valor_id from perfil_inversion_variable 
                                where   estado_id = 1 and variable_id = 105 and perfil_inv_id = ".$p_inversor_id." 
                                order by variable_valor_id";

            $idqry = $conn_time->query($v_sql_time);
            if (!$idqry) echo pg_last_error($conn_time->Link_ID);

            $v_sql_time = '';

            if ($conn_time->nrows() > 0){
                //---- tiene variable de tiempo
                $obj_time = $conn_time->next_record();
                $v_minimo = $obj_time->variable_valor_id;

                for($i = 0; $i < $conn_time->nrows(); $i ++){
                    $v_maximo = $obj_time->variable_valor_id;
                    $obj_time = $conn_time->next_record();
                }

                if ($v_minimo == 106) $v_valor_minimo = 30;
                elseif ($v_minimo == 107) $v_valor_minimo = 60;
                elseif ($v_minimo == 108) $v_valor_minimo = 80;
                elseif ($v_minimo == 109) $v_valor_minimo = 100;
                elseif ($v_minimo == 110) $v_valor_minimo = 120;

                if ($v_maximo == 106) $v_valor_maximo = 60;
                elseif ($v_maximo == 107) $v_valor_maximo = 80;
                elseif ($v_maximo == 108) $v_valor_maximo = 100;
                elseif ($v_maximo == 109) $v_valor_maximo = 120;
                elseif ($v_maximo == 110) $v_valor_maximo = 10000;

                $v_sql_time = " and (factura.fvencimiento - '".$v_fhoy."') >= ".$v_valor_minimo." and (factura.fvencimiento - '".$v_fhoy."') < ".$v_valor_maximo;
            }

            if ($p_tipo == 'SELECT'){
                $v_sql = "  select  subasta.id, subasta.estado, subasta.facturaid, empresa.nombre as cliente,factura.total, 
                                    factura.monedaid, tipos.nombre as monedanom, factura.fvencimiento,tipo_riesgo_empresa.id as riesgoid, 
                                    tipo_riesgo_empresa.nombre as riesgonom, tipo_riesgo_empresa.calificacion, tipo_riesgo_empresa.color,
                                    tipo_riesgo_empresa.color_fuente, factura.porciento_financia, 
                                    SUB_PROPUESTAS_INVERSOR_SUBASTA(subasta.id,".$p_inversor_id.") as qpropuestas,
                                    (factura.porciento_financia * factura.total) as monto_a_financiar 
                            from    subasta, factura, empresa, tipos, tipo_riesgo_empresa, perfil_inversion 
                            where   subasta.estado in (23,24) and factura.id = subasta.facturaid and empresa.id = factura.clienteid and 
                                    tipos.id = factura.monedaid and tipo_riesgo_empresa.id = factura.riesgofacturaid and perfil_inversion.estado_id = 1 and 
                                    perfil_inversion.id_usuario = ".$p_inversor_id." and subasta.monto_financia >= perfil_inversion.monto_minimo_inst and 
                                    ((perfil_inversion.monto_maximo_inst > 0 and subasta.monto_financia <= perfil_inversion.monto_maximo_inst) or 
                                    perfil_inversion.monto_maximo_inst = 0) ";

                if ($v_sql_risk != '') $v_sql .= $v_sql_risk;
                if ($v_sql_sector != '') $v_sql .= $v_sql_sector;
                if ($v_sql_time != '') $v_sql .= $v_sql_time;
                if ($p_filtros != '') $v_sql .= " and ".$p_filtros;

                $v_sql .= " order by ".$p_order."
                            limit ".$rows." offset ".$rowini;
            } else {
                $v_sql = "  select  count(1) as contador 
                            from    subasta, factura, empresa, tipos, tipo_riesgo_empresa, perfil_inversion 
                            where   subasta.estado in (23,24) and factura.id = subasta.facturaid and empresa.id = factura.clienteid and 
                                    tipos.id = factura.monedaid and tipo_riesgo_empresa.id = factura.riesgofacturaid and perfil_inversion.estado_id = 1 and 
                                    perfil_inversion.id_usuario = ".$p_inversor_id." and subasta.monto_financia >= perfil_inversion.monto_minimo_inst and 
                                    ((perfil_inversion.monto_maximo_inst > 0 and subasta.monto_financia <= perfil_inversion.monto_maximo_inst) or 
                                    perfil_inversion.monto_maximo_inst = 0) ";

                if ($v_sql_risk != '') $v_sql .= $v_sql_risk;
                if ($v_sql_sector != '') $v_sql .= $v_sql_sector;
                if ($v_sql_time != '') $v_sql .= $v_sql_time;
                if ($p_filtros != '') $v_sql .= " and ".$p_filtros;
            }
        } else {
            //---- el inversor no cuenta con perfil
            if ($p_tipo == 'SELECT'){
                $v_sql = "  select  subasta.id, subasta.estado, subasta.facturaid, empresa.nombre as cliente,factura.total, 
                                    factura.monedaid, tipos.nombre as monedanom, factura.fvencimiento,tipo_riesgo_empresa.id as riesgoid, 
                                    tipo_riesgo_empresa.nombre as riesgonom, tipo_riesgo_empresa.calificacion, tipo_riesgo_empresa.color,
                                    tipo_riesgo_empresa.color_fuente, factura.porciento_financia,
                                    SUB_PROPUESTAS_INVERSOR_SUBASTA(subasta.id,".$p_inversor_id.") as qpropuestas,
                                    (factura.porciento_financia * factura.total) as monto_a_financiar 
                            from    subasta, factura, empresa, tipos, tipo_riesgo_empresa 
                            where   subasta.estado in (23,24) and factura.id = subasta.facturaid and empresa.id = factura.clienteid and 
                                    tipos.id = factura.monedaid and tipo_riesgo_empresa.id = factura.riesgofacturaid ";

                if ($p_filtros != '') $v_sql .= " and ".$p_filtros;

                $v_sql .= "  order by ".$p_order." 
                            limit   ".$rows." offset ".$rowini;
            } else {
                $v_sql = "  select  count(1) as contador  
                            from    subasta, factura, empresa, tipos, tipo_riesgo_empresa 
                            where   subasta.estado in (23,24) and factura.id = subasta.facturaid and empresa.id = factura.clienteid and 
                                    tipos.id = factura.monedaid and tipo_riesgo_empresa.id = factura.riesgofacturaid ";

                if ($p_filtros != '') $v_sql .= " and ".$p_filtros;
            }
        }

        $idqry = $consulta->query($v_sql);
        if (!$idqry) echo pg_last_error($consulta->Link_ID);
        $obj = $consulta->next_record();

        if ($p_tipo == 'SELECT'){
            $varr_subastas = array();

            for($i = 0; $i < $consulta->nrows(); $i ++){
                $varr_subastas[$i] = array( 'subasta_id'=>$obj->id,     'estado_id'=>$obj->estado,              'factura_id' => $obj->facturaid, 
                                            'cliente' => $obj->cliente, 'total' => $obj->total,                 'moneda_id' => $obj->monedaid,
                                            'moneda' => $obj->monedanom,'f_vencimiento' => $obj->fvencimiento,  'riesgo_id' => $obj->riesgoid, 
                                            'riesgo' => $obj->riesgonom,'calificacion' => $obj->calificacion,   'color' => $obj->color,
                                            'qpropuestas' => $obj->qpropuestas,                                 'monto_fin' => $obj->monto_a_financiar,
                                            'color_fuente' => $obj->color_fuente
                                        );
                $obj = $consulta->next_record();
            }
        } else {
            $varr_subastas = $obj->contador;
        }
        
        $conector->close(); $consulta->close(); $conn_risk->close(); $conn_sector->close(); $conn_time->close();

        return $varr_subastas;
    }

    function get_subasta_xfactura_inversor($p_factura_id, $p_inversor, $p_tipoinv){
        $conn = new db_param_trans; $conn->connect();
        $conn_count = new db_param_trans; $conn_count->connect();
        $conn_prop = new db_param_trans; $conn_prop->connect();

        $v_sql = "select id, estado from subasta where facturaid = ".$p_factura_id;

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array('subasta_id' => $obj->id, 'estado_subasta' => $obj->estado);

        if ($obj->estado == 24){
            if ($p_tipoinv == 85){
                $v_sql = "select count(1) as contador from propuestas where subastaid = ".$obj->id." and usuarioid = ".$p_inversor." and estado > 0";
            } else {
                $v_sql = "select count(1) as contador from propuestas where subastaid = ".$obj->id." and empresaid = ".$p_inversor." and estado > 0";
            }

            $idqry = $conn_count->query($v_sql);
            if (!$idqry) echo pg_last_error($conn_count->Link_ID);
            $obj = $conn_count->next_record();

            if ($obj->contador > 0) {
                if ($p_tipoinv == 85){
                    $v_sql = "select id, estado from propuestas where subastaid = ".$obj->id." and usuarioid = ".$p_inversor." and estado > 0";
                } else {
                    $v_sql = "select id, estado from propuestas where subastaid = ".$obj->id." and empresaid = ".$p_inversor." and estado > 0";
                }

                $idqry = $conn_prop->query($v_sql);
                if (!$idqry) echo pg_last_error($conn_prop->Link_ID);
                $obj = $conn_prop->next_record();

                $varr_result['propuesta_id'] = $obj->id;
                $varr_result['estado_propuesta'] = $obj->estado;
            } else $varr_result['propuesta_id'] = 0;
        } else $varr_result['propuesta_id'] = 0;

        $conn->close(); $conn_count->close(); $conn_prop->close();

        return $varr_result;
    }
}
?>