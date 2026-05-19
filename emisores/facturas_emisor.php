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
    $acceso = 'FACTURAS';
    require("../lib/valida-acceso.php");
?>
    <script src="https://code.jquery.com/jquery-3.2.1.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#content').html('<div class="loading"><img src="../images/loading.gif" width="70px" height="70px"/></div>');
		        var page = $(this).attr('pagenum');
		        var rowcount = $(this).attr('rowcount');
                var estado = $(this).attr('estado');
                var tusuario = $(this).attr('tusuario');
                var empresaid = $(this).attr('empresaid');
		        var dataString = 'page='+page+'&rowcount='+rowcount+'&estado='+estado+'&tusuario='+tusuario+'&empresaid='+empresaid;

		        $.ajax({
                    type: "GET",
                    url: "pagina_facturas_emisor.php",
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
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;

$arrestados = $objmaestro->get_estados('FACTURA');
$varr_estadofin = $objmaestro->get_estados('FACTFIN');

//seleccion de los filtros y valores iniciales
if ($_SESSION['user']['tipousuario'] == 2 || $_SESSION['user']['tipousuario'] == 5){ //usuario global o pertenece a factureate
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 12;
    $estado_id_filtro = 12;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
} elseif ($_SESSION['user']['tipousuario'] == 3){   // emisores
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 0;
    $estado_id_filtro = 0;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
}

if (isset($_POST['estadoid'])){
    $estado_id_filtro = $_POST['estadoid'];
    $estadoid = 21;
}

$v_tipousuario = $_SESSION['user']['tipousuario'];
$v_empresaid = $_SESSION['user']['empresaid'];
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/facturas_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Facturas
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="facturas_emisor.php">
        <ul>
            <?
            if ($filtroestado == 'on'){
                echo '<li>Estado Factura:</li>
                    <li><select name="estadoid" class="formulario_control" id="estadoid">';
                
                if ($estadoid == 0) echo '<option value="0" selected>---- Todos ----</option>';
                else echo '<option value="0">---- Todos ----</option>';
                
                for ($i=0; $i<count($arrestados); $i++){
                    if ($arrestados[$i]['id'] == 21){
                    // FACTURAS APROBADAS
                        for ($j=0; $j<count($varr_estadofin); $j++){
                            if ($varr_estadofin[$j]['id'] != 36){
                                $v_id_option = 100 + $varr_estadofin[$j]['id'];

                                if ($estado_id_filtro == $v_id_option)
                                    echo '<option value="'.$v_id_option.'" selected>'.$varr_estadofin[$j]['nombre'].'</option>';
                                else
                                    echo '<option value="'.$v_id_option.'">'.$varr_estadofin[$j]['nombre'].'</option>';
                            }
                        }
                    } else {
                        if ($estado_id_filtro == $arrestados[$i]['id']) 
                            echo '<option value="'.$arrestados[$i]['id'].'" selected>'.$arrestados[$i]['nombre'].'</option>';
                        else 
                            echo '<option value="'.$arrestados[$i]['id'].'">'.$arrestados[$i]['nombre'].'</option>';
                    }
                }

                echo '  </select>
                    </li>';
            }
            ?>
            <li><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="filtrar()">
                    <i class="fa-solid fa-filter"></i> Filtrar</button></li>
        </ul>
        </form>
    </div>
    <div id="content2"><? require('pagina_facturas_emisor.php'); ?></div>
    <?
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'" estado="'.$estado_id_filtro.'" tusuario="'.$v_tipousuario.'" empresaid="'.$v_empresaid.'">Anterior</a></li>';
    
        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" estado="'.$estado_id_filtro.'" tusuario="'.$v_tipousuario.'" empresaid="'.$v_empresaid.'">'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" estado="'.$estado_id_filtro.'" tusuario="'.$v_tipousuario.'" empresaid="'.$v_empresaid.'">'.$i.'</a></li>';
        }
    
        if ($pageNum != $total_paginas) echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'" estado="'.$estado_id_filtro.'" tusuario="'.$v_tipousuario.'" empresaid="'.$v_empresaid.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
    ?>
    <!------ END CUERPO VARIABLE ------>
    <script type="text/javascript">
        function verdetalle(p_id, p_tipo){
            location.href = 'registro_factura.php?ret=fac&tipo='+p_tipo+'&id='+p_id;
        }
    </script>
</BODY>
</HTML>