<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'GMENU';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;

if ($_POST['mid'] > 0) $obj_mae->update_menu($_POST['nombre_back'],$_POST['nombre_visual'],$_POST['codigo'],$_POST['orden'],$_POST['pagina'],$_POST['mid'],$_POST['aid']);
else $obj_mae->insert_menu($_POST['nombre_back'],$_POST['nombre_visual'],$_POST['codigo'],$_POST['orden'],$_POST['pagina']);

$v_pagina_retorno = 'g-menu.php';

$redireccion = '    <script>
                        location.href = "'.$v_pagina_retorno.'";
                    </script>';

if ($redireccion != '') echo $redireccion;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php
    date_default_timezone_set("America/Lima");
    
    $menu = 'facturas/facturas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>