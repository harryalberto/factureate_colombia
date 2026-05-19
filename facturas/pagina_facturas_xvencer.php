<?php
require("../lib-trans/factura.php");

if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    $rowcount = $_GET['drowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objfactura = new factura;

if ($rowcount < 0){
    $arr_instrumentos = $objfactura->relacion_facturas_indicador('XVENCER');
    $rowcount = count($arr_instrumentos);
}

if ($rowcount > 0){
    $rowsxpage = 20;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    
    //-- HEADER
    $v_row_inicial_lb = $rowini + 1;

    echo '
    <div style="overflow:hidden;margin:1px;padding:2px;margin-left:10px;margin-right:10px;">
            <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_row_inicial_lb.' al '.$v_rowfinal.' de '.$rowcount.' resultados</li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">PAGADOR</th>
                    <th scope="col">FACTURA</th>
                    <th scope="col">F EMISION</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">DIAS X VENCER</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO FACTURA</th>
                    <th scope="col">MONTO FINANCIADO</th>
                    <th scope="col">ESTADO</th>
                    <th scope="col">RIESGO</th>
                    <th scope="col">PAGO CONFIRMADO</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';

    //-- DETALLE
    $v_max_row = $rowini + $rowsxpage;
    if ($v_max_row > count($arr_instrumentos)) $v_max_row = count($arr_instrumentos);
    
    for ($i=$rowini; $i<$v_max_row; $i++){
        $t_femision = strtotime($arr_instrumentos[$i]['femision']);
        $femision = date('d-m-Y',$t_femision);
        $t_fvencimiento = strtotime($arr_instrumentos[$i]['fvencimiento']);
        $fvencimiento = date('d-m-Y',$t_fvencimiento);
        //diferencias
        $v_fecha_hoy = date('Y-m-d');
        $v_fvencimiento_en = date('Y-m-d',$t_fvencimiento);
        $v_dt_hoy = new DateTime($v_fecha_hoy);
        $v_dt_fv = new DateTime($v_fvencimiento_en);
        $diff_fv = $v_dt_fv->diff($v_dt_hoy);

        $v_accion = '<button style="font-size:12px;background-color:var(--color-azulv2);" type="button" fid='.$arr_instrumentos[$i]['facturaid'].' eid='.$arr_instrumentos[$i]['clienteid'].' class="btn btn-primary openBtn2"><i class="fa-solid fa-address-book"></i> Contacto</button>';
        
        echo '      <tr>
                        <td data-label="ID">'.$arr_instrumentos[$i]['facturaid'].'</td>
                        <td data-label="PAGADOR">'.$arr_instrumentos[$i]['cliente'].'</td>
                        <td data-label="FACTURA">'.$arr_instrumentos[$i]['facturanro'].'</td>
                        <td data-label="F EMISION">'.$femision.'</td>
                        <td data-label="F VENCIMIENTO">'.$fvencimiento.'</td>
                        <td data-label="DIAS X VENCER">'.$diff_fv->days.'</td>
                        <td data-label="MONEDA">'.$arr_instrumentos[$i]['moneda'].'</td>
                        <td data-label="MONTO FACTURA">'.number_format($arr_instrumentos[$i]['monto_factura'],2,'.',',').'</td>
                        <td data-label="MONTO FINANCIADO">'.number_format($arr_instrumentos[$i]['monto_financiado'],2,'.',',').'</td>
                        <td data-label="ESTADO">'.$arr_instrumentos[$i]['estadofin'].'</td>
                        <td data-label="RIESGO">'.$arr_instrumentos[$i]['calificacion'].' - '.$arr_instrumentos[$i]['riesgo'].'</td>
                        <td data-label="PAGO CONFIRMADO">'.$arr_instrumentos[$i]['confirma_pago'].'</td>
                        <td data-label="ACCION">'.$v_accion.'</td>
                    </tr>';
    }

    echo '      </tbody>
            </table>
            </ul>
    </div>';

    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'">Anterior</a></li>';
    
        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a>'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'">'.$i.'</a></li>';
        }
    
        if ($pageNum != $total_paginas) echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
} else echo '<div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;text-align:center;">No se encontraron resultados ...</div>';
?>