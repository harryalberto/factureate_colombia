<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require('../lib-fpdf/fpdf.php');
require('../lib-fpdf/fpdf-full.php');
require("../lib-trans/c_inversiones.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-trans/maestros.php");

$vobj_exp_inv = new inversiones;
$vobj_exp_mae = new maestros;
$vobj_mail = new mail_util;
$vobj_seg = new seguridad;

//FILTROS
$v_filtros = '';

if ($_SESSION['user']['empresaid'] > 0){
    $v_filtros .= 'propuestas.empresaid = '.$_SESSION['user']['empresaid'];
    $varr_empresa = $vobj_exp_mae->get_datos_empresa($_SESSION['user']['empresaid']);
    $v_nombre_inversor = $varr_empresa['nombre'];
    $v_mail = $varr_empresa['email_contacto'];
} else {
    $v_filtros .= 'propuestas.usuarioid = '.$_SESSION['user']['usuarioid'];
    $varr_usuario = $vobj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
    $v_nombre_inversor = $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];
    $v_mail = $varr_usuario['email'];
}

$v_nombre_usuario = $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];

$v_filtros .= " and financiamiento.fpago_efectivo >= '".$_POST['f_inicio']."' and financiamiento.fpago_efectivo < '".$_POST['f_fin']."'";

// ORDEN
$v_order = 'financiamiento.fpago_efectivo';

// RELACION DE GANANCIAS DEL MES
$varr_liquidacion_pdf = $vobj_exp_inv->get_liquidacion_mes('SELECT', $v_filtros, $v_order);

// ARREGLO DE MESES
$varr_meses_exp = array();
$varr_meses_exp[1]['nombre'] = 'ENERO'; $varr_meses_exp[2]['nombre'] = 'FEBRERO'; $varr_meses_exp[3]['nombre'] = 'MARZO'; $varr_meses_exp[4]['nombre'] = 'ABRIL';
$varr_meses_exp[5]['nombre'] = 'MAYO'; $varr_meses_exp[6]['nombre'] = 'JUNIO'; $varr_meses_exp[7]['nombre'] = 'JULIO'; $varr_meses_exp[8]['nombre'] = 'AGOSTO';
$varr_meses_exp[9]['nombre'] = 'SEPTIEMBRE'; $varr_meses_exp[10]['nombre'] = 'OCTUBRE'; $varr_meses_exp[11]['nombre'] = 'NOVIEMBRE'; $varr_meses_exp[12]['nombre'] = 'DICIEMBRE';

