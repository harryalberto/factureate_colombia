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
    
</HEAD>
<?php
/*##########################################################
################## LOGICA
############################################################*/
$vobj_sub = new subasta;

$varr_confirmaciones = $vobj_sub->get_confirmaciones($_SESSION['user']['empresaid']);
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'emisores/confirmacion_financiamiento.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--#######################################################-->
    <!--########## CUERPO PRINCIPAL -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Confirmación de Financiamientos
    </div>

    <hr>

<?php
    if (count($varr_confirmaciones) > 0){
?>
    <div class="contenedor_principal" style="padding-right: 10px;" id="principal">
        <table class="tabla_resize">
            <thead>
                <tr>
                    <th scope="col">ID OPERACION</th>
                    <th scope="col">NRO FACTURA</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">CLIENTE</th>
                    <th scope="col">F CONFIRMACION</th>
                    <th scope="col">H CONFIRMACION</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO FACTURA</th>
                    <th scope="col">MONTO ADELANTO</th>
                    <th scope="col">CONFIRMA</th>
                    <th scope="col">RECHAZA</th>
                </tr>
            </thead>
            <tbody>
<?php
    for ($i=0; $i<count($varr_confirmaciones); $i++){
        $vf_vencimiento = date('d-m-Y',strtotime($varr_confirmaciones[$i]['fecha_vencimiento']));
        $vf_registro = date('d-m-Y',strtotime($varr_confirmaciones[$i]['fecha_registro']));

        echo '  <tr>
                    <td data-label="ID OPERACION">'.$varr_confirmaciones[$i]['factura_id'].'</td>
                    <td data-label="NRO FACTURA">'.$varr_confirmaciones[$i]['factura_numero'].'</td>
                    <td data-label="F VENCIMIENTO">'.$vf_vencimiento.'</td>
                    <td data-label="CLIENTE">'.$varr_confirmaciones[$i]['cliente_nombre'].'</td>
                    <td data-label="F CONFIRMACION">'.$vf_registro.'</td>
                    <td data-label="H CONFIRMACION">'.$varr_confirmaciones[$i]['hora_registro'].'</td>
                    <td data-label="MONEDA">'.$varr_confirmaciones[$i]['moneda_simbolo'].'</td>
                    <td data-label="MONTO FACTURA">'.number_format($varr_confirmaciones[$i]['monto_factura'],2,'.',',').'</td>
                    <td data-label="MONTO ADELANTO">'.number_format($varr_confirmaciones[$i]['monto'],2,'.',',').'</td>
                    <td data-label="CONFIRMA"><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="confirmar()">
                            <i class="fa-solid fa-square-check"></i> Confirmar</button></td>
                    <td data-label="RECHAZA"><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;" onclick="rechazar()">
                            <i class="fa-solid fa-ban"></i> Rechazar</button></td>
                </tr>';
    }
?>
            </tbody>
        </table>
    </div>
<?php
} else {
        echo '
    <div>
        No hay confirmaciones de financiamiento pendientes !!
    </div>';
    }
?>

    <!--#######################################################-->
    <!--########## ZONA MODAL -->

    <!--#######################################################-->
    <!--########## ZONA JS -->
    <script>
        function alertar(){
            alert('wwwww');
        }

        function confirmar(){
            alert('aaaa');
            //var confirma_id = $('#confirma_id').val();
            //alert(p_confirma_id);
            /*$.ajax({
                url:"confirmacion_financiamiento_proceso.php",
                type:'post',
                data:{
                        "confirma_id":p_confirma_id,
                        "accion":'confirmar'
                },
                success:function(data,status){
                    $('#principal').fadeIn(1000).html(data);
                    //$('#principal').modal('hide');
                    refresh_page();
                }
            });*/
        }
        
    </script>
</BODY>
</HTML>