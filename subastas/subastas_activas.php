<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'SUBASTAS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript" src="../lib/jquery.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').live('click', function(){
                $('#content').html('<div class="loading"><img src="../img/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var estadoid = $(this).attr('estadoid');
		        var dataString = 'page='+page+'&rowcount='+rowcount+'&estadoid='+estadoid;

		        $.ajax({
                    type: "GET",
                    url: "pagina_subastas_xestado.php",
                    data: dataString,
                    success: function(data) {
                        $('#content').fadeIn(1000).html(data);
                    }
                });
            });
        });
    </script>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$estadoid = 24;
$return = 1;    // flag para buscar la pagina de retorno
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'subastas/subastas_activas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Subastas Activas
    </div>
    <div class="listpag"><? require('pagina_subastas_xestado.php'); ?></div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>