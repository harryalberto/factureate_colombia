<?php
if(isset($_GET['page'])){
    //-------------- cuando cambia de pagina
    $rowcount = $_GET['rowcount'];
    $v_solopreliminar_page = $_GET['solo_preliminar'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$vobj_sub = new subasta;

$varr_confirmaciones = $vobj_sub->get_confirmaciones($_SESSION['user']['empresaid']);
$total_paginas = 0;

if ($rowcount < 0){
    $rowcount = count($varr_confirmaciones);
}

if ($rowcount > 0){
    $rowsxpage = 1000;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);

    // pintado del resultado
    echo '<div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">';
    //-- HEADER
    echo '  <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID OPERACION</th>
                    <th scope="col">NRO FACTURA</th>
                    <th scope="col">F VENCIMIENTO</th>
                    <th scope="col">CLIENTE</th>
                    <th scope="col">F CONFIRMACION</th>
                    <th scope="col">H CONFIRMACION</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO FACTURA</th>
                    <th scope="col">MONTO ADELANTO</th>
                    <th scope="col">CONFIRMA</th>
                    <th scope="col">RECHAZA</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($varr_confirmaciones); $i++){
        $vf_vencimiento = date('d-m-Y',strtotime($varr_confirmaciones[$i]['fecha_vencimiento']));
        $vf_registro = date('d-m-Y',strtotime($varr_confirmaciones[$i]['fecha_registro']));

        echo '          <tr>
                            <td data-label="ID OPERACION">'.$varr_confirmaciones[$i]['factura_id'].'</td>
                            <td data-label="NRO FACTURA">'.$varr_confirmaciones[$i]['factura_numero'].'</td>
                            <td data-label="F VENCIMIENTO">'.$vf_vencimiento.'</td>
                            <td data-label="CLIENTE">'.$varr_confirmaciones[$i]['cliente_nombre'].'</td>
                            <td data-label="F CONFIRMACION">'.$vf_registro.'</td>
                            <td data-label="H CONFIRMACION">'.$varr_confirmaciones[$i]['hora_registro'].'</td>
                            <td data-label="MONEDA">'.$varr_confirmaciones[$i]['moneda_simbolo'].'</td>
                            <td data-label="MONTO FACTURA">'.number_format($varr_confirmaciones[$i]['monto_factura'],2,'.',',').'</td>
                            <td data-label="MONTO ADELANTO">'.number_format($varr_confirmaciones[$i]['monto'],2,'.',',').'</td>
                            <td data-label="CONFIRMA"><button style="font-size:11px;background-color:var(--color-azulv2);border:none;" type="button" confirma_id='.$varr_confirmaciones[$i]['confirmacion_id'].' class="btn btn-primary openBtn2"><i class="fa-solid fa-square-check"></i> Confirmar
                    </button></td>
                            <td data-label="RECHAZA"><button type="button" confirma_id='.$varr_confirmaciones[$i]['confirmacion_id'].' class="btn btn-primary openBtn3" style="font-size:11px;background-color:var(--color-rojo);border:none;">
                                    <i class="fa-solid fa-ban"></i> Rechazar</button></td>
                        </tr>';
    }

    echo '      </tbody></table></ul></div>';
}