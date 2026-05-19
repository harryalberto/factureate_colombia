<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/c_subasta.php");

$vobj_subasta_proc = new subasta;
$obj_mail = new mail_util;
$obj_seg = new seguridad;

date_default_timezone_set($_SESSION['user']['zona_horaria']);
$v_hoy = date('Y-m-d');

if ($_POST['accion'] == 'terminar'){          
    //====== EL ANALISTA TERMINA MANUALMENTE LA SUBASTA
    if ($_POST['porc_minimo'] > $_POST['porc_propuestas']){
    	// ANULAR LA SUBASTA PORQUE NO SE CONSIGUIO EL FINANCIAMIENTO MINIMO
    	$vobj_subasta_proc->anular_subasta($_POST['subastaid']);

    	// NOTIFICACION DE ANULACION
    	$varr_mail = array('notificaid'=>39, 'datos_body'=>'OPERACION ID: '.$_POST['factura_id'].'<br>PAGADOR: '.$_POST['cliente_nombre'].'<br><br>APP FACTUREATE');
        $obj_mail->enviar_correo_xnotificacion($varr_mail);

    	echo 'ANULADO';
    } else{
    	$vobj_subasta_proc->termina_subasta($_POST['subastaid']);
        
        if ($_POST['porc_propuestas'] >= 1){
            //====== NOTIFICACION A INVERSORES EN CASO DE QUE LA SUBASTA SE TERMINO E INICIARA LA COMPENSACION
            $arr_inversionistas = $objsubasta->get_inversionistas_xsubasta($_POST['subastaid']);
            
            for ($i=0; $i<count($arr_inversionistas); $i++){
                $arr_usuario = $obj_seg->get_datos_usuario($arr_inversionistas[$i]['inversionista_id']);
                $obj_mail->enviar_multicorreo_externo(6,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
                $obj_mail->enviar_multicorreo_externo(7,$arr_inversionistas[$i]['inversionista_id'],$arr_usuario['email']);
            }

            //====== NOTIFICACION INTERNA
            $varr_mail_legal = array('notificaid'=>5, 'datos_body'=>'OPERACION ID: '.$_POST['factura_id'].'<br>PAGADOR: '.$_POST['cliente_nombre'].'<br><br>APP FACTUREATE');
            $obj_mail->enviar_correo_xnotificacion($varr_mail_legal);   //-- INICIO DE COMPENSACION LEGAL        
        } else{
            //====== NO SE LLEGO AL 100% PERO SI AL MINIMO
            //====== NOTIFICACION PARA QUE EL EMISOR CONFIRME EL MONTO CONSEGUIDO
            $obj_mail->enviar_notificacion_externo(9);
        }

        echo 'TERMINAR';
    }
}
?>