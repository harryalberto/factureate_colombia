<?php
if(isset($_GET['page'])){
    //-------------- cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/c_inversiones.php");
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$obj_inversiones = new inversiones;

if ($rowcount < 0) $rowcount = $obj_inversiones->get_count_inversiones($_SESSION['user']['usuarioid'],'total',$_SESSION['user']['empresaid']);  // inversiones activas

if ($rowcount > 0){
    $rowsxpage = 15;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arr_inversiones = $obj_inversiones->get_inversiones_xusuario($_SESSION['user']['usuarioid'],'total',$_SESSION['user']['empresaid'],$rowini, $rowsxpage);
    //===== calculando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;
    // pintado del resultado
    echo '<div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">';
    //-- HEADER
    $v_rowini_label = $rowini + 1;
    $v_rowfin_label = $rowsxpage * $pageNum;
    if ($v_rowfin_label > $rowcount) $v_rowfin_label = $rowcount;

    echo '  <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_rowini_label.' al '.$v_rowfin_label.' de '.$rowcount.' resultados <a href=javascript:exportar_reporte()><i class="fa-solid fa-file-pdf" style="font-size:18px;margin-left:10px;"></i></a></li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">PAGADOR</th>
                    <th scope="col">FACTURA</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">F PAGO</th>
                    <th scope="col">MONTO INVERSION</th>
                    <th scope="col">GANANCIA EST</th>
                    <th scope="col">PENDIENTE DEPOSITO</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">ESTADO</th>

                    <th scope="col">DETALLE</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($arr_inversiones); $i++){
        $f_vencimiento = date('d-m-Y',strtotime($arr_inversiones[$i]['f_vencimiento']));

        if ($arr_inversiones[$i]['factura_diff_pago'] == 0) $v_fpago = '<i class="fa-solid fa-thumbs-up"></i> '.$f_vencimiento;
        else $v_fpago = '<i class="fa-solid fa-circle-exclamation" style="color:var(--color-amarillo);"></i> '.date('d-m-Y', strtotime($arr_inversiones[$i]['factura_fpago']));
        
        if ($arr_inversiones[$i]['e_subasta_id'] != 26){    // no liquidada
            $ganancia = '-';
            $estado = $arr_inversiones[$i]['e_propuesta'];
            $v_saldo_pendiente = $arr_inversiones[$i]['monto_inversion'] - $arr_inversiones[$i]['fondo_disponible'];
        } else{
            $ganancia = number_format($arr_inversiones[$i]['ganancia'],2,'.',',');
            $estado = $arr_inversiones[$i]['e_financia'];
        }

        echo '          <tr>
                            <td data-label="ID">'.$arr_inversiones[$i]['factura_id'].'</td>
                            <td data-label="PAGADOR">'.$arr_inversiones[$i]['cliente'].'</td>
                            <td data-label="FACTURA">'.$arr_inversiones[$i]['factura_nro'].'</td>
                            <td data-label="F VENCIMIENTO">'.$f_vencimiento.'</td>
                            <td data-label="F PAGO">'.$v_fpago.'</td>
                            <td data-label="MONTO INVERSION">'.number_format($arr_inversiones[$i]['monto_inversion'],2,'.',',').'</td>
                            <td data-label="GANANCIA EST">'.$ganancia.'</td>';

        if ($v_saldo_pendiente > 0)
            echo '          <td data-label="PENDIENTE DEPOSITO">'.number_format($v_saldo_pendiente,2,'.',',').'</td>';
        else echo '         <td data-label="PENDIENTE DEPOSITO">0.00</td>';

        $v_accion = '<button style="font-size:12px;background-color:var(--color-azulv2);" type="button" fid='.$arr_inversiones[$i]['factura_id'].' pid='.$arr_inversiones[$i]['propuesta_id'].' class="btn btn-primary openBtn2"><span class="icon-search" style="font-size:16px;"></span> Detalle</button>';
        echo '              <td data-label="MONEDA">'.$arr_inversiones[$i]['moneda'].'</td>';

        if ($arr_inversiones[$i]['e_subasta_id'] != 26) $v_estado = '<abbr title="Su inevrsión aún no ha sido ejecutada falta completar la liquidación"><span class="icon-warning" style="color:#e89b24;font-weight: bold;"></span></abbr> '.$estado;
        else $v_estado = $estado;

        echo '              <td data-label="ESTADO">'.$v_estado.'</td>
                            <td data-label="DETALLE">'.$v_accion.'</td>';
        /*if ($v_saldo_pendiente > 0)
            echo '          <td data-label="DEPOSITO"><a href="pendientes_deposito.php?fid='.$arr_inversiones[$i]['factura_id'].'&ret=inversiones"><span class="icon-coin-dollar"></span> Depositar</a></td>';
        else echo '         <td data-label="DEPOSITO">-</td>';*/
        
        echo '          </tr>';
    }

    echo '</table></li></ul></div>';
} else{
    echo '<p style="margin:auto;margin-top:20px;font-size:16px;max-width:400px;text-align:center;"><i class="fa-solid fa-face-sad-tear"></i> Usted no tiene inversiones activas</p>';
}
?>