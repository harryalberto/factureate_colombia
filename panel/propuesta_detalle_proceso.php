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
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

$objsubasta = new subasta;
$obj_mail = new mail_util;
$obj_seg = new seguridad;
$obj_mae = new maestros;

$arrsubasta = $objsubasta->get_subasta($_POST['subasta_id']);
$varr_parametros = $obj_mae->get_parametros();

if ($arrsubasta['estadoid'] == 24){     // esta activa la subasta aún
    if ($_POST['propuestaid'] == 0){
        //##############################################################
        //############## PROPUESTA NUEVA
        $arrpropuesta = array('subastaid' => $_POST['subasta_id'], 'monto' => $_POST['monto'],
                            'representacion' => $_POST['porcmonto'], 'propuestaid' => $_POST['propuestaid'],
                            'tia' => $_POST['tia'], 'saldo_disponible'=>$_POST['saldo_disponible']
                        );
        $propuestaid = $objsubasta->genera_propuesta($arrpropuesta);
        
        if ($arrsubasta['tipofinancia'] == 23){     // FINANCIAMIENTO URGENTE
            //================= VERIFICA COMO QUEDO LA SUBASTA
            $varr_subasta_upd = $objsubasta->get_subasta($_POST['subasta_id']);

            if ($varr_subasta_upd['estadoid'] == 31){   //LA SUBASTA TERMINO COMPENSADA
                if ($varr_parametros['CONTRATO EMISOR AUTOM']['valornum'] == 1)     // EXISTE INTEGRACION TECNOLOGICA DE CONTRATO DIGITAL
                    $objsubasta->envia_contrado_endoso_emisor($varr_param_contrato);
                else{        // ENVIA CORREO AL RESPONSABLE LEGAL PARA QUE PREPARE EL CONTRATO
                    $varr_correo = array('notificaid' => 19, 'datos_body' => 'Operacion ID = '.$arrsubasta['facturaid'].'<br>Emisor = '.$arrsubasta['emisor'].'<br>Pagador = '.$arrsubasta['cliente'].'<br><br>FACTUREATE');
                    $obj_mail->enviar_correo_xnotificacion($varr_correo);
                }
            }
        }
        // correo al usuario
        $arr_usuario = $obj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
        $obj_mail->enviar_multicorreo_externo(6, $_SESSION['user']['usuarioid'], $arr_usuario['email']);    // CASO EL INVERSOR GANO LA SUBASTA Y FUE COMPENSADO
        $obj_mail->enviar_multicorreo_externo(7, $_SESSION['user']['usuarioid'], $arr_usuario['email']);    // CASO EL INVERSOR GANO LA SUBASTA Y LE FALTAN FONDOS DISPONIBLES
        
        echo $propuestaid;
    } else {                            
        //############################################################
        //############# PROPUESTA MODIFICADA
        if ($_POST['accion'] == 'grabar'){
            $arrposicion = $objsubasta->get_posicion($_POST['subasta_id'],$_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);

            if ($arrposicion['monto'] != $_POST['monto'] || $arrposicion['tia'] != $_POST['tia']){
                $arrpropuesta = array('subastaid' => $_POST['subasta_id'], 'monto' => $_POST['monto'],
                            'representacion' => $_POST['porcmonto'], 'propuestaid' => $_POST['propuestaid'],
                            'tia' => $_POST['tia']
                            );
                $propuestaid = $objsubasta->genera_propuesta($arrpropuesta);
                // correo al usuario
                $arr_usuario = $obj_seg->get_datos_usuario($_SESSION['user']['usuarioid']);
                $obj_mail->enviar_multicorreo_externo(6, $_SESSION['user']['usuarioid'], $arr_usuario['email']);    // CASO EL INVERSOR GANO LA SUBASTA Y FUE COMPENSADO
                $obj_mail->enviar_multicorreo_externo(7, $_SESSION['user']['usuarioid'], $arr_usuario['email']);    // CASO EL INVERSOR GANO LA SUBASTA Y LE FALTAN FONDOS DISPONIBLES
            } else $propuestaid = $_POST['propuestaid'];
        } else{
            $propuestaid = $objsubasta->anula_propuesta($_POST['propuestaid']);
        }

        echo $propuestaid;
    }
}
?>