<?
if(isset($_GET['page'])){
    //-------------- cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/c_inversiones.php");
    $rowcount = $_GET['rowcount'];
    $v_solopreliminar_page = $_GET['solo_preliminar'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
    $v_solopreliminar_page = $v_solopreliminar;
}

$obj_inversiones = new inversiones;

if ($rowcount < 0){
    if ($v_solopreliminar_page == 1) $rowcount = $obj_inversiones->get_propuestas_pre_xinversor('COUNT',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,0);
    else $rowcount = $obj_inversiones->get_propuestas_xinversor('COUNT',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,0);  // PROPUESTAS activas
}

if ($rowcount > 0){
    $rowsxpage = 15;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);

    if ($v_solopreliminar_page == 1) $arr_propuestas = $obj_inversiones->get_propuestas_pre_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],$rowini, $rowsxpage);
    else $arr_propuestas = $obj_inversiones->get_propuestas_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],$rowini, $rowsxpage);
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
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_rowini_label.' al '.$v_rowfin_label.' de '.$rowcount.' resultados</li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">PROPUESTA</th>
                    <th scope="col">CLIENTE</th>
                    <th scope="col">F REGISTRO</th>
                    <th scope="col">% FACTURA</th>
                    <th scope="col">MONEDA</th>
                    <th scope="col">MONTO</th>
                    <th scope="col">TIA</th>
                    <th scope="col">RIESGO</th>
                    <th scope="col">DETALLE</th>
                    <th scope="col">NOTE</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($arr_propuestas); $i++){
        $f_registro = date('d-m-Y',strtotime($arr_propuestas[$i]['propuesta_fcreacion']));
        if ($arr_propuestas[$i]['estado_id'] == 54) $v_preliminar = '<abbr title="Propuesta PRELIMINAR"><i class="fa-solid fa-triangle-exclamation" style="font-size:16px;color:var(--color-amarillo);"></i></abbr>';
        else $v_preliminar = '-';

        echo '          <tr>
                            <td data-label="ID">'.$arr_propuestas[$i]['factura_id'].'</td>
                            <td data-label="PROPUESTA">'.$arr_propuestas[$i]['propuesta_id'].'</td>
                            <td data-label="CLIENTE">'.$arr_propuestas[$i]['cliente_nombre'].'</td>
                            <td data-label="F REGISTRO">'.$f_registro.'</td>
                            <td data-label="% FACTURA">'.number_format($arr_propuestas[$i]['representacion']*100,0,'.',',').' %</td>
                            <td data-label="MONEDA">'.$arr_propuestas[$i]['moneda_nombre'].'</td>
                            <td data-label="MONTO">'.number_format($arr_propuestas[$i]['monto'],2,'.',',').'</td>
                            <td data-label="TIA">'.number_format($arr_propuestas[$i]['tia']*100,0,'.',',').' %</td>
                            <td data-label="RIESGO" style="color:#'.$arr_propuestas[$i]['color_letra'].';background-color:#'.$arr_propuestas[$i]['color_fondo'].'">['.$arr_propuestas[$i]['riesgo_calificacion'].'] '.$arr_propuestas[$i]['riesgo_nombre'].'</td>';

        $v_accion = '<button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" fid='.$arr_propuestas[$i]['factura_id'].' 
                         pid='.$arr_propuestas[$i]['propuesta_id'].' estado_id='.$arr_propuestas[$i]['estado_id'].' retorno=propuestas pagina='.$pageNum.' rowcount='.$rowcount.' 
                         subastaid='.$arr_propuestas[$i]['subasta_id'].' class="btn btn-primary openBtn2"><span class="icon-search" style="font-size:16px;"></span> Detalle
                    </button>';
        echo '              <td data-label="DETALLE">'.$v_accion.'</td>
                            <td data-label="NOTE">'.$v_preliminar.'</td>
                        </tr>';
    }

    echo '      </tbody></table></ul></div>';
} else {
    echo '<p style="margin:auto;font-size:16px;max-width:400px;text-align:center;"><i class="fa-solid fa-face-sad-tear"></i> Usted no tiene propuestas registradas</p>';
}
?>