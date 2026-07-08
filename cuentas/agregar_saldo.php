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
    $acceso = 'CTAEFE';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            if (document.frm.comprobante.value == '') alert('Debe ingresar el comprobante');
            else if (document.frm.monto_saldo.value == 0) alert ('Debe ingresar el monto a registrar');
            else{
                document.frm.action = "agregar_saldo_proc.php";
                document.frm.submit();
            } 
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cuenta = new cuentas;
$obj_inversion = new inversiones;
$arr_saldos = $obj_cuenta->get_saldos_cuenta($_GET['cuentaid']);
$arr_inversiones = $obj_inversion->get_inversion_xusuario_pendiente($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'], $arr_saldos['moneda_id']);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'cuentas/estado_cuenta.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Gesti&oacute;n de Saldos Inversionista
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="cuenta_id" value="<?=$_GET['cuentaid']?>">
    <div class="frmtransaccion">
        <ul>
            <li style="font-size:14px;font-weight: bold;">Moneda:</li>
            <li style="font-size:14px;font-weight: bold;"><?php echo $arr_saldos['moneda'];?></li>
        </ul>
        <ul>
            <li style="font-size:12px;font-weight: bold;">Saldo Contable:</li>
            <li style="font-size:12px;"><?php echo number_format($arr_saldos['saldo_contable'],2,'.',',');?></li>
            <li style="font-size:12px;font-weight: bold;">Saldo Comprometido:</li>
            <li style="font-size:12px;"><?php echo number_format($arr_saldos['saldo_comprometido'],2,'.',',');?></li>
            <li style="font-size:12px;font-weight: bold;">Saldo Disponible</li>
            <?php
            if ($arr_saldos['saldo_disponible'] < 0) 
                echo '<li style="font-size:12px;color:#d42639;">'.number_format($arr_saldos['saldo_disponible'],2,'.',',').'</li>';
            else echo '<li style="font-size:12px;">'.number_format($arr_saldos['saldo_disponible'],2,'.',',').'</li>';
            ?>
            <li style="font-size:12px;font-weight: bold;">Saldo Invertido</li>
            <li style="font-size:12px;"><?php echo number_format($arr_saldos['saldo_invertido'],2,'.',',');?></li>
            <li style="font-size:12px;font-weight: bold;">Saldo Transito</li>
            <li style="font-size:12px;"><?php echo number_format($arr_saldos['saldo_transito'],2,'.',',');?></li>
        </ul>
        <ul>
            <li style="font-size:12px;font-weight: bold;">Monto:</li>
            <li><input type="number" name="monto_saldo" style="width:100px;" value="0"></li>
            <li style="font-size:12px;font-weight: bold;">Comprobante:</li>
            <li><input type="file" name="comprobante"></li>
            <input type="hidden" name="moneda" value="<?=$arr_saldos['moneda']?>">
            <li style="width:200px;" class="botontransaccion"><a href=javascript:acciones("enviar")>Enviar</a></li>
        </ul>
    </div>
    </form>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <div class="listado">
        <ul class="listado_header"><li>Pendientes de Saldo</li></ul>
        <ul class="listado_header">
            <li style="width:100px;">ID Operaci&oacute;n</li>
            <li style="width:100px;">ID Subasta</li>
            <li style="width:100px;">Fecha</li>
            <li style="width:100px;">Monto</li>
            <li style="width:100px;">Factura</li>
            <li style="width:300px;">Pagador</li>
        </ul>
        <?php
        for ($i=0; $i<count($arr_inversiones); $i++){
            $fecha = date('d-m-Y',strtotime($arr_inversiones[$i]['f_creacion']));
            //$monto = $arr_inversiones[$i]['monto'] - $arr_inversiones[$i]['monto_comprometido'];
            $monto = $arr_inversiones[$i]['monto'];

            echo '  <ul>
                        <li style="width:100px;text-align:center;">'.$arr_inversiones[$i]['propuesta_id'].'</li>
                        <li style="width:100px;text-align:center;">'.$arr_inversiones[$i]['subasta_id'].'</li>
                        <li style="width:100px;text-align:center;">'.$fecha.'</li>
                        <li style="width:100px;text-align:right;">'.number_format($monto,2,'.',',').'</li>
                        <li style="width:100px;text-align:center;">'.$arr_inversiones[$i]['factura'].'</li>
                        <li style="width:300px;text-align:left;">'.$arr_inversiones[$i]['pagador'].'</li>
                    </ul>';
        }
        ?>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>