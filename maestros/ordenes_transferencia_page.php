<?
//require("../lib-trans/maestros.php");

if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objmaestro = new maestros;

if ($rowcount < 0) $rowcount = $objmaestro->ordenes_transferencia('COUNT', 46, 0, 0);     // PENDIENTES

if ($rowcount > 0){
    $rowsxpage = 20;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage); 
    $varr_ordenes = $objmaestro->ordenes_transferencia('SELEC', 46, $rowini, $rowsxpage);     // PENDIENTES
    //===== calculando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;
    //===========HEADER
    echo '
        <div style="overflow:hidden;margin:1px;padding:2px;">
            <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$rowini.' al '.$v_rowfinal.' de '.$rowcount.' resultados</li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ORDEN ID</th>
                    <th scope="col">TIPO</th>
                    <th scope="col">DESTINATARIO</th>
                    <th scope="col">FECHA ORDEN</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO</th>
                    <th scope="col">OPERACION ID</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';
    //=============DETALLE
    for ($i=0; $i<count($varr_ordenes); $i++){
        $t_forden = strtotime($varr_ordenes[$i]['fecha_orden']);
        $v_forden = date('d-m-Y',$t_forden);

        $v_valida_cuenta = $objmaestro->valida_cuenta_banco_empresa($varr_ordenes[$i]['destinatario_id'], $varr_ordenes[$i]['moneda_id']);

        /*$v_accion = '<button style="font-size:12px;" type="button" ot_id='.$varr_ordenes[$i]['ot_id'].' class="btn btn-primary openBtn2"><span class="icon-search" style="font-size:16px;"></span> Detalle</button>';*/
        $v_accion = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_ordenes[$i]['ot_id'].','.$v_valida_cuenta.')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

        echo '  <tr>
                    <td data-label="ORDEN ID">'.$varr_ordenes[$i]['ot_id'].'</td>
                    <td data-label="TIPO">'.$varr_ordenes[$i]['destino_tipo'].'</td>
                    <td data-label="DESTINATARIO">'.$varr_ordenes[$i]['destino_nombre'].'</td>
                    <td data-label="FECHA ORDEN">'.$v_forden.'</td>
                    <td data-label="MONEDA">'.$varr_ordenes[$i]['moneda'].'</td>
                    <td data-label="MONTO">'.number_format($varr_ordenes[$i]['monto'],2,'.',',').'</td>
                    <td data-label="OPERACION ID">'.$varr_ordenes[$i]['operacion_id'].'</td>
                    <td data-label="OPERACION ID">'.$v_accion.'</td>
                </tr>';
    }

    echo '      </tbody></table></li></ul></div>';
} else echo 'no hay registros';
?>