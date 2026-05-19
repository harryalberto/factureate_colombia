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
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PANELINV';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'panel/panel_inversionista.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------

?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:20px 50px;">
        Bienvenido <?echo $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];?> a la plataforma Factureate donde podrás invertir en instrumentos de alta calidad  y obtener las ganancias que decidas !!
    </div>

    <div style="overflow: hidden;text-align: left;font-size: 14px;padding-left: 30px;">
        Solo falta que firmes el contrato que le deba haber llegado a su correo y podra acceder a:
    </div>

    <div style="overflow: hidden;text-align: left;font-size: 12px;padding-left: 15px;margin-top: 20px; margin-left: 50px;">
        <ul>
            <li style="padding: 10px 0px;"><b><span class="icon-spinner6" style="margin-right:2px;"></span> Panel Inversionista: </b>Donde le mostramos todas las facturas disponibles para que pueda invertir y un resumen de los resultados o ganancias que va obteniendo</li>
            <li style="padding: 10px 0px;"><b><span class="icon-hammer2" style="margin-right:2px;"></span> Oportunidad de Inversión: </b>Donde le mostramos todas la facturas disponibles para que pueda invertir con una serie de filtros para encontrar lo que busca</li>
            <li style="padding: 10px 0px;"><b><span class="icon-menu4" style="margin-right:2px;"></span> Propuestas: </b>Donde le mostramos todas posiciones de inversión que usted a colocado, es decir, aún no son inversiones hasta que la plataforma lo apruebe</li>
            <li style="padding: 10px 0px;"><b><span class="icon-stats-bars2" style="margin-right:2px;"></span> Inversiones: </b>Son las inversiones que ya realizó y solo debe esperar al termino de la inversión para recibir sus ganancias</li>
            <li style="padding: 10px 0px;"><b><span class="icon-coin-dollar" style="margin-right:2px;"></span> Ganancias: </b>Relación de ganancias obtenidas con todo el respectivo detalle</li>
            <li style="padding: 10px 0px;"><b><span class="icon-library" style="margin-right:2px;"></span> Cuenta Efectivo: </b>Donde podrá observar los saldos que tiene en su cuenta, así como, los movimientos y estados de cuenta, con todo el detalle de sus inversiones y ganancias</li>
            <li style="padding: 10px 0px;"><b><span class="icon-user" style="margin-right:2px;"></span> Perfil: </b>Donde podrá personalizar sus preferencias</li>
        </ul>
    </div>

    <!------ END CUERPO VARIABLE ------>

</BODY>
</HTML>