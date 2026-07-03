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
$v_procede = 1;
$moneda_id = 0;

if(isset($_FILES['xml'])){
    $tmp = $_FILES['xml']['tmp_name'];
    libxml_use_internal_errors(true);

    $xml = simplexml_load_file($tmp);

    if($xml === false){
        echo 0;
    } else {
        // obteniendo los namespaces
        $namespaces = $xml->getNamespaces(true);

        //accediendo al description que contiene el xml que se necesita
        $description = (string)$xml
                            ->children($namespaces['cac'])
                            ->Attachment
                            ->ExternalReference
                            ->children($namespaces['cbc'])
                            ->Description;

        $invoice = simplexml_load_string($description);

        if ($invoice === false) {
            echo 0;
        } else {
            $ns2 = $invoice->getNamespaces(true);
            $cbc = $invoice->children($ns2['cbc']);

            $ProfileExecutionID = (string)$cbc->ProfileExecutionID;

            // valido si el indicador de factura en ambiente de produccion es correcto
            if ($ProfileExecutionID != '1') echo -5;
            else {
                $facturaID = (string)$cbc->ID;  // numero de la factura
                $IssueDate = (string)$cbc->IssueDate;   // fecha de emision yyyy-mm-dd
                $InvoiceTypeCode = (string)$cbc->InvoiceTypeCode;   // tipo de documento

                if ($InvoiceTypeCode != '01') echo -4;      // 01 es factura, 02 es exportacion
                else {
                    $DocumentCurrencyCode = (string)$cbc->DocumentCurrencyCode;     // moneda COP, EUR, USD
                    
                    if($DocumentCurrencyCode == 'COP') $moneda_id = 20;
                    elseif ($DocumentCurrencyCode == 'USD') $moneda_id = 21;
                    elseif ($DocumentCurrencyCode == 'EUR') $moneda_id = 22;

                    //valido la moneda
                    if ($DocumentCurrencyCode != 'COP' && $DocumentCurrencyCode != 'EUR' && $DocumentCurrencyCode != 'USD') echo -6;
                    else {
                        $cac = $invoice->children($ns2['cac']);

                        //datos del emisor
                        $AccountingSupplierParty = $cac->AccountingSupplierParty;
                        $cacAccountingSupplierParty = $AccountingSupplierParty->children($ns2['cac']);
                        $Party = $cacAccountingSupplierParty->Party;
                        $cacParty = $Party->children($ns2['cac']);

                        $PartyTaxScheme = $cacParty->PartyTaxScheme;
                        $cbcPartyTaxScheme = $PartyTaxScheme->children($ns2['cbc']);

                        $emisor_RegistrationName = (string)$cbcPartyTaxScheme->RegistrationName;    // nombre del emisor
                        $emisor_CompanyID = (string)$cbcPartyTaxScheme->CompanyID;      // NIT del emisor

                        $Contact = $cacParty->Contact;
                        $cbcContact = $Contact->children($ns2['cbc']);

                        $emisor_contacto = (string)$cbcContact->Name;
                        $emisor_telefono = (string)$cbcContact->Telephone;
                        $emisor_email = (string)$cbcContact->ElectronicMail;

                        // datos del cliente
                        $AccountingCustomerParty = $cac->AccountingCustomerParty;
                        $cbcAccountingCustomerParty = $AccountingCustomerParty->children($ns2['cbc']);

                        $cliente_AdditionalAccountID = (string)$cbcAccountingCustomerParty->AdditionalAccountID;

                        if ($cliente_AdditionalAccountID != '1') echo -7;
                        else {
                            $cacAccountingCustomerParty = $AccountingCustomerParty->children($ns2['cac']);
                            $Party = $cacAccountingCustomerParty->Party;
                            $cacParty = $Party->children($ns2['cac']);

                            $PartyTaxScheme = $cacParty->PartyTaxScheme;
                            $cbcPartyTaxScheme = $PartyTaxScheme->children($ns2['cbc']);
                            $cacPartyTaxScheme = $PartyTaxScheme->children($ns2['cac']);

                            $cliente_RegistrationName = (string)$cbcPartyTaxScheme->RegistrationName;   // nombre del cliente
                            $cliente_CompanyID = (string)$cbcPartyTaxScheme->CompanyID; // NIT del cliente
                            $RegistrationAddress = $cacPartyTaxScheme->RegistrationAddress;
                            $cacRegistrationAddress = $RegistrationAddress->children($ns2['cac']);
                            $cbcRegistrationAddress = $RegistrationAddress->children($ns2['cbc']);

                            $cliente_CityName = (string)$cbcRegistrationAddress->CityName;  // cliente ciudad
                            $cliente_CountrySubentity = (string)$cbcRegistrationAddress->CountrySubentity;  // cliente region
                            $AddressLine = $cacRegistrationAddress->AddressLine;
                            $cbcAddressLine = $AddressLine->children($ns2['cbc']);

                            $cliente_direccion = (string)$cbcAddressLine->Line;     // cliente direccion
                            $cliente_direccion .= ' '.$cliente_CityName.' '.$cliente_CountrySubentity;
                            $cliente_Contact = $cacParty->Contact;
                            $cbccliente_Contact  = $cliente_Contact->children($ns2['cbc']);

                            $cliente_Name = (string)$cbccliente_Contact->Name;  // cliente nombre contacto
                            $cliente_Telephone = (string)$cbccliente_Contact->Telephone;    // contacto telefono
                            $cliente_ElectronicMail = (string)$cbccliente_Contact->ElectronicMail;      // mail contacto cliente

                            $PaymentMeans = $cac->PaymentMeans;
                            $cbcPaymentMeans = $PaymentMeans->children($ns2['cbc']);

                            $fecha_pago = (string)$cbcPaymentMeans->PaymentDueDate;     // fecha de pago de la factura

                            // anticipos
                            if (isset($cac->PrepaidPayment)){
                                $PrepaidPayment = $cac->PrepaidPayment;
                                $cbcPrepaidPayment = $PrepaidPayment->children($ns2['cbc']);

                                $monto_anticipo = (float)$cbcPrepaidPayment->PaidAmount;
                            } else $monto_anticipo = 0;
                            
                            //retenciones
                            if (isset($cac->WithholdingTaxTotal)){
                                $WithholdingTaxTotal = $cac->WithholdingTaxTotal;
                                $cbcWithholdingTaxTotal = $WithholdingTaxTotal->children($ns2['cbc']);

                                $TaxAmount = (float)$cbcWithholdingTaxTotal->TaxAmount;
                                $TaxEvidenceIndicator = (string)$cbcWithholdingTaxTotal->TaxEvidenceIndicator;

                                if ($TaxEvidenceIndicator == 'false') $monto_retenciones = 0;       //**** validar esto */
                                else $monto_retenciones = $TaxAmount;
                            } else $monto_retenciones = 0;
                            
                            // montos
                            $TaxTotal = $cac->TaxTotal;
                            $cacTaxTotal = $TaxTotal->children($ns2['cac']);

                            $TaxSubtotal = $cacTaxTotal->TaxSubtotal;
                            $cbcTaxSubtotal = $TaxSubtotal->children($ns2['cbc']);

                            $iva_TaxAmount = (float)$cbcTaxSubtotal->TaxAmount;     // monto de impuesto del IVA

                            $LegalMonetaryTotal = $cac->LegalMonetaryTotal;
                            $cbcLegalMonetaryTotal = $LegalMonetaryTotal->children($ns2['cbc']);
                            $monto_neto = (float)$cbcLegalMonetaryTotal->LineExtensionAmount;       // monto neto
                            $monto_otros_cargos = (float)$cbcLegalMonetaryTotal->ChargeTotalAmount;     // monto otros cargos
                            $monto_pagar = (float)$cbcLegalMonetaryTotal->PayableAmount;    // monto a pagar

                            $monto_venta = $monto_neto - $monto_anticipo;

                            //valida datos del emisor
                            $varr_emisor = $vobj_mae_proc->get_datos_emisor($_SESSION['user']['empresaid']);

                            if ($varr_emisor['identificacion'] != $emisor_CompanyID || $varr_emisor['nombre'] != $emisor_RegistrationName){
                                $v_procede = 0;
                                echo -1;
                            }

                            //valida datos del cliente
                            $v_nombre_cliente = $vobj_mae_proc->get_nombre_empresa_xdoc($cliente_CompanyID);

                            if ($v_procede == 1 && ($v_nombre_cliente != '')){
                                if ($v_nombre_cliente != $cliente_RegistrationName){
                                    $v_procede = 0;
                                    echo -2;
                                } else {
                                    // valida la informacion de contacto del cliente
                                    $vobj_mae_proc->valida_contacto_empresa($cliente_CompanyID, $cliente_Name, $cliente_Telephone, $cliente_ElectronicMail, $cliente_direccion);
                                }
                            }

                            //validacion montos
                            if ($v_procede== 1){
                                if ($moneda_id == 20) $varr_monto_minimo = $vobj_mae_proc->get_parametro_detalle(33);
                                else $varr_monto_minimo = $vobj_mae_proc->get_parametro_detalle(34);

                                if ($varr_monto_minimo['valornum'] > $monto_pagar){
                                    $v_procede = 0;
                                    echo -3;
                                }
                            }

                            if ($v_procede == 1){
                                // GUARDO EL XML
                                $v_carpeta = '../pdf/'.'EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'];
                                if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

                                $v_carpeta = '../pdf/'.'EMP_'.$varr_emisor['nombre'].'_'.$varr_emisor['identificacion'].'/temp';
                                if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);

                                $v_xml_path = $v_carpeta.'/'.$facturaID.'-'.$_FILES['xml']['name'];
                                move_uploaded_file($_FILES['xml']['tmp_name'],  $v_xml_path);
                                $v_xml_name = $facturaID.'-'.$_FILES['xml']['name'];

                                $varr_datos = array('nro_factura' => $facturaID,    'moneda_id' => $moneda_id,         'cliente_doc' => $cliente_CompanyID, 'cliente_nom' => $cliente_RegistrationName,
                                                                'f_emision' => $IssueDate,      'f_vencimiento' => $fecha_pago,    'subtotal' => $monto_neto,                  'anticipos' => $monto_anticipo,
                                                                'descuentos' => 0,                   'valor_venta' => $monto_venta,     'itbis' => $iva_TaxAmount,                     'otros' => $monto_otros_cargos, 
                                                                'total' => $monto_pagar,        'xml_path' => $v_xml_path,             'xml_name' => $v_xml_name,               'retenciones' => $monto_retenciones,
                                                                'cliente_correo' => $cliente_ElectronicMail,                                     'cliente_direccion' => $cliente_direccion, 
                                                                'cliente_contacto' => $cliente_Name);

                                $v_ft_id = $vobj_factura_proc->registra_factura_temp($varr_datos);

                                echo $v_ft_id;
                            }
                        }
                    }
                }
            }
        }
    }
}   //0 no es legible, -1 no corresponde datos del emisor, -2 no corresponde datos del cliente, -3 no cumple el monto minimo, -4 documento no admitido
/*--------------------------------------------------------*/
?>
