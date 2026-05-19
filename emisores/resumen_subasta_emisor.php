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

if ($_SESSION['user']['tipousuario'] == 3){     //emisor
    $obj_mae = new maestros;
    $arr_emisor = $obj_mae->get_datos_emisor($_SESSION['user']['empresaid']);

    if ($arr_emisor['estado'] == 1) $redir = "<meta http-equiv=refresh content='0;url=registra_emisor.php?estado=1'>";
    elseif ($arr_emisor['estado'] == 2) $redir = "<meta http-equiv=refresh content='0;url=registra_emisor.php?estado=2'>";
    else $redir = '';
}
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PANELEMI';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function cerrar(p_pagina){
            location.href = p_pagina;
        }
    </script>
    <?echo $redir;?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_subasta = new subasta;

$arr_factura = $obj_factura->get_datos_financiamiento($_GET['factid']);
$arr_propuestas = $obj_subasta->get_subasta_posiciones($arr_factura['subasta_id']);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;margin:auto;font-size:12px;color:#5F6E7A;background-color:#efefef;height:30px;">
        <ul style="overflow:hidden;list-style:none;">
            <li style="padding:5px;font-weight: bold;font-size: 12px;">RESUMEN SUBASTA</li>
        </ul>
    </div>
    <?php
    $fsubasta_t = strtotime($arr_factura['subfcreacion']);
    $fsubasta = date('d-m-Y',$fsubasta_t);
    $monto_subasta = number_format($arr_factura['submonto_fin'],2,'.',',');
    $fvencimiento_t = strtotime($arr_factura['facfvencimiento']);
    $fvencimiento = date('d-m-Y',$fvencimiento_t);
    ?>
    <div class="frmtransaccion" style="max-width:600px;">
        <ul>
            <li class="frm_label">Subasta Nro</li>
            <li class="frm_input_off" style="width:50px;"><?echo $arr_factura['subasta_id'];?></li>
            <li class="frm_label">Fecha Inicio:</li>
            <li class="frm_input_off" style="width:80px;"><?echo $fsubasta;?></li>
            <li class="frm_label">Estado:</li>
            <li class="frm_input_off" style="width:80px;"><?echo $arr_factura['e_subasta'];?></li>
            <li class="frm_label">Monto:</li>
            <li class="frm_input_off" style="width:80px;"><?echo $monto_subasta;?></li>
        </ul>
        <ul>
            <li class="frm_label">Factura Nro:</li>
            <li class="frm_input_off" style="width:150px;"><?echo $arr_factura['facnumero'];?></li>
            <li class="frm_label">Fecha Venc:</li>
            <li class="frm_input_off" style="width:80px;"><?echo $fvencimiento;?></li>
            <li class="frm_label">Moneda:</li>
            <li class="frm_input_off" style="width:80px;"><?echo $arr_factura['facmoneda'];?></li>
        </ul>
        <ul>
            <li class="frm_label">Emisor:</li>
            <li class="frm_input_off" style="width:300px;"><?echo $arr_factura['facemisor'];?></li>
        </ul>
        <ul>
            <li class="frm_label">Cliente:</li>
            <li class="frm_input_off" style="width:300px;"><?echo $arr_factura['faccliente'];?></li>
        </ul>
    </div>
    <div style="overflow:hidden;margin:1px auto;font-size:10px;max-width:600px;">
        <ul>
            <li style="border-color: #5F6E7A;border-style: solid;border-width: 1px 0px 1px 0px;line-height: 15px;text-align:center;">RELACION DE PROPUESTAS</li>
        </ul>
    </div>
    <div class="listado" style="max-width:600px;">
        <ul>
            <li class="listado_header" style="width:100px;">Turno</li>
            <li class="listado_header" style="width:100px;">Monto</li>
            <li class="listado_header" style="width:100px;">Porcentaje</li>
            <li class="listado_header" style="width:100px;">% Inter&eacute;s</li>
        </ul>
        <?
        for ($i=0; $i<count($arr_propuestas); $i++){
            $porcentaje = number_format(100 * $arr_propuestas[$i]['posicion_porc'],2,'.',',');
            $tia = number_format(100 * $arr_propuestas[$i]['tia'],3,'.',',');
            $monto = number_format($arr_propuestas[$i]['posicion'],2,'.',',');

            echo '<ul>
                    <li style="width:100px;text-align:center;">'.$arr_propuestas[$i]['turno'].'</li>
                    <li style="width:100px;text-align:right;">'.$monto.'</li>
                    <li style="width:100px;text-align:center;">'.$porcentaje.' %</li>
                    <li style="width:100px;text-align:center;">'.$tia.' %</li>
                </ul>';
        }
        ?>
    </div>
    <div style="overflow:hidden;margin:auto;font-size:10px;padding:10px;max-width:600px;">
        <ul>
            <?
            $page = $_GET['page'].'.php';
            echo '<li class="botontransaccionrojo" style="width:100px;"><a href=javascript:cerrar("'.$page.'")><span class="icon-point-right"></span> Cerrar</a></li>';
            ?>
        </ul>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>