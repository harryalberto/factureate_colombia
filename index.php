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
</HEAD>

<?php
    if (isset($_POST['certificado'])) $v_parametros = '?token='.$_POST['certificado'].'&fid='.$_POST['factura_id'];
    else $v_parametros = '';
?>

<BODY>
    <!--<frameset rows="0%,*" frameborder="no" scrolling="no">
        <frame src="index-sup.html" name="cabecera"></frame>
        <frame src="bo-index.php<?php //echo $v_parametros;?>" frameborder="0" name="cuerpo"></frame>
    </frameset>-->
    <iframe src="bo-index.php<?php echo $v_parametros;?>" scrolling="auto" style="width: 100%; height: 100vh; border: none; display: block;"></iframe>
</BODY>
</HTML>