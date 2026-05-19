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
    $acceso = 'CTAEFE';
    require("../lib/valida-acceso.php");
?>

    <script type="text/javascript">
        
        function filtrar(){
            document.frm.submit();
        }
        
        function estado_cuenta(p_id){
            location.href = 'estado_cuenta_inversionista.php?cuenta_id='+p_id;
        }
        
        function closemodal(){
            $('.modalclase').fadeOut();
        }

    </script>

</HEAD>

<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------

$obj_cuenta = new cuentas;
$obj_mae = new maestros;
$obj_inv = new inversiones;
$objsubasta = new subasta;

$arr_saldos = $obj_cuenta->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
$varr_param = $obj_mae->get_parametros();
$v_detalle = 0;
$v_medios_pago = $varr_param['INTEGRACION MEDIOS DE PAGO']['valornum'];
$v_banco_autom = $varr_param['INTEGRACION CON BANCO']['valornum'];

//---- PROPUESTAS PRELIMINARES

$varr_prop = $obj_inv->get_propuestas_pre_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,1000);

//---- verifico si es broker o representante o titular de la cuenta

$v_titular = 'false';

if ($_SESSION['user']['empresaid'] > 0){
    if ($_SESSION['user']['empresaid'] == $_SESSION['user']['usuarioid']){
        //---- una persona natural con broker, esta accediendo el titular
        $v_titular = 'true';
    } else {
        //---- verifico si el usuario es el representante de la empresa
        if ($_SESSION['user']['perfiltipo'] == 10) $v_titular = 'true';
    }
} else $v_titular = 'true';

/*--------------------------------------------------------*/
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?
    date_default_timezone_set("America/Lima");
    $menu = 'cuentas/estado_cuenta.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    
    <!------ CUERPO VARIABLE ------>
    
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:0px auto;">
        Cuenta Efectivo Inversionista
    </div>
    
