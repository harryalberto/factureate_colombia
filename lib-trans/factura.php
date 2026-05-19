<?
class factura{
    function graba_factura($arrfactura){
        $cfact = new db_param_trans;
        $v_conn_e = new db_param_trans;

        $cfact->connect();
        $v_conn_e->connect();

        if ($arrfactura['accion'] == 'insert'){
            $idqry = $cfact->query("select fact_guarda_factura_V2('insert',0,".$_SESSION['user']['empresaid'].",'".$arrfactura['nrofactura']."','".$arrfactura['femision']."',
                '".$arrfactura['fvencimiento']."',".$arrfactura['clienteid'].",".$arrfactura['monedaid'].",".$arrfactura['subtotal'].",
                ".$arrfactura['anticipos'].",".$arrfactura['descuentos'].",".$arrfactura['valorventa'].",".$arrfactura['impuestoventa'].",
                ".$arrfactura['otroscargos'].",".$arrfactura['otrostributos'].",".$arrfactura['total'].",".$arrfactura['tipofinanciamiento'].",
                ".$arrfactura['maxdescuento'].",'".$arrfactura['pdfpath']."','".$arrfactura['xmlpath']."',".$arrfactura['conmaxdescuento'].",
                ".$_SESSION['user']['usuarioid'].",".$arrfactura['tipo_factura'].") as facturaid");

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $facturaid = $obj->facturaid;
        } elseif ($arrfactura['accion'] == 'update'){
            $idqry = $cfact->query("select fact_guarda_factura('update',".$arrfactura['facturaid'].",".$_SESSION['user']['empresaid'].",'".$arrfactura['nrofactura']."','".$arrfactura['femision']."',
                '".$arrfactura['fvencimiento']."',".$arrfactura['clienteid'].",".$arrfactura['monedaid'].",".$arrfactura['subtotal'].",
                ".$arrfactura['anticipos'].",".$arrfactura['descuentos'].",".$arrfactura['valorventa'].",".$arrfactura['impuestoventa'].",
                ".$arrfactura['otroscargos'].",".$arrfactura['otrostributos'].",".$arrfactura['total'].",".$arrfactura['tipofinanciamiento'].",
                ".$arrfactura['maxdescuento'].",'".$arrfactura['pdfpath']."','".$arrfactura['xmlpath']."',".$arrfactura['conmaxdescuento'].",
                ".$_SESSION['user']['usuarioid'].") as facturaid");
            
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $facturaid = $arrfactura['facturaid'];
        }
            
        $cfact->close();
        return $facturaid;
    }
    function get_datos_factura($facturaid){
        $cfact = new db_param_trans;
        $cfact->connect();

        $idqry = $cfact->query("select * from fact_datos_factura(".$facturaid.")");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $arrfactura = array('factura' => $obj->facnro,                      'femision' => $obj->facfemision,
                            'fvencimiento' => $obj->facfvencimiento,        'clienteid' => $obj->facclienteid,
                            'identificacion' => $obj->facclientenro,        'cliente' => $obj->faccliente,
                            'monedaid' => $obj->facmonedaid,                'moneda' => $obj->facmoneda,
                            'subtotal' => $obj->facsubtotal,                'anticipos' => $obj->facanticipos, 
                            'descuentos' => $obj->facdescuentos,            'valorventa' => $obj->facvalorventa, 
                            'impuestoventa' => $obj->facimpuestoventa,      'otroscargos' => $obj->facotroscargos, 
                            'otrostributos' => $obj->facotrostributos,      'total' => $obj->factotal, 
                            'tipofinanciamiento' => $obj->factfinanciaid,   'tfinancia' => $obj->factfinancia,
                            'condescuentomaximo' => $obj->faccondescuento,  'descuentomaximo' => $obj->facdescuentomax, 
                            'facturapath' => $obj->facfacturapath,          'xmlpath' => $obj->facxmlpath, 
                            'solicitudpath' => $obj->facsolicitudpath,      'acpath' => $obj->facacpath,
                            'rechazopath' => $obj->facrechazopath,          'estado' => $obj->facestadoid,
                            'facestado' => $obj->facestado,                 'financiamientoid' => $obj->facfinanciaid,
                            'estadofinanciamiento' => $obj->facestadofinanciaid, 
                            'facestadofinanciamiento' => $obj->facestadofinancia,
                            'fenvio' => $obj->facfenvio,                    'faprobacion' => $obj->facfaprobacion,
                            'frechazo' => $obj->facfrechazo,                'fanulacion' => $obj->facfanulacion,
                            'fac' => $obj->facfac,                          'fsolicitudac' => $obj->facfsolicitudac,
                            'emisornro' => $obj->facemisornro,              'emisor' => $obj->facemisor,
                            'emisorid' => $obj->facemisorid,                'riesgo' => $obj->facriesgo,
                            'calificacion' => $obj->faccalificacion,        'colorriesgo' => $obj->faccolorriesgo,
                            'motivorechazo' => $obj->facmotivorechazo,      'u_envio_id' => $obj->u_envio_id,
                            'porciento_adelanto' => $obj->porciento_adelanto, 
                            'riesgo_factura_id' => $obj->riesgo_factura_id, 'riesgo_factura_nombre' => $obj->riesgo_factura_nombre, 
                            'riesgo_factura_calificacion' => $obj->riesgo_factura_calificacion,
                            'riesgo_factura_descripcion' => $obj->riesgo_factura_descripcion, 
                            'riesgo_factura_color' => $obj->riesgo_factura_color,
                            'riesgo_factura_color_fuente' => $obj->riesgo_factura_color_fuente,
                            'f_confirmacion' => $obj->fconfirmacion
                        );
                                            
        $cfact->close();
        return $arrfactura;
    }
    function envia_factura($facturaid){
        $cfact = new db_param_trans; $cfact->connect();
        
        $idqry = $cfact->query("select fact_envia_factura(".$facturaid.",".$_SESSION['user']['usuarioid'].") as resultado");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        $resultado = $obj->resultado;

        // -1 si la factura ya esta aprobada    // -2 si se rechazo     // 1 queda en estado de enviada     // 2 aprobacion automatica

        $cfact->close();
        return $resultado;
    }
    function get_facturas_xemisor($emisorid){
        $cfact = new db_param_trans;
        $cfact->connect();
        $arrfacturas = array();
        
        $idqry = $cfact->query("select * from fact_facturas_xemisor(".$emisorid.")");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        for($i = 0; $i < $cfact->nrows(); $i ++){
            $arrfacturas[$i] = array('facturaid'=>$obj->facturaid,'facturanro'=>$obj->facturanro,
                                    'femision' => $obj->facfemision, 'fvencimiento' => $obj->facfvencimiento,
                                    'cliente' => $obj->faccliente, 'clientenro' => $obj->facclientenro,
                                    'monedaid' => $obj->facmonedaid, 'moneda' => $obj->facmoneda,
                                    'total' => $obj->factotal, 'tipofinanciamiento' => $obj->factipofin,
                                    'estado' => $obj->facestado, 'estadofinanciamiento' => $obj->facestadofin,
                                    'estadoid' => $obj->facestadoid, 'estadofinanciamientoid' => $obj->facestadofinid);
            $obj = $cfact->next_record();
        }

        $cfact->close();
        return $arrfacturas;
    }

