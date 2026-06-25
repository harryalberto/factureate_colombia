<?php
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/c_cuentas.php");
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
    $varr_fitros = '';
}

$obj_cuentas = new cuentas;

if ($rowcount < 0) $rowcount = $obj_cuentas->get_cuentas_inversores('COUNT',0,0, $varr_fitros);

if ($rowcount > 0){
    $rowsxpage = 20;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $varr_cuentas = $obj_cuentas->get_cuentas_inversores('SELEC',$rowini,$rowsxpage, $varr_fitros);
    // pintado del resultado
    echo '<div style="overflow:hidden;margin:1px;padding:2px;">';
    //-- HEADER
    echo '  <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID CUENTA</th>
                    <th scope="col">INVERSOR</th>
                    <th scope="col">TIPO INVERSOR</th>
                    <th scope="col">IDENTIFICACION</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">SALDO CONTABLE</th>
                    <th scope="col">SALDO COMPROMETIDO</th>
                    <th scope="col">SALDO DISPONIBLE</th>
                    <th scope="col">SALDO EN INVERSION</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($varr_cuentas); $i++){
        $v_accion = '<button style="font-size:12px;" type="button" cuentaid='.$varr_cuentas[$i]['cuenta_id'].' retorno=cuentas pagina='.$pageNum.' rowcount='.$rowcount.' class="btn btn-primary openBtnn"><span class="icon-search" style="font-size:16px;"></span> Detalle</button>';

        echo '  <tr>
                    <td data-label="ID CUENTA">'.$varr_cuentas[$i]['cuenta_id'].'</td>
                    <td data-label="INVERSOR">'.$varr_cuentas[$i]['nombre'].'</td>
                    <td data-label="TIPO INVERSOR">'.$varr_cuentas[$i]['tipo_persona'].'</td>
                    <td data-label="IDENTIFICACION">'.$varr_cuentas[$i]['identificacion'].'</td>
                    <td data-label="MONEDA">'.$varr_cuentas[$i]['moneda'].'</td>
                    <td data-label="SALDO CONTABLE">'.number_format($varr_cuentas[$i]['saldo_contable'],2,'.',',').'</td>
                    <td data-label="SALDO COMPROMETIDO">'.number_format($varr_cuentas[$i]['saldo_comprometido'],2,'.',',').'</td>
                    <td data-label="SALDO DISPONIBLE">'.number_format($varr_cuentas[$i]['saldo_disponible'],2,'.',',').'</td>
                    <td data-label="SALDO EN INVERSION">'.number_format($varr_cuentas[$i]['saldo_invertido'],2,'.',',').'</td>
                    <td data-label="ACCION">'.$v_accion.'</td>
                </tr>';
    }

    echo '      </tbody></table></ul></div>';
    
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) 
            echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'" >Anterior</a></li>';

        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a>'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" >'.$i.'</a></li>';
        }

        if ($pageNum != $total_paginas) 
            echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'" >Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
} else echo 'NO SE ENCONTRARON CUENTAS';
?>