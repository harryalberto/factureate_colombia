<?php
require("../lib-trans/factura.php");

if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    $v_estados = $_GET['estados'];
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objfactura = new factura;

if ($rowcount < 0) $rowcount = $objfactura->get_financiamiento_xestado_varios($v_estados,0,0,'count');

if ($rowcount > 0){
    $rowsxpage = 7;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage); 
    $v_arr_finan = $objfactura->get_financiamiento_xestado_varios($v_estados,$rowsxpage,$rowini,'select');
    //===== calculando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;
    //===========HEADER
    $v_row_inicial_lb = $rowini + 1;

    echo '
        <div style="overflow:hidden;margin:1px;padding:2px;margin-left:10px;margin-right:10px;">
            <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_row_inicial_lb.' al '.$v_rowfinal.' de '.$rowcount.' resultados</li>
            </ul>

            <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">OPERACION ID</th>
                    <th scope="col">EMISOR</th>
                    <th scope="col">PAGADOR</th>
                    <th scope="col">FACTURA</th>
                    <th scope="col">F VTO</th>
                    <th scope="col">DIAS FINAN</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO FINAN</th>
                    <th scope="col">MONTO FACTURA</th>
                    <th scope="col">DIAS XVENCER</th>
                    <th scope="col">PAGO CONFIRMA</th>
                    <th scope="col">F CONFIRMA</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';
    //=============DETALLE
    for ($i=0; $i<count($v_arr_finan); $i++){
        $t_fpago = strtotime($v_arr_finan[$i]['fpago']);
        $v_fpago = date('d-m-Y',$t_fpago);
        $t_fini_finan = strtotime($v_arr_finan[$i]['fregistro_finan']);
        // calculo de dias
        $v_fecha_hoy = date('Y-m-d');
        $v_fini_finan_en = date('Y-m-d',$t_fini_finan);
        $v_fpago_en = date('Y-m-d',$t_fpago);

        $v_dt_hoy = new DateTime($v_fecha_hoy);
        $v_dt_fini = new DateTime($v_fini_finan_en);
        $v_dt_fpago = new DateTime($v_fpago_en);

        $diff_dias_finan = $v_dt_fini->diff($v_dt_fpago);
        $diff_dias_xvencer = $v_dt_hoy->diff($v_dt_fpago);
        // confirmacion de pago
        if ($v_arr_finan[$i]['confirma_pago'] == 'SI'){
            $t_fconfirm = strtotime($v_arr_finan[$i]['f_confirmacion']);
            $v_fconfirm = date('d-m-Y',$t_fconfirm);
        } else $v_fconfirm = '';

        $v_accion = '<button style="font-size:12px;background-color:var(--color-azulv2);" type="button" ffid='.$v_arr_finan[$i]['finan_id'].' class="btn btn-primary openBtn2"><span class="icon-search" style="font-size:16px;"></span> Detalle</button>';
        
        echo '  <tr>
                    <td data-label="OPERACION ID">'.$v_arr_finan[$i]['factura_id'].'</td>
                    <td data-label="EMISOR">'.$v_arr_finan[$i]['emisor_nombre'].'</td>
                    <td data-label="PAGADOR">'.$v_arr_finan[$i]['cliente_nombre'].'</td>
                    <td data-label="FACTURA">'.$v_arr_finan[$i]['factura_numero'].'</td>
                    <td data-label="F VTO">'.$v_fpago.'</td>
                    <td data-label="DIAS FINAN">'.$diff_dias_finan->days.'</td>
                    <td data-label="MONEDA">'.$v_arr_finan[$i]['moneda'].'</td>
                    <td data-label="MONTO FINAN">'.number_format($v_arr_finan[$i]['monto_financiado'],2,'.',',').'</td>
                    <td data-label="MONTO FACTURA">'.number_format($v_arr_finan[$i]['monto_factura'],2,'.',',').'</td>
                    <td data-label="DIAS XVENCER">'.$diff_dias_xvencer->days.'</td>
                    <td data-label="PAGO CONFIRMA">'.$v_arr_finan[$i]['confirma_pago'].'</td>
                    <td data-label="F CONFIRMA">'.$v_fconfirm.'</td>
                    <td data-label="ACCION">'.$v_accion.'</td>
                </tr>';
    }

    echo '      </tbody></table></li></ul></div>';
} else echo 'no hay registros';
?>