    function get_facturas_activas_xemisor($p_tipo, $p_rowini, $p_numrows, $p_filtros, $p_order, $p_emisor_id){
        $conn = new db_param_trans; $conn->connect();

        if ($p_tipo == 'COUNT'){
            $v_qry = "  select  count(1) as contador 
                        from    (factura left outer join estados as estadofin on factura.estadofinanciamiento = estadofin.id), empresa, tipos, tipos as tfin, estados 
                        where   factura.emisorid = ".$p_emisor_id." and factura.estado not in (20,17) and
                                factura.estadofinanciamiento not in (19,16,17,20) and empresa.id = factura.clienteid and 
                                tipos.id = factura.monedaid and tfin.id = factura.tipofinanciamiento and estados.id = factura.estado";

            if ($p_filtros != '') $v_qry .= " and ".$p_filtros;

            $idqry = $conn->query($v_qry);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_result = $obj->contador;
        } else {
            $v_qry = "  select  factura.id, factura.numero, factura.femision, factura.fvencimiento, factura.clienteid,
                                empresa.identificacion as numcliente, empresa.nombre as nomcliente,factura.monedaid,
                                tipos.nombre as moneda, factura.total,factura.tipofinanciamiento, tfin.nombre as tipofin,
                                factura.estado, estados.nombre as estadofactura,factura.estadofinanciamiento, 
                                estadofin.nombre as estadofinancia
                        from    (factura left outer join estados as estadofin on factura.estadofinanciamiento = estadofin.id), empresa, tipos, tipos as tfin, estados 
                        where   factura.emisorid = ".$p_emisor_id." and factura.estado not in (20,17) and
                                factura.estadofinanciamiento not in (19,16,17,20) and empresa.id = factura.clienteid and 
                                tipos.id = factura.monedaid and tfin.id = factura.tipofinanciamiento and estados.id = factura.estado";

            if ($p_filtros != '') $v_qry .= " and ".$p_filtros;
            if ($p_order != '') $v_qry .= " ORDER BY ".$p_order;
            if ($p_numrows > 0) $v_qry .= " LIMIT ".$p_numrows." OFFSET ".$p_rowini;

            $idqry = $conn->query($v_qry);
            if (!$idqry) echo pg_last_error($conn->Link_ID);
            $obj = $conn->next_record();
            $v_result = array();

            for($i = 0; $i < $conn->nrows(); $i ++){
                $v_result[$i] = array(  'facturaid'=>$obj->id,          'facturanro'=>$obj->numero,
                                        'femision' => $obj->femision,   'fvencimiento' => $obj->fvencimiento,
                                        'cliente' => $obj->nomcliente,  'clientenro' => $obj->numcliente,
                                        'monedaid' => $obj->monedaid,   'moneda' => $obj->moneda,
                                        'total' => $obj->total,         'tipofinanciamiento' => $obj->tipofinanciamiento,
                                        'estado' => $obj->estadofactura,'estadofinanciamiento' => $obj->estadofinancia,
                                        'estadoid' => $obj->estado,     'estadofinanciamientoid' => $obj->estadofinanciamiento);

                $obj = $conn->next_record();
            }
        }

        $conn->close();

        return $v_result;
    }

