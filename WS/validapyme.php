<?php
ini_set('display_errors', 0);
ini_set('pcre.jit', 0);

header('Content-Type: application/json');

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
      $output = array('id' => 1, 'mensaje' => $resultado);
      $arr_nusuario = array('identificacion' => $input['nrodoc_repre'], 'password' => '',
                          'email' => $input['email_repre'], 'nombre' => $input['nombre_repre'], 'apellido' => $input['a_paterno_repre'].' '.$input['a_materno_repre'],
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

      // envio de correo al usuario registrado
      $arr_mail = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'Factureate',
                        'mail_destino' => $input['email_repre'], 'subject' => 'Registro en FACTUREATE',
                        'body' => 'Hola '.$input['nombre_repre'].',<br><br>Se ha registrado su empresa '.$input['nombre'].' con NIT '.$input['identificacion'].' en nuestra plataforma, 
                                debe seguir los siguientes pasos para terminar el registro:<br><ul>
                                <li>Ingresar a nuestra plataforma con las credenciales al final de este correo</li>
                                <li>Completar la informacion de su empresa y enviar su registro (botom enviar)</li>
                                <li>Nuestros analistas en menos de 24 horas verificaran la documentacion y aprobaran su registro</li>
                                <li>Recibira un correo '.$v_provee_docugid.', lo cual servira para poder realizar las operaciones de adelanto de facturas que usted solicite</li>
                                <li>Recibira un correo de confirmacion, desde ese momento puede registrar sus facturas</li></ul><br>
                                Sus credenciales de acceso son las siguientes:<br>
                                <ul><li>usuario: '.$arr_resultado['identificacion'].'</li><li>contrase&ntilde;a: '.$arr_resultado['password'].'</li>
                                <li>Link de la plataforma: <a href="'.$varr_link['valorchar'].'" target="_blank">Plataforma Factureate</a></li></ul><br><br>
                                Gracias por confiar en nosotros, trabajaremos para conseguir el financiamiento que necesita.<br>
                                * Tildes omitidas intencionalmente<br><br>
                                FACTUREATE');
      $obj_mail->enviar_correo($arr_mail);

       // envio de cooreo a los analistas de factureate
      $arr_mail_perfil = array('perfilid' => 8, 'nombre_salida' => 'Operaciones FACTUREATE',
                              'subject' => 'Registro de nuevo EMISOR',
                              'body' => 'Hola, <br><br>Se ha registrado a trav&eacute;s de la web el siguiente emisor;<br>
                                      <ul><li>Nombre: '.$input['nombre'].'</li><li>NIT: '.$input['identificacion'].'</li></ul><br>
                                      Gracias<br><br>
                                      FACTUREATE');
      $obj_mail->enviar_correo_xperfil($arr_mail_perfil);

      //@@@@ TRANSFERENCIA DE ARCHIVOS DESDE LA WEB A LA PLATAFORMA
      // VERIFICACION DE CARPETAS
      $v_carpeta_destino = $_SERVER['DOCUMENT_ROOT'].'/pdf/EMP_'.$input['nombre'].'_'.$input['identificacion'];
      if (!is_dir($v_carpeta_destino)) mkdir($v_carpeta_destino, 0777, true);

      $v_carpeta_destino = $_SERVER['DOCUMENT_ROOT'].'/pdf/EMP_'.$input['nombre'].'_'.$input['identificacion'].'/vinculacion';
      if (!is_dir($v_carpeta_destino)) mkdir($v_carpeta_destino, 0777, true);

      //ACTUALIZO LOS PATH EN EL REGISTRO EMPRESA
      $file_registro_mercantil = $v_carpeta_destino.'/registro_mercantil_'.$input['identificacion'].'.pdf';
      $file_poderes = $v_carpeta_destino.'/estatutos_'.$input['identificacion'].'.pdf';
      $file_documento_repre = $v_carpeta_destino.'/documento_'.$input['nombre_repre'].'_'.$input['a_paterno_repre'].'_'.$input['a_materno_repre'].'.pdf';

      $varr_path_documentos = array('registro_mercantil' => $file_registro_mercantil, 'documento_repre' => $file_documento_repre, 'poderes_empresa' => $file_poderes, 'empresa_id' => $resultado);

      $obj_mae->registra_path_documentos_empresa($varr_path_documentos);

      //@@@@ TRANSFERENCIA DE ARCHIVOS
      $host = "ftp.brdkairos.com";
      $port = 21;
      $user = "vincula_emisorwebco@factureate.com";
      $password = "^_d[1SgwONoD+D{=";
      //$arrpassword = explode(".",$password);
      //$pass = $arrpassword[0];
      $pass = $password;
                
      $connection = ftp_connect($host, $port);

      if (!$connection) {
        die("No conecta al FTP");
      }

      $login = ftp_login($connection, $user, $pass);

      if (!$login) {
          die("Login FTP incorrecto");
      }

      ftp_pasv($connection, true);

      $varr_zona = $obj_mae->get_parametro_detalle(21);
      date_default_timezone_set($varr_zona['valorchar']);
      $v_hoy = date('Y-m-d');

      //@@@@ DOCUMENTO IDENTIDAD
      $destino = $file_documento_repre;
      $origen = $v_hoy."_".$input['nombre']."_".$input['identificacion']."/documento_representante_".$input['nombre_repre']."_".$input['a_paterno_repre']."_".$input['a_materno_repre']."_".$input['nrodoc_repre'].".pdf";
      $upload = ftp_get($connection, $destino, $origen, FTP_BINARY);
      if (!$upload) { echo 'Fallo la subida al FTP'; }

      //@@@@ REGISTRO MERCANTIL
      $destino = $file_registro_mercantil;
      $origen = $v_hoy."_".$input['nombre']."_".$input['identificacion']."/certificado_existencia_".$input['identificacion'].".pdf";
      $upload = ftp_get($connection, $destino, $origen, FTP_BINARY);
      if (!$upload) { echo 'Fallo la subida al FTP'; }

      //@@@@ PODERES
      $destino = $file_poderes;
      $origen = $v_hoy."_".$input['nombre']."_".$input['identificacion']."/ficha_rut_".$input['identificacion'].".pdf";
      $upload = ftp_get($connection, $destino, $origen, FTP_BINARY);
      if (!$upload) { echo 'Fallo la subida al FTP'; }

      ftp_close($connection);
    } else $output = array('id' => 2, 'mensaje' => '');
  }

      /*echo '<pre>';
      print_r($arr_resultado);
      die();*/
      
  //header("HTTP/1.1 200 OK");
  //echo json_encode($output);
  //exit();
  http_response_code(200);
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
//header("HTTP/1.1 400 Bad Request");
echo json_encode($output);
exit;
?>