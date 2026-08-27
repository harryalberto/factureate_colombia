<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");   //seguridad
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-trans/c_seguridad_trans.php");
require("../lib-trans/maestros.php");
require("../lib-seg/seguridad-acceso.php");
require("../libmail/class.phpmailer.php");
require("../lib/mail_util.php");

$obj_st = new seguridad_trans;
$obj_mae = new maestros;
$obj_seg = new seguridad;
$obj_mail = new mail_util;

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  $input = $_POST;
  $empresa_existe = $obj_st->valida_existe_empresa($input['identificacion']);

  if ($empresa_existe > 0) $output = array('id' => -1, 'mensaje' => 'Empresa ya existe');
  else {
    //@@@@ llamada a la inserción de empresas
    $resultado = $obj_mae->registro_empresa($input);

    if ($resultado > 0){
      $output = array('id' => 1, 'mensaje' => 'Empresa creada');
      //@@@@ NOTIFICACION AL REPRESENTANTE LEGAL DE LA EMRPESA
      $arr_nusuario = array('identificacion' => $input['nrodoc_repre'], 'password' => '',
                          'email' => $input['email_repre'], 'nombre' => $input['nombre_representante'], 'apellido' => '',
                          'tipodoc' => $input['tipodoc_repre'], 'tipousuario' => 3, 'perfilid' => 4,
                          'empresaid' => $resultado);
      $arr_resultado = $obj_seg->crear_usuario($arr_nusuario);

      $varr_link = $obj_mae->get_parametro_detalle(53);

      //==== verifico el endoso y proveedor de contratos
      $varr_endoso = $obj_mae->get_parametro_detalle(77);
      $varr_docudig = $obj_mae->get_parametro_detalle(78);

      if ($varr_endoso['valornum'] == 1) $v_provee_docugid = ' con el contrato de autorizacion de nuestro proveedor '.$varr_endoso['valorchar'].' para poder realizar los endosos de las facturas que usted decida vender ';
      else $v_provee_docugid = '';

      if ($varr_docudig['valornum'] == 1) {
        if ($v_provee_docugid != '') $v_provee_docugid .= ' y otro correo de nuestro proveedor de contratos digitales '.$varr_docudig['valorchar'].' con el contrato de vinculacion con FACTUREATE ';
        else $v_provee_docugid .= ' de nuestro proveedor de contratos digitales '.$varr_docudig['valorchar'].' con el contrato de vinculacion con FACTUREATE ';
      }

      //++++ prepracion de arreglo de respuesta
      $output['mail_salida'] = 'pymes@factureate.com';
      $output['nombre_salida'] = 'Factureate';
      $output['mail_destino'] = $input['email_repre'];
      $output['subject'] = 'Registro en FACTUREATE';
      $output['body'] = 'Hola '.$input['nombre_repre'].',<br><br>
                        Se ha registrado su empresa '.$input['nombre'].' con RNC '.$input['identificacion'].' en nuestra plataforma,
                                debe seguir los siguientes pasos para completar el registro:<br><ul>
                                <li>PASO 1: Ingresar a nuestra plataforma con las credenciales al final de este correo</li>
                                <li>PASO 2: Completar la informacion de su empresa adjuntando en PDF el Registro Mercantil, Estatutos/Camara de Comercio donde aparezcan los accionistas,
                                  Documento del Representante Legal</li>
                                <li>PASO 3: Nuestros analistas en menos de 24 horas verificaran la documentacion y aprobaran su registro</li>
                                <li>PASO 4: Recibira un correo '.$v_provee_docugid.', lo cual servira para poder realizar las operaciones de adelanto de facturas que usted solicite</li>
                                <li>PASO 5: Recibira un correo de confirmacion, desde ese momento puede registrar sus facturas</li></ul><br>
                                Sus credenciales de acceso son las siguientes:<br>
                                <ul><li>usuario: '.$arr_resultado['identificacion'].'</li><li>contrase&ntilde;a: '.$arr_resultado['password'].'</li>
                                <li>Link de la plataforma: <a href="'.$varr_link['valorchar'].'" target="_blank">Plataforma Factureate</a></li></ul><br><br>
                                Gracias por confiar en nosotros, trabajaremos para conseguir el financiamiento que necesita.<br>
                                * Tildes omitidas intencionalmente<br><br>
                                <img src="cid:logo_factureate" width="100">';
      $output['firma'] = '../images/logo.png';
      $output['firma_nombre'] = 'logo_factureate';
      
      //@@@@ ACTUALIZO LOS PATH DE LOS ARCHIVOS RECIBIDOS
      $v_carpeta_destino = $_SERVER['DOCUMENT_ROOT'].'/pdf/EMP_'.$input['nombre'].'_'.$input['identificacion'];
      if (!is_dir($v_carpeta_destino)) mkdir($v_carpeta_destino, 0777, true);

      $v_carpeta_destino = $_SERVER['DOCUMENT_ROOT'].'/pdf/EMP_'.$input['nombre'].'_'.$input['identificacion'].'/vinculacion';
      if (!is_dir($v_carpeta_destino)) mkdir($v_carpeta_destino, 0777, true);
    } else $output = array('id' => 2, 'mensaje' => 'Fallo el registro de empresa');
  }
  
  echo json_encode($output);
  exit();
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");
?>