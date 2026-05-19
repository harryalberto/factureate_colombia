<?php
session_start();
require("conn/conn_db.inc");
require("conn/conn_db_param.inc");
require("conn/conn_db_trans.inc");
require("conn/conn_db_param_trans.inc");
require("lib-trans/maestros.php");
require("lib-seg/seguridad-acceso.php");

$certificado = $_POST['certificado'];
$usuarioid = $_POST['usuarioid'];
$objseg = new seguridad;
$obj_mae = new maestros;
$resultado = $objseg->valida_certificado($usuarioid,$certificado);

$mensaje = '';

if ($resultado == 0){
    $mensaje = 'Lo sentimos, el certificado ingresado est&aacute; equivocado';
    $redir = "<meta http-equiv=refresh content='5;url=bo-index.php'>";
} else{
    $v_pagina_inicio = $objseg->inicia_acceso($usuarioid,$certificado); // carga las sesion con los datos del usuario y el menu de acceso
    
    //==== ZONA HORARIA
    $_SESSION['user']['zona_horaria'] = $obj_mae->get_zona_horaria();

    if ($_SESSION['user']['empresaid'] > 0){
        $arr_empresa = $obj_mae->get_datos_emisor($_SESSION['user']['empresaid']);
        $_SESSION['user']['empresa'] = $arr_empresa['nombre'];
    } else $_SESSION['user']['empresa'] = '';

    if ($_SESSION['user']['tipousuario'] == 2) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_global.php'>";   //GLOBAL
    elseif ($_SESSION['user']['tipousuario'] == 3) $redir = "<meta http-equiv=refresh content='0;url=emisores/panel_emisor.php'>";   //EMISOR
    elseif ($_SESSION['user']['tipousuario'] == 4) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_inversionista.php'>";   //INVERSIONISTA
    elseif ($_SESSION['user']['tipousuario'] == 6) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_inversionista.php'>";   //INVERSIONISTA EMISOR
    else{   //FACTUREATE
        if ($_SESSION['user']['perfiltipo'] == 7) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_analista.php'>";   //ANALISTA OP
        elseif ($_SESSION['user']['perfiltipo'] == 8) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_gerente.php'>";   //GERENTE
        elseif ($_SESSION['user']['perfiltipo'] == 13) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_analista_fin.php'>";   //ANALISTA FIN
        elseif ($_SESSION['user']['perfiltipo'] == 14) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_clo.php'>";   //CLO
        elseif ($_SESSION['user']['perfiltipo'] == 15) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_coo.php'>";   //COO
        elseif ($_SESSION['user']['perfiltipo'] == 16) $redir = "<meta http-equiv=refresh content='0;url=panel/panel_ceo.php'>";   //CEO
        else $redir = "";
    }
    //===== NUEVO CODIGO DE ACCESO
    if ($v_pagina_inicio != '') $redir = "<meta http-equiv=refresh content='0;url=".$v_pagina_inicio."'>";
}
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
    <?php
    echo $redir;
    ?>
</HEAD>
<BODY>
    <div style="padding:20px;text-align:center;font-size:14px;">
        <?php
        echo $mensaje;
        ?>
    </div>
</BODY>
</HTML>