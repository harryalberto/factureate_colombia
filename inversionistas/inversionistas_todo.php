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
    $acceso = 'EMPRESAS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.paginate').on('click', function(){
                $('#contenedor_listado').html('<div class="loading"><img src="../img/loading.gif" width="70px" height="70px"/></div>');
                var page = $(this).attr('pagenum');
                var rowcount = $(this).attr('rowcount');
                
                var dataString = 'page='+page+'&rowcount='+rowcount;

                $.ajax({
                    type: "GET",
                    url: "pagina_empresas.php",
                    data: dataString,
                    success: function(data) {
                        $('#contenedor_listado').fadeIn(1000).html(data);
                        $('.pagination li').removeClass('active');
                        $('.pagination li a[pagenum="'+page+'"]').parent().addClass('active');
                    }
                });
            });
        });
    </script>
</HEAD>

<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA

$vobj_mae = new maestros;

$varr_estados = $vobj_mae->get_estados('INVERSIONISTA');
$v_q_estados = count($varr_estados);

if (!empty($_POST['estados'])){
    $v_i = 0;
    $v_estados = '';
    
    foreach($_POST['estados'] as $selected){
        if ($v_i > 0) $v_estados .= ',';

        $v_estados .= $selected;
        $varr_estados_f[$v_i] = $selected;
        $v_i ++;
    }

    $v_i++;

    if ($v_i <= $v_q_estados){
        for ($v_j = $v_i; $v_j <= $v_q_estados; $v_j++){
            $v_estados .= ',-1';
        }
    }
} else {
    $v_estados = '7, 8, -1, -1, -1';
    $varr_estados_f[0] = 7; $varr_estados_f[1] = 8;
}
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'inversionistas/inversionistas_todo.php';
    //$pagina = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:300px;margin:auto;">
        Relaci&oacute;n de Inversores
    </div>

    <!--================================================== 
    ========================== FILTROS -->

    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="inversores.php">
        <ul>
            <li><span class="icon-filter"></span> Estados:</li>
        </ul>
        <ul>
<?php
    for ($i=0; $i<count($varr_estados); $i++){
        if (in_array($varr_estados[$i]['id'], $varr_estados_f))
            echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="'.$varr_estados[$i]['id'].'" checked><label style="padding-left:5px;padding-right:5px;">'.$varr_estados[$i]['nombre'].'</label></li>';
        else 
            echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="'.$varr_estados[$i]['id'].'"><label style="padding-left:5px;padding-right:5px;">'.$varr_estados[$i]['nombre'].'</label></li>';
    }
?>
            
        </ul>
        </form>
    </div>

    <!--================================================== 
    ========================== PAGINA -->

    <div id="contenedor_listado">
        <? require('inversionistas_todo_pagina.php');?>
    </div>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA MODAL -->

    <div class="modal fade" id="InversorDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Subasta</h5></li>
                <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
            </ul>
        </div>
        <div class="modal-body">
        </div>
        <!--<div class="modal-footer">-->
            <!--<p class="botontransaccionazul" id="btn_grabar_accionistas"><span class="icon-floppy-disk"></span><a href="javascript:guarda_accionistas('accionistas')" style=""> Guardar</a></p>-->
        <!--</div>-->
        </div>
    </div>
    </div>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA SCRIPT -->
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }

        function verDetalle(p_inversor, p_tipo_inversor){
            if (p_tipo_inversor == 85){
            //persona natural
                $('.modal-title').text('DETALLE INVERSOR');
                $('.modal-body').load('inversor_upd_modal.php?inversor_id='+p_inversor,function(){
                    $('#InversorDetalle').modal({show:true});
                });
            } else {
                //persona juridica
            }
        }

        function bloquear(p_inversor){
            alert(p_inversor);
        }

        function nuevoInversor(){
            $('.modal-title').text('NUEVO INVERSOR');
            $('.modal-body').load('inversor_registro_modal.php',function(){
                $('#InversorDetalle').modal({show:true});
            });
        }

        function nuevoInversorEmp(){
            alert('Funcion no habilitada');
        }
    </script>
</BODY>
</HTML>