    function anular_factura($facturaid){
        $cfact = new db_param_trans;
        $cfact->connect();
        
        $idqry = $cfact->query("select fact_anula_factura(".$facturaid.",".$_SESSION['user']['usuarioid'].")");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        //$obj = $cfact->next_record();
        //$resultado = $obj->resultado;

        $cfact->close();
        return 1;
    }
    function get_facturas_xestado($estadoid,$rows,$rowini,$tipo){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        if ($tipo == 'select'){
            $arrfacturas = array();
            
            if ($_SESSION['user']['tipousuario'] == 3) //emisor
                $idqry = $cfact->query("select * from fact_facturas_xestado_xemp(".$estadoid.",".$rowini.",".$rows.",".$_SESSION['user']['empresaid'].")");
            else
                $idqry = $cfact->query("select * from fact_facturas_xestado(".$estadoid.",".$rowini.",".$rows.")");

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        
            for($i = 0; $i < $cfact->nrows(); $i ++){
                $arrfacturas[$i] = array('facturaid'=>$obj->facturaid,'facturanro'=>$obj->facturanro,
                                    'femision' => $obj->facfemision, 'fvencimiento' => $obj->facfvencimiento,
                                    'emisor' => $obj->facemisor, 'emisornro' => $obj->facemisornro,
                                    'monedaid' => $obj->facmonedaid, 'moneda' => $obj->facmoneda,
                                    'total' => $obj->factotal, 'tipofinanciamiento' => $obj->factipofin,
                                    'estado' => $obj->facestado, 'estadofinanciamiento' => $obj->facestadofin,
                                    'estadoid' => $obj->facestadoid, 'estadofinanciamientoid' => $obj->facestadofinid,
                                    'fenvio' => $obj->facfenvio, 'fsolicitudac' => $obj->facfsolicitudac,
                                    'faprobacion' => $obj->facfaprobacion, 'clienteid' => $obj->clienteid,
                                    'clientenombre' => $obj->clientenombre
                                );
                $obj = $cfact->next_record();
            }
        } else{
            if ($_SESSION['user']['tipousuario'] == 3) // emisor
                $idqry = $cfact->query("select fact_facturas_xestadoxemp_count(".$estadoid.",".$_SESSION['user']['empresaid'].") as contador");
            else 
                $idqry = $cfact->query("select fact_facturas_xestado_count(".$estadoid.") as contador");

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $arrfacturas = $obj->contador;
        }

        $cfact->close();
        return $arrfacturas;
    }
    function get_facturas_xestado_v2($estadoid,$rows,$rowini,$tipo, $p_tipousuario, $p_empresaid){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        if ($tipo == 'select'){
            $arrfacturas = array();
            
            if ($p_tipousuario == 3) //emisor
                $idqry = $cfact->query("select * from fact_facturas_xestado_xemp(".$estadoid.",".$rowini.",".$rows.",".$p_empresaid.")");
            else
                $idqry = $cfact->query("select * from fact_facturas_xestado(".$estadoid.",".$rowini.",".$rows.")");

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        
            for($i = 0; $i < $cfact->nrows(); $i ++){
                $arrfacturas[$i] = array('facturaid'=>$obj->facturaid,'facturanro'=>$obj->facturanro,
                                    'femision' => $obj->facfemision, 'fvencimiento' => $obj->facfvencimiento,
                                    'emisor' => $obj->facemisor, 'emisornro' => $obj->facemisornro,
                                    'monedaid' => $obj->facmonedaid, 'moneda' => $obj->facmoneda,
                                    'total' => $obj->factotal, 'tipofinanciamiento' => $obj->factipofin,
                                    'estado' => $obj->facestado, 'estadofinanciamiento' => $obj->facestadofin,
                                    'estadoid' => $obj->facestadoid, 'estadofinanciamientoid' => $obj->facestadofinid,
                                    'fenvio' => $obj->facfenvio, 'fsolicitudac' => $obj->facfsolicitudac,
                                    'faprobacion' => $obj->facfaprobacion, 'clienteid' => $obj->clienteid,
                                    'clientenombre' => $obj->clientenombre
                                );
                $obj = $cfact->next_record();
            }
        } else{
            if ($p_tipousuario == 3) // emisor
                $idqry = $cfact->query("select fact_facturas_xestadoxemp_count(".$estadoid.",".$p_empresaid.") as contador");
            else 
                $idqry = $cfact->query("select fact_facturas_xestado_count(".$estadoid.") as contador");

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $arrfacturas = $obj->contador;
        }

        $cfact->close();
        return $arrfacturas;
    }
    function aprobar_factura($facturaid){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        $idqry = $cfact->query("select fact_aprobacion_factura(".$facturaid.",".$_SESSION['user']['usuarioid'].",'','','','aprobar')");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $cfact->close();
        return 1;
    }
    function grabar_factura_enrevision($facturaid,$solicitudpath,$acpath){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        $idqry = $cfact->query("select fact_grabar_factura_enrev(".$facturaid.",".$_SESSION['user']['usuarioid'].",'".$acpath."','".$solicitudpath."')");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $cfact->close();
        return 1;
    }
    function upd_riesgo_factura($p_factura_id, $p_riesgo_id, $p_riesgo_desc){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        $idqry = $cfact->query("update factura set riesgofacturaid = ".$p_riesgo_id.", desc_riesgofactura = '".$p_riesgo_desc."' 
                                where id = ".$p_factura_id);

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $cfact->close();
    }
    function anotarencuenta_factura($facturaid,$acpath){
        $cfact = new db_param_trans;
        $cfact->connect();
        
        $idqry = $cfact->query("select fact_aprobacion_factura(".$facturaid.",".$_SESSION['user']['usuarioid'].",'','','".$acpath."','anotarencuenta') as resultado");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        $resultado = $obj->resultado;
        
        $cfact->close();
        return $resultado;
    }
    function rechazar_factura($facturaid,$rechazopath,$motivo){
        $cfact = new db_param_trans;
        $cfact->connect();
            
        $idqry = $cfact->query("select fact_aprobacion_factura(".$facturaid.",".$_SESSION['user']['usuarioid'].",'".$motivo."','".$rechazopath."','','rechazar')");

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $cfact->close();
        return 1;
    }
    function riesgo_factura($facturaid){
        $cfact = new db_param_trans;
        $cfact->connect();
            
        $idqry = $cfact->query("select factura.riesgofacturaid, factura.desc_riesgofactura, tipo_riesgo_empresa.nombre, tipo_riesgo_empresa.calificacion 
                                from factura left outer join tipo_riesgo_empresa on factura.riesgofacturaid = tipo_riesgo_empresa.id 
                                where factura.id = ".$facturaid);

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        $result = array('riesgoid' => $obj->riesgofacturaid, 'desc_riesgo' => $obj->desc_riesgofactura, 
                        'nombre_riesgo' => $obj->nombre, 'calificacion_riesgo' => $obj->calificacion
                    );
        
        $cfact->close();
        return $result;
    }
    function get_datos_financiamiento($p_factura_id){
        $conn_1 = new db_param_trans;
        $conn_1->connect();

        $idqry = $conn_1->query("select * from fact_datos_financiamiento(".$p_factura_id.")");

        if (!$idqry) echo pg_last_error($conn_1->Link_ID);
        $obj = $conn_1->next_record();
        
        $arr_datos = array('facemisor_id'=>$obj->facemisor_id,'facemisor'=>$obj->facemisor,
                                'facemisor_nro'=>$obj->facemisor_nro, 'facfvencimiento'=>$obj->facfvencimiento,
                                'faccliente_id'=>$obj->faccliente_id, 'faccliente'=>$obj->faccliente,
                                'faccliente_nro'=>$obj->faccliente_nro, 'facestado_id'=>$obj->facestado_id,
                                'facestado'=>$obj->facestado, 'financiamiento_id'=>$obj->financiemiento_id,
                                'e_financiamiento_id'=>$obj->e_financiamiento_id, 'e_financiamiento'=>$obj->e_financiamiento,
                                'subasta_id'=>$obj->subasta_id, 'subfcreacion'=>$obj->subfcreacion,
                                'e_subasta_id'=>$obj->e_subasta_id, 'e_subasta'=>$obj->e_subasta,
                                'subporciento_fin'=>$obj->subporciento_fin, 'submonto_fin'=>$obj->submonto_fin,
                                'submonto_rem'=>$obj->submonto_rem, 'facmoneda_id'=>$obj->facmoneda_id,
                                'facmoneda'=>$obj->facmoneda, 'facnumero'=>$obj->facnumero, 'f_financiamiento' => $obj->r_fecha_financiamiento);
        $conn_1->close();
        return $arr_datos;
    }
    function get_count_facturas_xestadoemisor($p_emisor, $p_estado, $p_estadofin){
        $conn_1 = new db_param_trans;
        $conn_1->connect();

        if ($p_estado > 0){
            $idqry = $conn_1->query("select count(1) as contador from factura where emisorid = ".$p_emisor." and estado = ".$p_estado);
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $v_contador = $obj->contador;
            $conn_1->close();
        } else{
            $idqry = $conn_1->query("select count(1) as contador from factura where emisorid = ".$p_emisor." and estadofinanciamiento = ".$p_estadofin." and estado > 0");
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $v_contador = $obj->contador;
            $conn_1->close();
        }

        return $v_contador;
    }
    function get_financiamiento_xemisor($p_emisorid, $p_fechaini, $p_tipo){
        $conn_1 = new db_param_trans;
        $conn_1->connect();

        if ($p_tipo == 'count'){
            $idqry = $conn_1->query("select FINAN_XEMISOR_COUNT(".$p_emisorid.",'".$p_fechaini."') as resultado");

            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $resultado = $obj->resultado;
        
            $conn_1->close();
            return $resultado;
        } else{
            $idqry = $conn_1->query("select * from FINAN_XEMISOR(".$p_emisorid.",'".$p_fechaini."')");
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();

            for($i = 0; $i < $conn_1->nrows(); $i ++){
                $arr_datos[$i] = array('facturaid'=>$obj->facturaid,'facturanro'=>$obj->facturanro,
                                'empidentificacion'=>$obj->empidentificacion, 'empnombre'=>$obj->empnombre,
                                'subid'=>$obj->subid, 'subporcientofin'=>$obj->subporcientofin,
                                'monto_financiado'=>$obj->submontofin, 'monto_remanente'=>$obj->submontorem,
                                'finfregistro'=>$obj->finfregistro, 'moneda_id'=>$obj->moneda_id,
                                'moneda'=>$obj->moneda, 'finan_estado_id'=>$obj->finan_estado_id, 'total_factura' => $obj->r_total_factura, 'moneda_simbolo' => $obj->r_moneda_simbolo,
                                'finan_estado' => $obj->r_estado_finan, 'f_vencimiento' => $obj->r_fecha_vencimiento);
                $obj = $conn_1->next_record();
            }
        
            $conn_1->close();
            return $arr_datos;
        }
    }
    function facturas_indicadores ($p_tipo){
        $conn_1 = new db_param_trans;
        $conn_2 = new db_param_trans;
        $conn_1->connect();
        $conn_2->connect();
        date_default_timezone_set("America/Santo_Domingo");

        if ($p_tipo == 'ENVIADAS'){
            $idqry = $conn_1->query("select count(1) as contador from factura where estado = 12");
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $arr_result['count'] = $obj->contador;
            $idqry = $conn_2->query("SELECT max(current_date - factura.fenvio) as maximo, parametros.valor_num from factura, parametros where parametros.id = 11 and factura.estado = 12 group by parametros.valor_num");
            if (!$idqry) echo pg_last_error($conn_2->Link_ID);
            $obj2 = $conn_2->next_record();
            $arr_result['maximo'] = $obj2->maximo;
            $arr_result['parametro'] = $obj2->valor_num;
        } elseif ($p_tipo == 'XVENCER'){
            $idqry = $conn_1->query("select valor_num from parametros where id = 12");
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $arr_result['parametro'] = $obj->valor_num;
            $idqry = $conn_2->query("select count(1) as contador from factura where estado > 0 and estadofinanciamiento = 15 and (fvencimiento - current_date) <= ".$arr_result['parametro']." and (fvencimiento - current_date) >= 0");
            if (!$idqry) echo pg_last_error($conn_2->Link_ID);
            $obj2 = $conn_2->next_record();
            $arr_result['count'] = $obj2->contador;
        } elseif ($p_tipo == 'VENCIDAS'){
            $idqry = $conn_1->query("select count(1) as contador from factura where estadofinanciamiento = 15 and estado > 0 and (current_date - fvencimiento) > 0");
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $arr_result['count'] = $obj->contador;
        }

        $conn_1->close();
        $conn_2->close();
        return $arr_result;
    }
    function relacion_facturas_indicador($p_tipo){
        $conn_1 = new db_param_trans;
        $conn_2 = new db_param_trans;
        $conn_1->connect();
        $conn_2->connect();
        $arr_result = array();

        if ($p_tipo == 'XVENCER'){
            $idqry = $conn_1->query("select valor_num from parametros where id = 12");  // dias por vencer
            if (!$idqry) echo pg_last_error($conn_1->Link_ID);
            $obj = $conn_1->next_record();
            $v_dias_xvencer = $obj->valor_num;
            $idqry = $conn_2->query("select factura.id as facturaid, factura.numero as facturanro, factura.femision, factura.fvencimiento, factura.clienteid, empresa.nombre as cliente,
                                        factura.monedaid, tipos.nombre as moneda, factura.total as monto_factura, financiamiento.monto_financiado, 
                                        financiamiento.estado as estadofin_id, estados.nombre as estadofin, tipo_riesgo_empresa.nombre as riesgo,
                                        tipo_riesgo_empresa.calificacion, factura.confirma_pago 
                                    from factura, empresa, tipos, financiamiento, estados, riesgo_empresa, tipo_riesgo_empresa 
                                    where factura.estadofinanciamiento = 15 and (factura.fvencimiento - current_date) <= ".$v_dias_xvencer." and 
                                        (factura.fvencimiento - current_date) >= 0 and empresa.id = factura.clienteid and tipos.id = factura.monedaid and 
                                        financiamiento.facturaid = factura.id and estados.id = financiamiento.estado and 
                                        riesgo_empresa.empresaid = factura.clienteid and riesgo_empresa.estado = 1 and tipo_riesgo_empresa.id = riesgo_empresa.riesgoid");
            if (!$idqry) echo pg_last_error($conn_2->Link_ID);
            $obj2 = $conn_2->next_record();
            
            for($i = 0; $i < $conn_2->nrows(); $i ++){
                $arr_result[$i] = array('facturaid'=>$obj2->facturaid,'facturanro'=>$obj2->facturanro, 'femision'=>$obj2->femision, 'fvencimiento'=>$obj2->fvencimiento,
                                        'clienteid'=>$obj2->clienteid, 'cliente'=>$obj2->cliente, 'monedaid'=>$obj2->monedaid, 'moneda'=>$obj2->moneda, 
                                        'monto_factura'=>$obj2->monto_factura, 'monto_financiado'=>$obj2->monto_financiado, 'estadofin_id'=>$obj2->estadofin_id, 
                                        'estadofin'=>$obj2->estadofin, 'riesgo'=>$obj2->riesgo, 'calificacion'=>$obj2->calificacion, 'confirma_pago'=>$obj2->confirma_pago
                                );
                $obj2 = $conn_2->next_record();
            }
        }

        $conn_1->close();
        $conn_2->close();
        return $arr_result;
    }
    function comunica_pagador($p_empresaid, $p_facturaid){
        $conn_1 = new db_param_trans;
        $conn_1->connect();
        $arr_result = array();

        $idqry = $conn_1->query("select empresa_comunicacion.id, empresa_comunicacion.fecha, empresa_comunicacion.hora, empresa_comunicacion.comunicacion, 
                                    empresa_comunicacion.nombre_comunica
                                from empresa_comunicacion  
                                where empresa_comunicacion.empresa_id = ".$p_empresaid." and empresa_comunicacion.factura_id = ".$p_facturaid." and empresa_comunicacion.estado = 1 
                                order by empresa_comunicacion.id");
        if (!$idqry) echo pg_last_error($conn_1->Link_ID);
        $obj = $conn_1->next_record();

        for($i = 0; $i < $conn_1->nrows(); $i ++){
            $arr_result[$i] = array('comunica_id'=>$obj->id, 'fcomunica'=>$obj->fecha, 'hcomunica'=>$obj->hora, 'comunicacion'=>$obj->comunicacion, 'nombre'=>$obj->nombre_comunica);
            $obj = $conn_1->next_record();
        }

        $conn_1->close();
        return $arr_result;
    }
    function registra_comunicacion($p_empresaid, $p_facturaid, $p_mensaje, $p_nombre){
        $conn_1 = new db_param_trans;
        $conn_1->connect();
        
        $idqry = $conn_1->query("select FAC_REGISTRA_COMUNICACION(".$p_empresaid.",".$p_facturaid.",'".$p_mensaje."','".$p_nombre."') as comu_id");
        if (!$idqry) echo pg_last_error($conn_1->Link_ID);
        $obj = $conn_1->next_record();
        $resultado = $obj->comu_id;

        $conn_1->close();
        return $resultado;
    }
    function confirma_pago($p_facturaid, $p_fpago){
        $conn_1 = new db_param_trans;
        $conn_1->connect();
        
        $idqry = $conn_1->query("update factura set confirma_pago = 'SI', f_confirmacion = '".$p_fpago."' where id = ".$p_facturaid);
        if (!$idqry) echo pg_last_error($conn_1->Link_ID);
        $obj = $conn_1->next_record();
        
        $conn_1->close();
        return 1;
    }
    /*==============================================================
    FINANCIAMIENTO 
    ===============================================================*/
    function get_financiamiento_xestado($p_estado_id,$rows,$rowini,$tipo){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        if ($tipo == 'select'){
            $v_arr_result = array();
            
            $idqry = $cfact->query("select financiamiento.id, financiamiento.facturaid, financiamiento.monto_financiado, financiamiento.monto_remanente, 
                                        financiamiento.fpago_efectivo, financiamiento.monedaid, financiamiento.monto_remanente_e, financiamiento.fpago, 
                                        financiamiento.dias_retraso, financiamiento.fregistro, tipos.nombre as moneda, factura.numero, factura.total as monto_factura, 
                                        factura.confirma_pago, factura.f_confirmacion, emisor.nombre as emisor_nombre, emisor.identificacion as emisor_numero, 
                                        factura.emisorid, cliente.nombre as cliente_nombre, cliente.identificacion as cliente_numero, factura.clienteid
                                    from financiamiento, tipos, factura, empresa as emisor, empresa as cliente 
                                    where tipos.id = financiamiento.monedaid and factura.id = financiamiento.facturaid and 
                                        emisor.id = factura.emisorid and cliente.id = factura.clienteid and financiamiento.estado = ".$p_estado_id);

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        
            for($i = 0; $i < $cfact->nrows(); $i ++){
                $v_arr_result[$i] = array('finan_id'=>$obj->id,'factura_id'=>$obj->facturaid, 'monto_financiado'=>$obj->monto_financiado,
                                    'monto_remanente' => $obj->monto_remanente, 'fpago_efectivo' => $obj->fpago_efectivo,
                                    'moneda_id' => $obj->monedaid, 'monto_remanente_e' => $obj->monto_remanente_e, 'fpago' => $obj->fpago,
                                    'dias_retraso' => $obj->dias_retraso, 'fregistro_finan' => $obj->fregistro, 'moneda' => $obj->moneda,
                                    'factura_numero' => $obj->numero, 'monto_factura' => $obj->monto_factura, 'confirma_pago' => $obj->confirma_pago,
                                    'f_confirmacion' => $obj->f_confirmacion, 'emisor_nombre' => $obj->emisor_nombre,
                                    'emisor_numero' => $obj->emisor_numero, 'emisor_id' => $obj->emisorid, 'cliente_nombre' => $obj->cliente_nombre,
                                    'cliente_numero' => $obj->cliente_numero, 'cliente_id' => $obj->clienteid);
                $obj = $cfact->next_record();
            }
        } else{
            $idqry = $cfact->query("select count(1) as contador from financiamiento where financiamiento.estado = ".$p_estado_id);

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $v_arr_result = $obj->contador;
        }

        $cfact->close();
        return $v_arr_result;
    }
    function get_financiamiento_xestado_varios($p_estados,$rows,$rowini,$tipo){
        $cfact = new db_param_trans;
        $cfact->connect();
                
        if ($tipo == 'select'){
            $v_arr_result = array();
            $idqry = $cfact->query("select financiamiento.id, financiamiento.facturaid, financiamiento.monto_financiado, financiamiento.monto_remanente, 
                                        financiamiento.fpago_efectivo, financiamiento.monedaid, financiamiento.monto_remanente_e, financiamiento.fpago, 
                                        financiamiento.dias_retraso, financiamiento.fregistro, tipos.nombre as moneda, factura.numero, factura.total as monto_factura, 
                                        factura.confirma_pago, factura.f_confirmacion, emisor.nombre as emisor_nombre, emisor.identificacion as emisor_numero, 
                                        factura.emisorid, cliente.nombre as cliente_nombre, cliente.identificacion as cliente_numero, factura.clienteid,
                                        financiamiento.estado as estado_finan_id, estados.nombre as estado_finan 
                                    from financiamiento, tipos, factura, empresa as emisor, empresa as cliente, estados 
                                    where tipos.id = financiamiento.monedaid and factura.id = financiamiento.facturaid and estados.id = financiamiento.estado and 
                                        emisor.id = factura.emisorid and cliente.id = factura.clienteid and financiamiento.estado in (".$p_estados.") 
                                        order by financiamiento.id limit ".$rows." offset ".$rowini);

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        
            for($i = 0; $i < $cfact->nrows(); $i ++){
                $v_arr_result[$i] = array('finan_id'=>$obj->id,'factura_id'=>$obj->facturaid, 'monto_financiado'=>$obj->monto_financiado,
                                    'monto_remanente' => $obj->monto_remanente, 'fpago_efectivo' => $obj->fpago_efectivo,
                                    'moneda_id' => $obj->monedaid, 'monto_remanente_e' => $obj->monto_remanente_e, 'fpago' => $obj->fpago,
                                    'dias_retraso' => $obj->dias_retraso, 'fregistro_finan' => $obj->fregistro, 'moneda' => $obj->moneda,
                                    'factura_numero' => $obj->numero, 'monto_factura' => $obj->monto_factura, 'confirma_pago' => $obj->confirma_pago,
                                    'f_confirmacion' => $obj->f_confirmacion, 'emisor_nombre' => $obj->emisor_nombre,
                                    'emisor_numero' => $obj->emisor_numero, 'emisor_id' => $obj->emisorid, 'cliente_nombre' => $obj->cliente_nombre,
                                    'cliente_numero' => $obj->cliente_numero, 'cliente_id' => $obj->clienteid, 'estado_finan_id' => $obj->estado_finan_id,
                                    'estado_finan' => $obj->estado_finan);
                $obj = $cfact->next_record();
            }
        } else{
            $idqry = $cfact->query("select count(1) as contador from financiamiento where financiamiento.estado in (".$p_estados.")");

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $v_arr_result = $obj->contador;
        }

        $cfact->close();
        return $v_arr_result;
    }

    function get_financiamiento_detalle($p_finan_id){
        $cfact = new db_param_trans; $cfact->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        
        $idqry = $cfact->query("select      financiamiento.id, financiamiento.facturaid, financiamiento.monto_financiado, financiamiento.monto_remanente, 
                                            financiamiento.fpago_efectivo, financiamiento.monedaid, financiamiento.monto_remanente_e, financiamiento.fpago, 
                                            financiamiento.dias_retraso, financiamiento.fregistro, tipos.nombre as moneda, factura.numero, factura.total as monto_factura, 
                                            factura.confirma_pago, factura.f_confirmacion, emisor.nombre as emisor_nombre, emisor.identificacion as emisor_numero, 
                                            factura.emisorid, cliente.nombre as cliente_nombre, cliente.identificacion as cliente_numero, factura.clienteid,
                                            financiamiento.estado, tipos.dato1 as simbolo, financiamiento.notifica_op, financiamiento.notifica_comprobante,
                                            financiamiento.ganancia_e, financiamiento.comision_factureate_e 
                                    from    financiamiento, tipos, factura, empresa as emisor, empresa as cliente 
                                    where   tipos.id = financiamiento.monedaid and factura.id = financiamiento.facturaid and 
                                            emisor.id = factura.emisorid and cliente.id = factura.clienteid and financiamiento.id = ".$p_finan_id);

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();

        $idqry = $conn2->query("select coalesce(sum(pagos_op.monto),0) as total from pagos_op, financiamiento
                                where pagos_op.estado_id = 39 and pagos_op.id_instrumento = financiamiento.facturaid and financiamiento.id = ".$p_finan_id);
        if (!$idqry) echo pg_last_error($conn2->Link_ID);
        $obj2 = $conn2->next_record();
        
        $v_arr_result = array(      'finan_id'=>$obj->id,                       'factura_id'=>$obj->facturaid,              'monto_financiado'=>$obj->monto_financiado,
                                    'monto_remanente' => $obj->monto_remanente, 'fpago_efectivo' => $obj->fpago_efectivo,   'moneda_id' => $obj->monedaid, 
                                    'monto_remanente_e' => $obj->monto_remanente_e,                                         'fpago' => $obj->fpago,
                                    'dias_retraso' => $obj->dias_retraso,       'fregistro_finan' => $obj->fregistro,       'moneda' => $obj->moneda,
                                    'factura_numero' => $obj->numero,           'monto_factura' => $obj->monto_factura,     'confirma_pago' => $obj->confirma_pago,
                                    'f_confirmacion' => $obj->f_confirmacion,   'emisor_nombre' => $obj->emisor_nombre,     'emisor_numero' => $obj->emisor_numero, 
                                    'emisor_id' => $obj->emisorid,              'cliente_nombre' => $obj->cliente_nombre,   'cliente_numero' => $obj->cliente_numero, 
                                    'cliente_id' => $obj->clienteid,            'estado_finan_id'=>$obj->estado,            'simbolo'=>$obj->simbolo, 
                                    'monto_pagado'=>$obj2->total,               'notifica_op' => $obj->notifica_op,         'notifica_comprobante' => $obj->notifica_comprobante,
                                    'ganancia' => $obj->ganancia_e,             'comision_factureate' => $obj->comision_factureate_e);
        
        $cfact->close();
        $conn2->close();
        return $v_arr_result;
    }
    function get_financiamiento_xfactura($p_factura_id){
        $cfact = new db_param_trans;
        $cfact->connect();
        
        $idqry = $cfact->query("select financiamiento.id
                                    from financiamiento 
                                    where financiamiento.facturaid = ".$p_factura_id);

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $v_arr_result = array('finan_id'=>$obj->id);
        
        $cfact->close();
        return $v_arr_result;
    }
    function registra_pago ($p_factura_id,$p_operacion_nro, $p_moneda_id, $p_monto, $p_fecha, $p_hora){
        //******* registro del pago realizado por el OP de forma manual, es decir, desde la plataforma por el ANALISTA FINANCIERO
        $cfact = new db_param_trans;
        $cfact->connect();
        
        $idqry = $cfact->query("select FINAN_REGISTRA_PAGO_OP(".$p_factura_id.",'".$p_operacion_nro."',".$p_moneda_id.",".$p_monto.",'".$p_fecha."','".$p_hora."') as resultado");
        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();
        
        $cfact->close();
        
        return 1;
    }
    function liquida_financiamiento ($p_factura_id, $p_moneda_id, $p_monto, $p_datosop, $p_tipo_mov){
        $cfact = new db_param_trans; $cfact->connect();
        $conn1 = new db_param_trans; $conn1->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $v_return = 1;
        
        $idqry = $conn1->query("select valor_num from parametros where id = 35");
        if (!$idqry) echo pg_last_error($conn1->Link_ID);
        $obj1 = $conn1->next_record();

        if ($obj1->valor_num = 0){      // NO EXISTE INTEGRACION CON EL BANCO
            // LIQUIDACION DEL FINANCIAMIENTO
            $idqry = $cfact->query("select FINAN_LIQUIDA_FINAN(".$p_factura_id.") as resultado");
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        } else{
            $idqry = $cfact->query("select FINAN_LIQUIDA_FINAN(".$p_factura_id.") as resultado");
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        }
        
        $cfact->close(); $conn1->close(); $conn2->close();
        
        return $v_return;
    }
    function carga_factura_electronica($p_path){
        //========== lectura del archivo de factura electronica y resultado en un arreglo ============
        $v_arr_result = array('nro_factura'=>'E001-29042023', 'f_emision'=>'2023-04-25', 'f_vencimiento'=>'2023-07-20', 'cliente_nro'=>'ID-03042023-4',
                                'cliente'=>'AFP LOS FONDOS SRL', 'moneda_id'=>21, 'subtotal'=>1000, 'anticipos'=>0, 'descuentos'=>0, 'valor_venta'=>1000,
                                'impuesto_venta'=>180, 'otros_cargos'=>0, 'otros_tributos'=>0, 'total'=>1180, 'cliente_id'=>49);
        // debe validar la existencia de la factura en la direccion de impuestos
        $v_arr_result['factura_valida'] = 1;
        // debe validar los montos de las facturas
        $v_arr_result['montos_validos'] = 1;
        // validar que la factura este confirmada
        $v_arr_result['factura_comfirmada'] = 1;
        // validar que la factura no este endosada
        $v_arr_result['factura_endosada'] = 0;
        return $v_arr_result;
    }
    function get_riesgo_op($p_arr){
        $conn = new db_param_trans;
        $conn->connect();

        if ($p_arr['id'] != 0) 
            $idqry = $conn->query("select riesgo_empresa.riesgoid, riesgo_empresa.riesgoscoreid, tipo_riesgo_empresa.porc_financiamiento, tipo_riesgo_empresa.calificacion 
                                    from riesgo_empresa, tipo_riesgo_empresa 
                                    where riesgo_empresa.empresaid = ".$p_arr['id']." and riesgo_empresa.estado = 1 and tipo_riesgo_empresa.id = riesgo_empresa.riesgoid");
        else{ 
            $idqry = $conn->query("select riesgo_empresa.riesgoid, riesgo_empresa.riesgoscoreid, tipo_riesgo_empresa.porc_financiamiento, tipo_riesgo_empresa.calificacion 
                                    from riesgo_empresa, tipo_riesgo_empresa, empresa 
                                    where empresa.identificacion = '".$p_arr['identificacion']."' and riesgo_empresa.empresaid = empresa.id and riesgo_empresa.estado = 1 and 
                                    tipo_riesgo_empresa.id = riesgo_empresa.riesgoid");
        }
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_arr = array('riesgo_factureate'=>$obj->riesgoid, 'riesgo_score'=>$obj->riesgoscoreid, 'porc_financiamiento'=>$obj->porc_financiamiento, 'calificacion'=>$obj->calificacion);
        $conn->close();

        return $v_arr;
    }

    function get_evaluacion_factura($p_factura_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select factura_evaluacion.evaluacion_tipo, tipos.nombre as evaluacion_tipo_nom, factura_evaluacion.f_evaluacion, factura_evaluacion.calificacion 
                                from factura_evaluacion, tipos 
                                where tipos.id = factura_evaluacion.evaluacion_tipo and factura_evaluacion.factura_id = ".$p_factura_id);
        
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_arr = array();
        
        for($i = 0; $i < $conn->nrows(); $i ++){
            $v_arr[$i] = array('evaluacion_tipo' => $obj->evaluacion_tipo, 'evaluacion_tipo_nom' => $obj->evaluacion_tipo_nom, 
                                'f_evaluacion' => $obj->f_evaluacion, 'calificacion' => $obj->calificacion);
            $obj = $conn->next_record();
        }

        $conn->close();

        return $v_arr;
    }

    function preliquida_financiamiento($p_factura_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update financiamiento set estado = 51 where facturaid = ".$p_factura_id." and estado > 0");
        
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $conn->close();

        return $v_arr;
    }

    function get_tea_maxima($p_factura_id){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select CALCULA_TASA_MAXIMA_RESTRICCION(".$p_factura_id.") as tea");
        
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $v_tea = $obj->tea;

        $conn->close();

        return $v_tea;
    }

    function graba_comprobante_libertad($p_factura_id, $p_file){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("update factura set factura_libre = '".$p_file."' where id = ".$p_factura_id);
        
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        
        $conn->close();
    }

    function get_financiamientos($p_tipo,$p_rows,$p_rowini,$p_filtros,$p_order){   //$p_estados,$rows,$rowini,$tipo
        $cfact = new db_param_trans; $cfact->connect();
                
        if ($p_tipo == 'SELECT'){
            $varr_result = array();

            $v_qry = "select financiamiento.id, financiamiento.facturaid, financiamiento.monto_financiado, financiamiento.monto_remanente, 
                            financiamiento.fpago_efectivo, financiamiento.monedaid, financiamiento.monto_remanente_e, financiamiento.fpago, 
                            financiamiento.dias_retraso, financiamiento.fregistro, tipos.nombre as moneda, factura.numero, factura.total as monto_factura, 
                            factura.confirma_pago, factura.f_confirmacion, emisor.nombre as emisor_nombre, emisor.identificacion as emisor_numero, 
                            factura.emisorid, cliente.nombre as cliente_nombre, cliente.identificacion as cliente_numero, factura.clienteid,
                            financiamiento.estado as estado_finan_id, estados.nombre as estado_finan, financiamiento.notifica_op 
                        from financiamiento, tipos, factura, empresa as emisor, empresa as cliente, estados 
                        where tipos.id = financiamiento.monedaid and factura.id = financiamiento.facturaid and estados.id = financiamiento.estado and 
                            emisor.id = factura.emisorid and cliente.id = factura.clienteid";

            if ($p_filtros != '') $v_qry .= " and ".$p_filtros;
            if ($p_order != '') $v_qry .= " order by ".$p_order;
            if ($p_rows > 0) $v_qry .= " limit ".$p_rows." offset ".$p_rowini;

            $idqry = $cfact->query($v_qry);

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        
            for($i = 0; $i < $cfact->nrows(); $i ++){
                $varr_result[$i] = array('finan_id'=>$obj->id,                 'factura_id'=>$obj->facturaid,                  'monto_financiado'=>$obj->monto_financiado,
                                    'monto_remanente' => $obj->monto_remanente, 'fpago_efectivo' => $obj->fpago_efectivo,
                                    'moneda_id' => $obj->monedaid,              'monto_remanente_e' => $obj->monto_remanente_e, 'fpago' => $obj->fpago,
                                    'dias_retraso' => $obj->dias_retraso,       'fregistro_finan' => $obj->fregistro,           'moneda' => $obj->moneda,
                                    'factura_numero' => $obj->numero,           'monto_factura' => $obj->monto_factura,         'confirma_pago' => $obj->confirma_pago,
                                    'f_confirmacion' => $obj->f_confirmacion,   'emisor_nombre' => $obj->emisor_nombre,
                                    'emisor_numero' => $obj->emisor_numero,     'emisor_id' => $obj->emisorid,                  'cliente_nombre' => $obj->cliente_nombre,
                                    'cliente_numero' => $obj->cliente_numero,   'cliente_id' => $obj->clienteid,                'estado_finan_id' => $obj->estado_finan_id,
                                    'estado_finan' => $obj->estado_finan,       'notifica_op' => $obj->notifica_op);
                $obj = $cfact->next_record();
            }
        } else{
            $v_qry = "select count(1) as contador from financiamiento, factura where financiamiento.facturaid = factura.id";

            if ($p_filtros != '') $v_qry .= " and ".$p_filtros;
            $idqry = $cfact->query($v_qry);

            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $varr_result = $obj->contador;
        }

        $cfact->close();
        return $varr_result;
    }

    function registra_notificacion_op($p_finan_id, $p_file_path){
        $cfact = new db_param_trans; $cfact->connect();

        $idqry = $cfact->query("update financiamiento set notifica_op = 1, notifica_comprobante = '".$p_file_path."' where id = ".$p_finan_id);

        if (!$idqry) echo pg_last_error($cfact->Link_ID);
        $obj = $cfact->next_record();

        $cfact->close();
    }

    function liquida_financiamiento_v2 ($p_factura_id, $p_moneda_id, $p_monto, $p_datosop, $p_tipo_mov, $p_fecha_pago, $p_hora_pago){
        $cfact = new db_param_trans; $cfact->connect();
        $conn1 = new db_param_trans; $conn1->connect();
        $conn2 = new db_param_trans; $conn2->connect();
        $v_return = 1;

        if ($p_monto > 0){
            $this->registra_pago($p_factura_id,$p_datosop,$p_moneda_id,$p_monto,$p_fecha_pago,$p_hora_pago);
        }
        
        $idqry = $conn1->query("select valor_num from parametros where id = 35");
        if (!$idqry) echo pg_last_error($conn1->Link_ID);
        $obj1 = $conn1->next_record();

        if ($obj1->valor_num = 0){      // NO EXISTE INTEGRACION CON EL BANCO
            // LIQUIDACION DEL FINANCIAMIENTO
            $idqry = $cfact->query("select FINAN_LIQUIDA_FINAN(".$p_factura_id.") as resultado");
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        } else{
            $idqry = $cfact->query("select FINAN_LIQUIDA_FINAN(".$p_factura_id.") as resultado");
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        }
        
        $cfact->close(); $conn1->close(); $conn2->close();
        
        return $v_return;
    }

    function preliquida_financiamiento_v2($p_factura_id, $p_moneda_id, $p_monto, $p_datosop, $p_fecha_pago, $p_hora_pago){
        $conn = new db_param_trans; $conn->connect();

        if ($p_monto > 0){
            $this->registra_pago($p_factura_id,$p_datosop,$p_moneda_id,$p_monto,$p_fecha_pago,$p_hora_pago);
        }

        $idqry = $conn->query("update financiamiento set estado = 51 where facturaid = ".$p_factura_id." and estado > 0");
        
        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();

        $conn->close();

        return $v_arr;
    }

    //============== 16-12-2025

    function get_facturas($p_tipo, $p_rowini, $p_rowcount, $p_filtros, $p_order){
        $cfact = new db_param_trans; $cfact->connect();
                
        if ($p_tipo == 'SELECT'){
            $arrfacturas = array();
            
            $v_sql = "  select  factura.id, factura.numero, factura.femision, factura.fvencimiento, factura.emisorid,
                                empresa.identificacion as numemisor, empresa.nombre as nomemisor,factura.monedaid,
                                tipos.nombre as moneda, factura.total,factura.tipofinanciamiento, tfin.nombre as tipofin,
                                factura.estado, estados.nombre as estadofactura,factura.estadofinanciamiento, 
                                estadofin.nombre as estadofinancia, factura.fenvio, factura.fsolicitudac,
                                factura.faprobacion, factura.clienteid, cliente.nombre as nombrecliente
                    from        (factura left outer join estados as estadofin on factura.estadofinanciamiento = estadofin.id), 
                                empresa, tipos, tipos as tfin, estados, empresa as cliente 
                    where       empresa.id = factura.emisorid and tipos.id = factura.monedaid and tfin.id = factura.tipofinanciamiento and 
                                estados.id = factura.estado and cliente.id = factura.clienteid";

            if ($p_filtros != "") $v_sql .= " and ".$p_filtros;
            if ($p_order != "") $v_sql .= " order by ".$p_order;
            if ($p_rowcount > 0) $v_sql .= " limit ".$p_rowcount." offset ".$p_rowini;

            $idqry = $cfact->query($v_sql);
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
        
            for($i = 0; $i < $cfact->nrows(); $i ++){
                $arrfacturas[$i] = array(   'facturaid'=>$obj->id,              'facturanro'=>$obj->numero,
                                            'femision' => $obj->femision,       'fvencimiento' => $obj->fvencimiento,
                                            'emisor' => $obj->nomemisor,        'emisornro' => $obj->numemisor,
                                            'monedaid' => $obj->monedaid,       'moneda' => $obj->moneda,
                                            'total' => $obj->total,             'tipofinanciamiento' => $obj->tipofin,
                                            'estado' => $obj->estadofactura,    'estadofinanciamiento' => $obj->estadofinancia,
                                            'estadoid' => $obj->estado,         'estadofinanciamientoid' => $obj->estadofinanciamiento,
                                            'fenvio' => $obj->fenvio,           'fsolicitudac' => $obj->fsolicitudac,
                                            'faprobacion' => $obj->faprobacion, 'clienteid' => $obj->clienteid,
                                            'clientenombre' => $obj->nombrecliente
                                );
                $obj = $cfact->next_record();
            }
        } else{
            $v_sql = "  select  count(1) as contador 
                        from    factura 
                        where   factura.estado > 0 ";

            if ($p_filtros!= "") $v_sql .= " and ".$p_filtros;

            $idqry = $cfact->query($v_sql);
            if (!$idqry) echo pg_last_error($cfact->Link_ID);
            $obj = $cfact->next_record();
            $arrfacturas = $obj->contador;
        }

        $cfact->close();
        return $arrfacturas;
    }

    function valida_param_factura($p_tipo, $p_numero, $p_cliente_id, $p_emisor_id, $p_duracion, $p_moneda_id, $p_monto){
        $conn = new db_param_trans; $conn->connect();

        $idqry = $conn->query("select fact_valida_param_factura('".$p_tipo."', '".$p_numero."', ".$p_cliente_id.", ".$p_emisor_id.", ".$p_duracion.", ".$p_moneda_id.", ".$p_monto.") as resultado");

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $resultado = $obj->resultado;

        $conn->close();

        return $resultado;
    }

    function get_proveedores_finan_xcliente ($p_cliente_id){
        $conn = new db_param_trans; $conn->connect();

        $qry = "select  distinct factura.emisorid, empresa.nombre as emisor
                from    factura, empresa, financiamiento
                where   financiamiento.estado not in (28,29,37) and factura.id = financiamiento.facturaid and factura.clienteid = ".$p_cliente_id." and
                        empresa.id = factura.emisorid
                order by empresa.nombre";

        $idqry = $conn->query($qry);

        if (!$idqry) echo pg_last_error($conn->Link_ID);
        $obj = $conn->next_record();
        $varr_result = array();

        for($i = 0; $i < $conn->nrows(); $i ++){
            $varr_result[$i] = array('emisor_id' => $obj->emisorid, 'emisor' => $obj->emisor);
            $obj = $conn->next_record();
        }

        $conn->close();

        return $varr_result;
    }


}
?>