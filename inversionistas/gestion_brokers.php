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

<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA

$vobj_mae = new maestros;

$varr_estados = $vobj_mae->get_estados('INVERSIONISTA');
$v_q_estados = 2;

if (!empty($_POST['estados'])){
    $v_i = 0;
    $v_estados = '';
    
    foreach($_POST['estados'] as $selected){
        if ($v_i > 0) $v_estados .= ',';

        $v_estados .= $selected;
        $varr_estados_f[$v_i] = $selected;
        $v_i ++;
    }

    $v_i++;

    if ($v_i <= $v_q_estados){
        for ($v_j = $v_i; $v_j <= $v_q_estados; $v_j++){
            $v_estados .= ',-1';
        }
    }
} else {
    $v_estados = '2';
    $varr_estados_f[0] = 2;
}

//==== CALCULO LOS FILTROS

$filtros = 'usuarios.estado_activo in ('.$v_estados.')';

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS

$rowcount = $vobj_mae->get_brokers_list('COUNT', 0, 0, $filtros,'');
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'inversionistas/gestion_brokers.php';
    //$pagina = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:300px;margin:auto;">
        Relaci&oacute;n de Brokers
    </div>

    <!--================================================== 
    ========================== FILTROS -->

    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="inversores.php">
        <ul>
            <li><span class="icon-filter"></span> Estados:</li>
        </ul>
        <ul>

            <li><input type="checkbox" class="frminput_text" name="estados[]" value="1" onclick="checkEvent(this)">
                <label style="padding-left:5px;padding-right:5px;">ACTIVO</label>
            </li>
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="2" checked onclick="checkEvent(this)">
                <label style="padding-left:5px;padding-right:5px;">PENDIENTE</label>
            </li>
            
        </ul>
        </form>
    </div>

    <!--==== TABLA HEADER -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID</th>         <th scope="col" class="sort asc">NOMBRE</th>
                        <th scope="col" class="sort asc">DOCUMENTO</th>  <th scope="col" class="sort asc">EMAIL</th>
                        <th scope="col" class="sort asc">ESTADO</th>     <th scope="col" class="sort asc">DETALLE</th>
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
            <input type="hidden" id="orderCol" value="1">
            <input type="hidden" id="orderType" value="asc">
            <input type="hidden" id="num_registros" value="10">
            <input type="hidden" id="rowcount" value="<?=$rowcount?>">
            <input type="hidden" id="filtros" value="<?=$filtros?>">
        </div>
    </div>

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA MODAL -->

    <div class="modal fade" id="InversorDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Subasta</h5></li>
                <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
            </ul>
        </div>
        <div class="modal-body">
        </div>
        <!--<div class="modal-footer">-->
            <!--<p class="botontransaccionazul" id="btn_grabar_accionistas"><span class="icon-floppy-disk"></span><a href="javascript:guarda_accionistas('accionistas')" style=""> Guardar</a></p>-->
        <!--</div>-->
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

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            
            fetch("gestion_brokers_load.php", {
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
    
        function filtrar(){
            document.frm.submit();
        }

        function checkEvent(p_obj){
            let checkboxes = document.getElementsByName('estados[]');
            let result = 0;
            let estados = '';
            let filtros = '';
            var v_nombre = $('#nombre').val();

            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    if (result == 0) estados = checkboxes[i].value;
                    else estados += ',' + checkboxes[i].value;

                    result = result + 1;
                }
            }

            if (result == 0){
                alert('Debe seleccionar al menos una opción');
                p_obj.checked = true;
            } else {
                let objfiltros = document.getElementById('filtros');
                let objrowcount = document.getElementById('rowcount');

                filtros = 'usuarios.estado_activo in ('+estados+')';
                objfiltros.value = filtros;
                objrowcount.value = 0;

                getData();
            }
        }

        function verDetalle(p_broker){
            $('.modal-title').text('DETALLE BROKER');
            $('.modal-body').load('gestion_brokers_modal.php?broker_id='+p_broker,function(){
                $('#InversorDetalle').modal({show:true});
            });
        }

        function refresh_page(){
            location.href = 'gestion_brokers.php';
        }

    </script>
</BODY>
</HTML>