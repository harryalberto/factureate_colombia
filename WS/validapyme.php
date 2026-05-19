<?php
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

  if ($empresa_existe > 0) $output = array('id' => -1, 'mensaje' => '-1');
  else {
    // llamada a la inserción de empresas
    $resultado = $obj_mae->registro_empresa($input);
    
    if ($resultado > 0){ 
      //@@@@ TRANSFERENCIA DE ARCHIVOS DESDE LA WEB A LA PLATAFORMA
      $varr_archivos = array( 'razon_social' => $input['nombre'], 'identificacion' => $input['identificacion'], 
                              'nombre_repre' => $input['nombre_repre'].'_'.$input['a_paterno_repre'].'_'.$input['a_materno_repre'],
                              'nrodoc_repre' => $input['nrodoc_repre'], 'nombre_representante' => $input['nombre_repre'],
                              'ap_representante' => $input['a_paterno_repre'], 'am_representante' => $input['a_materno_repre']);
      $obj_st->ws_transfiere_archivos($varr_archivos);

      $output = array('id' => 1, 'mensaje' => $resultado);
      $arr_nusuario = array('identificacion' => $input['nrodoc_repre'], 'password' => $input['password'],
                          'email' => $input['email_repre'], 'nombre' => $input['nombre_repre'], 'apellido' => $input['a_paterno_repre'].' '.$input['a_materno_repre'],
                          'tipodoc' => $input['tipodoc_repre'], 'tipousuario' => 3, 'perfilid' => 4,
                          'empresaid' => $resultado);
      
      $arr_resultado = $obj_seg->crear_usuario($arr_nusuario);
      $varr_link = $obj_mae->get_parametro_detalle(53);

      // envio de correo al usuario registrado
      $arr_mail = array('mail_salida' => 'pymes@factureate.com', 'nombre_salida' => 'Factureate',
                        'mail_destino' => $input['email_repre'], 'subject' => 'Registro en FACTUREATE',
                        'body' => 'Hola '.$input['nombre_repre'].',<br><br>Se ha registrado su empresa en nuestra plataforma, ingrese a la plataforma con la siguiente informaci&oacute;n y confirme su informaci&oacute;n presionando el bot&oacute;n "ENVIAR", luego de ello recibir&aacute; una comunicaci&oacute;n de nuestra &aacute;rea de vinculaciones para la firma del contrato y listo!! podr&aacute; registrar sus facturas para recibir financiamiento, tenga presente que nuestra vincuaci&oacute;n no tiene ning&uacute;n costo.<br>
                                Sus credenciales de acceso son las siguientes:<br>
                                Usuario: '.$arr_resultado['identificacion'].'<br>
                                Contrase&ntilde;a: '.$arr_resultado['password'].'<br>
                                Link de la plataforma: '.$varr_link['valorchar'].'<br><br>
                                Gracias por confiar en nosotros, trabajaremos para conseguir el financiamiento que necesita.<br><br>
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
    } else $output = array('id' => 2, 'mensaje' => '');
  }
  
  header("HTTP/1.1 200 OK");
  echo json_encode($output);
  exit();
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");
?>