<?
class documentos_xml{
    function lee_factura($path){
        $factura = simplexml_load_file('https://www.factureate.com/plataforma/xml/prueba.XML');
        $arrfactura = array();
        if (!$factura) echo 'error de archivo';
        //$namespaces = $factura->getNameSpaces(true);
        ///home/brdkai60/factureate.com/plataforma/lib/tratamiento-xml.php
        //$data=$factura->children($namespaces['cac'])->children($namespaces['ns2']);
        /*foreach ($factura->xpath('//cac:Signature//cbc:ID') as $facnumero){
            $arrfactura['numero'] = $facnumero;
        }*/
        $facnumero = $factura->xpath('//cac:Signature//cbc:ID');
        while(list( , $nodo) = each($facnumero)) {
            $arrfactura['numero'] = $nodo;
        }
        
        $rucemisor = $factura->xpath('//cac:AccountingSupplierParty//cac:Party//cac:PartyIdentification//cbc:ID');
        while(list( , $nodo) = each($rucemisor)) {
            $arrfactura['rucemisor'] = $nodo;
        }

        $emisor = $factura->xpath('//cac:AccountingSupplierParty//cac:Party//cac:PartyLegalEntity//cbc:RegistrationName');
        while(list( , $nodo) = each($emisor)) {
            $arrfactura['emisor'] = $nodo;
        }

        return $arrfactura;
    }
}
?>