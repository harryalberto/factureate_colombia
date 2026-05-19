<?
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/c_subasta.php");
    $seconomicoid = $_GET['dseconomicoid'];
    $triesgoid = $_GET['dtriesgoid'];
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objsubasta = new subasta;
$origen = 'subastas';

if ($rowcount < 0) $rowcount = $objsubasta->get_subastas_disponibles_riesgo_sector(0,0,'count',$seconomicoid,$triesgoid);

if ($rowcount > 0){
    $rowsxpage = 25;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arrsubastas = $objsubasta->get_subastas_disponibles_riesgo_sector($rowsxpage,$rowini,'select',$seconomicoid,$triesgoid);
    //===== caluclando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;
    // pintado del resultado
    echo '<div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">';
    //-- HEADER
    $v_rowini_label = $rowini + 1;
    $v_rowfin_label = $rowsxpage * $pageNum;
    if ($v_rowfin_label > $rowcount) $v_rowfin_label = $rowcount;

    echo '
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_rowini_label.' al '.$v_rowfin_label.' de '.$rowcount.' resultados</li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">PAGADOR</th>
                    <th scope="col">MONTO FACTURA</th>
                    <th scope="col">FINANCIAMIENTO</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">DIAS x COBRAR</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">RIESGO</th>
                    <th scope="col">ACCION</th>
                    <th scope="col">M</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($arrsubastas); $i++){
        if ($arrsubastas[$i]['qpropuestas'] == 0){      // NO TIENE PROPUESTA SOBRE ESTA FACTURA
            $v_detalle = '<button type="button" class="btn btn-primary openBtnn" style="font-size:12px;background-color:var(--color-azulv2);border:none;" fid='.$arrsubastas[$i]['facturaid'].' subastaid='.$arrsubastas[$i]['subastaid'].' pid=0 retorno=panelinv pagina='.$pageNum.' rowcount='.$rowcount.'><span class="icon-coin-dollar" style="font-size:16px;"></span> Comprar</button>';
            $v_marca = '.';
            
            if ($arrsubastas[$i]['estadoid'] == 23){    // SUBASTA AUN NO ACTIVA
                $bcolor = '#aaaaaa;';
                $tcolor = '#555555;';
            } else{                                     // SUBASTA ACTIVA
                $bcolor = '#ffffff;';/*064677*/
                $tcolor = '#000000;';/*ffffff*/
            }
            $conpropuesta = 0;
        } else {    // CUENTA CON PROPUESTA ESTA FACTURA
            $conpropuesta = $objsubasta->get_propuesta_xinversionista($arrsubastas[$i]['subastaid'],$_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid']);
            if ($conpropuesta > 0){
                $v_marca = '<span class="icon-checkbox-checked" style="font-size:14px;"></span>';
                $bcolor = '#a8aa1f;';
                $tcolor = '#ffffff;';
                $v_detalle = '<button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" fid='.$arrsubastas[$i]['facturaid'].' subastaid='.$arrsubastas[$i]['subastaid'].' pid='.$conpropuesta.' retorno=panelinv pagina='.$pageNum.' rowcount='.$rowcount.' class="btn btn-primary openBtnn"><span class="icon-coin-dollar" style="font-size:16px;"></span> Comprar</button>';
            } else{
                $v_marca = '.';
                $bcolor = '#ffffff;';
                $tcolor = '#000000;';
                if ($arrsubastas[$i]['estadoid'] == 23) $v_detalle = 'Comprar';
                else $v_detalle = '<button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" fid='.$arrsubastas[$i]['facturaid'].' subastaid='.$arrsubastas[$i]['subastaid'].' pid=0 retorno=panelinv pagina='.$pageNum.' rowcount='.$rowcount.' class="btn btn-primary openBtnn"><span class="icon-coin-dollar" style="font-size:16px;"></span> Comprar</button>';
            }
        }
        // ---- calculo los dias por vencer
        $hoy = date('Y-m-d');
        $dhoy = new DateTime($hoy);
        $dvenc = new DateTime($arrsubastas[$i]['fvencimiento']);
        $dif = $dhoy->diff($dvenc);
        $dias = $dif->days;
        $v_fvencimiento_t = strtotime($arrsubastas[$i]['fvencimiento']);
        $v_fvencimiento_l = date('d-m-Y',$v_fvencimiento_t);
        
        echo '  
            <tr>
            <td data-label="ID">'.$arrsubastas[$i]['facturaid'].'</td>
            <td data-label="PAGADOR">'.$arrsubastas[$i]['cliente'].'</td>
            <td data-label="MONTO FACTURA">'.number_format($arrsubastas[$i]['total'],2,'.',',').'</td>
            <td data-label="FINANCIAMIENTO">'.number_format($arrsubastas[$i]['montofin'],2,'.',',').'</td>
            <td data-label="MONEDA">'.$arrsubastas[$i]['moneda'].'</td>
            <td data-label="DIAS x COBRAR">'.$dias.'</td>
            <td data-label="F VENCIMIENTO">'.$v_fvencimiento_l.'</td>
            <td data-label="RIESGO" style="background-color:#'.$arrsubastas[$i]['color'].';color:#'.$arrsubastas[$i]['color_fuente'].';">['.$arrsubastas[$i]['calificacion'].'] '.$arrsubastas[$i]['riesgo'].'</td>
            <td data-label="ACCION" style="color:#064677;font-size:12px;">'.$v_detalle.'</td>
            <td data-label="M" style="color:#b30a1f;">'.$v_marca.'</td>
            </tr>';
    }

    echo '</tbody></table></li></ul></div>';
}
?>