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
<?php
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
    <!--<script src="https://code.jquery.com/jquery-3.2.1.js"></script>-->
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#content').html('<div class="loading"><img src="../images/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var dataString = 'page='+page+'&rowcount='+rowcount;

		        $.ajax({
                    type: "GET",
                    url: "propuestas_inversion_pagina.php",
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
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'emisores/confirmacion_financiamiento.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Confirmación de Financiamientos
    </div>

    <hr>

    <div id="content2"><?php require('confirmacion_financiamiento_pagina.php'); ?></div>
    <?php
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
    
    <!-- llamada al modal -->
    <script>
        $('.openBtn2').on('click',function(){
            var confirma_id = $(this).attr('confirma_id');
            
            $.ajax({
                url:"confirmacion_financiamiento_proceso.php",
                type:'post',
                data:{
                        "confirma_id":confirma_id,
                        "accion":'confirmar'
                },
                success:function(data,status){
                    $('#content2').fadeIn(1000).html(data);
                    //$('#content2').modal('hide');
                    refresh_page();
                }
            });
        });

        $('.openBtn3').on('click',function(){
            var confirma_id = $(this).attr('confirma_id');
            
            $.ajax({
                url:"confirmacion_financiamiento_proceso.php",
                type:'post',
                data:{
                        "confirma_id":confirma_id,
                        "accion":'rechazar'
                },
                success:function(data,status){
                    $('#content2').fadeIn(1000).html(data);
                    //$('#principal').modal('hide');
                    refresh_page();
                }
            });
        });

        function refresh_page(){
            location.href = 'confirmacion_financiamiento.php';
        }
    </script>
    <!---=============== end modal ==============--->
</BODY>
</HTML>