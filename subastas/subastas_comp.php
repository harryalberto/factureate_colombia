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
<?php
    require("../lib/head.php");
    $acceso = 'SUBASTAS COMP';
    require("../lib/valida-acceso.php");
?>
    <!--<script src="https://code.jquery.com/jquery-3.2.1.js"></script>-->
   <!-- <script type="text/javascript" src="../lib/jquery.js"></script>-->
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').live('click', function(){
                $('#content').html('<div class="loading"><img src="../img/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var dataString = 'page='+page+'&rowcount='+rowcount;

		        $.ajax({
                    type: "GET",
                    url: "pagina_subastas_comp.php",
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
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'subastas/subastas_comp.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Subastas Compensadas
    </div>
    
    <div id="content2"><?php require('pagina_subastas_comp.php'); ?></div>
    <!------ END CUERPO VARIABLE ------>
    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="SubastaDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Subasta</h5></li>
                <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
            </ul>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
            <!--<p class="botontransaccionazul" id="btn_grabar_accionistas"><span class="icon-floppy-disk"></span><a href="javascript:guarda_accionistas('accionistas')" style=""> Guardar</a></p>-->
        </div>
        </div>
    </div>
    </div>
    <!--============= LLAMADA AL MODAL CON PARAMETROS -->`
    <script>
        $('.openBtnn').on('click',function(){
            var retorno = $(this).attr('retorno');
            var pagina = $(this).attr('pagina');
            var rowcount = $(this).attr('rowcount');
            var subastaid = $(this).attr('subastaid');
            
            $('.modal-body').load('subasta_detalle_modal.php?retorno='+retorno+'&pagina='+pagina+'&rowcount='+rowcount+'&subastaid='+subastaid,function(){
                $('#SubastaDetalle').modal({show:true});
            });
        });
    </script>
</BODY>
</HTML>