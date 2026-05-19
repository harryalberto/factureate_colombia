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
$objfactura = new factura;
$emp = new maestros;
$obj_mail = new mail_util;

date_default_timezone_set($_SESSION['user']['zona_horaria']);

// compruebo que la llamada del post es valida
if (isset($_POST['numeroemisor'])){
    $tipoaccion = $_POST['tipoaccion'];

    //========RECUPERANDO INFORMACION DE LA FACTURA
    if (isset($_POST['conmaxdescuento'])){
        $v_conmaxdcto = $_POST['conmaxdescuento'];
        $v_maxdcto = $_POST['maxdescuento'];
    } else {
        $v_conmaxdcto = 0;
        $v_maxdcto = 0;
    }
    if (isset($_POST['xml_path'])) $xml_path = $_POST['xml_path'];
    else $xml_path = '';

    $arrfactura = array(
        'porcimpuestoventa' => $_POST['porcimpuestoventa'],
        'MAX_FILE_SIZE' => $_POST['MAX_FILE_SIZE'],
        'numeroemisor' => $_POST['numeroemisor'],
        'emisor' => $_POST['emisor'],
        'nrofactura' => $_POST['nrofactura'],
        'femision' => $_POST['femision'],
        'fvencimiento' => $_POST['fvencimiento'],
        'numerocliente' => trim($_POST['numerocliente']),
        'cliente' => $_POST['cliente'],
        'monedaid' => $_POST['monedaid'],
        'subtotal' => $_POST['subtotal'],
        'anticipos' => $_POST['anticipos'],
        'descuentos' => $_POST['descuentos'],
        'valorventa' => $_POST['valorventa'],
        'impuestoventa' => $_POST['impuestoventa'],
        'otroscargos' => $_POST['otroscargos'],
        'otrostributos' => $_POST['otrostributos'],
        'total' => $_POST['total'],
        'tipofinanciamiento' => $_POST['tipofinanciamiento'],
        'conmaxdescuento' => $v_conmaxdcto,
        'maxdescuento' => $v_maxdcto,
        'xmlpath' => $xml_path,
        'tipo_factura' => $_POST['tipo_factura']
        );

    $tipo_archivo = $_FILES['facturafile']['type'];
    $tamano_archivo = $_FILES['facturafile']['size'];
    $nombre_archivo = $_FILES['facturafile']['name'];
    $var_formato_permitido = array('pdf','jpg','jpeg','png');

    // verifica si tiene limite de descuento
    /*if ($_POST['conmaxdescuento'] != 1){
        $arrfactura['conmaxdescuento'] = 0;
        $arrfactura['maxdescuento'] = 0;
    }*/
    
    // verifica si adjunto o no el pdf
    if (!isset($_FILES['facturafile'])){ 
        $arrfactura['pdfpath'] = '';
    } else{
        $var_extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        
        if ((in_array($var_extension,$var_formato_permitido)) && ($tamano_archivo < 1000000)){
            $var_path_carpeta = '../pdf/'.'EMI'.$_SESSION['user']['empresaid'];
            
            if (!is_dir($var_path_carpeta)) mkdir($var_path_carpeta, 0777, true);
            
            $arrfactura['pdfpath'] = '../pdf/'.'EMI'.$_SESSION['user']['empresaid'].'/'.$arrfactura['nrofactura'].'-'.$nombre_archivo;
            
            move_uploaded_file($_FILES['facturafile']['tmp_name'],  $arrfactura['pdfpath']);
            //else echo 'ocurrio un error '.$_FILES['facturafile']['error'];
        } else {
            $arrfactura['pdfpath'] = '';
            $error = 10002;
        }
    }

    //TRATAMIENTO DEL XML
    if (isset($_POST['xml_path'])){
        rename('../pdf/'.'EMI'.$_SESSION['user']['empresaid'].'/temp/'.$_POST['nombre_xml'], '../pdf/'.'EMI'.$_SESSION['user']['empresaid'].'/'.$_POST['nombre_xml']);
        $xml_path = '../pdf/'.'EMI'.$_SESSION['user']['empresaid'].'/'.$_POST['nombre_xml'];
    } else $xml_path = '';

    $arrfactura['xmlpath'] = $xml_path;

    if ($tipoaccion == 'new' || $tipoaccion == 'xml'){
        // verificacion de existencia del cliente
        $arrfactura['clienteid'] = $emp->registro_express_cliente($arrfactura['numerocliente'],$arrfactura['cliente']);
        // llamada a la grabacion de la factura
        $arrfactura['accion'] = 'insert';
        
        $facturaid = $objfactura->graba_factura($arrfactura);

        if ($facturaid > 0) echo $facturaid;
        else echo -1;
    } else{
        if ($tipoaccion == 'grabaenvia'){
            $arrfactura['clienteid'] = $emp->registro_express_cliente($arrfactura['numerocliente'],$arrfactura['cliente']);
            $arrfactura['accion'] = 'insert';
            $facturaid = $objfactura->graba_factura($arrfactura);
        } else {
            //if ($_POST['numeroclienteold'] != $arrfactura['numerocliente']) 
            $arrfactura['clienteid'] = $emp->registro_express_cliente($arrfactura['numerocliente'],$arrfactura['cliente']);
            //else $arrfactura['clienteid'] = $_POST['clienteid'];

            $arrfactura['accion'] = 'update';
            $arrfactura['facturaid'] = $_POST['facturaid'];
            $facturaid = $objfactura->graba_factura($arrfactura);
        }

        if ($facturaid > 0 && ($tipoaccion == 'envia' || $tipoaccion == 'grabaenvia')) {
        //###### ENVIO DE LA FACTURA
            $resultado = $objfactura->envia_factura($facturaid);
            // esta accion puede generar una notificacion interna
            $arr_mail = array('notificaid' => 1, 'datos_body' => 'Emisor: '.$_POST['emisor'].'<br>'.'RNC: '.$_POST['numeroemisor'].'<br>Factura: '.
                                                            $_POST['nrofactura'].'<br>Factura ID: '.$facturaid);
            $obj_mail->enviar_correo_xnotificacion($arr_mail);
            
            if ($resultado > 0) echo 1;
            else echo -2;
        } else echo -1;
    }
} else {
    if ($_POST['accion'] == 'envia_express'){
        $varr_factura = $objfactura->get_datos_factura($_POST['factura_id']);
        $v_valida = 1;

        // VERIFICA INFORMACION
        if ($varr_factura['tipofinanciamiento'] == 0 || $varr_factura['facturapath'] == '') $v_valida = -2;
        
        if ($v_valida == 1){
            $resultado = $objfactura->envia_factura($_POST['factura_id']);
            // esta accion puede generar una notificacion interna
            $arr_mail = array('notificaid' => 1, 'datos_body' => 'Emisor: '.$varr_factura['emisor'].'<br>'.'RNC: '.$varr_factura['emisornro'].'<br>Factura: '.
                                                            $varr_factura['factura'].'<br>Factura ID: '.$_POST['factura_id']);
            $obj_mail->enviar_correo_xnotificacion($arr_mail);

            if ($resultado > 0) echo 1;
            else echo -1;
        } else echo -2;
        //echo $v_valida;
    } else echo -3;
}
