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

$varr_inversiones = $vobj_exp_inv->get_inversiones_xusuario($_SESSION['user']['usuarioid'],'total',$_SESSION['user']['empresaid'],0,10000);

if (count($varr_inversiones) > 0){
    $pdf = new PDF(); $pdf->AliasNbPages();

    //==== CABECERA DE LA PAGINA
    $pdf->AddPage('L');
    $pdf->Image('../img/logo.png',10,8,50);     // windth 50px

    // TITULO
    $pdf->SetFont('Arial','B',15);
    $pdf->Ln(30);
    $pdf->Cell(80);
    $pdf->Cell(30,10,utf8_decode('INVERSIONES REALIZADAS ACTIVAS'),0,0,'C');      // Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])
    $pdf->Ln(20);

    // SUBTITULO
    $v_zona_horaria = $vobj_exp_mae->get_zona_horaria();
    date_default_timezone_set($v_zona_horaria);
    $v_dt_hoy = date('d-m-Y G:s');

    $pdf->SetFont('Times','B',13);
    $v_nombre_titular = $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];
    $pdf->Cell(0,5,utf8_decode('Titular de la cuenta: '.$v_nombre_titular),0,1);
    $pdf->Ln();
    $pdf->Cell(0,5,utf8_decode($v_dt_hoy.'   '.$v_zona_horaria),0,1);
    $pdf->Ln();
    $pdf->SetFont('Times','B',10);
    $pdf->MultiCell(0,5,utf8_decode('* La información a continuación corresponden a las inversiones que se encuentran activas, es decir, se encuentran en espera del pago del Obligado al Pago, adicionalmente los montos de ganancia son estimadas que depende de la fecha efectiva del pago de la operación'),0,1);
    $pdf->Ln();

    //==== CONTENIDO TABLA
    $header = array('ID', 'PAGADOR', 'FACTURA NRO', 'F VCTO', 'F PAGO', 'INVERSION', 'GANANCIA EST', 'MONEDA', 'ESTADO');    
    $orientacion = array('L','L','C','C','C','R','R','C','C');
    $tamanos_w = array(10,70,35,25,25,30,30,20,25);
    $data = array();

    for ($i=0; $i<count($varr_inversiones); $i++){
        $f_vencimiento = date('d-m-Y',strtotime($varr_inversiones[$i]['f_vencimiento']));
        if ($varr_inversiones[$i]['factura_diff_pago'] == 0) $v_fpago = $f_vencimiento;
        else $v_fpago = date('d-m-Y', strtotime($varr_inversiones[$i]['factura_fpago']));

        if ($varr_inversiones[$i]['e_subasta_id'] != 26){    // no liquidada
            $ganancia = '-';
            $estado = $varr_inversiones[$i]['e_propuesta'];
        } else{
            $ganancia = number_format($varr_inversiones[$i]['ganancia'],2,'.',',');
            $estado = $varr_inversiones[$i]['e_financia'];
        }

        $data[$i] = array($varr_inversiones[$i]['factura_id'],$varr_inversiones[$i]['cliente'],$varr_inversiones[$i]['factura_nro'],$f_vencimiento,$v_fpago,number_format($varr_inversiones[$i]['monto_inversion'],2,'.',','),$ganancia,$varr_inversiones[$i]['moneda'],$estado);
    }
    // PINTADO DE LA TABLA
    $pdf->FancyTableNew($header,$data,$orientacion,$tamanos_w);    // con colores    

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

    echo '  <script>
                alert("El reporte ha sido enviado a su correo electronico");
            </script>';
} else{
    echo '  <script>
                alert("Usted no cuenta con inversiones para exportar !!");
            </script>';
}
/*
echo '  <script>
            location.href = "facturas_inversion.php";
        </script>';
*/
?>