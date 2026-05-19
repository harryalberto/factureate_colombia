<?php
session_start();
require("conn/conn_db.inc");
require("conn/conn_db_param.inc");
require("conn/conn_db_trans.inc");
require("conn/conn_db_param_trans.inc");
require("lib-seg/seguridad-acceso.php");
require("lib-trans/maestros.php");

$objseg = new seguridad(); 
$obj_mae = new maestros;

$identi = $_POST['usuario'];
$pass = $_POST['pass'];

$usuario = $objseg->valida_user($identi,$pass);

if ($usuario['encontrado'] == 0){
  if (isset($_POST['token'])) $v_parametros = '?token='.$_POST['token'].'&fid='.$_POST['fid'];
  else $v_parametros = '';

  $mensaje = 'Lo sentimos ingreso mal el usuario o el password';
  $redir = "<meta http-equiv=refresh content='5;url=bo-index.php".$v_parametros."'>";
} else $redir = '';
?>
<HTML>
<HEAD>
  <title>Factureate</title>
  <meta content='text/html; charset=UTF-8' http-equiv='content-type'>
  <meta name='description' content='Factureate'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <link rel='stylesheet' type='text/css' href='css/factureate.css'>
  <link rel='stylesheet' type='text/css' href='css/style.css'>
  <link rel='shortcut icon' href='img/factureate.ico'>
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>
  <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
  <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>

  <script type="text/javascript">
    function send_form(paccion){
      if (paccion == 'valida') document.frm.submit();
    }
  </script>
<?php
  echo $redir;
?>
</HEAD>
<BODY>
<?php
  if ($usuario['encontrado'] == 0) echo $mensaje;
  else{
    //---- el usuario y clave son correctos

    if (isset($_POST['token']) && $_POST['token'] != ''){
      $acceso_express = $objseg->valida_acceso_express($usuario['id'], $_POST['token'], $_POST['fid']);

      if ($acceso_express > 0){
        //---- el acceso es correcto
        
        $objseg->inicia_session_usuario($usuario['id']);
        
        $_SESSION['user']['zona_horaria'] = $obj_mae->get_zona_horaria();

        if ($_SESSION['user']['empresaid'] > 0){
          $arr_empresa = $obj_mae->get_datos_emisor($_SESSION['user']['empresaid']);
          $_SESSION['user']['empresa'] = $arr_empresa['nombre'];
        } else $_SESSION['user']['empresa'] = '';
        
        echo '
        <input type="hidden" id="accion" value="panel">
        <input type="hidden" id="fid" value="'.$_POST['fid'].'">';
      } else{
        //---- no corresponde el acceso
        echo 'Lo sentimos la información ingresada no es correcta, posiblemente ingreso mal su usuario y password o el link utilizado no es valido, si el link no es valido le sugerimos ingresar a la plataforma y buscar la factura de su interes ... Gracias!!';
        echo '
        <input type="hidden" id="accion" value="rechazo_express">';
      }
    } else {
        echo '
        <input type="hidden" id="accion" value="tradicional">';
      //==== acceso tradicional
?>
    <div class="login-container">
        <h1 class="title-login">Login Plataforma FACTUREATE</h1>
        <div class="img-login">
            <img src="img/logo.png" alt="Factureate">
        </div>
        <form class="form-login" name='frm' method='post' action='valida_cert.php'>
            <input type=hidden name="usuarioid" value="<?=$usuario['id']?>">
            <p style="font-weight: bold;">Ingrese el siguiente codigo certificado</p>
            <p style="font-weight: bold;font-size:24px;"><?php echo $objseg->decrip($usuario['certificado']); ?></p>
            <input class="input-login-on" type="text" name="certificado" placeholder="Certificado" autofocus>
            <button class="btn-login" onclick=javascript:send_form("valida")>Enviar</button>
        </form>
    </div>

<?php
    }
  }
?>

  <script>
      document.addEventListener("DOMContentLoaded", redireccionaAcceso);

      function redireccionaAcceso(){
        let accion = document.getElementById("accion").value

        if (accion == 'panel'){
          let fid = document.getElementById("fid").value

          location.href = "panel/panel_inversionista.php?fid="+fid;
        } else {
          if (accion == 'rechazo_express') {
            setTimeout(function() {
              window.location.href = "bo-index.php";
            }, 3000);
          }
        }
      }
  </script>
</BODY>
</HTML>
