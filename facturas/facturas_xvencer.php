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
    $acceso = 'FACTURAS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#content').html('<div class="loading"><img src="../images/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var dataString = 'page='+page+'&rowcount='+rowcount;

		        $.ajax({
                    type: "GET",
                    url: "pagina_facturas_xvencer.php",
                    data: dataString,
                    success: function(data) {
                    $('#content').fadeIn(1000).html(data);
                }
                });
            });
        });
    </script>
</HEAD>
<!-- modal de la comunicacion -->
<div class="modalclase">
        <div class="bodymodal">
        <iframe id="iframepdf" frameborder="0" scrolling="yes" width="90%" height="90%"></iframe>
            <a href="#" class="closemodal" onclick="closemodal();" style="text-align:center;font-weight:bold;color:#ffffff;font-size: 14px;width:50px;background-color: #e89b24;">Cerrar</a>
        </div>
</div>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'facturas/facturas_xvencer.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Facturas x Vencer
    </div>
    <div class="listpag"><?php require('pagina_facturas_xvencer.php'); ?></div>
    <!--============ Modal =============-->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
        <!-- Modal contenido-->
        <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Detalle de comunicaci&oacute;n con pagador</h4>
        </div>
        <div class="modal-body">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
    </div>
    <script>
        $('.openBtn2').on('click',function(){
            var fid = $(this).attr('fid');
            var eid = $(this).attr('eid');

            $('.modal-body').load('detalle_comunica_pagador.php?fid='+fid+'&eid='+eid,function(){
                $('#myModal').modal({show:true});
            });
        });
    </script>
    <!---=============== end modal ==============--->
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>