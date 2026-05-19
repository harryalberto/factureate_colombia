<?php
session_start();
session_destroy();
session_start();
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
            if (paccion == 'cambiapass'){
                document.frm.action = "cambia_pass.php";
                document.frm.submit();
            }
            if (paccion == 'olvidepass'){
                document.frm.action = "olvide_pass.php";
                document.frm.submit();
            }
        }
    </script>
</HEAD>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Santo_Domingo");

    if (isset($_GET['token'])){
        $v_token = $_GET['token'];
        $v_fid = $_GET['fid'];
    } else {
        $v_token = '';
        $v_fid = '';
    }
?>
    <div class="login-container">
        <h1 class="title-login">Login Plataforma FACTUREATE</h1>
        <div class="img-login">
            <img src="img/logo.png" alt="Factureate">
        </div>
        <form class="form-login" name='frm' method='post' action='valida_user.php'>
            <input class="input-login-on" type="text" name="usuario" placeholder="Documento identificacion" autofocus>
            <input class="input-login-on" type="password" name="pass" placeholder="Password">
            <input type="hidden" name="token" id="token" value="<?=$v_token?>">
            <input type="hidden" name="fid" id="fid" value="<?=$v_fid?>">
            <p><span class="span-form"><a href=javascript:send_form("olvidepass")>Olvide mi password</a></span><br><br><span class="span-form"><a href=javascript:send_form("cambiapass")>Cambiar password</a></span></p>
            <button class="btn-login" onclick=javascript:send_form("valida")>login</button>
        </form>
    </div>
</BODY>
</HTML>

