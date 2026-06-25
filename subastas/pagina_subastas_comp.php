<?php
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/c_subasta.php");
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objsubasta = new subasta;

if ($rowcount < 0) $rowcount = $objsubasta->get_subastas_xestado(31,0,0,'count');

if ($rowcount > 0){
    $rowsxpage = 20;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arrsubastas = $objsubasta->get_subastas_xestado(31,$rowsxpage,$rowini,'select');
    // pintado del resultado
    echo '<div style="overflow:hidden;margin:1px;padding:2px;">';
    //-- HEADER
    echo '  <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">EMISOR</th>
                    <th scope="col">PAGADOR</th>
                    <th scope="col">FACTURA NRO</th>
                    <th scope="col">TIEMPO EN PENDIENTE</th>
                    <th scope="col">ESTADO</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    $v_fecha_hoy = date('Y-m-d H:i:s');
    $v_dt_hoy = new DateTime($v_fecha_hoy);

    for ($i=0; $i<count($arrsubastas); $i++){
        $t_fvencimiento = strtotime($arrsubastas[$i]['fvencimiento']);
        $fvencimiento = date('d-m-Y',$t_fvencimiento);
        $t_fsubasta = strtotime($arrsubastas[$i]['fsubasta_creacion']);
        $fsubasta = date('d-m-Y', $t_fsubasta);
        // calculo de las horas de subasta
        $v_dt_subasta = new DateTime($arrsubastas[$i]['fsubasta_compensado'].' '.$arrsubastas[$i]['hsubasta_compensado']);
        $v_diferencia = $v_dt_subasta->diff($v_dt_hoy);
        $v_accion = '<button style="font-size:12px;" type="button" subastaid='.$arrsubastas[$i]['subastaid'].' retorno=subcomp pagina='.$pageNum.' rowcount='.$rowcount.' class="btn btn-primary openBtnn"><span class="icon-search" style="font-size:16px;"></span> Detalle</button>';
        
        echo '  <tr>
                    <td data-label="ID">'.$arrsubastas[$i]['facturaid'].'</td>
                    <td data-label="EMISOR">'.$arrsubastas[$i]['emisor_nombre'].'</td>
                    <td data-label="PAGADOR">'.$arrsubastas[$i]['cliente'].'</td>
                    <td data-label="FACTURA NRO">'.$arrsubastas[$i]['facnumero'].'</td>
                    <td data-label="TIEMPO EN PENDIENTE">'.$v_diferencia->days.' dias '.$v_diferencia->h.' horas</td>
                    <td data-label="ESTADO">'.$arrsubastas[$i]['estado_compensacion_nombre'].'</td>
                    <td data-label="ACCION">'.$v_accion.'</td>
                </tr>';
    }

    echo '      </tbody></table></ul></div>';
    
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) 
            echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'">Anterior</a></li>';

        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a>'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'">'.$i.'</a></li>';
        }

        if ($pageNum != $total_paginas) 
            echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
}
?>
