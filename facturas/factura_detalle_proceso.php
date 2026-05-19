<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/c_inversiones.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$obj_subasta = new subasta;
$obj_mail = new mail_util;
$obj_seg = new seguridad;
$obj_mae = new maestros;
$vobj_inversiones = new inversiones;

$accion = $_POST['accion'];
$v_notifica_inversores = 0;

if ($accion == 'aprobar'){
    $objfactura->aprobar_factura($_POST['facturaid']);
    $v_resultado = 'factura aprobada';
    //@@@@@@@@@@@@@@@@@ NECEISTA COMPROBANTE DE LIBERTAD
    if ($_POST['factura_libre'] == 1){
        if (isset($_FILES['file_factura_libre']) && $_FILES['file_factura_libre']['name'] != ''){
            $archivo = '../pdf/empresa_'.$_POST['emisornro'];
            if (!file_exists($archivo)) mkdir($archivo,0700);

            $archivo_op = '../pdf/empresa_'.$_POST['emisornro'].'/OP_'.$_POST['facturaid'];
            if (!file_exists($archivo_op)) mkdir($archivo_op,0700);

            $file_libre_path = '../pdf/'.'empresa_'.$_POST['emisornro'].'/OP_'.$_POST['facturaid'].'/titulolibre-'.$_FILES['file_factura_libre']['name'];
            move_uploaded_file($_FILES['file_factura_libre']['tmp_name'],  $file_libre_path);

            $objfactura->graba_comprobante_libertad($_POST['facturaid'],$file_libre_path);
            $v_resultado = 'factura libre';
        }
    }

    //################ ENVIO DE CORREOS
    //================ CORREO AL EMISOR
    $arr_usuario = $obj_seg->get_datos_usuario($_POST['u_envio_id']);
    $arr_mail_user = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'FACTUREATE',
                            'mail_destino' => $arr_usuario['email'],
                            'subject' => 'Su factura fue aprobada',
                            'body' => 'El proceso de financiamiento de su factura ha iniciado.<br><br>
                                        Factura ID: '.$_POST['facturaid'].'<br>
                                        Factura: '.$_POST['factura_nro'].'<br>
                                        Cliente: '.$_POST['cliente'].'<br>
                                        Monto: '.$_POST['total'].' '.$_POST['moneda'].'<br><br>
                                        * Recuerde el numero de FACTURA ID para darle seguimiento a su financiamiento <br><br>
                                        FACTUREATE');
    $obj_mail->enviar_correo($arr_mail_user);
    $v_resultado = 'correo emisor';

    //==== notificacion a inversores con perfil registrado
    $varr_factura = $objfactura->get_datos_factura($_POST['facturaid']);
    $varr_cliente = $obj_mae->get_datos_empresa($varr_factura['clienteid']);

    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $dt_hoy = new DateTime(date('Y-m-d'));
    $dt_fv = new DateTime($varr_factura['fvencimiento']);
    $v_tiempo_vto = $dt_hoy->diff($dt_fv);
    $v_dias_vto = $v_tiempo_vto->days;
    
    $varr_inv_perfil = array(   'id' => $_POST['facturaid'],                'monto' => $varr_factura['total'], 'riesgo_id' => $varr_factura['riesgo_factura_id'],
                                'sector_id' => $varr_cliente['sectorid'],   'plazo' => $v_dias_vto);

    //---- arreglo con los inversores que tienen un perfil definido y cumple la caracteristica de la factura
    $varr_inversores_perfil = $vobj_inversiones->get_inversores_perfil_match($varr_inv_perfil);
    
    //---- se genera un acceso rapido a la inversion
    if (count($varr_inversores_perfil) > 0) $acceso = $vobj_inversiones->genera_acceso_inversion($_POST['facturaid']);
    $v_resultado = 'acceso inversion';

    for ($i = 0; $i < count($varr_inversores_perfil); $i++){
        if ($varr_inversores_perfil[$i]['estado_id'] == 1)
            $varr_usuario = $obj_mae->valida_usuario_inversor($varr_inversores_perfil[$i]['inversor_id']);

            for ($j = 0; $j < count($varr_usuario); $j++){
                $varr_usuario_datos = $obj_seg->get_datos_usuario($varr_usuario[$j]['usuario_id']);

                $arr_mail_user = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $varr_usuario_datos['email'],
                            'subject' => 'Oportunidad de inversion en Factureate',
                            'body' => 'Se registro una nueva oportunidad de inversion de acuerdo a tu perfil.<br><br>
                                        Factura ID: '.$_POST['facturaid'].'<br>
                                        Factura: '.$_POST['factura_nro'].'<br>
                                        Pagador: '.$_POST['cliente'].'<br>
                                        Monto: '.$_POST['total'].' '.$_POST['moneda'].'<br>
                                        Nivel de Calidad: '.$varr_factura['riesgo_factura_nombre'].' ['.$varr_factura['riesgo_factura_calificacion'].']<br>
                                        Desea invertir?: <a href="'.$acceso.'" target="_blank">INVERTIR</a>
                                        <br><br>* Tildes omitidas intencionalmente 
                                        <br><br>FACTUREATE');
                $obj_mail->enviar_correo($arr_mail_user);
            }
    }

    $v_resultado = '1';

    //================ RESULTADO
    echo $v_resultado;
} elseif ($accion == 'grabar'){
    if (isset($_FILES['solicitudfile']) && $_FILES['solicitudfile']['name'] != ''){ //coloco el archivo de la solicitud en el servidor
        $solicitudpath = '../pdf/'.'EMI'.$_POST['emisorid'].'/solicitudac-'.$_FILES['solicitudfile']['name'];
        move_uploaded_file($_FILES['solicitudfile']['tmp_name'],  $solicitudpath);
    }
    if (isset($_FILES['acfile']) && $_FILES['acfile']['name'] != ''){ //coloco el archivo de la ac en el servidor
        $acpath = '../pdf/'.'EMI'.$_POST['emisorid'].'/AC-'.$_FILES['acfile']['name'];
        move_uploaded_file($_FILES['acfile']['tmp_name'],  $acpath);
    }
    // grabo en la BD
    $objfactura->grabar_factura_enrevision($_POST['facturaid'],$solicitudpath,$acpath);
    
    echo "2";
} elseif ($accion == 'rechazar'){
    if (isset($_FILES['rechazofile']) && $_FILES['rechazofile']['name'] != ''){
        $rechazopath = '../pdf/'.'EMI'.$_POST['emisorid'].'/rechazo-'.$_FILES['rechazofile']['name'];
        move_uploaded_file($_FILES['rechazofile']['tmp_name'],  $rechazopath);
    } else $rechazopath = '';
    //grabacion en la BD
    $objfactura->rechazar_factura($_POST['facturaid'],$rechazopath,$_POST['rechazo']);
    $redireccion = '';
    
    echo "3";
}

?>
