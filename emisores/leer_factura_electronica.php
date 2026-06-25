<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

$vobj_factura_proc = new factura;
$vobj_mae_proc = new maestros;

if(isset($_FILES['xml'])){
    $tmp = $_FILES['xml']['tmp_name'];
    libxml_use_internal_errors(true);

    $xml = simplexml_load_file($tmp);

    if($xml === false){
        echo 0;
    } else {
        $encabezado = $xml->Encabezado;
        $v_procede = 1;
        $v_encontrado = 0;

        //HEADER
        $v_tipodoc = (integer)$encabezado->IdDoc->TipoeCF;  // 31 es factura de credito fiscal, 44 acogidas a regimen especial, 45 para ventas al gobierno, 46 exportacion

        // // validacion del tipo de documento
        $varr_tipodoc_elec = $vobj_mae_proc->get_parametro_detalle(72);
        $varr_permitidos = $vobj_mae_proc->get_tipos_documentos_elec($varr_tipodoc_elec['valorchar']);

        for ($i = 0; $i < count($varr_permitidos); $i++){
            if ($v_tipodoc == $varr_permitidos[$i]) $v_encontrado = 1;
        }

        if ($v_encontrado == 0) $v_procede = 0;

        if ($v_procede == 1){
            // CONTINUACION DE HEADER
            $v_nro_doc = (string)$encabezado->IdDoc->eNCF;
            $v_femision = (string)$encabezado->Emisor->FechaEmision; //dd-mm-yyyy
            $v_fvencimiento = (string)$encabezado->IdDoc->FechaLimitePago;    //dd-mm-yyyy

            $v_femision_dt = DateTime::createFromFormat('d-m-Y', $v_femision);
            $v_femision_eng = $v_femision_dt->format('Y-m-d');
            //$v_femision_eng = date("Y-m-d", strtotime(str_replace("-", "/", $v_femision)));

            $v_fvencimiento_dt = DateTime::createFromFormat('d-m-Y', $v_fvencimiento);
            $v_fvencimiento_eng = $v_fvencimiento_dt->format('Y-m-d');

            //EMISOR
            $v_emisor_rnc = (string)$encabezado->Emisor->RNCEmisor;
            $v_emisor_nom = (string)$encabezado->Emisor->RazonSocialEmisor;

            //CLIENTE
            $v_cliente_rnc =(string)$encabezado->Comprador->RNCComprador;
            $v_cliente_nom = (string)$encabezado->Comprador->RazonSocialComprador;

            if (isset($encabezado->Comprador->CorreoComprador)) $v_email_cliente = $encabezado->Comprador->CorreoComprador;
            else $v_email_cliente = '';

            if (isset($encabezado->Comprador->DireccionComprador)) $v_dir_cliente = $encabezado->Comprador->DireccionComprador;
            else $v_dir_cliente = '';

            if (isset($encabezado->Comprador->ContactoComprador)) $v_nombre_contacto_cliente = $encabezado->Comprador->ContactoComprador;
            else $v_nombre_contacto_cliente = '';

            //TOTALES
            $v_monto_total = (float)$encabezado->Totales->MontoTotal;
            $v_itbis = (float)$encabezado->Totales->TotalITBIS;

            if (isset($encabezado->Totales->MontoImpuestoAdicional)) $v_monto_impadicional = $encabezado->Totales->MontoImpuestoAdicional;
            else $v_monto_impadicional = 0;

            if (isset($encabezado->Totales->OtrosImpuestosAdicionales)) $v_monto_otrosimpadicional = $encabezado->Totales->OtrosImpuestosAdicionales;
            else $v_monto_otrosimpadicional = 0;

            $v_otros_impuestos = $v_monto_impadicional + $v_monto_otrosimpadicional;

            if (isset($encabezado->Totales->MontoAvancePago)) $v_monto_adelanto = $encabezado->Totales->MontoAvancePago;
            else $v_monto_adelanto = 0;

            if (isset($encabezado->Totales->TotalITBISRetenido)) $v_itbis_retenido = $encabezado->Totales->TotalITBISRetenido;
            else $v_itbis_retenido = 0;

            if (isset($encabezado->Totales->TotalISRRetencion)) $v_isr_retenido = $encabezado->Totales->TotalISRRetencion;
            else $v_isr_retenido = 0;

            if (isset($encabezado->Totales->TotalITBISPercepcion)) $v_itbis_percepcion = $encabezado->Totales->TotalITBISPercepcion;
            else $v_itbis_percepcion = 0;

            if (isset($encabezado->Totales->TotalISRPercepcion)) $v_isr_percepcion = $encabezado->Totales->TotalISRPercepcion;
            else $v_isr_percepcion = 0;

            $v_total_retenciones = $v_itbis_retenido + $v_isr_retenido + $v_itbis_percepcion + $v_isr_percepcion;
            $v_total_financiar = $v_monto_total - $v_monto_adelanto - $v_total_retenciones;
            $v_subtotal = $v_monto_total - $v_itbis - $v_otros_impuestos + $v_monto_adelanto;
            $v_valor_venta = $v_subtotal - $v_monto_adelanto;
            
            //MONEDA
            if (isset($encabezado->OtraMoneda)){
                if ((string)$encabezado->TipoMoneda == 'USD') $v_moneda_id = 21;
                if ((string)$encabezado->TipoMoneda == 'EUR') $v_moneda_id = 22;
            } else $v_moneda_id = 20;

            //VALIDACIONES
            //VALIDACION DE EMISOR
            $varr_emisor = $vobj_mae_proc->get_datos_emisor($_SESSION['user']['empresaid']);

            if ($varr_emisor['identificacion'] != $v_emisor_rnc || $varr_emisor['nombre'] != $v_emisor_nom){
                $v_procede = 0;
                echo -1;
            }

            //VALIDACION DE CLIENTE
            $v_nombre_cliente = $vobj_mae_proc->get_nombre_empresa_xdoc($v_cliente_rnc);

            if ($v_procede == 1 && ($v_nombre_cliente != '')){
                if ($v_nombre_cliente != $v_cliente_nom){
                    $v_procede = 0;
                    echo -2;
                }
            }

            //VALIDACION DE MONTO
            if ($v_procede== 1){
                if ($v_moneda_id == 20) $varr_monto_minimo = $vobj_mae_proc->get_parametro_detalle(33);
                else $varr_monto_minimo = $vobj_mae_proc->get_parametro_detalle(34);

                if ($varr_monto_minimo['valornum'] > $v_total_financiar){
                    $v_procede = 0;
                    echo -3;
                }
            }

            //GUARDA DATOS DEL XML
            if ($v_procede == 1){
                // GUARDO EL XML
                $v_carpeta = '../pdf/'.'EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'];
                if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

                $v_carpeta = '../pdf/'.'EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'].'/temp';
                if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

                $v_xml_path = $v_carpeta.'/'.$v_nro_doc.'-'.$_FILES['xml']['name'];
                move_uploaded_file($_FILES['xml']['tmp_name'],  $v_xml_path);
                $v_xml_name = $v_nro_doc.'-'.$_FILES['xml']['name'];

                $varr_datos = array('nro_factura' => $v_nro_doc, 'moneda_id' => $v_moneda_id, 'cliente_doc' => $v_cliente_rnc, 'cliente_nom' => $v_cliente_nom,
                                    'f_emision' => $v_femision_eng, 'f_vencimiento' => $v_fvencimiento_eng, 'subtotal' => $v_subtotal, 'anticipos' => $v_monto_adelanto,
                                    'descuentos' => 0, 'valor_venta' => $v_valor_venta, 'itbis' => $v_itbis, 'otros' => $v_otros_impuestos, 'total' => $v_monto_total,
                                    'xml_path' => $v_xml_path, 'xml_name' => $v_xml_name, 'retenciones' => $v_total_retenciones,
                                    'cliente_correo' => $v_email_cliente, 'cliente_direccion' => $v_dir_cliente, 'cliente_contacto' => $v_nombre_contacto_cliente);

                $v_ft_id = $vobj_factura_proc->registra_factura_temp($varr_datos);

                echo $v_ft_id;
            }

        } else echo -4;
    }
}   //0 no es legible, -1 no corresponde datos del emisor, -2 no corresponde datos del cliente, -3 no cumple el monto minimo, -4 documento no admitido
/*--------------------------------------------------------*/
?>
