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
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'REGFACT';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function redireccion(id){
            window.location.href = "registro_factura.php?tipo=upd&id="+id;
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$emp = new maestros;
$obj_mail = new mail_util;
// compruebo que la llamada del post es valida
if (isset($_POST['numeroemisor'])){
    $tipoaccion = $_POST['tipoaccion'];
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
        'conmaxdescuento' => $_POST['conmaxdescuento'],
        'maxdescuento' => $_POST['maxdescuento'],
        'xmlpath' => $_POST['xmlpath'],
        'tipo_factura' => $_POST['tipo_factura']
        );
    $tipo_archivo = $_FILES['facturafile']['type'];
    $tamano_archivo = $_FILES['facturafile']['size'];
    $nombre_archivo = $_FILES['facturafile']['name'];
    $var_formato_permitido = array('pdf','jpg','jpeg','png');
    // verifica si tiene limite de descuento
    if ($_POST['conmaxdescuento'] != 1){
        $arrfactura['conmaxdescuento'] = 0;
        $arrfactura['maxdescuento'] = 0;
    }
    // verifica si adjunto o no el pdf
    if (!isset($_FILES['facturafile'])){ 
        $arrfactura['pdfpath'] = '';
    }
    else{
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

    if ($tipoaccion == 'new' || $tipoaccion == 'xml'){
        // verificacion de existencia del cliente
        $arrfactura['clienteid'] = $emp->registro_express_cliente($arrfactura['numerocliente'],$arrfactura['cliente']);
        // llamada a la grabacion de la factura
        $arrfactura['accion'] = 'insert';
        $facturaid = $objfactura->graba_factura($arrfactura);
    } else{
        if ($tipoaccion == 'grabaenvia'){
            $arrfactura['clienteid'] = $emp->registro_express_cliente($arrfactura['numerocliente'],$arrfactura['cliente']);
            $arrfactura['accion'] = 'insert';
            $facturaid = $objfactura->graba_factura($arrfactura);
        } else {
            if ($_POST['numeroclienteold'] != $arrfactura['numerocliente']) 
                $arrfactura['clienteid'] = $emp->registro_express_cliente($arrfactura['numerocliente'],$arrfactura['cliente']);
            else $arrfactura['clienteid'] = $_POST['clienteid'];

            $arrfactura['accion'] = 'update';
            $arrfactura['facturaid'] = $_POST['facturaid'];
            $facturaid = $objfactura->graba_factura($arrfactura);
        }

        if ($facturaid > 0 && ($tipoaccion == 'envia' || $tipoaccion == 'grabaenvia')) {
        //#############################################
        //###### ENVIO DE LA FACTURA
            $resultado = $objfactura->envia_factura($facturaid);
            // esta accion puede generar una notificacion interna
            $arr_mail = array('notificaid' => 1, 'datos_body' => 'Emisor: '.$_POST['emisor'].'<br>'.'RNC: '.$_POST['numeroemisor'].'<br>Factura: '.
                                                            $_POST['nrofactura'].'<br>Factura ID: '.$facturaid);
            $obj_mail->enviar_correo_xnotificacion($arr_mail);
        }
    }

    if ($facturaid > 0 && $tipoaccion != 'envia' && $tipoaccion != 'grabaenvia'){
        //redireccion
        $redireccion = '<script languaje="javascript">
                            redireccion('.$facturaid.');
                        </script>';
        //$redireccion = '';
    } elseif ($facturaid < 0){
        $redireccion = 'Error';
        $mensaje = 'error con el cliente';
    }
} else $error = 10001;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
    echo $redireccion;
?>
    <!------ CUERPO VARIABLE ------>
    <?php
    if ($tipoaccion == 'envia' || $tipoaccion == 'grabaenvia'){
        if ($resultado > 0) //todo ok
        echo '<div style="overflow:hidden;text-align:center;font-size: 16px;font-weight: bold;width:50%;padding:30px;list-style:none;">
                Su factura ha sido enviada para la evaluaci&oacute;n de nuestros analistas, inmediatamente supere la evaluaci&oacute;n le 
                enviaremos un correo de confirmaci&oacute;n
            </div>
            <div style="overflow:hidden;text-align:center;font-size: 16px;font-weight: bold;width:50%;padding:30px;list-style:none;">
                <ul style="list-style:none;">';
        if ($_SESSION['user']['tipousuario'] == 3 || $_SESSION['user']['tipousuario'] == 6)
            echo '  <li class="botontransaccion" style="width:200px;margin:auto;"><a href="../emisores/facturas_emisor.php">Ver mis Facturas</a></li>
                </ul>
            </div>';
        else
            echo '  <li class="botontransaccion" style="width:200px;margin:auto;"><a href="../facturas/facturas.php">Ver mis Facturas</a></li>
                </ul>
            </div>';
    }
    ?>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>