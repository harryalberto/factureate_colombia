<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'EMPRESAS';
    require("../lib/valida-acceso.php");
?>

</HEAD>
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA
$objmaestro = new maestros;

if (!empty($_POST['estados'])){
    $varr_estados = array('PENDIENTES'=>0,'VENCIDOS'=>0,'XVENCER'=>0,'ACTIVOS'=>0);
    
    foreach($_POST['estados'] as $selected){
        if ($selected == 1) $varr_estados['PENDIENTES'] = 1; if ($selected == 2) $varr_estados['VENCIDOS'] = 2; if ($selected == 3) $varr_estados['XVENCER'] = 3; 
        if ($selected == 4) $varr_estados['ACTIVOS'] = 4;
    }

    $v_estados = $varr_estados['PENDIENTES'].','.$varr_estados['VENCIDOS'].','.$varr_estados['XVENCER'].','.$varr_estados['ACTIVOS'];
} elseif (!empty($_GET['estados'])){
    $v_estados = $_GET['estados'];
    $v_longitud = strlen($v_estados);
    $v_aux = '';
    $v_count = 1;

    for ($i = 0; $i < $v_longitud; $i++){
        $v_pos = ($v_longitud - $i) * -1;
        $v_char = substr($v_estados, $v_pos, 1);

        if ($v_char != ',') $v_aux .= $v_char;
        else {
            if ($v_count == 1) $varr_estados['PENDIENTES'] = $v_aux; if ($v_count == 2) $varr_estados['VENCIDOS'] = $v_aux; if ($v_count == 3) $varr_estados['XVENCER'] = $v_aux; 
            if ($v_count == 4) $varr_estados['ACTIVOS'] = $v_aux;
            $varr_estados[$v_count] = $v_aux;
            $v_count ++;
            $v_aux = '';
        }
    }

    $varr_estados['ACTIVOS'] = $v_aux;
} else $varr_estados = array('PENDIENTES'=>1,'VENCIDOS'=>2,'XVENCER'=>3,'ACTIVOS'=>0);

//==== CALCULO LOS FILTROS
//$filtros = 'factura.estado ='.$estadoid;

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS

$rowcount = $objmaestro->get_empresas_xestadoriesgo($varr_estados['PENDIENTES'], $varr_estados['VENCIDOS'], $varr_estados['XVENCER'], $varr_estados['ACTIVOS'], 0, 0,'count');
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'empresas/empresas_xriesgo.php';
    $pagina = 'empresas/empresas_xriesgo.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:500px;margin:auto;">
        Relaci&oacute;n de Empresas Pagadoras con evaluación de Riesgo
    </div>
    
    <!--================================================== 
    ========================== FILTROS -->
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="empresas_xriesgo.php">
        <ul>
            <li><span class="icon-filter"></span> Estado de evaluación de riesgos de Pagador:</li>
        </ul>
        <ul>
<?php
    // PENDIENTES, VENCIDOS, XVENCER, ACTIVO
    if (in_array('1', $varr_estados)) echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="1" checked><label style="padding-left:5px;padding-right:5px;">PENDIENTES</label></li>
            <input type="hidden" name="e_pendientes" id="e_pendientes" value="1">';
    else echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="1"><label style="padding-left:5px;padding-right:5px;">PENDIENTES</label></li>
            <input type="hidden" name="e_pendientes" id="e_pendientes" value="0">';
    if (in_array('2', $varr_estados)) echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="2" checked><label style="padding-left:5px;padding-right:5px;">VENCIDOS</label></li>
            <input type="hidden" name="e_vencidos" id="e_vencidos" value="1">';
    else echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="2"><label style="padding-left:5px;padding-right:5px;">VENCIDOS</label></li>
            <input type="hidden" name="e_vencidos" id="e_vencidos" value="0">';
    if (in_array('3', $varr_estados)) echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="3" checked><label style="padding-left:5px;padding-right:5px;">x_VENCER</label></li>
            <input type="hidden" name="e_xvencer" id="e_xvencer" value="1">';
    else echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="3"><label style="padding-left:5px;padding-right:5px;">x_VENCER</label></li>
            <input type="hidden" name="e_xvencer" id="e_xvencer" value="0">';
    if (in_array('4', $varr_estados)) echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="4" checked><label style="padding-left:5px;padding-right:5px;">ACTIVO</label></li>
            <input type="hidden" name="e_activo" id="e_activo" value="1">';
    else echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="4"><label style="padding-left:5px;padding-right:5px;">ACTIVO</label></li>
            <input type="hidden" name="e_activo" id="e_activo" value="0">';
    
    echo '  <li><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="filtrar()"><i class="fa-solid fa-filter"></i> Filtrar</button></li>
        </ul>
        </form>
    </div>';
