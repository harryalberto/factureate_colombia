<?
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/c_subasta.php");
    require("../lib-trans/maestros.php");
    $estadoid = $_GET['estadoid'];
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$objsubasta = new subasta;
$obj_maestros_pag = new maestros;

if ($rowcount < 0) $rowcount = $objsubasta->get_subastas_xestado($estadoid,0,0,'count');

if ($rowcount > 0){
    $rowsxpage = 20;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arrsubastas = $objsubasta->get_subastas_xestado($estadoid,$rowsxpage,$rowini,'select');
    $arrparam = $obj_maestros_pag->get_parametros();
    // pintado del resultado
    echo '<div class="listado">';
    //-- HEADER
    echo '  <ul class="listado_header">
                <li style="width:50px;text-align:center;">ID</li>
                <li style="width:300px;">Pagador</li>
                <li style="width:100px;">Factura</li>
                <li style="width:100px;">FVencimiento</li>
                <li style="width:100px;">Monto Subasta</li>
                <li style="width:50px;">Moneda</li>
                <li style="width:80px;">FSubasta</li>
                <li style="width:50px;">Tiempo(hrs)</li>
                <li style="width:100px;">Acci&oacute;n</li>
            </ul>';
    //-- DETALLE
    for ($i=0; $i<count($arrsubastas); $i++){
        $t_fvencimiento = strtotime($arrsubastas[$i]['fvencimiento']);
        $fvencimiento = date('d-m-Y',$t_fvencimiento);
        $vt_fsubasta = strtotime($arrsubastas[$i]['fsubasta_creacion'].' '.$arrsubastas[$i]['hsubasta_creacion']);
        $v_fsubasta = date('d-m-Y',strtotime($arrsubastas[$i]['fsubasta_creacion'])).' '.$arrsubastas[$i]['hsubasta_creacion'];
        $v_tiempo = round(((time() - $vt_fsubasta) / 60) / 60);

        if ($v_tiempo < $arrparam['horas medio subasta']['valornum']) $v_bgtiempo = '';
        elseif ($v_tiempo >= $arrparam['horas medio subasta']['valornum'] || $v_tiempo <= $arrparam['horas medio subasta']['valorchar']) $v_bgtiempo = 'background-color:#e89b24;';
        else $v_bgtiempo = 'background-color:#ff3333;color:#ffffff;';
        
        echo '  <ul>
                    <li style="width:50px;text-align:center;">'.$arrsubastas[$i]['subastaid'].'</li>
                    <li style="width:300px;text-align:left;">'.$arrsubastas[$i]['cliente'].'</li>
                    <li style="width:100px;">'.$arrsubastas[$i]['facnumero'].'</li>
                    <li style="width:100px;">'.$fvencimiento.'</li>
                    <li style="width:100px;">'.number_format($arrsubastas[$i]['montofin'],2,'.',',').'</li>
                    <li style="width:50px;">'.$arrsubastas[$i]['moneda'].'</li>
                    <li style="width:80px;">'.$v_fsubasta.'</li>
                    <li style="width:50px;text-align:center;'.$v_bgtiempo.'">'.$v_tiempo.'</li>
                    <li style="width:100px;text-align:center;"><a href="subasta_gestion_detalle.php?id='.$arrsubastas[$i]['subastaid'].'&return='.$estadoid.'">Ver</a></li>
                </ul>';
    }

    echo '</div>';
    
    if ($total_paginas > 1) {
        echo '<div class="pagination">';
        echo '  <ul>';
        if ($pageNum != 1) 
            echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'" estadoid="'.$estadoid.'">Anterior</a></li>';

        for ($i=1;$i<=$total_paginas;$i++) {
            if ($pageNum == $i) echo '<li class="active"><a>'.$i.'</a></li>';
            else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'" estadoid="'.$estadoid.'">'.$i.'</a></li>';
        }

        if ($pageNum != $total_paginas) 
            echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'" estadoid="'.$estadoid.'">Siguiente</a></li>';
        echo '  </ul>
            </div>';
    }
}
?>