if (count($varr_liquidacion_pdf) > 0){
    $pdf = new PDF(); $pdf->AliasNbPages();

    //==== CABECERA DE LA PAGINA
    $pdf->AddPage('L');
    $pdf->Image('../img/logo.png',10,8,50);     // windth 50px

    // TITULO
    $pdf->SetFont('Arial','B',15);
    $pdf->Ln(30);
    $pdf->Cell(80);
    $pdf->Cell(30,10,mb_convert_encoding('REPORTE DE LIQUIDACION DE GANANCIAS '.$varr_meses_exp[$_POST['mes']]['nombre'].' '.$_POST['anno'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
    $pdf->Ln(20);

    // SUBTITULO
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $v_dt_hoy = date('d-m-Y Hi');

    $pdf->SetFont('Times','B',13);
    $v_nombre_titular = $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];

    $pdf->Cell(0,5,mb_convert_encoding('Titular de la cuenta: '.$v_nombre_inversor, 'ISO-8859-1', 'UTF-8'),0,1);
    $pdf->Ln();
    $pdf->Cell(0,5,mb_convert_encoding('Generación del reporte: '.$v_dt_hoy, 'ISO-8859-1', 'UTF-8'),0,1);

    $pdf->Ln();
    $pdf->SetFont('Times','B',10);
    $pdf->MultiCell(0,5,mb_convert_encoding('* La información a continuación corresponden a las ganancias obtenidas por el inversionista en el mes especifico del reporte', 'ISO-8859-1', 'UTF-8'),0,1);

    $pdf->Ln();

    //==== CONTENIDO TABLA
    $header = array('ID OP', 'DOC', 'CLIENTE', 'F INVER', 'F GANANCIA', 'MONEDA', '%TASA ANUAL', 'DIAS INVER', 'MONTO INVER', 'GANANCIA', 'COMI FACTU', '%TASA COMISION');
    $orientacion = array('C','L','L','C','C','C','R','C','R','R','R','R');
    $tamanos_w = array(10,25,70,20,20,20,20,15,20,20,20,20);
    $data = array();

    $v_total_ganancia = 0;
    $v_total_comision = 0;
    $v_total_ganancia_neta = 0;

    for ($i=0; $i<count($varr_liquidacion_pdf); $i++){
        $f_inversion = date('d-m-Y', strtotime($varr_liquidacion_pdf[$i]['f_inversion']));
        $f_ganancia = date('d-m-Y', strtotime($varr_liquidacion_pdf[$i]['f_pago']));

        $v_tia = ($varr_liquidacion_pdf[$i]['tia'] * 100).' %';
        $v_tasa_comision = ($varr_liquidacion_pdf[$i]['tasa_comision'] * 100).' %';

        $data[$i] = array(  $varr_liquidacion_pdf[$i]['factura_id'],$varr_liquidacion_pdf[$i]['factura_nro'],$varr_liquidacion_pdf[$i]['cliente_nombre'],
                            $f_inversion,$f_ganancia,$varr_liquidacion_pdf[$i]['moneda'], $v_tia, $varr_liquidacion_pdf[$i]['dias_inversion'],
                            number_format($varr_liquidacion_pdf[$i]['monto_inversion'],2,'.',','), number_format($varr_liquidacion_pdf[$i]['monto_ganancia'],2,'.',','),
                            number_format($varr_liquidacion_pdf[$i]['monto_comision'],2,'.','.'), $v_tasa_comision);

        $v_total_ganancia = $v_total_ganancia + $varr_liquidacion_pdf[$i]['monto_ganancia'];
        $v_total_comision = $v_total_comision + $varr_liquidacion_pdf[$i]['monto_comision'];
    }
    // PINTADO DE LA TABLA
    $pdf->FancyTableNew($header,$data,$orientacion,$tamanos_w);    // con colores    

    // TOTALES
    $v_total_ganancia_neta = $v_total_ganancia - $v_total_comision;

    $pdf->Ln();
    $pdf->Cell(35,5,'TOTAL GANANCIA',0,0,'R',false);
    $pdf->Cell(70,5,number_format($v_total_ganancia,2,'.',','),0,0,'R',false);

    $pdf->Ln();
    $pdf->Cell(35,5,'TOTAL COMISION',0,0,'R',false);
    $pdf->Cell(70,5,number_format($v_total_comision,2,'.',','),0,0,'R',false);

    $pdf->Ln();
    $pdf->Cell(35,5,'TOTAL GANANCIA NETA',0,0,'R',false);
    $pdf->Cell(70,5,number_format($v_total_ganancia_neta,2,'.',','),0,0,'R',false);

    //==== GUARDADO EN UNA CARPETA
    $v_nombre_report = '../report_attach/reporte_inversiones_'.$_SESSION['user']['usuarioid'].'_'.$v_dt_hoy.'.pdf';
    $pdf->Output('F',$v_nombre_report);

    //==== ENVIO ARCHIVO POR CORREO
    if ($_SESSION['user']['empresaid'] > 0){
        $varr_empresa = $vobj_exp_mae->get_datos_empresa($_SESSION['user']['empresaid']);
        $v_nombre = $varr_empresa['nombre'];
        $v_mail = $varr_empresa['email_contacto'];
    } else{
        $varr_usuario = $vobj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
        $v_nombre = $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];
        $v_mail = $varr_usuario['email'];
    }

    $varr_correo = array('mail_salida' => 'operaciones@factureate.com', 'nombre_salida' => 'FACTUREATE', 'mail_destino' => $v_mail,
                        'subject' => '[FACTUREATE] Reporte de inversiones realizadas activas',
                        'body' => 'Hola '.$v_nombre.', adjuntamos el reporte de inversiones activas solicitado.<br><br>FACTUREATE',
                        'root_archivo' => $v_nombre_report, 'nombre_archivo' => 'reporte_inversiones.pdf');
    
    $vobj_mail->enviar_correo_attach($varr_correo);

    echo '1';
} else{
    echo '-1';
}
?>