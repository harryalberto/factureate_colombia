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
<?
    require("../lib/head.php");
    $acceso = 'FINANCIAMIENTO';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#content2').html('<div class="loading"><img src="../img/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var estados = $(this).attr('estados');
		        var dataString = 'page='+page+'&rowcount='+rowcount+'&estados='+estados;

		        $.ajax({
                    type: "GET",
                    url: "pagina_finan_xestado.php",
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
    </script>
</HEAD>
<?
/*###############################################
############## LOGICA */
$objmaestro = new maestros;
$arrestados = $objmaestro->get_estados('FINANCIAMIENTO');
$dias_max_xvencer = $objmaestro->dias_vigencia_riesgos(12);

date_default_timezone_set("America/Santo_Domingo");
//========= LOGICA DE ESTADOS
$v_estado_id = 27;
$v_estados = '27,51,-1,-1,-1,-1';
$v_arr_estados[0] = 27; $v_arr_estados[1] = 51;

if (!empty($_POST['estados'])){
    $v_i = 0;
    $v_estados = '';
    foreach($_POST['estados'] as $selected){
        if ($v_i > 0) $v_estados .= ',';
        $v_estados .= $selected;
        $v_arr_estados[$v_i] = $selected;
        $v_i ++;
    }

    $v_i++;

    if ($v_i <= 6){
        for ($v_j = $v_i; $v_j <= 6; $v_j++){
            $v_estados .= ',-1';
        }
    }
}
if (!empty($_GET['estados'])){
    $v_estados = $_GET['estados'];
    $v_longitud = strlen($v_estados);
    $v_aux = '';
    $v_count = 0;

    for ($i = 0; $i < $v_longitud; $i++){
        $v_pos = ($v_longitud - $i) * -1;
        $v_char = substr($v_estados, $v_pos, 1);

        if ($v_char != ',') $v_aux .= $v_char;
        else {
            $v_arr_estados[$v_count] = $v_aux;
            $v_count ++;
            $v_aux = '';
        }
    }

    $v_arr_estados[$v_count] = $v_aux;
}
//#############################################################
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'facturas/finan_xestado.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO PRINCIPAL ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Financiamientos
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="finan_xestado.php">
        <ul>
            <li><span class="icon-filter"></span> Estados de los FINANCIAMIENTOS:</li>
        </ul>
        <ul>
    <?
        for ($i=0; $i<count($arrestados); $i++){
            if (in_array($arrestados[$i]['id'], $v_arr_estados))
                echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="'.$arrestados[$i]['id'].'" checked><label style="padding-left:5px;padding-right:5px;">'.$arrestados[$i]['nombre'].'</label></li>';
            else 
                echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="'.$arrestados[$i]['id'].'"><label style="padding-left:5px;padding-right:5px;">'.$arrestados[$i]['nombre'].'</label></li>';
        }
        
    ?>
            <!--<li class="botontransaccionazul" style="width:100px;"><a href="javascript:filtrar()"><span class="icon-filter"></span> Filtrar</a></li>-->
            <button style="font-size:12px;background-color:var(--color-azulv2);" type="button" class="btn btn-primary" onclick="filtrar()"><span class="icon-filter" style="font-size:16px;"></span> Filtrar</button>
        </ul>
        </form>
    </div>
    
    <!--###############################################
        ############### PAGINA -->
    <div id="content2"><? require('pagina_finan_xestado.php'); ?></div>
    
    <!-- ############################################## 
        ############## BOTONES DE PAGINACION -->
    <?
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'" estados="'.$v_estados.'">Anterior</a></li>';
    
        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" estados="'.$v_estados.'">'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" estados="'.$v_estados.'">'.$i.'</a></li>';
        }
    
        if ($pageNum != $total_paginas) echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'" estados="'.$v_estados.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
    ?>
    <!------ END CUERPO PRINCIPAL ------>
    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido -->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:600px;float:left;"><h5 id="exampleModalLabel" style="color:var(--color-azulv2);font-weight: bold;font-size:20px;">Detalle del Financiamiento</h5></li>
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
            var ffid = $(this).attr('ffid');
            
            $('.modal-body').load('detalle_financiamiento.php?ffid='+ffid,function(){
                $('#myModal').modal({show:true});
            });
        });
    </script>
    <!---=============== end modal ==============--->
</BODY>
</HTML>