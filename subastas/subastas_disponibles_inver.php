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
    $acceso = 'SUBASTAS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#content').html('<div class="loading"><img src="../img/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
		        var sectorid = $(this).attr('sectorid');
                var riesgoid = $(this).attr('riesgoid');
		        var dataString = 'page='+page+'&rowcount='+rowcount+'&dsectoreconomicoid='+sectorid+'&dtriesgoid='+riesgoid;

		        $.ajax({
                    type: "GET",
                    url: "pagina_subastas_dispo_inver.php",
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
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;

$arrestados = $objmaestro->get_estados('SUBASTA');
$seconomicoid = 0;
$triesgoid = 0;
//seleccion de los filtros y valores iniciales
if ($_SESSION['user']['tipousuario'] == 2 || $_SESSION['user']['tipousuario'] == 5){ //usuario global o pertenece a factureate
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 31;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
}
if (isset($_POST['seconomicoid'])){
    $seconomicoid = $_POST['seconomicoid'];
    $triesgoid = $_POST['triesgoid'];
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'subastas/subastas_disponibles_inver.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Subastas Disponibles para inversi&oacute;n
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="subastas_disponibles_inver.php">
        <ul>
            <li>Sector econ&oacute;mico:</li>
            <li>
                <select name="seconomicoid" class="frminput_text">
                <?php
                    $arrsectores = $objmaestro->get_tipos('SECTORECO');

                    if ($seconomicoid == 0) echo '<option value = "0" selected>Todos los sectores</option>';
                    else echo '<option value = "0">Todos los sectores</option>';

                    for ($i=0; $i<count($arrsectores); $i++){
                        if ($seconomicoid == $arrsectores[$i]['id']) 
                            echo '<option value = "'.$arrsectores[$i]['id'].'" selected>'.$arrsectores[$i]['nombre'].'</option>';
                        else echo '<option value = "'.$arrsectores[$i]['id'].'">'.$arrsectores[$i]['nombre'].'</option>';
                    }
                ?>
                </select>
            </li>
            <li>Riesgo Pagador:</li>
            <li>
                <select name="triesgoid" class="frminput_text">
                <?php
                    $arrtriesgo = $objmaestro->get_triesgopagador();

                    if ($triesgoid == 0) echo '<option value = "0" selected>Todos los tipos</option>';
                    else echo '<option value = "0">Todos los tipos</option>';

                    for ($i=0; $i<count($arrtriesgo); $i++){
                        if ($triesgoid == $arrtriesgo[$i]['id'])
                            echo '<option value = "'.$arrtriesgo[$i]['id'].'" selected>'.$arrtriesgo[$i]['calificacion'].' - '.$arrtriesgo[$i]['nombre'].'</option>';
                        else echo '<option value = "'.$arrtriesgo[$i]['id'].'">'.$arrtriesgo[$i]['calificacion'].' - '.$arrtriesgo[$i]['nombre'].'</option>';
                    }
                ?>
                </select>
            </li>
            <!--<li class="botontransaccion" style="margin-top:5px;"><a href="javascript:filtrar()"><span class="icon-filter"></span> Filtrar</a></li>-->
            <button type="button" class="btn btn-primary" style="font-size:12px;background-color:var(--color-azulv2);border:none;" onclick=filtrar()>
                <span class="icon-filter" style="font-size:16px;"></span> Filtrar
            </button>
        </ul>
        </form>
    </div>
    <div id="content2"><?php require('pagina_subastas_dispo_inver.php'); ?></div>
    <?php
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) 
            echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'" sectorid="'.$seconomicoid.'" riesgoid="'.$triesgoid.'">Anterior</a></li>';

        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" sectorid="'.$seconomicoid.'" riesgoid="'.$triesgoid.'">'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" sectorid="'.$seconomicoid.'" riesgoid="'.$triesgoid.'">'.$i.'</a></li>';
        }

        if ($pageNum != $total_paginas) 
            echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'" sectorid="'.$seconomicoid.'" riesgoid="'.$triesgoid.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
    ?>
    <!------ END CUERPO VARIABLE ------>
    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="PropuestaDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Propuesta</h5></li>
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
            var fid = $(this).attr('fid');
            var pid = $(this).attr('pid');
            var retorno = $(this).attr('retorno');
            var pagina = $(this).attr('pagina');
            var rowcount = $(this).attr('rowcount');
            var subastaid = $(this).attr('subastaid');
            
            $('.modal-body').load('propuesta_detalle_modal.php?fid='+fid+'&pid='+pid+'&retorno='+retorno+'&pagina='+pagina+'&rowcount='+rowcount+'&subastaid='+subastaid,function(){
                $('#PropuestaDetalle').modal({show:true});
            });
        });

        function refresh_page(){
            location.href = 'subastas_disponibles_inver.php';
        }
    </script>
</BODY>
</HTML>