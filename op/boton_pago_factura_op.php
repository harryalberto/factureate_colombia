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
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>

<HEAD>

<?
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>

</HEAD>

<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@ LOGICA

if (isset($_GET['contador'])){
    echo 'pago en bloque<br>';

    for ($i = 0; $i < $_GET['contador']; $i++){
        echo 'factura id = '.$_GET['fid'.$i].'/ monto = '.$_GET['monto'.$i].'//';
    }
} else {
    echo 'pago individual<br>';
    echo 'factura id='.$_GET['fid'];
}

?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

    <div id="principal" style="padding-left: 10px; overflow: hidden;">

        <div class="contenedor_formulario" style="width: 100%; overflow: hidden;">


        </div>  <!-- END CONTENEDOR FORMULARIO -->

        <!--========================================
        ===================== BOTONERA
        ============================================-->
        <div style="width:100%; float:left;margin-bottom:5px;overflow: hidden;">
            
        </div>

    </div> <!-- END CONTENEDOR PRINCIPAL -->

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">

        
    </script>

</BODY>
</HTML>