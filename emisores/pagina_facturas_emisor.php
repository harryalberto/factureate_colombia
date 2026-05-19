<?php
require("../lib-trans/factura.php");

if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    $estado_id_filtro = $_GET['estado'];
    $rowcount = $_GET['rowcount'];
    $v_tipousuario = $_GET['tusuario'];
    $v_empresaid = $_GET['empresaid'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objfactura = new factura;

if ($rowcount < 0) $rowcount = $objfactura->get_facturas_xestado($estado_id_filtro,0,0,'count');

if ($rowcount > 0){
    $rowsxpage = 10;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    
    $arrfacturas = $objfactura->get_facturas_xestado_v2($estado_id_filtro,$rowsxpage,$rowini,'select', $v_tipousuario, $v_empresaid);
    // pintado del resultado
    echo '<div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">';

    if ($v_tipousuario == 3) $v_label_header = 'CLIENTE'; else $v_label_header = 'EMISOR';
    if ($estadoid == 12) $v_label_fecha = 'F ENVIO'; else $v_label_fecha = 'F REVISION';
    //-- HEADER
    $v_rowini_label = $rowini + 1;
    $v_rowfin_label = $rowsxpage * $pageNum;
    if ($v_rowfin_label > $rowcount) $v_rowfin_label = $rowcount;

    echo '
            <ul style="list-style:none;overflow: hidden;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_rowini_label.' al '.$v_rowfin_label.' de '.$rowcount.' resultados</li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">'.$v_label_header.'</th>
                    <th scope="col">FACTURA</th>
                    <th scope="col">F EMISION</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO</th>
                    <th scope="col">ESTADO</th>
                    <th scope="col">'.$v_label_fecha.'</th>
                    <th scope="col">DETALLE</th>
                </tr></thead>
                <tbody>';
                
    //-- DETALLE
    for ($i=0; $i<count($arrfacturas); $i++){
        if (is_null($arrfacturas[$i]['fenvio'])) $v_fenvio = '';
        else $v_fenvio = $arrfacturas[$i]['fenvio'];

        if (is_null($arrfacturas[$i]['faprobacion'])) $v_faprobacion = '';
        else $v_faprobacion = $arrfacturas[$i]['faprobacion'];

        $t_femision = strtotime($arrfacturas[$i]['femision']);
        $femision = date('d-m-Y',$t_femision);
        $t_fvencimiento = strtotime($arrfacturas[$i]['fvencimiento']);
        $fvencimiento = date('d-m-Y',$t_fvencimiento);
        $t_fenvio = strtotime($v_fenvio);
        $fenvio = date('d-m-Y',$t_fenvio);
        $t_faprobacion = strtotime($v_faprobacion);
        $faprobacion = date('d-m-Y',$t_faprobacion);
        if ($arrfacturas[$i]['estadoid'] == 11) $v_tipo_acceso = 'upd';
        else $v_tipo_acceso = 'view';

        if ($arrfacturas[$i]['estadoid'] == 21 && $arrfacturas[$i]['estadofinanciamientoid'] != 19){    // REVISADA Y NO LIQUIDADA
            if ($arrfacturas[$i]['estadofinanciamientoid'] == 15) $label_finan = 'EN FINANCIAMIENTO';
            else $label_finan = 'EN SUBASTA';

            $estado_label = $arrfacturas[$i]['estadofinanciamiento'];
        } else{ 
            $estado_label = $arrfacturas[$i]['estado'];
        }

        if ($v_tipousuario == 3) $v_data_clienteemisor = $arrfacturas[$i]['clientenombre'];   // emisor
        else $v_data_clienteemisor = $arrfacturas[$i]['emisor'];

        if ($estadoid == 12) $v_label_fechas = $fenvio;
        else $v_label_fechas = $faprobacion;

        $v_funcion = "verdetalle(".$arrfacturas[$i]['facturaid'].",'".$v_tipo_acceso."')";

        echo '      <tr>
                        <td data-label="ID">'.$arrfacturas[$i]['facturaid'].'</td>
                        <td data-label="'.$v_label_header.'">'.$v_data_clienteemisor.'</td>
                        <td data-label="FACTURA">'.$arrfacturas[$i]['facturanro'].'</td>
                        <td data-label="F EMISION">'.$femision.'</td>
                        <td data-label="F VENCIMIENTO">'.$fvencimiento.'</td>
                        <td data-label="MONEDA">'.$arrfacturas[$i]['moneda'].'</td>
                        <td data-label="MONTO">'.number_format($arrfacturas[$i]['total'],2,'.',',').'</td>
                        <td data-label="ESTADO">'.$estado_label.'</td>
                        <td data-label="'.$v_label_fecha.'">'.$v_label_fechas.'</td>
                        <td data-label="DETALLE"><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="'.$v_funcion.'"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button></td>
                    </tr>';

        //echo '              <td style="border: 1px solid;padding:5px;text-align:center;"><a //href="registro_factura.php?ret=fac&tipo='.$v_tipo_acceso.'&id='.$arrfacturas[$i]['facturaid'].'"><span class="icon-search"></span> Ver Detalle</a></td>
          //              </tr>';
    }

    echo '      </tbody>
            </table>
            </ul>
        </div>';
}
?>