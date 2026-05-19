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
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");

/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mail = new mail_util;
$obj_seg = new seguridad;
$obj_mae = new maestros;
$vobj_cuenta = new cuentas;
$vobj_inversiones = new inversiones;

if ($_POST['accion'] == 'guarda_datos'){
    //@@@@ GUARDA INFORMACION PERSONAL 
    $varr_datos = array('inversor_id'=>$_POST['usuario_id'],        'telefono_persona' => $_POST['telefono_persona'],   'email_persona' => $_POST['email_persona'],
                        'estado_inversor_id' => $_POST['estado_id'],'inversionista_id' => $_POST['inversor_id'],        'tipo_documento' => $_POST['tipo_doc_nom'],
                        'nro_documento' => $_POST['nro_doc'],       'condicion_laboral' => $_POST['condicion_laboral']);
    
    //@@@@ CARPETA DE VINCULACION
    $v_apellidos = ltrim(rtrim($_POST['apellido']));
    $varr_apellidos = explode(" ", $v_apellidos);
    $v_carpeta = '../archivos/INV_'.$_POST['nombre'].'_'.$varr_apellidos[0].'_'.$varr_apellidos[1].'_'.$_POST['nro_doc'].'/vinculacion';

    if ($_POST['estado_id'] == 6){
        // REGISTRADO
        if ($_POST['condicion_laboral'] == 113){
            // ASALARIADO
            if (isset($_FILES['ec_nomina']) && $_FILES['ec_nomina']['name'] != ''){
                $v_file_nomina = $v_carpeta.'/ecnomina_'.$_FILES['ec_nomina']['name'];
                move_uploaded_file($_FILES['ec_nomina']['tmp_name'],  $v_file_nomina);
                $varr_datos['nomina_path'] = $v_file_nomina;
            }
        }

        if ($_POST['condicion_laboral'] == 114){
            // INDEPENDIENTE
            if (isset($_FILES['declaracion_impuestos']) && $_FILES['declaracion_impuestos']['name'] != ''){
                $v_file_impuestos = $v_carpeta.'/impuestos_'.$_FILES['declaracion_impuestos']['name'];
                move_uploaded_file($_FILES['declaracion_impuestos']['tmp_name'],  $v_file_impuestos);
                $varr_datos['impuestos_path'] = $v_file_impuestos;
            }

            if (isset($_FILES['movimientos_banco']) && $_FILES['movimientos_banco']['name'] != ''){
                $v_file_bancos = $v_carpeta.'/movbancos_'.$_FILES['movimientos_banco']['name'];
                move_uploaded_file($_FILES['movimientos_banco']['tmp_name'],  $v_file_bancos);
                $varr_datos['bancos_path'] = $v_file_bancos;
            }

            $varr_datos['actividad'] = $_POST['explica_actividad'];
        }

        if ($_POST['condicion_laboral'] == 115){
            // EMPRESARIO
            if (isset($_FILES['eeff']) && $_FILES['eeff']['name'] != ''){
                $v_file_eeff = $v_carpeta.'/eeff_'.$_FILES['eeff']['name'];
                move_uploaded_file($_FILES['eeff']['tmp_name'],  $v_file_eeff);
                $varr_datos['eeff_path'] = $v_file_eeff;
            }

            if (isset($_FILES['movimientos_banco']) && $_FILES['movimientos_banco']['name'] != ''){
                $v_file_bancos = $v_carpeta.'/movbancos_'.$_FILES['movimientos_banco']['name'];
                move_uploaded_file($_FILES['movimientos_banco']['tmp_name'],  $v_file_bancos);
                $varr_datos['bancos_path'] = $v_file_bancos;
            }

            if (isset($_FILES['registro_mercantil']) && $_FILES['registro_mercantil']['name'] != ''){
                $v_file_regmercantil = $v_carpeta.'/regmercantil_'.$_FILES['registro_mercantil']['name'];
                move_uploaded_file($_FILES['registro_mercantil']['tmp_name'],  $v_file_regmercantil);
                $varr_datos['regmercantil_path'] = $v_file_regmercantil;
            }
        }

        if (isset($_FILES['f_servicios']) && $_FILES['f_servicios']['name'] != ''){
            $v_file_servicios = $v_carpeta.'/servicios_'.$_FILES['f_servicios']['name'];
            move_uploaded_file($_FILES['f_servicios']['tmp_name'],  $v_file_servicios);
            $varr_datos['servicios_path'] = $v_file_servicios;
        }

        if (isset($_FILES['formulario_kyc']) && $_FILES['formulario_kyc']['name'] != ''){
            $v_file_kyc = $v_carpeta.'/kyc_'.$_FILES['formulario_kyc']['name'];
            move_uploaded_file($_FILES['formulario_kyc']['tmp_name'],  $v_file_kyc);
            $varr_datos['formulario_kyc'] = $v_file_kyc;
        }
    }
    $obj_mae->upd_datos_inversor($varr_datos);
    $obj_seg->upd_datos_usuario($varr_datos);
    
} elseif ($_POST['accion'] == 'nueva_cuenta'){
    // PATH DEL CERTIFICADO
    $v_apellidos = ltrim(rtrim($_SESSION['user']['apellido']));
    $varr_apellidos = explode(" ", $v_apellidos);
    $v_carpeta = '../archivos/INV_'.$_SESSION['user']['nombre'].'_'.$varr_apellidos[0].'_'.$varr_apellidos[1].'_'.$_POST['nro_doc'].'/cuentas';
    $v_file_certificado = $v_carpeta.'/certificado_'.$_POST['cuenta'].'_'.$_FILES['certificado']['name'];

    $varr_datos = array('inversor_id' => $_POST['inversor_id'], 'banco_id' => $_POST['banco_id'], 'tcuenta_id' => $_POST['tcuenta_id'], 'cuenta' => $_POST['cuenta'],
                        'moneda_id' => $_POST['moneda_id'], 'empresa_id' => $_SESSION['user']['empresaid'], 'certificado_path' => $v_file_certificado);
    
    $v_rpta = $vobj_cuenta->crear_cuenta($varr_datos);

    if ($v_rpta > 0){
        // GUARDADO DEL CERTIFICADO DEL BANCO
        if (!is_dir($v_carpeta)) mkdir($v_carpeta, 0777, true);
        move_uploaded_file($_FILES['certificado']['tmp_name'],  $v_file_certificado);
    }

    echo $v_rpta;
} elseif ($_POST['accion'] == 'eliminar_cuenta'){
    $vobj_cuenta->eliminar_cuenta_banco($_POST['cuenta_id']);

    echo '<script type="text/javascript">
                alert("La cuenta de banco fue eliminada");
            </script>';
} elseif ($_POST['accion'] == 'perfil_inversion'){
    if (isset($_POST['activa_perfil'])){
        if ($_POST['activa_inversion_atm'] == 1) $v_automatico = 1; else $v_automatico = 0;
        if ($_POST['notifica_email'] == 1) $v_mail_legal = 1; else $v_mail_legal = 0;
        if ($_POST['notifica_msg'] == 1) $v_msg_legal = 1; else $v_msg_legal = 0;
        if ($_POST['notifica_email_opor'] == 1) $v_mail_opor = 1; else $v_mail_opor = 0;
        if ($_POST['notifica_msg_opor'] == 1) $v_msg_opor = 1; else $v_msg_opor = 0;
        if ($_POST['notifica_cada_opor'] == 1) $v_cada_opor = 1; else $v_cada_opor = 0;
        if ($_POST['activa_perfil'] == 1) $v_activa_perfil = 1; else $v_activa_perfil = 0;

        $v_mail_legal = 1;
        $v_msg_legal = 1;

        $varr_datos[0] = array('inversor_id' => $_POST['inversor_id'], 'activa_perfil' => $v_activa_perfil, 'automatico' => $v_automatico, 'email_legal' => $v_mail_legal, 
                                'msg_legal' => $v_msg_legal, 'email_opor' => $v_mail_opor, 'msg_opor' => $v_msg_opor, 'cada_opor' => $v_cada_opor, 'monto_minimo' => $_POST['monto_minimo'],
                                'monto_maximo' => $_POST['monto_maximo'], 'tea_automatica' => $_POST['tea_automatica']);
        
        $v_riesgos = $_POST['RIESGO'];
        $v_j = 1;

        for ($i=0;$i<count($v_riesgos);$i++){
            $ind = $v_j;
            $varr_datos[$v_j] = array('variable' => $_POST['RIESGO-1'], 'variable_detalle' => $v_riesgos[$i]);
            $v_j ++;
        }

        $v_sector = $_POST['SECTOR'];
        
        for ($i=0;$i<count($v_sector);$i++){
            $ind = $v_j;
            $varr_datos[$v_j] = array('variable' => $_POST['SECTOR-1'], 'variable_detalle' => $v_sector[$i]);
            $v_j++;
        }

        $v_tiempo = $_POST['TIEMPO'];
        
        for ($i=0;$i<count($v_tiempo);$i++){
            $varr_datos[$v_j] = array('variable' => $_POST['TIEMPO-1'], 'variable_detalle' => $v_tiempo[$i]);
            $v_j++;
        }

        $vobj_inversiones->activa_perfil_inversor($varr_datos);

        echo '<script type="text/javascript">
                alert("Perfil de inversion guardada");
            </script>';
    } else{
        $v_resultado = $vobj_inversiones->desactivar_perfil_inversor($_POST['inversor_id']);

        if ($v_resultado == 2)
            echo '<script type="text/javascript">
                    alert("No se hicieron cambios porque usted mantiene el perfil de inversión desactivado");
                </script>';
        else
            echo '<script type="text/javascript">
                    alert("Se desactivo el perfil de inversión y las variables incluidas eliminadas");
                </script>';
    } 
} elseif ($_POST['accion'] == 'enviar'){
    $obj_mae->envia_inversor($_POST['inversor_id']);

    //#### NOTIFICACION AL AREA LEGAL
    $varr_mail_envio = array('notificaid' => 29, 'datos_body' => 'INVERSOR ID: '.$_POST['inversor_id'].'<br>INVERSOR NOMBRE: '.$_POST['nombre'].' '.$_POST['apellido'].'<br>FACTUREATE');
    $obj_mail->enviar_correo_xnotificacion($varr_mail_envio);
    echo '1';
}

//echo json_encode($output, JSON_UNESCAPED_UNICODE);
/*--------------------------------------------------------*/
?>