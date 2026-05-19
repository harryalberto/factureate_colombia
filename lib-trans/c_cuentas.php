<?

class cuentas{

    function get_saldos($usuarioid, $empresaid){
        $conn = new db_param_trans; $conn->connect();

        $arr_cuentas = array();

        if ($empresaid > 0) $v_inversor_id = $empresaid;
        else $v_inversor_id = $usuarioid;

        $idqry = $conn->query("select * from CTA_GET_SALDOS(".$v_inversor_id.")");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();
        
        for($i = 0; $i < $conn->nrows(); $i ++){
            $arr_cuentas[$i] = array('cuenta_id'=>$obj->r_cuenta_id,'moneda_id'=>$obj->r_moneda_id,
                                    'moneda' => $obj->r_moneda, 'saldo_comprometido' => $obj->r_saldo_comprometido,
                                    'saldo_contable' => $obj->r_saldo_contable, 'saldo_disponible' => $obj->r_saldo_disponible,
                                    'saldo_invertido' => $obj->r_saldo_invertido, 'saldo_transito' => $obj->r_saldo_transito
                                );

            $obj = $conn->next_record();
        }

        $conn->close();

        return $arr_cuentas;
    }

    function get_movimientos_cuenta($cuentaid, $numrows, $rowini){

        $conn = new db_param_trans;

        $conn->connect();

        $arr_movimientos = array();

        

        $idqry = $conn->query("select * from CTA_GET_MOVIMIENTOS(".$cuentaid.", ".$rowini.", ".$numrows.")");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        

        for($i = 0; $i < $conn->nrows(); $i ++){

            $arr_movimientos[$i] = array('movimiento_id'=>$obj->r_movimiento_id,'f_movimiento'=>$obj->r_f_movimiento,

                                    'monto' => $obj->r_monto, 'tipo_movimiento' => $obj->r_tipo_movimiento,

                                    'movimiento' => $obj->r_movimiento, 'subasta_id' => $obj->r_subasta_id,

                                    'propuesta_id' => $obj->r_propuesta_id

                                );

            $obj = $conn->next_record();

        }



        $conn->close();

        return $arr_movimientos;

    }

    function get_saldos_cuenta($cuenta_id){

        $conn = new db_param_trans;

        $conn->connect();

        

        $idqry = $conn->query("select * from CTA_GET_SALDOS_CUENTA(".$cuenta_id.")");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        

        $arr_saldos = array('moneda_id'=>$obj->r_moneda_id,

                                    'moneda' => $obj->r_moneda, 'saldo_comprometido' => $obj->r_saldo_comprometido,

                                    'saldo_contable' => $obj->r_saldo_contable, 'saldo_disponible' => $obj->r_saldo_disponible,

                                    'saldo_invertido' => $obj->r_saldo_invertido, 'saldo_transito' => $obj->r_monto_transito

                            );



        $conn->close();

        return $arr_saldos;

    }

    function registra_saldo_transito($cuenta_id, $monto, $path, $p_banco_id){

        $conn = new db_param_trans;

        $conn->connect();

        

        $idqry = $conn->query("select CTA_REGISTRA_SALDO_TRANSITO(".$p_banco_id.",".$cuenta_id.",".$monto.",'".$path."',".$_SESSION['user']['usuarioid'].") as resultado");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

                

        $conn->close();

    }

    function procesa_saldo_transito($p_cuentaid){

        $conn = new db_param_trans;

        $conn->connect();

        

        $idqry = $conn->query("select CTA_PROCESA_SALDO_TRANSITO(".$p_cuentaid.") as resultado");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

                

        $conn->close();

    }

    function get_saldo_detalle ($p_usuario_id, $p_empresa_id, $p_moneda_id, $p_tipo){

        $conn = new db_param_trans;

        $conn->connect();



        $idqry = $conn->query("select * from CTA_GET_SALDOS_DETALLE(".$p_usuario_id.",".$p_empresa_id.",".$p_moneda_id.",'".$p_tipo."')");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        for($i = 0; $i < $conn->nrows(); $i ++){

            $arr_saldo_detalle[$i] = array('propuesta_id'=>$obj->r_propuesta_id,'subasta_id'=>$obj->r_subasta_id,

                                    'monto' => $obj->r_monto, 'fecha' => $obj->r_fecha,

                                    'instrumento_nro' => $obj->r_instrumento_nro, 'pagador_nombre' => $obj->r_pagador_nombre, 'factura_id'=>$obj->r_factura_id

                                    );

            $obj = $conn->next_record();

        }



        $conn->close();

        return $arr_saldo_detalle;

    }

    function get_saldos_transito(){

        $conn = new db_param_trans;

        $conn->connect();



        $idqry = $conn->query("select * from CTA_GET_SALDOS_TRANSITO(0)");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        for($i = 0; $i < $conn->nrows(); $i ++){

            $arr_transito[$i] = array('saldo_id'=>$obj->r_saldo_id,'cuenta_id'=>$obj->r_cuenta_id,

                                    'monto' => $obj->r_saldo, 'moneda_id' => $obj->r_moneda_id,

                                    'f_creacion' => $obj->r_f_creacion, 'comp_path' => $obj->r_comp_path,

                                    'moneda' => $obj->r_moneda, 'inversionista_id' => $obj->r_inversionista_id,

                                    'empresa_id' => $obj->r_empresa_id, 'nombre_inversor'=>$obj->r_nombre_inversor, 'nombre_banco'=>$obj->r_nombre_banco,

                                    'moneda_simbolo'=>$obj->r_moneda_simbolo);

            $obj = $conn->next_record();

        }



        $conn->close();

        return $arr_transito;

    }

    function procesa_transito_verificado($p_verificados){
        $conn = new db_param_trans; $conn->connect();

        for ($i=0; $i<count($p_verificados); $i++){
            $idqry = $conn->query("select CTA_PROCESA_TRANSITO_VERIFICADO(".$p_verificados[$i]['saldo_id'].",".$_SESSION['user']['usuarioid'].",'".$p_verificados[$i]['operacion']."','".$p_verificados[$i]['remitente']."') as resultado");

            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
        }

        $conn->close();
    }

    function agregar_saldo_manual($parr_datos){

        $conn = new db_param_trans; $conn->connect();

        //@@@@ SI RPTA ES 1 NO PASO NADA SOLO SE INCREMENTO EL SALDO // SI ES 2 LA SUBASTA QUEDA COMPENSANDA Y HAY QUE PROCESAR EN EL PROCESO QUE LA LLAMO

        $idqry = $conn->query("select CTA_AGREGAR_SALDO_INVERSOR(".$parr_datos['cuenta_id'].",".$parr_datos['saldo'].",".$_SESSION['user']['usuarioid'].",".$parr_datos['transito_id'].",'".$parr_datos['constancia']."') as resultado");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $v_resultado = $obj->resultado;



        $conn->close();



        return $v_resultado;

    }



    function get_saldos_ini($usuarioid, $empresaid, $p_mes, $p_anho){

        $conn = new db_param_trans;

        $conn->connect();

        $arr_cuentas = array();

        

        $idqry = $conn->query("select * from CTA_GET_SALDOS_INICIAL_FECHA(".$usuarioid.",".$empresaid.",".$p_mes.",".$p_anho.")");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        

        for($i = 0; $i < $conn->nrows(); $i ++){

            $arr_cuentas[$i] = array('cuenta_id'=>$obj->r_cuenta_id,'moneda_id'=>$obj->r_moneda_id,

                                    'moneda' => $obj->r_moneda, 'saldo_comprometido' => $obj->r_saldo_comprometido,

                                    'saldo_contable' => $obj->r_saldo_contable, 'saldo_disponible' => $obj->r_saldo_disponible,

                                    'saldo_invertido' => $obj->r_saldo_invertido, 'saldo_transito' => $obj->r_saldo_transito

                                );

            $obj = $conn->next_record();

        }



        $conn->close();

        return $arr_cuentas;

    }