?>
    
    <!--==== TABLA HEADER -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID</th>            <th scope="col" class="sort asc">EMPRESA</th>
                        <th scope="col" class="sort asc">NRO DOC</th>       <th scope="col" class="sort asc">REGISTRO</th>
                        <th scope="col" class="sort asc">F MOD</th>         <th scope="col" class="sort asc">F ENVIO</th>
                        <th scope="col" class="sort asc">F APROB</th>       <th scope="col" class="sort asc">TIPO</th>
                        <th scope="col" class="sort asc">RIESGO PEND</th>   <th scope="col" class="sort asc">ESTADO</th>
                        <th scope="col" class="sort asc">DIAS</th>          <th scope="col" class="sort asc">DETALLE</th>
                    </tr>
                </thead>
                <tbody id="content">

                </tbody>
            </table>
        </div>

        <!--==== PAGINACION -->
        <div class="row justify-content-between">
            <div class="col-12 col-md-4">
                <label id="lbl-total" style="font-size: 10px;"></label>
            </div>

            <div class="col-12 col-md-4" id="nav-paginacion"></div>

            <input type="hidden" id="pagina" value="1">
            <input type="hidden" id="orderCol" value="0">
            <input type="hidden" id="orderType" value="asc">
            <input type="hidden" id="num_registros" value="10">
            <input type="hidden" id="rowcount" value="<?=$rowcount?>">
            <input type="hidden" id="filtros" value="<?=$filtros?>">
        </div>
    </div>

    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="EmpresaDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Empresa Detalle</h5></li>
                <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
            </ul>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
            <!--<p class="botontransaccionazul" id="btn_grabar_accionistas"><span class="icon-floppy-disk"></span><a href="javascript:guarda_accionistas('accionistas')" style=""> Guardar</a></p>-->
        </div>
        </div>
    </div>
    </div>

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA SCRIPT -->

    <script type="text/javascript">
        // Llamando a la función getData() al cargar la página
        document.addEventListener("DOMContentLoaded", getData);

        // Función para obtener datos con AJAX
        function getData() {
            //let input = document.getElementById("campo").value
            let num_registros = document.getElementById("num_registros").value
            let content = document.getElementById("content")
            let pagina = document.getElementById("pagina").value || 1;
            let orderCol = document.getElementById("orderCol").value
            let orderType = document.getElementById("orderType").value
            let rowcount = document.getElementById("rowcount").value
            let filtros = document.getElementById("filtros").value

            let e_pendientes = document.getElementById("e_pendientes").value
            let e_vencidos = document.getElementById("e_vencidos").value
            let e_xvencer = document.getElementById("e_xvencer").value
            let e_activo = document.getElementById("e_activo").value

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)

            formaData.append('e_pendientes', e_pendientes)
            formaData.append('e_vencidos', e_vencidos)
            formaData.append('e_xvencer', e_xvencer)
            formaData.append('e_activo', e_activo)
            
            fetch("empresas_xriesgo_load.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = data.data
                    document.getElementById("lbl-total").innerHTML = `Mostrando ${data.totalFiltro} de ${data.totalRegistros} registros`;
                    document.getElementById("nav-paginacion").innerHTML = data.paginacion

                    // Si la página actual no tiene resultados, ajustar la paginación para mostrar la primera página
                    if (data.data.includes('Sin resultados') && parseInt(pagina) !== 1) {
                        nextPage(1); // Ir a la primera página
                    }
                    
                })
                .catch(err => console.log(err))
        }

        // Función para cambiar de página
        function nextPage(pagina) {
            document.getElementById('pagina').value = pagina
            getData()
        }

        // Función para ordenar columnas
        function ordenar(e) {
            let elemento = e.target;
            let orderType = elemento.classList.contains("asc") ? "desc" : "asc";

            document.getElementById('orderCol').value = elemento.cellIndex;
            document.getElementById("orderType").value = orderType;
            elemento.classList.toggle("asc");
            elemento.classList.toggle("desc");

            getData()
        }

        // Event listeners para los eventos de cambio en el campo de entrada y el select
        //document.getElementById("campo").addEventListener("keyup", getData);
        //document.getElementById("num_registros").addEventListener("change", getData);

        // Event listener para ordenar las columnas
        let columns = document.querySelectorAll(".sort");
        columns.forEach(column => {
            column.addEventListener("click", ordenar);
        });
    </script>
    
    <script type="text/javascript">
        function verDetalle(p_id){
            //location.href = 'empresa_gestion_detalle.php?id='+p_id+'&previo=empresas_xriesgo';
            $('.modal-title').text('EMPRESA DETALLE');
            $('.modal-body').load('empresa_xriesgo_modal.php?id='+p_id,function(){
                $('#EmpresaDetalle').modal({show:true});
            });
        }

        function filtrar(){
            document.frm.submit();
        }

        function refresh_page(){
            //var v_empresa_id = $('#empresaid').val();
            location.href = "empresas_xriesgo.php";
        }
    </script>
</BODY>
</HTML>