<?
require("../lib-trans/factura.php");

if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    $estadoid = $_GET['destadoid'];
    $rowcount = $_GET['drowcount'];
    $v_tipousuario = $_GET['tusuario'];
    $v_empresaid = $_GET['empresaid'];
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
    
    if ($_SESSION['user']['tipousuario'] == 3) $v_label_header = 'Cliente'; //emisor
    else $v_label_header = 'Emisor';
    //-- HEADER
    echo '
    <div style="overflow:hidden;margin:1px;padding:2px;margin-left:10px;margin-right:10px;">
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">EMISOR</th>
                    <th scope="col">PAGADOR</th>
                    <th scope="col">FACTURA</th>
                    <th scope="col">F EMISION</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO FACTURA</th>
                    <th scope="col">ESTADO</th>';

        if ($estadoid == 12) echo '
                    <th scope="col">F ENVIO</th>
                    <th scope="col">DIAS</th>';

        if ($estadoid == 21) echo '
                    <th scope="col">F APROBACION</th>';

        echo '      <th scope="col">DETALLE</th>
                </tr>
                </thead>
                <tbody>';

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
        // diferencias
        $v_fecha_hoy = date('Y-m-d');
        $v_fenvio_en = date('Y-m-d',$t_fenvio);
        $v_dt_hoy = new DateTime($v_fecha_hoy);
        $v_dt_fe = new DateTime($v_fenvio_en);
        $diff_fe = $v_dt_fe->diff($v_dt_hoy);

        if ($_SESSION['user']['tipousuario'] == 3) $v_data_clienteemisor = $arrfacturas[$i]['clientenombre'];   // emisor
        else $v_data_clienteemisor = $arrfacturas[$i]['emisor'];

        $v_accion = '<button style="font-size:12px;background-color:var(--color-azulv2);" type="button" eid='.$estadoid.' ret=facxe tipo=mng id='.$arrfacturas[$i]['facturaid'].' class="btn btn-primary openBtn2"><span class="icon-search" style="font-size:16px;"></span> Detalle</button>';

        echo '      <tr>
                        <td data-label="ID">'.$arrfacturas[$i]['facturaid'].'</td>
                        <td data-label="EMISOR">'.$v_data_clienteemisor.'</td>
                        <td data-label="PAGADOR">'.$arrfacturas[$i]['clientenombre'].'</td>
                        <td data-label="FACTURA">'.$arrfacturas[$i]['facturanro'].'</td>
                        <td data-label="F EMISION">'.$femision.'</td>
                        <td data-label="F VENCIMIENTO">'.$fvencimiento.'</td>
                        <td data-label="MONEDA">'.$arrfacturas[$i]['moneda'].'</td>
                        <td data-label="MONTO">'.number_format($arrfacturas[$i]['total'],2,'.',',').'</td>
                        <td data-label="ESTADO">'.$arrfacturas[$i]['estado'].'</td>';

        if ($estadoid == 12) echo '
                        <td data-label="F ENVIO">'.$fenvio.'</td>
                        <td data-label="DIAS">'.$diff_fe->days.'</td>';
        if ($estadoid == 21)
            echo '      <td data-label="F APROBACION">'.$faprobacion.'</td>';
        
        echo '          <td data-label="DETALLE">'.$v_accion.'</td>
                    </tr>';
    }

    echo '      </tbody>
            </table>
        </ul>
    </div>';
}
?>