<?php
    
    for ($i=0; $i<count($arr_saldos); $i++){
        if ($arr_saldos[$i]['saldo_disponible'] < 0) $estilo = 'style="color:#d42639;font-size:10px;"';
        else $estilo = 'style="font-size:10px;"';
        
        $arr_movimientos = $obj_cuenta->get_movimientos_cuenta($arr_saldos[$i]['cuenta_id'],5,0);

        // PROPUESTAS PRELIMINARES
        
        $v_monto_preliminares = 0;
        $v_simbolo_moneda = '';
        $v_monto_enpropuestas = $objsubasta->monto_propuestas_subasta($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$arr_saldos[$i]['moneda_id']);
        $v_disponible_final = $arr_saldos[$i]['saldo_disponible'] - $v_monto_enpropuestas;

        for ($j=0; $j<count($varr_prop); $j++){
            if ($varr_prop[$j]['moneda_id'] == $arr_saldos[$i]['moneda_id']){
                $v_monto_preliminares = $v_monto_preliminares + $varr_prop[$j]['monto'];
                $v_simbolo_moneda = $varr_prop[$j]['simbolo_moneda'];
            }
        }

        $v_monto_preliminares = $v_monto_preliminares - $v_disponible_final;
?>

        <div class="frmtransaccion">
            
            <ul style="margin-top:5px;">
                <li style="font-size:14px;font-weight: bold;padding: 1px;margin: 1px;">NRO DE CUENTA: <? echo $arr_saldos[$i]['cuenta_id'];?></li>
            </ul>

            <ul style="margin-top:5px;">
                <li style="font-size:14px;font-weight: bold;padding: 1px;margin: 1px;">Moneda:</li>
                <li style="font-size:14px;font-weight: bold;padding: 1px;margin: 1px;"><? echo $arr_saldos[$i]['moneda'];?></li>
                <li><button type="button" class="btn btn-primary" onclick="estado_cuenta(<?=$arr_saldos[$i]['cuenta_id']?>)" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
                        <span class="icon-search" style="font-size:16px;"></span> Ver Estado Cuenta</button></li>

<?php
        if ($v_titular == 'true'){
?>

                <li><button type="button" class="btn btn-primary" onclick="cargar_saldo(<?=$arr_saldos[$i]['cuenta_id']?>,<?=$arr_saldos[$i]['moneda_id']?>,<?=$v_medios_pago?>,<?=$v_banco_autom?>)" style="font-size:12px;background-color:var(--color-verde);border:none;">
                        <span class="icon-coin-dollar" style="font-size:16px;"></span> Cargar Saldo</button></li>
                <li><button type="button" class="btn btn-primary" onclick="retirar_saldo(<?=$arr_saldos[$i]['cuenta_id']?>)" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
                        <span class="icon-library" style="font-size:16px;"></span> Retirar Saldo</button></li>

<?php
        }
?>

            </ul>
<?php

        echo '
            <ul style="margin-top:10px;padding:0px;">
                <li class="box_subtitulo_blanco"><p><span class="icon-stats-bars2" style="font-size:16px;"></span> Saldo Contable:</p><p>'.number_format($arr_saldos[$i]['saldo_contable'],2,'.',',').'</p></li>
                <li class="box_subtitulo_blanco"><p><span class="icon-shield" style="font-size:16px;"></span> S. Comprometido:</p><p>'.number_format($arr_saldos[$i]['saldo_comprometido'],2,'.',',').'</p></li>
                <li class="box_subtitulo_blanco"><p><span class="icon-coin-dollar" style="font-size:16px;"></span> Saldo Disponible:</p><p>'.number_format($arr_saldos[$i]['saldo_disponible'],2,'.',',').'</p></li>
                <li class="box_subtitulo_blanco"><p><span class="icon-stats-dots" style="font-size:16px;"></span> Saldo Invertido:</p><p>'.number_format($arr_saldos[$i]['saldo_invertido'],2,'.',',').'</p></li>';
    
    //---- MONTO RESUMEN DE LAS PROPUESTAS PRELIMINARES
    
    if ($v_monto_preliminares > 0)
        echo '  <li class="box_subtitulo_blanco" style="background-color:var(--color-rojo);color:#ffffff;"><p><i class="fa-solid fa-triangle-exclamation"></i> Pendiente de Saldo:</p><p>'.$v_simbolo_moneda.' '.number_format($v_monto_preliminares,2,'.',',').'</p></li>';
    
    //---- SALDO EN TRANSITO
    if ($arr_saldos[$i]['saldo_transito'] > 0){
        echo '  <li class="box_subtitulo_blanco" style="background-color:var(--color-verde);color:#ffffff;"><p><i class="fa-solid fa-hourglass-half"></i> Saldo en Transito:</p><p>'.$v_simbolo_moneda.' '.number_format($arr_saldos[$i]['saldo_transito'],2,'.',',').'</p></li>';
    }

    echo '</ul>
        </div>

        <div style="overflow:hidden;margin:5px;padding:5px;">';

        //---- PROPUESTAS PRELIMINARES

        if ($v_monto_preliminares > 0){
            echo '
            <ul>
                <li style="font-weight: bold;color:var(--color-rojo);font-size:12px;">PROPUESTAS PRELIMINARES SIN SALDO:</li>
            </ul>
            
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                    <thead>
                        <tr style="background-color:var(--color-rojo);color:#ffffff;">
                        <th scope="col">ID</th>
                        <th scope="col">PROPUESTA</th>
                        <th scope="col">FECHA</th>
                        <th scope="col">PAGADOR</th>
                        <th scope="col">MONTO PENDIENTE</th>
                        </tr>
                    </thead>
                    <tbody>';

            for ($j=0; $j<count($varr_prop); $j++){
                $v_fecha = date('d-m-Y',strtotime($varr_prop[$j]['propuesta_fcreacion']));

                echo '  <tr>
                            <td data-label="ID">'.$varr_prop[$j]['factura_id'].'</td>
                            <td data-label="PROPUESTA">'.$varr_prop[$j]['propuesta_id'].'</td>
                            <td data-label="FECHA">'.$v_fecha.'</td>
                            <td data-label="PAGADOR">'.$varr_prop[$j]['cliente_nombre'].'</td>
                            <td data-label="MONTO PENDIENTE">'.number_format($varr_prop[$j]['monto'],2,'.',',').'</td>
                        </tr>';
            }

            echo '  </tbody>
                </table>
            </ul>';
        }

        //---- SALDO EN TRANSITO
        
        if ($arr_saldos[$i]['saldo_transito'] > 0){
            $varr_transito = $obj_cuenta->get_saldo_transito_detalle($arr_saldos[$i]['cuenta_id']);

            echo '
            <ul>
                <li style="font-weight: bold;color:var(--color-verde);font-size:12px;">SALDO EN TRANSITO:</li>
            </ul>
            
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                    <thead>
                        <tr style="background-color:var(--color-verde);color:#ffffff;">
                        <th scope="col">ID</th>
                        <th scope="col">BANCO</th>
                        <th scope="col">FECHA</th>
                        <th scope="col">MONTO</th>
                        </tr>
                    </thead>
                    <tbody>';

            for ($j=0; $j<count($varr_transito); $j++){
                $v_fecha = date('d-m-Y',strtotime($varr_transito[$j]['fecha_creacion']));

                echo '  <tr>
                            <td data-label="ID">'.$varr_transito[$j]['id'].'</td>
                            <td data-label="BANCO">'.$varr_transito[$j]['banco_nombre'].'</td>
                            <td data-label="FECHA">'.$v_fecha.'</td>
                            <td data-label="MONTO">'.number_format($varr_transito[$j]['monto'],2,'.',',').'</td>
                        </tr>';
            }

            echo '  </tbody>
                </table>
            </ul>';
        }

        //---- SALDO COMPROMETIDO
        
        if ($arr_saldos[$i]['saldo_comprometido'] != 0){
            $arr_comprometido = $obj_cuenta->get_saldo_detalle($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],$arr_saldos[$i]['moneda_id'],'COM');
            $v_detalle = 1;
            $v_detalle_saldop = '
            <ul>
                <li style="font-weight: bold;color:#b30a1f;font-size:12px;">SALDO COMPROMETIDO:</li>
            </ul>
            
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">ID</th>
                        <th scope="col">PROPUESTA</th>
                        <th scope="col">FECHA</th>
                        <th scope="col">INSTRUMENTO</th>
                        <th scope="col">PAGADOR</th>
                        <th scope="col">MONTO</th>
                    </tr></thead>
                    <tbody>';
            
            echo $v_detalle_saldop;

            for ($j=0; $j<count($arr_comprometido); $j++){
                $v_fecha = date('d-m-Y',strtotime($arr_comprometido[$j]['fecha']));
                echo '
                        <tr>
                            <td data-label="ID">'.$arr_comprometido[$j]['factura_id'].'</td>
                            <td data-label="PROPUESTA">'.$arr_comprometido[$j]['propuesta_id'].'</td>
                            <td data-label="FECHA">'.$v_fecha.'</td>
                            <td data-label="INSTRUMENTO">'.$arr_comprometido[$j]['instrumento_nro'].'</td>
                            <td data-label="PAGADOR">'.$arr_comprometido[$j]['pagador_nombre'].'</td>
                            <td data-label="MONTO">'.number_format($arr_comprometido[$j]['monto'],2,'.',',').'</td>
                        </tr>';
            }

            echo '  </tbody>
                </table>
            </ul>';
        }
        
        //---- SALDO DISPONIBLE
        
        if ($arr_saldos[$i]['saldo_disponible'] < 0){
            $arr_disponible = $obj_cuenta->get_saldo_detalle($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],$arr_saldos[$i]['moneda_id'],'DIS');
            $v_detalle = 1;
            $v_detalle_saldod .= '
            <ul>
                <li style="font-weight: bold;color:#b30a1f;font-size:12px;">SALDO DISPONIBLE:</li>
            </ul>
            
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">ID</th>
                        <th scope="col">PROPUESTA</th>
                        <th scope="col">FECHA</th>
                        <th scope="col">INSTRUMENTO</th>
                        <th scope="col">PAGADOR</th>
                        <th scope="col">MONTO</th>
                    </tr></thead>
                    <tbody>';
            
            echo $v_detalle_saldod;

            for ($j=0; $j<count($arr_disponible); $j++){
                $v_fecha = date('d-m-Y',strtotime($arr_disponible[$j]['fecha']));
                echo '<tr>
                            <td data-label="ID">'.$arr_disponible[$j]['factura_id'].'</td>
                            <td data-label="PROPUESTA">'.$arr_disponible[$j]['propuesta_id'].'</td>
                            <td data-label="FECHA">'.$v_fecha.'</td>
                            <td data-label="INSTRUMENTO">'.$arr_disponible[$j]['instrumento_nro'].'</td>
                            <td data-label="PAGADOR">'.$arr_disponible[$j]['pagador_nombre'].'</td>
                            <td data-label="MONTO">'.number_format($arr_disponible[$j]['monto'],2,'.',',').'</td>
                        </tr>';
            }

            echo '  </tbody>
                </table>
            </ul>';
        }

        if ($arr_saldos[$i]['saldo_invertido'] != 0){
            $arr_invertido = $obj_cuenta->get_saldo_detalle($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],$arr_saldos[$i]['moneda_id'],'INV');
            $v_detalle = 1;
            $v_detalle_saldoi .= '
            <ul>
                <li style="font-weight: bold;color:#b30a1f;font-size:12px;">SALDO INVERTIDO:</li>
            </ul>

            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">ID</th>
                        <th scope="col">PROPUESTA</th>
                        <th scope="col">FECHA</th>
                        <th scope="col">INSTRUMENTO</th>
                        <th scope="col">PAGADOR</th>
                        <th scope="col">MONTO</th>
                    </tr></thead>
                    <tbody>';

            echo $v_detalle_saldoi;

            for ($j=0; $j<count($arr_invertido); $j++){
                $v_fecha = date('d-m-Y',strtotime($arr_invertido[$j]['fecha']));

                echo '
                        <tr>
                            <td data-label="ID">'.$arr_invertido[$j]['factura_id'].'</td>
                            <td data-label="PROPUESTA">'.$arr_invertido[$j]['propuesta_id'].'</td>
                            <td data-label="FECHA">'.$v_fecha.'</td>
                            <td data-label="INSTRUMENTO">'.$arr_invertido[$j]['instrumento_nro'].'</td>
                            <td data-label="PAGADOR">'.$arr_invertido[$j]['pagador_nombre'].'</td>
                            <td data-label="MONTO">'.number_format($arr_invertido[$j]['monto'],2,'.',',').'</td>
                        </tr>';
            }

            echo '  </tbody>
                </table>
            </ul>';
        }
        echo '
        </div>';
    }
    ?>

    <!------ END CUERPO VARIABLE ------>
    
    <!--###################################
    ########### ZONA MODAL -->
    
    <div class="modal fade" id="SaldosModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="display:block;float:left;"></h5>
            <button type="button" class="btn btn-default" style="display:block;float:right;" data-dismiss="modal">X</button>
          </div>
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
            <!--<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
          </div>
        </div>
      </div>
    </div>

    <!--============= llamada al modal -->
    
    <script>
        
        function cargar_saldo(p_cuenta_id, p_moneda_id, p_medios_pago,p_bancos){
            if (p_medios_pago > 0 || p_bancos > 0){
                alert('medios de pago y bancos');
            } else{
                $('.modal-title').text('CARGA DE COMPROBANTE DE DEPOSITO');
                $('.modal-body').load('carga_saldo_inversor_modal.php?cuenta_id='+p_cuenta_id+'&moneda_id='+p_moneda_id,function(){
                    $('#SaldosModal').modal({show:true});
                });
            }
        }

        function refresh_page(){
            location.href='estado_cuenta.php';
        }

    </script>

    <!----------------------------------->

</BODY>

</HTML>