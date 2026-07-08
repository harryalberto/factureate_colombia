<?php
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/maestros.php");
    $estadoid = $_GET['estadoid'];
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$obj_empresas = new maestros;

if ($rowcount < 0) $rowcount = $obj_empresas->get_empresas_xestado($estadoid,0,0,'count',0);

if ($rowcount > 0){
    $rowsxpage = 10;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arr_empresas = $obj_empresas->get_empresas_xestado($estadoid,$rowsxpage,$rowini,'select',0);
    //===== calculando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;
    
    //-- HEADER
    $v_rowini_label = $rowini + 1;
    echo '
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$v_rowini_label.' al '.$v_rowfinal.' de '.$rowcount.' resultados</li>
            </ul>
            <ul style="overflow:hidden;list-style:none;">
                <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">EMPRESA</th>
                    <th scope="col">IDENTIFICACION</th>
                    <th scope="col">F REGISTRO</th>
                    <th scope="col">F MODIF</th>
                    <th scope="col">F ENVIO</th>
                    <th scope="col">F APROB</th>
                    <th scope="col">TIPO</th>
                    <th scope="col">PENDIENTE RIESGO</th>
                    <th scope="col">DETALLE</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($arr_empresas); $i++){
        if (!is_null($arr_empresas[$i]['f_registro'])){
            $t_fregistro = strtotime($arr_empresas[$i]['f_registro']);
            $f_registro = date('d-m-Y',$t_fregistro);
        }
        if (!is_null($arr_empresas[$i]['f_modifica'])){
            $t_fmodifica = strtotime($arr_empresas[$i]['f_modifica']);
            $f_modifica = date('d-m-Y',$t_fmodifica);
        }
        if (!is_null($arr_empresas[$i]['f_envio'])){
            $t_fenvio = strtotime($arr_empresas[$i]['f_envio']);
            $f_envio = date('d-m-Y',$t_fenvio);
        }
        if (!is_null($arr_empresas[$i]['f_aprobacion'])){
            $t_faprobacion = strtotime($arr_empresas[$i]['f_aprobacion']);
            $f_aprobacion = date('d-m-Y',$t_faprobacion);
        }
        if ($arr_empresas[$i]['t_empresa'] == 46 || $arr_empresas[$i]['t_empresa'] == 47) $v_pendiente_riesgo = 'NO APLICA';
        else $v_pendiente_riesgo = $arr_empresas[$i]['pendiente_riesgo'];
        
        echo '      <tr>
                    <td data-label="ID">'.$arr_empresas[$i]['empresaid'].'</td>
                    <td data-label="EMPRESA">'.$arr_empresas[$i]['empresa'].'</td>
                    <td data-label="IDENTIFICACION">'.$arr_empresas[$i]['nrodoc'].'</td>
                    <td data-label="F REGISTRO">'.$f_registro.'</td>
                    <td data-label="F MODIF">'.$f_modifica.'</td>
                    <td data-label="F ENVIO">'.$f_envio.'</td>
                    <td data-label="F APROB">'.$f_aprobacion.'</td>
                    <td data-label="TIPO">'.$arr_empresas[$i]['t_empresa_nombre'].'</td>
                    <td data-label="PENDIENTE RIESGO">'.$v_pendiente_riesgo.'</td>
                    <td data-label="DETALLE"><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$arr_empresas[$i]['empresaid'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button></td>
                    </tr>';
                //<td data-label="DETALLE"><a href="empresa_gestion_detalle.php?id='.$arr_empresas[$i]['empresaid'].'&previo=empresas" style="color:#000000;font-weight: bold;"><span class="icon-search"></span> Ver Detalle</a></td>
    }

    echo '      </tbody></table>
            </ul>
        </div>';
}
?>