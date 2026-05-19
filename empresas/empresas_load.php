<?php
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");

$vobj_mae_load = new maestros;

$rowini = ($_POST['pagina'] - 1) * $_POST['registros'];
$v_order = 'empresa.id ';
$pagina = $_POST['pagina'];

if ($_POST['rowcount'] == 0) $rowcount = $vobj_mae_load->get_empresas('COUNT', 0, 0, $_POST['filtros'], '');
else $rowcount = $_POST['rowcount'];

if ($_POST['orderCol'] == 0) $v_order = 'empresa.id '.$_POST['orderType'];
if ($_POST['orderCol'] == 1) $v_order = 'empresa.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 2) $v_order = 'empresa.identificacion '.$_POST['orderType'];
if ($_POST['orderCol'] == 3) $v_order = 'empresa.email_contacto '.$_POST['orderType'];
if ($_POST['orderCol'] == 4) $v_order = 'empresa.telefonocontacto '.$_POST['orderType'];
if ($_POST['orderCol'] == 5) $v_order = 'estado.nombre '.$_POST['orderType'];
if ($_POST['orderCol'] == 6) $v_order = 'tipos.nombre '.$_POST['orderType'];

$varr_empresas = $vobj_mae_load->get_empresas('SELECT', $_POST['registros'], $rowini, $_POST['filtros'], $v_order);
$totalFiltro = count($varr_empresas);
$totalRegistros = $rowcount;

// Mostrado resultados
$output = [];
$output['totalRegistros'] = $rowcount;
$output['totalFiltro'] = $totalFiltro;
$output['data'] = '';
$output['paginacion'] = '';

for ($i = 0; $i < count($varr_empresas); $i++){
    $v_boton_detalle = '<button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="verDetalle('.$varr_empresas[$i]['empresa_id'].')"><i class="fa-solid fa-magnifying-glass"></i> Detalle</button>';

    if ($varr_empresas[$i]['tipo_empresa'] == 46){
        //EMISOR
        $v_categoria ='NO APLICA';

        if ($varr_empresas[$i]['contrato'] == 'VACIO'){
            if ($varr_empresas[$i]['envio_contrato'] == '2000-01-01') $v_contrato = 'NO ENVIADO';
            else $v_contrato = 'ENVIADO';
        } else $v_contrato = 'FIRMADO';
    } elseif ($varr_empresas[$i]['tipo_empresa'] == 48){
        //OBLIGADO AL PAGO
        $v_contrato = 'NO APLICA';

        if ($varr_empresas[$i]['categoria'] == 'VACIO') $v_categoria = 'PENDIENTE';
        else $v_categoria = $varr_empresas[$i]['categoria'].' ('.$varr_empresas[$i]['calificacion'].')';
    } else{
        if ($varr_empresas[$i]['contrato'] == 'VACIO'){
            if ($varr_empresas[$i]['envio_contrato'] == '2000-01-01') $v_contrato = 'NO ENVIADO';
            else $v_contrato = 'ENVIADO';
        } else $v_contrato = 'FIRMADO';

        if ($varr_empresas[$i]['categoria'] == 'VACIO') $v_categoria = 'PENDIENTE';
        else $v_categoria = $varr_empresas[$i]['categoria'].' ('.$varr_empresas[$i]['calificacion'].')';
    }
    
    $output['data'] .= '<tr>
                            <td data-label="ID">'.$varr_empresas[$i]['empresa_id'].'</td>       <td data-label="NOMBRE">'.$varr_empresas[$i]['empresa_nom'].'</td>
                            <td data-label="DOCUMENTO">'.$varr_empresas[$i]['nrodoc'].'</td>    <td data-label="EMAIL">'.$varr_empresas[$i]['email'].'</td>
                            <td data-label="TELEFONO">'.$varr_empresas[$i]['telefono'].'</td>   <td data-label="ESTADO">'.$varr_empresas[$i]['estado_nom'].'</td>
                            <td data-label="TIPO">'.$varr_empresas[$i]['tipo_empresa_nom'].'</td>   <td data-label="CATEGORIA">'.$v_categoria.'</td>
                            <td data-label="CONTRATO">'.$v_contrato.'</td>                      <td data-label="ACCION">'.$v_boton_detalle.'</td>
                        </tr>';
}

if ($totalFiltro == 0){
	$output['data'] .= '<tr>';
    $output['data'] .= '<td colspan="9">Sin resultados</td>';
    $output['data'] .= '</tr>';
}

if ($totalRegistros > 0) {
    $totalPaginas = ceil($totalRegistros / $_POST['registros']);

    $output['paginacion'] .= '<nav>';
    $output['paginacion'] .= '<ul class="pagination">';

    $numeroInicio = max(1, $pagina - 4);
    $numeroFin = min($totalPaginas, $numeroInicio + 9);

    for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
        $output['paginacion'] .= '<li class="page-item' . ($pagina == $i ? ' active' : '') . '">';
        $output['paginacion'] .= '<a class="page-link" href="#" onclick="nextPage(' . $i . ')">' . $i . '</a>';
        $output['paginacion'] .= '</li>';
    }

    $output['paginacion'] .= '</ul>';
    $output['paginacion'] .= '</nav>';
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>