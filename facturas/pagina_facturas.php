<?php
require("../lib-trans/factura.php");

if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    $estadoid = $_GET['destadoid'];
    $rowcount = $_GET['drowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objfactura = new factura;

if ($rowcount < 0) $rowcount = $objfactura->get_facturas_xestado($estadoid,0,0,'count');

if ($rowcount > 0){
    $rowsxpage = 20;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arrfacturas = $objfactura->get_facturas_xestado($estadoid,$rowsxpage,$rowini,'select');
    // pintado del resultado
    echo '<div class="listado">';

    if ($_SESSION['user']['tipousuario'] == 3) $v_label_header = 'Cliente'; //emisor
    else $v_label_header = 'Emisor';
    //-- HEADER
    echo '  <ul class="listado_header">
                <li style="width:300px;">'.$v_label_header.'</li>
                <li style="width:100px;">Factura</li>
                <li style="width:100px;">FEmision</li>
                <li style="width:100px;">FVencimiento</li>
                <li style="width:50px;">Moneda</li>
                <li style="width:100px;">Monto</li>';
    if ($estadoid == 12) echo '<li>F Envio</li>';   //enviada
    if ($estadoid == 21){ //revisada
        echo '<li>F Revisi&oacute;n</li>
            <li>Solicitud AC</li>';   
    }
    echo '      <li style="width:100px;text-align:center;">Acci&oacute;n</li>
            </ul>';
    //-- DETALLE
    for ($i=0; $i<count($arrfacturas); $i++){
        $t_femision = strtotime($arrfacturas[$i]['femision']);
        $femision = date('d-m-Y',$t_femision);
        $t_fvencimiento = strtotime($arrfacturas[$i]['fvencimiento']);
        $fvencimiento = date('d-m-Y',$t_fvencimiento);
        $t_fenvio = strtotime($arrfacturas[$i]['fenvio']);
        $fenvio = date('d-m-Y',$t_fenvio);
        $t_faprobacion = strtotime($arrfacturas[$i]['faprobacion']);
        $faprobacion = date('d-m-Y',$t_faprobacion);

        if ($_SESSION['user']['tipousuario'] == 3) $v_data_clienteemisor = $arrfacturas[$i]['clientenombre'];   // emisor
        else $v_data_clienteemisor = $arrfacturas[$i]['emisor'];

        echo '  <ul>
                    <li style="width:300px;text-align:left;">'.$v_data_clienteemisor.'</li>
                    <li style="width:100px;">'.$arrfacturas[$i]['facturanro'].'</li>
                    <li style="width:100px;">'.$femision.'</li>
                    <li style="width:100px;">'.$fvencimiento.'</li>
                    <li style="width:50px;">'.$arrfacturas[$i]['moneda'].'</li>
                    <li style="width:100px;text-align:right;">'.number_format($arrfacturas[$i]['total'],2,'.',',').'</li>';
        if ($estadoid == 12) echo '<li>'.$fenvio.'</li>';
        if ($estadoid == 21){
            if (is_null($arrfacturas[$i]['fsolicitudac']) || $arrfacturas[$i]['fsolicitudac'] == '') $solicitudac = 'NO';
            else $solicitudac = 'SI';

            echo '<li>'.$faprobacion.'</li>
                <li>'.$solicitudac.'</li>';
        }
        echo '      <li style="width:100px;text-align:center;"><a href="factura_detalle.php?id='.$arrfacturas[$i]['facturaid'].'">Ver</a></li>
                </ul>';
    }

    echo '</div>';
}
?>