    function get_saldo_inicial_cuenta($p_cuenta_id, $p_mes, $p_anho){

        $conn = new db_param_trans; $conn->connect();

        $conn2 = new db_param_trans; $conn2->connect();



        $idqry = $conn->query("select count(1) as contador from saldo_cierre_mes where cuenta_id = ".$p_cuenta_id." and mes = ".$p_mes." and anho = ".$p_anho);

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        if ($obj->contador > 0){

            $idqry = $conn2->query("select saldo_cierre_mes.saldo_contable, saldo_cierre_mes.saldo_comprometido, saldo_cierre_mes.saldo_disponible, saldo_cierre_mes.saldo_invertido,

                                        tipos.nombre, cuenta_inversionista.monedaid 

                                    from saldo_cierre_mes, cuenta_inversionista, tipos 

                                    where saldo_cierre_mes.cuenta_id = ".$p_cuenta_id." and saldo_cierre_mes.mes = ".$p_mes." and saldo_cierre_mes.anho = ".$p_anho." and 

                                        cuenta_inversionista.id = saldo_cierre_mes.cuenta_id and tipos.id = cuenta_inversionista.monedaid");

            if (!$idqry) echo pg_last_error($conn2->Link_ID);

            $obj = $conn2->next_record();

            $varr_saldo = array('saldo_contable'=>$obj->saldo_contable, 'saldo_comprometido'=>$obj->saldo_comprometido, 'saldo_disponible'=>$obj->saldo_disponible, 

                                'saldo_invertido'=>$obj->saldo_invertido, 'moneda_id'=>$obj->monedaid, 'moneda'=>$obj->nombre);

        } else{

            $idqry = $conn2->query("select cuenta_inversionista.monedaid, tipos.nombre from cuenta_inversionista, tipos where cuenta_inversionista.id = ".$p_cuenta_id." and 

                                    tipos.id = cuenta_inversionista.monedaid");

            if (!$idqry) echo pg_last_error($conn2->Link_ID);

            $obj = $conn2->next_record();



            $varr_saldo = array('saldo_contable'=>0, 'saldo_comprometido'=>0, 'saldo_disponible'=>0, 'saldo_invertido'=>0, 'moneda_id'=>0, 'moneda_id'=>$obj->monedaid,

                                'moneda'=>$obj->nombre);

        }



        $conn->close(); $conn2->close();

        return $varr_saldo;

    }



    function get_movimientos_cuenta_fecha($cuentaid, $numrows, $rowini, $p_finicio, $p_tipo, $p_ffin){

        $conn = new db_param_trans;

        $conn->connect();

        $arr_movimientos = array();

        

        $idqry = $conn->query("select * from CTA_GET_MOVIMIENTOS_FECHA(".$cuentaid.", ".$rowini.", ".$numrows.",'".$p_finicio."','".$p_tipo."','".$p_ffin."')");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        

        for($i = 0; $i < $conn->nrows(); $i ++){

            $arr_movimientos[$i] = array('movimiento_id'=>$obj->r_movimiento_id,'f_movimiento'=>$obj->r_f_movimiento,

                                    'monto' => $obj->r_monto, 'tipo_movimiento' => $obj->t_tipo_movimiento,

                                    'movimiento' => $obj->r_movimiento, 'subasta_id' => $obj->r_subasta_id,

                                    'propuesta_id' => $obj->r_propuesta_id, 'factura_id' => $obj->r_factura_id, 'simbolo'=>$obj->r_simbolo

                                );

            $obj = $conn->next_record();

        }



        $conn->close();

        return $arr_movimientos;

    }

    function get_cuenta_omnibus_detalle(){

        $conn = new db_param_trans;

        $conn_seg = new db_param;

        $conn->connect();

        $conn_seg->connect();

        $arr_result = array();

        

        $idqry = $conn->query("select cuenta_inversionista.id, cuenta_inversionista.monedaid, cuenta_inversionista.saldo_comprometido, cuenta_inversionista.saldo_disponible, 

                                    cuenta_inversionista.inversionistaid, cuenta_inversionista.empresaid, empresa.nombre, empresa.identificacion, tipos.nombre as moneda

                                from (cuenta_inversionista left outer join empresa on cuenta_inversionista.empresaid = empresa.id), tipos

                                where cuenta_inversionista.estado = 1 and (cuenta_inversionista.saldo_comprometido <> 0 or cuenta_inversionista.saldo_disponible <> 0) and 

                                    tipos.id = cuenta_inversionista.monedaid

                                order by cuenta_inversionista.monedaid");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $personas_id = '';

        

        for($i = 0; $i < $conn->nrows(); $i ++){

            $arr_result[$i] = array('cuenta_id'=>$obj->id,'moneda_id'=>$obj->monedaid,'saldo_comprometido'=>$obj->saldo_comprometido,

                                    'saldo_disponible' => $obj->saldo_disponible, 'moneda' => $obj->moneda, 'empresa_id' => $obj->empresaid);



            if ($obj->empresaid > 0){

                $arr_result[$i]['inversionista_id'] = 'EMP-'.$obj->empresaid;

                $arr_result[$i]['nombre'] = $obj->nombre;

                $arr_result[$i]['identificacion'] = $obj->identificacion;

            } else{

                $arr_result[$i]['inversionista_id'] = $obj->inversionistaid;

                

                if ($personas_id != '') $personas_id .= ','.$obj->inversionistaid;

                else $personas_id .= $obj->inversionistaid;

            }



            $obj = $conn->next_record();

        }



        if ($personas_id != ''){

            $idqry = $conn_seg->query("select id, identificacion, nombre, apellido from usuarios where id in (".$personas_id.")");

            if (!$idqry) echo pg_last_error($conn_seg->Link_ID);

            $obj_seg = $conn_seg->next_record();

            

            for ($j = 0; $j < $conn_seg->nrows(); $j++){

                for ($i=0; $i<count($arr_result); $i++){

                    if ($arr_result[$i]['inversionista_id'] == $obj_seg->id){

                        $arr_result[$i]['nombre'] = $obj_seg->nombre.' '.$obj_seg->apellido;

                        $arr_result[$i]['identificacion'] = $obj_seg->identificacion;

                        break;

                    }

                }

                $obj_seg = $conn_seg->next_record();

            }

        }



        $conn->close();

        $conn_seg->close();

        return $arr_result;

    }



    function get_cuentas_inversores($p_tipo, $p_rowini, $p_rowcount, $parr_filtros){

        $conn = new db_param_trans;

        $conn_seg = new db_param;

        $conn->connect();

        $conn_seg->connect();



        if ($p_tipo == 'COUNT'){

            $idqry = $conn->query("select count(1) as resultado 

                                from (cuenta_inversionista left outer join empresa on cuenta_inversionista.empresaid = empresa.id), tipos

                                where cuenta_inversionista.estado = 1 and tipos.id = cuenta_inversionista.monedaid and cuenta_inversionista.inversionistaid > 0");

            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();

            $arr_result = $obj->resultado;

        } else {

            $arr_result = array();

            $idqry = $conn->query("select cuenta_inversionista.id, cuenta_inversionista.monedaid, cuenta_inversionista.saldo_comprometido, cuenta_inversionista.saldo_disponible, 

                                    cuenta_inversionista.inversionistaid, cuenta_inversionista.empresaid, empresa.nombre, empresa.identificacion, tipos.nombre as moneda, 

                                    cuenta_inversionista.saldo_contable, cuenta_inversionista.saldo_invertido 

                                from (cuenta_inversionista left outer join empresa on cuenta_inversionista.empresaid = empresa.id), tipos

                                where cuenta_inversionista.estado = 1 and tipos.id = cuenta_inversionista.monedaid and cuenta_inversionista.inversionistaid > 0

                                order by cuenta_inversionista.id");

            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();

            $personas_id = '';

        

            for($i = 0; $i < $conn->nrows(); $i ++){

                $arr_result[$i] = array('cuenta_id'=>$obj->id,'moneda_id'=>$obj->monedaid,'saldo_comprometido'=>$obj->saldo_comprometido,

                                        'saldo_disponible' => $obj->saldo_disponible, 'moneda' => $obj->moneda, 'empresa_id' => $obj->empresaid,

                                        'saldo_contable' => $obj->saldo_contable, 'saldo_invertido' => $obj->saldo_invertido);



                if ($obj->empresaid > 0){

                    $arr_result[$i]['inversionista_id'] = 'EMP-'.$obj->empresaid;

                    $arr_result[$i]['nombre'] = $obj->nombre;

                    $arr_result[$i]['identificacion'] = $obj->identificacion;

                    $arr_result[$i]['tipo_persona'] = 'EMPRESA';

                } else{

                    $arr_result[$i]['inversionista_id'] = $obj->inversionistaid;

                    $arr_result[$i]['tipo_persona'] = 'PERSONA';

                    

                    if ($personas_id != '') $personas_id .= ','.$obj->inversionistaid;

                    else $personas_id .= $obj->inversionistaid;

                }



                $obj = $conn->next_record();

            }



            if ($personas_id != ''){

                $idqry = $conn_seg->query("select id, identificacion, nombre, apellido from usuarios where id in (".$personas_id.")");

                if (!$idqry) echo pg_last_error($conn_seg->Link_ID);

                $obj_seg = $conn_seg->next_record();

                

                for ($j = 0; $j < $conn_seg->nrows(); $j++){

                    for ($i=0; $i<count($arr_result); $i++){

                        if ($arr_result[$i]['inversionista_id'] == $obj_seg->id){

                            $arr_result[$i]['nombre'] = $obj_seg->nombre.' '.$obj_seg->apellido;

                            $arr_result[$i]['identificacion'] = $obj_seg->identificacion;

                            break;

                        }

                    }

                    $obj_seg = $conn_seg->next_record();

                }

            }

        }



        $conn->close();

        $conn_seg->close();

        return $arr_result;

    }



    function get_cuenta_detalle($p_cuenta_id){

        $conn = new db_param_trans; $conn->connect();

        $conn2 = new db_param_trans; $conn2->connect();

        $conn3 = new db_param_trans; $conn3->connect();

        $varr_result = array();

        

        $varr_saldos = $this->get_saldos_cuenta($p_cuenta_id);

        //====== DATOS DE LOS SALDOS

        $varr_result['SALDOS'] = array('saldo_contable' => $varr_saldos['saldo_contable'], 'saldo_comprometido' => $varr_saldos['saldo_comprometido'], 

                                        'saldo_disponible' => $varr_saldos['saldo_disponible'], 'saldo_invertido' => $varr_saldos['saldo_invertido'],

                                        'saldo_transito' => $varr_saldos['saldo_transito']);

        //====== DATOS DE LA CUENTA

        $idqry = $conn->query("select cuenta_inversionista.inversionistaid as inversor_id, cuenta_inversionista.monedaid as moneda_id, tipos.nombre as moneda, 

                                RTRIM(inversionista.nombre || ' ' || inversionista.apellido) as nombre_inversor, inversionista.tipodoc, tdocumento.nombre as tdocumento_nombre, 

                                inversionista.tipo_inversor, tinversor.nombre as tinversor_nombre, inversionista.identificacion 

                            from cuenta_inversionista, tipos, inversionista,tipos as tdocumento, tipos as tinversor 

                            where tipos.id = cuenta_inversionista.monedaid and cuenta_inversionista.id = ".$p_cuenta_id." and inversionista.inversor_id = cuenta_inversionista.inversionistaid and 

                                tdocumento.id = inversionista.tipodoc and tinversor.id = inversionista.tipo_inversor");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        $varr_result['HEADER'] = array('inversor_id' => $obj->inversor_id, 'moneda_id' => $obj->moneda_id, 'moneda' => $obj->moneda, 'inversor_nombre' => $obj->nombre_inversor,

                                        'inversor_tipodoc_id' => $obj->tipodoc, 'inversor_tipodoc' => $obj->tdocumento_nombre, 'tipo_inversor_id' => $obj->tipo_inversor,

                                        'tipo_inversor' => $obj->tinversor_nombre, 'identificacion' => $obj->identificacion);

        

        if ($varr_saldos['saldo_transito'] > 0){    // INVERSOR CON SALDO TRANSITO

            $idqry = $conn2->query("select id, monto, comprobante_path from saldo_transito where cuenta_id = ".$p_cuenta_id." and estado_id = 1");

            if (!$idqry) echo pg_last_error($conn2->Link_ID);

            $obj2 = $conn2->next_record();



            for ($i = 0; $i < $conn2->nrows(); $i++){

                $varr_result['SALDO_TRANSITO'][$i] = array('monto' => $obj2->monto, 'st_id' => $obj2->id, 'comprobante' => $obj2->comprobante_path);

                $obj2 = $conn2->next_record();

            }

        }



        if ($varr_saldos['saldo_comprometido'] > 0){    // INVERSOR CON SALDO COMPROMETIDO

            if ($varr_result['HEADER']['tipo_inversor_id'] == 85){      // INVERSOR PERSONA

                $idqry = $conn3->query("select subasta.facturaid, propuestas.monto as monto_comprometido, (propuestas.monto - propuestas.fondo_disponible) as monto_pendiente 

                                        from propuestas, cuenta_inversionista, subasta, factura

                                        where cuenta_inversionista.id = ".$p_cuenta_id." and propuestas.usuarioid = cuenta_inversionista.inversionistaid and 

                                            subasta.id = propuestas.subastaid and factura.id = subasta.facturaid and factura.monedaid = cuenta_inversionista.monedaid and 

                                            subasta.estado in (31,25)");

            } else {

                $idqry = $conn3->query("select subasta.facturaid, propuestas.monto as monto_comprometido, (propuestas.monto - propuestas.fondo_disponible) as monto_pendiente 

                                        from propuestas, cuenta_inversionista, subasta, factura

                                        where cuenta_inversionista.id = ".$p_cuenta_id." and propuestas.empresaid = cuenta_inversionista.empresaid and 

                                            subasta.id = propuestas.subastaid and factura.id = subasta.facturaid and factura.monedaid = cuenta_inversionista.monedaid and 

                                            subasta.estado in (31,25)");

            }



            if (!$idqry) echo pg_last_error($conn3->Link_ID);

            $obj3 = $conn3->next_record();



            for ($i = 0; $i < $conn3->nrows(); $i++){

                $varr_result['SALDO_COMPROMETIDO'][$i] = array('factura_id' => $obj3->facturaid, 'monto_comprometido' => $obj3->monto_comprometido, 'monto_pendiente' => $obj3->monto_pendiente);

                $obj3 = $conn3->next_record();

            }

        }



        $conn->close();

        $conn2->close();

        $conn3->close();



        return $varr_result;

    }



    function get_cuentas_inversor($p_inversor_id, $p_empresa_id){

        $conn = new db_param_trans; $conn->connect();



        if ($p_empresa_id > 0){

            $idqry = $conn->query("select cuenta_inversionista.monedaid,cuenta_inversionista.saldo_comprometido, cuenta_inversionista.saldo_contable,cuenta_inversionista.saldo_disponible,

                                    cuenta_inversionista.saldo_invertido,cuenta_inversionista.id, cuenta_inversionista.banco_id, tipos.nombre as moneda, 

                                    cuenta_inversionista.numero_cuenta_banco, cuenta_inversionista.tipo_cuenta_banco 

                                    from cuenta_inversionista,tipos 

                                    where cuenta_inversionista.inversionistaid = ".$p_empresa_id." and tipos.id = cuenta_inversionista.monedaid");

        } else {

            $idqry = $conn->query("select cuenta_inversionista.monedaid,cuenta_inversionista.saldo_comprometido, cuenta_inversionista.saldo_contable,cuenta_inversionista.saldo_disponible,

                                    cuenta_inversionista.saldo_invertido,cuenta_inversionista.id, cuenta_inversionista.banco_id, tipos.nombre as moneda, 

                                    cuenta_inversionista.numero_cuenta_banco, cuenta_inversionista.tipo_cuenta_banco 

                                    from cuenta_inversionista,tipos 

                                    where cuenta_inversionista.inversionistaid = ".$p_inversor_id." and tipos.id = cuenta_inversionista.monedaid");

        }



        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $varr_result = array();



        for ($i = 0; $i < $conn->nrows(); $i++){

            $varr_result[$i] = array('moneda_id'=>$obj->monedaid, 'saldo_comprometido'=>$obj->saldo_comprometido, 'saldo_contable'=>$obj->saldo_contable, 'saldo_disponible'=>$obj->saldo_disponible,

                                'saldo_invertido'=>$obj->saldo_invertido, 'cuenta_id'=>$obj->id, 'moneda'=>$obj->moneda, 'banco_id'=>$obj->banco_id, 'banco_nombre'=>$obj->nombre_banco,

                                'numero_cuenta'=>$obj->numero_cuenta_banco, 'tcuenta_id'=>$obj->tipo_cuenta_banco, 'tcuenta_nombre'=>$obj->tcuenta_nombre);

            $obj = $conn->next_record();

        }



        $conn->close();

        return $varr_result;

    }



    function get_datos_inversor($p_usuario_id, $p_empresa_id){

        $conn = new db_param_trans; $conn->connect();



        if ($p_empresa_id > 0){

            $idqry = $conn->query("select inversionista.id, inversionista.identificacion, CONCAT(inversionista.nombre, ' ',inversionista.apellido) as nombre_inversor,

                                        inversionista.email

                                    from inversionista 

                                    where inversionista.inversor_id = ".$p_empresa_id);

        } else{

            $idqry = $conn->query("select inversionista.id, inversionista.identificacion, CONCAT(inversionista.nombre, ' ',inversionista.apellido) as nombre_inversor,

                                        inversionista.email

                                    from inversionista 

                                    where inversionista.inversor_id = ".$p_usuario_id);

        }



        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $varr_result = array('id'=>$obj->id, 'identificacion'=>$obj->identificacion, 'nombre_inversor'=>$obj->nombre_inversor, 'email'=>$obj->email);



        $conn->close();

        return $varr_result;

    }



    function get_st($p_st_id){

        $conn = new db_param_trans; $conn->connect();



        $idqry = $conn->query("select * from CTA_GET_SALDOS_TRANSITO(".$p_st_id.")");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        $arr_transito = array('saldo_id'=>$obj->r_saldo_id,'cuenta_id'=>$obj->r_cuenta_id,

                                    'monto' => $obj->r_saldo, 'moneda_id' => $obj->r_moneda_id,

                                    'f_creacion' => $obj->r_f_creacion, 'comp_path' => $obj->r_comp_path,

                                    'moneda' => $obj->r_moneda, 'inversionista_id' => $obj->r_inversionista_id,

                                    'empresa_id' => $obj->r_empresa_id, 'nombre_inversor'=>$obj->r_nombre_inversor, 'nombre_banco'=>$obj->r_nombre_banco,

                                    'moneda_simbolo'=>$obj->r_moneda_simbolo);



        $conn->close();

        return $arr_transito;

    }



    function crear_cuenta($p_datos){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $conn3 = new db_param_trans; $conn3->connect();

        $idqry = $conn->query("select count(1) as contador 
                            from cuenta_inversionista 
                            where inversionistaid = ".$p_datos['inversor_id']." and estado > 0 and monedaid = ".$p_datos['moneda_id']." and banco_id = ".$p_datos['banco_id']." and tipo_cuenta_banco = ".$p_datos['tcuenta_id']." and numero_cuenta_banco = '".$p_datos['cuenta']."'");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($obj->contador > 0) $rpta = 0;
        else {
            $idqry = $conn2->query("select CTA_REGISTRA_CUENTA_INVERSOR(".$p_datos['inversor_id'].", ".$_SESSION['user']['usuarioid'].",".$p_datos['moneda_id'].", ".$p_datos['empresa_id'].",".$p_datos['banco_id'].",".$p_datos['tcuenta_id'].",'".$p_datos['cuenta']."') as result");

            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj = $conn2->next_record();

            $v_cuenta_id = $obj->result;
            $v_sql = "update cuenta_banco_inversionista set certificado_path = '".$p_datos['certificado_path']."' where id = ".$v_cuenta_id;

            $idqry = $conn3->query($v_sql);

            if (!$idqry) echo pg_last_error($conn3->Link_ID);
            $conn3->next_record();

            $rpta = $v_cuenta_id;
        }

        $conn->close(); $conn2->close(); $conn3->close();

        return $rpta;
    }



    function get_cuentas_banco_inversor($p_inversor_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select cuenta_banco_inversionista.id, cuenta_banco_inversionista.banco_id, bancos.nombre_banco, cuenta_banco_inversionista.tcuenta_id,
                                tipos.nombre as tcuenta_nombre, cuenta_banco_inversionista.cuenta, cuenta_banco_inversionista.moneda_id, tmoneda.nombre as moneda_nombre,
                                cuenta_banco_inversionista.estado_id, estados.nombre as estado_nombre, cuenta_banco_inversionista.certificado_path 
                                from cuenta_banco_inversionista, bancos, tipos, tipos as tmoneda, estados 
                                where cuenta_banco_inversionista.inversor_id = ".$p_inversor_id." and bancos.id = cuenta_banco_inversionista.banco_id and 
                                    tipos.id = cuenta_banco_inversionista.tcuenta_id and tmoneda.id = cuenta_banco_inversionista.moneda_id and cuenta_banco_inversionista.estado_id > 0 and 
                                    estados.id = cuenta_banco_inversionista.estado_id");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for ($i = 0; $i < $conn->nrows(); $i++){
            $varr_result[$i] = array('cuenta_banco_id'=>$obj->id,   'banco_id'=>$obj->banco_id,     'banco_nombre'=>$obj->nombre_banco,     'tcuenta_id'=>$obj->tcuenta_id,
                                    'tcuenta_nombre'=>$obj->tcuenta_nombre,                         'cuenta'=>$obj->cuenta,                 'moneda_id'=>$obj->moneda_id, 
                                    'moneda_nombre'=>$obj->moneda_nombre,                           'estado_id' => $obj->estado_id,         'estado_nombre' => $obj->estado_nombre,
                                    'certificado_path' => $obj->certificado_path);

            $obj = $conn->next_record();
        }

        $conn->close();

        return $varr_result;
    }

    function get_cta_banco_inversor($p_inversor_id, $p_moneda_id, $p_cuenta, $p_banco_id){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "  select  cuenta_banco_inversionista.id, cuenta_banco_inversionista.banco_id, bancos.nombre_banco, cuenta_banco_inversionista.tcuenta_id,
                            tipos.nombre as tcuenta_nombre, cuenta_banco_inversionista.cuenta, cuenta_banco_inversionista.moneda_id, tmoneda.nombre as moneda_nombre,
                            cuenta_banco_inversionista.estado_id, estados.nombre as estado_nombre, cuenta_banco_inversionista.certificado_path 
                    from    cuenta_banco_inversionista, bancos, tipos, tipos as tmoneda, estados 
                    where   cuenta_banco_inversionista.inversor_id = ".$p_inversor_id." and cuenta_banco_inversionista.moneda_id = ".$p_moneda_id." and 
                            cuenta_banco_inversionista.cuenta = '".$p_cuenta."' and cuenta_banco_inversionista.banco_id = ".$p_banco_id." and 
                            bancos.id = cuenta_banco_inversionista.banco_id and tipos.id = cuenta_banco_inversionista.tcuenta_id and 
                            tmoneda.id = cuenta_banco_inversionista.moneda_id and cuenta_banco_inversionista.estado_id > 0 and 
                            estados.id = cuenta_banco_inversionista.estado_id";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array(   'banco_id' => $obj->banco_id,               'nro_cuenta' => $obj->cuenta,           'tcuenta_id' => $obj->tcuenta_id, 
                                'certificado' => $obj->certificado_path,    'estado_id' => $obj->estado_id,         'tcuenta_nombre' => $obj->tcuenta_nombre,
                                'estado_nombre' => $obj->estado_nombre,     'banco_nombre' => $obj->nombre_banco,   'nombre_moneda' => $obj->moneda_nombre,
                                'cuenta_id' => $obj->id);

        $conn->close();

        return $varr_result;
    }



    function eliminar_cuenta_banco($p_cuenta_id){

        $conn = new db_param_trans; $conn->connect();



        $idqry = $conn->query("update cuenta_banco_inversionista set estado_id = 0 where id = ".$p_cuenta_id);

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        $conn->close();

        return 1;

    }



    function get_saldo_transito_detalle($p_cuenta_id){

        $conn = new db_param_trans; $conn->connect();



        $idqry = $conn->query("select saldo_transito.id, saldo_transito.monto, saldo_transito.f_creacion, saldo_transito.banco_id, bancos.nombre_banco 

                                from saldo_transito, bancos 

                                where saldo_transito.estado_id = 1 and saldo_transito.cuenta_id = ".$p_cuenta_id." and saldo_transito.banco_id = bancos.id");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $varr_result = array();



        for ($i = 0; $i < $conn->nrows(); $i++){

            $varr_result[$i] = array('id' => $obj->id, 'monto' => $obj->monto, 'fecha_creacion' => $obj->f_creacion, 'banco_id' => $obj->banco_id, 'banco_nombre' => $obj->nombre_banco);

            $obj = $conn->next_record();

        }



        $conn->close();

        return $varr_result;

    }



    function get_cuentas_banco_emisor($p_emisor_id){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "  select  empresa_cuenta_banco.moneda_id, tipos.nombre as moneda_nombre, empresa_cuenta_banco.banco_id, bancos.nombre_banco, empresa_cuenta_banco.nro_cuenta,
                            empresa_cuenta_banco.tcuenta_id, tcuenta.nombre as tcuenta_nombre, empresa_cuenta_banco.estado_id, 
                            empresa_cuenta_banco.id 
                    from    empresa_cuenta_banco, tipos, bancos, tipos as tcuenta 
                    where   empresa_cuenta_banco.empresa_id = ".$p_emisor_id." and empresa_cuenta_banco.estado_id > 0 and tipos.id = empresa_cuenta_banco.moneda_id and 
                            bancos.id = empresa_cuenta_banco.banco_id and tcuenta.id = empresa_cuenta_banco.tcuenta_id";

        $idqry = $conn->query($v_sql);
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array();

        for ($i = 0; $i < $conn->nrows(); $i++){
            $varr_result[$i] = array(   'moneda_id' => $obj->moneda_id,          'moneda_nombre' => $obj->moneda_nombre,     
                                        'banco_id' => $obj->banco_id,            'tcuenta_id' => $obj->tcuenta_id,
                                        'banco_nombre' => $obj->nombre_banco,    'nro_cuenta' => $obj->nro_cuenta, 
                                        'tcuenta_nombre' => $obj->tcuenta_nombre,'estado_id' => $obj->estado_id,
                                        'id' => $obj->id);

            $obj = $conn->next_record();
        }

        $conn->close();

        return $varr_result;
    }

    function registra_cuenta_banco_emisor($parr_cuenta){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $conn3 = new db_param_trans; $conn3->connect();

        $idqry = $conn->query(" select count(1) as contador 
                                from empresa_cuenta_banco 
                                where empresa_id = ".$parr_cuenta['emisor_id']." and moneda_id = ".$parr_cuenta['moneda_id']." and estado_id > 0");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($obj->contador > 0) $v_rpta = 1;
        else $v_rpta = 0;

        $idqry = $conn3->query("select nextval('s_cuenta_empresa') as id_secuencia");

        if (!$idqry) echo pg_last_error($conn3->Link_ID);
        $obj3 = $conn3->next_record();
        $v_id = $obj3->id_secuencia;

        $idqry = $conn2->query("insert into empresa_cuenta_banco(empresa_id,moneda_id,banco_id,nro_cuenta,estado_id,tcuenta_id, certificado, id) 
                                values(".$parr_cuenta['emisor_id'].",".$parr_cuenta['moneda_id'].",".$parr_cuenta['banco_id'].",'".$parr_cuenta['nro_cuenta']."',66,".$parr_cuenta['tcuenta_id'].",'".$parr_cuenta['certificado']."',".$v_id.")");

        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();

        $conn->close(); $conn2->close();

        return $v_rpta;
    }



    function update_cuenta_banco_emisor($parr_cuenta){
        $conn = new db_param_trans; $conn->connect();     
        $v_rpta = 0;

        $idqry = $conn->query(" update empresa_cuenta_banco 
                                set banco_id = ".$parr_cuenta['banco_id'].", nro_cuenta = '".$parr_cuenta['nro_cuenta']."', 
                                    tcuenta_id = ".$parr_cuenta['tcuenta_id']." 
                                where empresa_id = ".$parr_cuenta['emisor_id']." and moneda_id = ".$parr_cuenta['moneda_id']." and 
                                    id = ".$parr_cuenta['id']);

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $conn->close();

        return $v_rpta;
    }



    function delete_cuenta_banco_emisor($p_emisor_id, $p_moneda_id, $p_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query(" update empresa_cuenta_banco set estado_id = 0 
                                where empresa_id = ".$p_emisor_id." and moneda_id = ".$p_moneda_id." and id = ".$p_id);

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $conn->close();
    }



    function get_cuenta_banco_emisor($p_emisor_id, $p_moneda_id, $p_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query(" select banco_id, nro_cuenta, tcuenta_id, certificado 
                                from empresa_cuenta_banco 
                                where empresa_id = ".$p_emisor_id." and estado_id > 0 and moneda_id = ".$p_moneda_id." and id = ".$p_id);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array('banco_id' => $obj->banco_id, 'nro_cuenta' => $obj->nro_cuenta, 'tcuenta_id' => $obj->tcuenta_id, 'certificado' => $obj->certificado);

        $conn->close();

        return $varr_result;
    }

    function get_cuenta_banco_emisor_v2($p_emisor_id, $p_moneda_id, $p_cuenta, $p_banco_id){
        $conn = new db_param_trans; $conn->connect();

        $v_sql = "  select  empresa_cuenta_banco.banco_id, empresa_cuenta_banco.nro_cuenta, empresa_cuenta_banco.tcuenta_id, empresa_cuenta_banco.certificado, 
                            empresa_cuenta_banco.estado_id, tipos.nombre as tcuenta_nombre, estados.nombre as estado_nombre, bancos.nombre_banco,
                            tmoneda.nombre as moneda_nombre 
                    from    empresa_cuenta_banco, tipos, estados, bancos, tipos as tmoneda 
                    where   empresa_cuenta_banco.empresa_id = ".$p_emisor_id." and empresa_cuenta_banco.estado_id > 0 and 
                            empresa_cuenta_banco.moneda_id = ".$p_moneda_id." and banco_id = ".$p_banco_id." and empresa_cuenta_banco.nro_cuenta = '".$p_cuenta."' and 
                            tipos.id = empresa_cuenta_banco.tcuenta_id and estados.id = empresa_cuenta_banco.estado_id and bancos.id = empresa_cuenta_banco.banco_id and 
                            tmoneda.id = empresa_cuenta_banco.moneda_id";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $varr_result = array(   'banco_id' => $obj->banco_id,           'nro_cuenta' => $obj->nro_cuenta,       'tcuenta_id' => $obj->tcuenta_id, 
                                'certificado' => $obj->certificado,     'estado_id' => $obj->estado_id,         'tcuenta_nombre' => $obj->tcuenta_nombre,
                                'estado_nombre' => $obj->estado_nombre, 'banco_nombre' => $obj->nombre_banco,   'nombre_moneda' => $obj->moneda_nombre);

        $conn->close();

        return $varr_result;
    }



    function get_movimientos_depositos_inversor($p_filtros, $p_order){

        $conn = new db_param_trans; $conn->connect();



        $v_sql = "select movimientos_cuenta.id, movimientos_cuenta.cuentaid, cuenta_inversionista.inversionistaid, inversionista.nombre, inversionista.apellido, 

                    movimientos_cuenta.monedaid, tipo_moneda.nombre as moneda_nom, movimientos_cuenta.monto, movimientos_cuenta.fregistro 

                from movimientos_cuenta, cuenta_inversionista, inversionista, tipos as tipo_moneda 

                where cuenta_inversionista.id = movimientos_cuenta.cuentaid and inversionista.inversor_id = cuenta_inversionista.inversionistaid and movimientos_cuenta.estado = 1 and 

                    tipo_moneda.id = movimientos_cuenta.monedaid and movimientos_cuenta.tipo_operacionid = 56";



        if ($p_filtros != '') $v_sql .= " and ".$p_filtros;

        if ($p_order != '') $v_sql .= " order by ".$p_order;



        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        $varr_result = array();



        for ($i = 0; $i < $conn->nrows(); $i++){

            $varr_result[$i] = array('movimiento_id' => $obj->id,       'cuenta_id' => $obj->cuentaid,  'inversor_id' => $obj->inversionistaid,

                                        'nombre' => $obj->nombre,       'apellido' => $obj->apellido,   'moneda_id' => $obj->monedaid,

                                        'moneda' => $obj->moneda_nom,   'monto' => $obj->monto,          'f_registro' => $obj->fregistro);

            $obj = $conn->next_record();

        }



        $conn->close();

        return $varr_result;

    }



    function get_cuenta_omnibus($p_cuenta_id){

        $conn = new db_param_trans; $conn->connect();



        $v_sql = "select cuenta_omnibus.id, cuenta_omnibus.numero_cuenta, cuenta_omnibus.tipo_cuenta, tcuenta.nombre as tipo_cuenta_nom, cuenta_omnibus.banco_id,

                        bancos.nombre_banco, cuenta_omnibus.moneda_id, tmoneda.nombre as moneda, cuenta_omnibus.saldo_contable, cuenta_omnibus.saldo_inversor,

                        cuenta_omnibus.saldo_disponible, cuenta_omnibus.saldo_vendedor, cuenta_omnibus.saldo_transito, tmoneda.dato1 as moneda_simbol

                    from cuenta_omnibus, tipos as tcuenta, bancos, tipos as tmoneda 

                    where tcuenta.id = cuenta_omnibus.tipo_cuenta and bancos.id = cuenta_omnibus.banco_id and tmoneda.id = cuenta_omnibus.moneda_id and cuenta_omnibus.estado_id = 1";



        if ($p_cuenta_id > 0) $v_sql .= " and cuenta_omnibus.id = ".$p_cuenta_id;

        $v_sql .= " order by cuenta_omnibus.id";



        $idqry = $conn->query($v_sql);



        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        $varr_result = array();



        for ($i = 0; $i < $conn->nrows(); $i++){

            $varr_result[$i] = array(    'cuenta_id' => $obj->id,                    'cuenta_numero' => $obj->numero_cuenta,    'tipo_cuenta_id' => $obj->tipo_cuenta,

                                         'tipo_cuenta_nom' => $obj->tipo_cuenta_nom, 'banco_id' => $obj->banco_id,              'banco_nombre' => $obj->nombre_banco,

                                         'moneda_id' => $obj->moneda_id,             'moneda_nombre' => $obj->moneda,           's_contable' => $obj->saldo_contable,

                                         's_inversor' => $obj->saldo_inversor,       's_disponible' => $obj->saldo_disponible,  's_vendedor' => $obj->saldo_vendedor,

                                         's_transito' => $obj->saldo_transito,       'moneda_simbol' => $obj->moneda_simbol);

            $obj = $conn->next_record();

        }



        $conn->close();

        return $varr_result;

    }



    function get_movimientos_cuenta_omnibus($p_tipo, $p_cuenta_id, $p_tipo_movimiento, $p_alcance_movimiento, $p_rowini, $p_num_rows, $p_fini, $p_ffin){

        //---- TIPO MOVIMIENTO = SI ES INGRESO O SALIDA O TOTAL // ALCANCE DE MOVIMIENTO ES SI ES BANCARIO O INTERNO O TOTAL

        $conn = new db_param_trans; $conn->connect();



        $v_ffin = strtotime($p_ffin); $v_ffin = date('Y-m-d',$v_ffin); $v_ffin = strtotime('+1 day', strtotime($v_ffin)); $v_ffin = date('Y-m-d',$v_ffin);



        if ($p_tipo == 'SELECT'){

            $v_sql = "select    movimientos_cuenta.id, movimientos_cuenta.cuentaid, inversionista.nombre, inversionista.apellido, 

                            movimientos_cuenta.fregistro, movimientos_cuenta.monedaid, tmoneda.dato1 as moneda_simbol, movimientos_cuenta.monto, 

                            movimientos_cuenta.tipo_operacionid, toperacion.nombre as movimiento_nombre, movimientos_cuenta.beneficiario_depositante,

                            toperacion.dato_num as ing_sal

                from        cuenta_omnibus, movimientos_cuenta, (cuenta_inversionista left outer join inversionista on inversionista.inversor_id = cuenta_inversionista.inversionistaid), 

                            tipos as tmoneda, tipos as toperacion 

                where       movimientos_cuenta.monedaid = cuenta_omnibus.moneda_id and cuenta_inversionista.id = movimientos_cuenta.cuentaid and movimientos_cuenta.estado = 1 and 

                            tmoneda.id = movimientos_cuenta.monedaid and toperacion.id = movimientos_cuenta.tipo_operacionid and cuenta_omnibus.id = ".$p_cuenta_id." and 

                            movimientos_cuenta.fregistro >= '".$p_fini."' and movimientos_cuenta.fregistro < '".$v_ffin."'";



            if ($p_tipo_movimiento == 'ING') $v_sql .= " and toperacion.dato_num = 1";

            elseif ($p_tipo_movimiento == 'SAL') $v_sql .= " and toperacion.dato_num = -1";



            if ($p_alcance_movimiento == 'BANCARIO' || $p_alcance_movimiento == 'INTERNO') $v_sql .= " and toperacion.dato1 = '".$p_alcance_movimiento."'";



            $v_sql .= " order by movimientos_cuenta.fregistro";



            if ($p_num_rows > 0) $v_sql .= " limit ".$p_num_rows." offset ".$p_rowini;



            $idqry = $conn->query($v_sql);



            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();



            $varr_result = array();



            for ($i = 0; $i < $conn->nrows(); $i++){

                $varr_result[$i] = array(    'movimiento_id' => $obj->id,               'cuenta_id' => $obj->cuentaid,      'inversor_nombre' => $obj->nombre,

                                             'inversor_apellido' => $obj->apellido,     'f_movimiento' => $obj->fregistro,  'moneda_id' => $obj->monedaid,

                                             'moneda_simbol' => $obj->moneda_simbol,    'monto' => $obj->monto,             'tmovimiento_id' => $obj->tipo_operacionid,

                                             'tmovimiento' => $obj->movimiento_nombre,  'beneficiario_depositante' => $obj->beneficiario_depositante,

                                             'ing_sal' => $obj->ing_sal);

                $obj = $conn->next_record();

            }

        } else {

            $v_sql = "select count(1) as contador

                from        cuenta_omnibus, movimientos_cuenta, (cuenta_inversionista left outer join inversionista on inversionista.inversor_id = cuenta_inversionista.inversionistaid), 

                            tipos as tmoneda, tipos as toperacion 

                where       movimientos_cuenta.monedaid = cuenta_omnibus.moneda_id and cuenta_inversionista.id = movimientos_cuenta.cuentaid and movimientos_cuenta.estado = 1 and 

                            tmoneda.id = movimientos_cuenta.monedaid and toperacion.id = movimientos_cuenta.tipo_operacionid and cuenta_omnibus.id = ".$p_cuenta_id." and 

                            movimientos_cuenta.fregistro >= '".$p_fini."' and movimientos_cuenta.fregistro < '".$v_ffin."'";



            if ($p_tipo_movimiento == 'ING') $v_sql .= " and toperacion.dato_num = 1";

            elseif ($p_tipo_movimiento == 'SAL') $v_sql .= " and toperacion.dato_num = -1";



            if ($p_alcance_movimiento == 'BANCARIO' || $p_alcance_movimiento == 'INTERNO') $v_sql .= " and toperacion.dato1 = '".$p_alcance_movimiento."'";



            $idqry = $conn->query($v_sql);



            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();

            $varr_result = $obj->contador;

        }



        $conn->close();

        return $varr_result;

    }



    function get_saldos_cuenta_omnibus($p_tipo, $p_row_ini, $p_num_rows, $p_filtros, $p_order){

        $conn = new db_param_trans; $conn->connect();



        if ($p_tipo == 'COUNT'){

            $v_sql = "select count(1) as contador from cuenta_inversionista where estado = 1 and saldo_contable > 0";



            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;



            $idqry = $conn->query($v_sql);



            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();

            $varr_result = $obj->contador;

        } else {

            $v_sql = "select    cuenta_inversionista.id, inversionista.nombre, inversionista.apellido, cuenta_inversionista.saldo_contable, 

                                cuenta_inversionista.saldo_comprometido, cuenta_inversionista.saldo_disponible, cuenta_inversionista.saldo_invertido,

                                cuenta_inversionista.inversionistaid

                    from        cuenta_inversionista left outer join inversionista on inversionista.inversor_id = (case when cuenta_inversionista.empresaid = 0 then cuenta_inversionista.inversionistaid else cuenta_inversionista.empresaid end) 

                    where       cuenta_inversionista.estado = 1 and cuenta_inversionista.saldo_contable > 0";



            if ($p_filtros != '') $v_sql .= " and ".$p_filtros;

            if ($p_order != '') $v_sql .= " order by ".$p_order;

            if ($p_num_rows > 0) $v_sql .= " limit ".$p_num_rows." offset ".$p_row_ini;



            $idqry = $conn->query($v_sql);



            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();



            $varr_result = array();



            for ($i = 0; $i < $conn->nrows(); $i++){

                $varr_result[$i] = array(    'cuenta_id' => $obj->id,                'nombre' => $obj->nombre,                      'apellido' => $obj->apellido,

                                             's_contable' => $obj->saldo_contable,   's_comprometido' => $obj->saldo_comprometido,  's_disponible' => $obj->saldo_disponible,

                                             's_invertido' => $obj->saldo_invertido, 'inversionista_id' => $obj->inversionistaid);

                $obj = $conn->next_record();

            }

        }



        $conn->close();

        return $varr_result;

    }



    function get_cuenta_omnibus_xmoneda($p_moneda_id){

        $conn = new db_param_trans; $conn->connect();



        $v_sql = "select    cuenta_omnibus.id, cuenta_omnibus.numero_cuenta, cuenta_omnibus.tipo_cuenta, tcuenta.nombre as tipo_cuenta_nom, cuenta_omnibus.banco_id,

                            bancos.nombre_banco, cuenta_omnibus.moneda_id, tmoneda.nombre as moneda, cuenta_omnibus.saldo_contable, cuenta_omnibus.saldo_inversor,

                            cuenta_omnibus.saldo_disponible, cuenta_omnibus.saldo_vendedor, cuenta_omnibus.saldo_transito, tmoneda.dato1 as moneda_simbol

                    from    cuenta_omnibus, tipos as tcuenta, bancos, tipos as tmoneda 

                    where   tcuenta.id = cuenta_omnibus.tipo_cuenta and bancos.id = cuenta_omnibus.banco_id and tmoneda.id = cuenta_omnibus.moneda_id and cuenta_omnibus.estado_id = 1 and 

                            cuenta_omnibus.moneda_id = ".$p_moneda_id;



        $idqry = $conn->query($v_sql);



        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        $varr_result = array();



        for ($i = 0; $i < $conn->nrows(); $i++){

            $varr_result[$i] = array(    'cuenta_id' => $obj->id,                    'cuenta_numero' => $obj->numero_cuenta,    'tipo_cuenta_id' => $obj->tipo_cuenta,

                                         'tipo_cuenta_nom' => $obj->tipo_cuenta_nom, 'banco_id' => $obj->banco_id,              'banco_nombre' => $obj->nombre_banco,

                                         'moneda_id' => $obj->moneda_id,             'moneda_nombre' => $obj->moneda,           's_contable' => $obj->saldo_contable,

                                         's_inversor' => $obj->saldo_inversor,       's_disponible' => $obj->saldo_disponible,  's_vendedor' => $obj->saldo_vendedor,

                                         's_transito' => $obj->saldo_transito,       'moneda_simbol' => $obj->moneda_simbol);

            $obj = $conn->next_record();

        }



        $conn->close();

        return $varr_result;

    }



    function get_cierre_diario($p_fecha, $p_cuenta_id){

        // FORMATO YYYY-MM-DD

        $conn = new db_param_trans; $conn->connect();

        $conn2 = new db_param_trans; $conn2->connect();



        $v_fecha = strtotime($p_fecha);

        $v_dia = date('j', $v_fecha); $v_mes = date('n', $v_fecha); $v_anho = date('Y', $v_fecha);



        $v_sql_count = "select  count(1) as contador from cierre_diario_omnibus 

                        where   dia = ".$v_dia." and mes = ".$v_mes." and anho = ".$v_anho." and cuenta_id = ".$p_cuenta_id;



        $idqry = $conn->query($v_sql_count);



        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();



        if ($obj->contador > 0){

            $v_sql = "  select  saldo_contable, saldo_inversor, saldo_disponible, saldo_vendedor, saldo_transito

                        from    cierre_diario_omnibus 

                        where   dia = ".$v_dia." and mes = ".$v_mes." and anho = ".$v_anho." and cuenta_id = ".$p_cuenta_id;



            $idqry = $conn2->query($v_sql);



            if (!$idqry) echo pg_last_error($conn2->Link_ID);

            $obj2 = $conn2->next_record();



            $varr_saldos = array('saldo_contable' => $obj2->saldo_contable,     'saldo_inversor' => $obj2->saldo_inversor,      'saldo_disponible' => $obj2->saldo_disponible,

                                 'saldo_vendedor' => $obj2->saldo_vendedor,     'saldo_transito' => $obj2->saldo_transito);

        } else {

            $varr_saldos = array('saldo_contable' => 0,                         'saldo_inversor' => 0,                          'saldo_disponible' => 0,

                                 'saldo_vendedor' => 0,                         'saldo_transito' => 0);

        }



        $conn->close(); $conn2->close();

        return $varr_saldos;

    }

    function get_monto_total_preliminar($p_cuenta_id, $p_moneda_id, $p_varr_prop, $p_arr_saldos, $p_v_monto_enpropuestas){
        $v_monto_preliminares = 0;
        $v_disponible_final = 0;

        for ($i=0; $i<count($p_arr_saldos); $i++){
            if($p_arr_saldos[$i]['cuenta_id'] == $p_cuenta_id){
                $v_disponible_final = $p_arr_saldos[$i]['saldo_disponible'] - $p_v_monto_enpropuestas;
                break;
            }
        }

        
        for ($j=0; $j<count($p_varr_prop); $j++){
            if ($p_varr_prop[$j]['moneda_id'] == $p_moneda_id){
                $v_monto_preliminares = $v_monto_preliminares + $p_varr_prop[$j]['monto'];
            }
        }

        $v_monto_preliminares = $v_monto_preliminares - $v_disponible_final;
        
        return number_format($v_monto_preliminares,2,'.','');
    }

    function aprobar_cuenta_banco_inversor($p_inversor_id, $p_cuenta_banco_id){
        $conn = new db_param_trans; $conn->connect();
        
        $v_sql = "update cuenta_banco_inversionista set estado_id = 1 where inversor_id = ".$p_inversor_id." and id = ".$p_cuenta_banco_id;

        $idqry = $conn->query($v_sql);
        if (!$idqry){
            echo pg_last_error($conn->Link_ID);
            $v_retorno = -1;
        } else $v_retorno = 1;

        $conn->next_record();

        $conn->close();
        return $v_retorno;
    }

    function get_cuentas_banco_pendiente($p_tipo, $p_row_ini, $p_num_rows, $p_filtros, $p_order){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();

        if ($p_tipo == 'COUNT'){
            $v_sql_1 = "select count(1) as contador_emisor from empresa_cuenta_banco where estado_id = 66";
            $v_sql_2 = "select count(1) as contador_inversor from cuenta_banco_inversionista where estado_id = 65";


            $idqry = $conn->query($v_sql_1);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj_emisor = $conn->next_record();
            $v_resultado = $obj_emisor->contador_emisor;

            $idqry = $conn2->query($v_sql_2);
            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj_inversor = $conn2->next_record();
            $v_resultado = $v_resultado + $obj_inversor->contador_inversor;
        } else {
            $v_sql = "  select  id, moneda_id, banco_id, nro_cuenta, tcuenta_id, moneda_nombre, nombre_banco, tcuenta_nombre, tipo_persona, nombre 
                        from (  select  empresa_cuenta_banco.empresa_id as id, empresa_cuenta_banco.moneda_id, empresa_cuenta_banco.banco_id, 
                                    empresa_cuenta_banco.nro_cuenta, empresa_cuenta_banco.tcuenta_id, tmoneda.nombre as moneda_nombre,
                                    bancos.nombre_banco, tcuenta.nombre as tcuenta_nombre, 'EMISOR' as tipo_persona, empresa.nombre as nombre 
                                from    empresa_cuenta_banco, tipos as tmoneda, bancos, tipos as tcuenta, empresa 
                                where   empresa_cuenta_banco.estado_id = 66 and tmoneda.id = empresa_cuenta_banco.moneda_id and bancos.id = empresa_cuenta_banco.banco_id and 
                                    tcuenta.id = empresa_cuenta_banco.tcuenta_id and empresa.id = empresa_cuenta_banco.empresa_id
                                UNION
                                select  cuenta_banco_inversionista.inversor_id as id, cuenta_banco_inversionista.moneda_id, cuenta_banco_inversionista.banco_id,
                                    cuenta_banco_inversionista.cuenta as nro_cuenta, cuenta_banco_inversionista.tcuenta_id, tmoneda.nombre as moneda_nombre,
                                    bancos.nombre_banco, tcuenta.nombre as tcuenta_nombre, 'INVERSOR' as tipo_persona, 
                                    (inversionista.nombre || ' ' || inversionista.apellido) as nombre 
                                from    cuenta_banco_inversionista, tipos as tmoneda, bancos, tipos as tcuenta, inversionista 
                                where   cuenta_banco_inversionista.estado_id = 65 and tmoneda.id = cuenta_banco_inversionista.moneda_id and 
                                    bancos.id = cuenta_banco_inversionista.banco_id and tcuenta.id = cuenta_banco_inversionista.tcuenta_id and 
                                    inversionista.inversor_id = cuenta_banco_inversionista.inversor_id) as unidas ";

            if ($p_order != '') $v_sql .= " order by ".$p_order;
            if ($p_num_rows > 0) $v_sql .= " limit ".$p_num_rows." offset ".$p_row_ini;

            $idqry = $conn->query($v_sql);
            if (!$idqry) echo pg_last_error($conn->Link_ID);

            $obj = $conn->next_record();
            $v_resultado = array();

            for ($i = 0; $i < $conn->nrows(); $i++){
                $v_resultado[$i] = array(   'id'            =>  $obj->id,           'moneda_id'         =>  $obj->moneda_id,    'banco_id'      =>  $obj->banco_id,
                                            'nro_cuenta'    =>  $obj->nro_cuenta,   'tcuenta_id'        =>  $obj->tcuenta_id,   'moneda_nombre' =>  $obj->moneda_nombre,
                                            'nombre_banco'  =>  $obj->nombre_banco, 'tcuenta_nombre'    =>  $obj->tcuenta_nombre,
                                            'tipo_persona'  =>  $obj->tipo_persona, 'nombre'            =>  $obj->nombre);

                $obj = $conn->next_record();
            }
        }

        $conn->close(); $conn2->close();

        return $v_resultado;
    }

    function aprobar_cuenta_banco_emisor($p_empresa_id, $p_banco_id, $p_nro_cuenta, $p_moneda_id){
        $conn = new db_param_trans; $conn->connect();
        
        $v_sql = "  update empresa_cuenta_banco set estado_id = 68 
                    where empresa_id = ".$p_empresa_id." and moneda_id = ".$p_moneda_id." and banco_id = ".$p_banco_id." and nro_cuenta = '".$p_nro_cuenta."'";

        $idqry = $conn->query($v_sql);
        if (!$idqry){
            echo pg_last_error($conn->Link_ID);
            $v_retorno = -1;
        } else $v_retorno = 1;

        $conn->next_record();

        $conn->close();
        return $v_retorno;
    }

    function rechazar_cuenta_banco_emisor($p_empresa_id, $p_banco_id, $p_nro_cuenta, $p_moneda_id){
        $conn = new db_param_trans; $conn->connect();
        
        $v_sql = "  update empresa_cuenta_banco set estado_id = 67 
                    where empresa_id = ".$p_empresaid." and moneda_id = ".$p_moneda_id." and banco_id = ".$p_banco_id." and nro_cuenta = '".$p_nro_cuenta."'";

        $idqry = $conn->query($v_sql);
        if (!$idqry){
            echo pg_last_error($conn->Link_ID);
            $v_retorno = -1;
        } else $v_retorno = 1;

        $conn->next_record();

        $conn->close();
        return $v_retorno;
    }

    function valida_cuenta_banco_emisor($p_empresa_id, $p_moneda_id){
        $conn = new db_param_trans; $conn->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $v_resultado = 1;
        
        $v_sql = "  select count(1) as contador from empresa_cuenta_banco where empresa_id = ".$p_empresa_id." and moneda_id = ".$p_moneda_id." and estado_id > 0";

        $idqry = $conn->query($v_sql);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        if ($obj->contador > 0){    // EL EMISOR TIENE REGISTRADA SUS CUENTAS BANCARIAS
            $v_sql = "  select count(1) as contador from empresa_cuenta_banco where empresa_id = ".$p_empresa_id." and moneda_id = ".$p_moneda_id." and estado_id = 66";

            if (!$idqry) echo pg_last_error($conn2->Link_ID);
            $obj2 = $conn2->next_record();

            if ($obj->contador == $obj2->contador) $v_resultado = 2;    // RESPONSABILIDAD DEL ANALISTA FINANCIERO
        } else $v_resultado = 3;    // RESPONSABILIDAD DEL EMISOR

        $conn->close(); $conn2->close();

        return $v_resultado;
    }

    //================================================
    //==== integracion con BPD

    function get_secuencia_boton_pago(){
        $conn = new db_param_trans; $conn->connect();

        //@@@@ funcion que devuelve la secuencia de boton de pago

        $idqry = $conn->query("select nextval('s_boton_pago') as numero");

        if (!$idqry) echo pg_last_error($conn->Link_ID);

        $obj = $conn->next_record();

        $v_resultado = $obj->numero;

        $conn->close();

        return $v_resultado;
    }
    //================================================
}

?>