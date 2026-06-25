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
$obj_inv = new inversiones;
$vobj_cuenta = new cuentas;
$objsubasta = new subasta;

//---- CALCULO DE SALDOS PRELIMINARES
$varr_prop = $obj_inv->get_propuestas_pre_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,1000);
$v_monto_preliminar = 0;
$varr_preliminares = array();
$v_j = 0;

for ($i=0; $i<count($varr_prop); $i++){
    if ($varr_prop[$i]['estado_id'] == 54){
        $v_monto_preliminar = $v_monto_preliminar + $varr_prop[$i]['monto'];
        $v_encontrado = 0;

        for ($j=0; $j<count($varr_preliminares); $j++){
            if ($varr_preliminares[$j]['moneda_id'] == $varr_prop[$i]['moneda_id']){
                $varr_preliminares[$j]['monto'] = $varr_preliminares[$j]['monto'] + $varr_prop[$i]['monto'];
                $v_encontrado = 1;
            }
        }

        if ($v_encontrado == 0){
            $varr_preliminares[$v_j]['moneda_id'] = $varr_prop[$i]['moneda_id'];
            $varr_preliminares[$v_j]['monto'] = $varr_prop[$i]['monto'];
            $varr_preliminares[$v_j]['simbolo_moneda'] = $varr_prop[$i]['simbolo_moneda'];
            $v_j ++;
        }
    }
}

//---- CALCULO DE SALDOS DISPONIBLES
$varr_cuentas = $vobj_cuenta->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);

//---- FILTRADO DE PRELIMINARES
if (isset($_POST['check_preliminar'])){
    if ($_POST['check_preliminar'] == 1) $v_solopreliminar = 1;
    else $v_solopreliminar = 0;
} else $v_solopreliminar = 0;
/*--------------------------------------------------------*/
$total_paginas = 0;
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'inversionistas/propuestas_inversion.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin: 0px auto;">
        Propuestas realizadas
    </div>

    <!-- verificacion de propuestas preliminares -->
<?php
    if ($v_monto_preliminar > 0){
        $v_label_pre = '';

        for ($j=0; $j<count($varr_preliminares); $j++){
            for ($i=0; $i<count($varr_cuentas); $i++){
                if ($varr_cuentas[$i]['moneda_id'] == $varr_preliminares[$j]['moneda_id']){
                    $v_monto_enpropuestas = $objsubasta->monto_propuestas_subasta($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$varr_cuentas[$i]['moneda_id']);
                    $v_pendiente = ($varr_cuentas[$i]['saldo_disponible'] - $v_monto_enpropuestas);
                    $v_pendiente = $varr_preliminares[$j]['monto'] - $v_pendiente;
                }
            }

            /*if ($j == 0) $v_label_pre .= $varr_preliminares[$j]['simbolo_moneda'].' '.number_format($varr_preliminares[$j]['monto'],2,'.',',');
            else $v_label_pre .= ' + '.$varr_preliminares[$j]['simbolo_moneda'].' '.number_format($varr_preliminares[$j]['monto'],2,'.',',');*/
            if ($j == 0) $v_label_pre .= $varr_preliminares[$j]['simbolo_moneda'].' '.number_format($v_pendiente,2,'.',',');
            else $v_label_pre .= ' + '.$varr_preliminares[$j]['simbolo_moneda'].' '.number_format($v_pendiente,2,'.',',');
        }

        if ($v_solopreliminar == 1) $v_checked = 'checked="yes"';
        else $v_checked = '';
?>
    <form name='filtros' method='post' id='filtros'>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="check_preliminar" id="check_preliminar" style="margin-left:20px;" value="1" onclick="checkPreliminares()" <?php echo $v_checked;?>>
        <label class="form-check-label" for="check_preliminar">Mostrar solo propuestas preliminares</label>
        <label style="margin-left:20px;margin-right:10px;font-size:16px;color:var(--color-rojo);">Propuestas preliminares pendiente de saldo: <?php echo $v_label_pre;?></label>
        <button style="font-size:12px;background-color:var(--color-rojo);border:none;" type="button" class="btn btn-primary"><i class="fa-solid fa-coins" style="font-size:16px;"></i> Cargar Saldo</button>
    </div>
    </form>
<?php
    }
?>

    <div id="content2"><?php require('propuestas_inversion_pagina.php'); ?></div>
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
    <div class="modal fade" id="PropuestaDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">DETALLE DE PROPUESTA</h5></li>
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
            var retorno = $(this).attr('retorno');
            var pagina = $(this).attr('pagina');
            var rowcount = $(this).attr('rowcount');
            var subastaid = $(this).attr('subastaid');
            var estado_id = $(this).attr('estado_id');
            
            if (estado_id == 54) $('.modal-title').text('DETALLE DE PROPUESTA');

            $('.modal-body').load('propuesta_detalle_modal.php?fid='+fid+'&pid='+pid+'&retorno='+retorno+'&pagina='+pagina+'&rowcount='+rowcount+'&subastaid='+subastaid,function(){
                $('#PropuestaDetalle').modal({show:true});
            });
        });

        function refresh_page(){
            location.href = 'propuestas_inversion.php';
        }

        function checkPreliminares(){
            var checkpre = document.getElementById("check_preliminar");

            document.filtros.action = 'propuestas_inversion.php'
            document.filtros.submit();
        }
    </script>
    <!---=============== end modal ==============--->
</BODY>
</HTML>