<?
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/maestros.php");
    
    $rowcount = $_GET['rowcount'];
    $v_estados = $_GET['estados'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$obj_mae_pag = new maestros;

$varr_filtros[0]['filtro'] = 'ESTADO';
$varr_filtros[0]['valor'] = '('.$v_estados.')';

if ($rowcount < 0) $rowcount = $obj_mae_pag->get_inversores('COUNT', 0, 0, $varr_filtros);

if ($rowcount > 0){
    $rowsxpage = 15;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);

    $varr_inversores = $obj_mae_pag->get_inversores('SELECT', $rowini, $rowsxpage, $varr_filtros);
    
    //===== calculando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;

    $v_view_rowini = $rowini + 1;
    
    // pintado del resultado
    echo '<div style="overflow:hidden;margin:5px;padding:5px;">';
    //-- HEADER
    echo '  <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_view_rowini.' al '.$v_rowfinal.' de '.$rowcount.' resultados</li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                <thead>
                    <tr>
                    <th scope="col">ID</th>
                    <th scope="col">NOMBRE</th>
                    <th scope="col">DOCUMENTO</th>
                    <th scope="col">EMAIL</th>
                    <th scope="col">TELEFONO</th>
                    <th scope="col">ESTADO</th>
                    <th scope="col">TIPO</th>
                    <th scope="col">CATEGORIA</th>
                    <th scope="col">DETALLE</th>
                    </tr>
                </thead>
                <tbody>';

    //-- DETALLE
    for ($i=0; $i<count($varr_inversores); $i++){
        $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_inversores[$i]['inversor_id'].','.$varr_inversores[$i]['tipo_inversor'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';
        $v_boton_bloqueo = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;" onclick="bloquear('.$varr_inversores[$i]['inversor_id'].')"><i class="fa-solid fa-ban"></i> Bloquear</button>';

        echo '      <tr>
                    <td data-label="ID">'.$varr_inversores[$i]['inversor_id'].'</td>
                    <td data-label="NOMBRE">'.$varr_inversores[$i]['nombre'].'</td>
                    <td data-label="DOCUMENTO">'.$varr_inversores[$i]['identificacion'].'</td>
                    <td data-label="EMAIL">'.$varr_inversores[$i]['email'].'</td>
                    <td data-label="TELEFONO">'.$varr_inversores[$i]['telefono'].'</td>
                    <td data-label="ESTADO">'.$varr_inversores[$i]['estado_nombre'].'</td>
                    <td data-label="TIPO">'.$varr_inversores[$i]['tipo_inversor_nombre'].'</td>
                    <td data-label="CATEGORIA">'.$varr_inversores[$i]['categoria_nombre'].'</td>
                    <td data-label="DETALLE">'.$v_boton_detalle.'</td>
                    </tr>';
    }

    echo '      </tbody>
                </table>
            </ul>
        </div>';

        if ($total_paginas > 1) {
            echo '<div class="pagination">';
            echo '  <ul>';
            if ($pageNum != 1) 
                echo '  <li><a class="paginate" pagenum="'.($pageNum-1).'" rowcount="'.$rowcount.'">Anterior</a></li>';

            for ($i=1;$i<=$total_paginas;$i++) {
                if ($pageNum == $i) echo '<li class="active"><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'">'.$i.'</a></li>';
                else echo '<li><a class="paginate" pagenum="'.$i.'" rowcount="'.$rowcount.'">'.$i.'</a></li>';
            }

            if ($pageNum != $total_paginas) 
                echo '<li><a class="paginate" pagenum="'.($pageNum+1).'" rowcount="'.$rowcount.'">Siguiente</a></li>';
            echo '  </ul>
                </div>';
        }
}
?>