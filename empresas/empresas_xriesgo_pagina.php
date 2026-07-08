<?php
if(isset($_GET['page'])){
    // cuando cambia de pagina
    require("../conn/conn_db_trans.inc");
    require("../conn/conn_db_param_trans.inc");
    require("../lib-trans/maestros.php");
    $estados = $_GET['estados'];
    $rowcount = $_GET['rowcount'];
} else{
    $rowcount = -1; // significa que se debe llamar al query
}

$obj_empresas = new maestros;

if ($rowcount < 0) $rowcount = $obj_empresas->get_empresas_xestadoriesgo($varr_estados['PENDIENTES'], $varr_estados['VENCIDOS'], $varr_estados['XVENCER'], $varr_estados['ACTIVOS'], 0, 0,'count');

if ($rowcount > 0){
    $rowsxpage = 15;

    if(isset($_GET['page'])){
        sleep(1);
        $pageNum = $_GET['page'];
    } else $pageNum = 1;

    $rowini = ($pageNum - 1) * $rowsxpage;
    $total_paginas = ceil($rowcount / $rowsxpage);
    $arr_empresas = $obj_empresas->get_empresas_xestadoriesgo($varr_estados['PENDIENTES'], $varr_estados['VENCIDOS'], $varr_estados['XVENCER'], $varr_estados['ACTIVOS'],$rowsxpage,$rowini,'select');
    //===== calculando barra de ayuda de numeracion =======
    if ($pageNum == $total_paginas) $v_rowfinal = $rowcount;
    else $v_rowfinal = $pageNum * $rowsxpage;
    // pintado del resultado
    echo '<div style="overflow:hidden;margin:1px;padding:2px;">
            <ul style="overflow:hidden;list-style:none;">
                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">'.$rowini.' al '.$v_rowfinal.' de '.$rowcount.' resultados</li>
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
                    <th scope="col">PEND RIESGO</th>
                    <th scope="col">ESTADO</th>
                    <th scope="col">DIAS</th>
                    <th scope="col">ACCION</th>
                </tr></thead>
                <tbody>';
    //-- DETALLE
    for ($i=0; $i<count($arr_empresas); $i++){
        if (!is_null($arr_empresas[$i]['f_registro'])){$t_fregistro = strtotime($arr_empresas[$i]['f_registro']);$f_registro = date('d-m-Y',$t_fregistro);}
        if (!is_null($arr_empresas[$i]['f_modifica'])){$t_fmodifica = strtotime($arr_empresas[$i]['f_modifica']);$f_modifica = date('d-m-Y',$t_fmodifica);}
        if (!is_null($arr_empresas[$i]['f_envio'])){$t_fenvio = strtotime($arr_empresas[$i]['f_envio']);$f_envio = date('d-m-Y',$t_fenvio);}
        if (!is_null($arr_empresas[$i]['f_aprobacion'])){$t_faprobacion = strtotime($arr_empresas[$i]['f_aprobacion']);$f_aprobacion = date('d-m-Y',$t_faprobacion);}
        if ($arr_empresas[$i]['tempresa_id'] == 46 || $arr_empresas[$i]['tempresa_id'] == 47) $v_pendiente_riesgo = 'NO APLICA'; else $v_pendiente_riesgo = $arr_empresas[$i]['pendiente_riesgo'];

        echo '  <tr>
                    <td data-label="ID">'.$arr_empresas[$i]['empresa_id'].'</td>
                    <td data-label="EMPRESA">'.$arr_empresas[$i]['empresa'].'</td>
                    <td data-label="IDENTIFICACION">'.$arr_empresas[$i]['nrodoc'].'</td>
                    <td data-label="F REGISTRO">'.$f_registro.'</td>
                    <td data-label="F MODIF">'.$f_modifica.'</td>
                    <td data-label="F ENVIO">'.$f_envio.'</td>
                    <td data-label="F APROB">'.$f_aprobacion.'</td>
                    <td data-label="TIPO">'.$arr_empresas[$i]['tempresa'].'</td>
                    <td data-label="PEND RIESGO">'.$v_pendiente_riesgo.'</td>
                    <td data-label="ESTADO">'.$arr_empresas[$i]['estado'].'</td>
                    <td data-label="DIAS">'.$arr_empresas[$i]['dias_alert'].'</td>
                    <td data-label="ACCION"><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verdetalle('.$arr_empresas[$i]['empresa_id'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button></td>
                </tr>';
    }

    echo '      </tbody></table>
            </ul>
        </div>';
}
?>