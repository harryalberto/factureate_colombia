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
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#content').html('<div class="loading"><img src="../images/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var dataString = 'page='+page+'&rowcount='+rowcount;

		        $.ajax({
                    type: "GET",
                    url: "pagina_facturas_inversion.php",
                    data: dataString,
                    success: function(data) {
                    $('#content2').fadeIn(1000).html(data);
                    $('.pagination li').removeClass('active');
                    $('.pagination li a[pagenum="'+page+'"]').parent().addClass('active');
                }
                });
            });
        });
    </script>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
        function closemodal(){
            $('.modalclase').fadeOut();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'inversionistas/facturas_inversion.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:0px auto;">
        Inversiones realizadas
    </div>
    
    <div id="content2"><? require('pagina_facturas_inversion.php'); ?></div>
    <?
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'">Anterior</a></li>';
    
        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'">'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'">'.$i.'</a></li>';
        }
    
        if ($pageNum != $total_paginas) echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
    ?>
    <!------ END CUERPO VARIABLE ------>
    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="INVERSION" tabindex="-1" role="dialog">
        <div class="modal-dialog">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de Inversi&oacute;n</h5></li>
                        <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
                    </ul>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
                </div>
            </div>
        </div>
    </div>
    <!-- llamada al modal -->
    <script>
        $('.openBtn2').on('click',function(){
            var fid = $(this).attr('fid');
            var pid = $(this).attr('pid');

            $('.modal-body').load('detalle_inversion.php?fid='+fid+'&pid='+pid,function(){
                $('#INVERSION').modal({show:true});
            });
        });

        function exportar_reporte(){
            $.ajax({
                type: "GET",
                url: "exportar_inversiones.php",
                data:{
                        "accion":'exportar'
                },
                success: function(data) {
                    $('#content2').fadeIn(1000).html(data);
                    refresh_page();
                }
            });
        }

        function refresh_page(){
            location.href = "facturas_inversion.php";
        }
    </script>
    <!---=============== end modal ==============--->
</BODY>
</HTML>