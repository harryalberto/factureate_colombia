<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'FINANCIAMIENTO';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA
$objmaestro = new maestros;
$vobj_factura = new factura;

$arrestados = $objmaestro->get_estados('FINANCIAMIENTO');
$dias_max_xvencer = $objmaestro->dias_vigencia_riesgos(12);

date_default_timezone_set("America/Santo_Domingo");
//========= LOGICA DE ESTADOS
$v_q_estados = count($arrestados);

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
    $v_estados = '27, 51, -1, -1, -1';
    $varr_estados_f[0] = 27; $varr_estados_f[1] = 51;
}

//==== CALCULO LOS FILTROS
$filtros = 'financiamiento.estado in ('.$v_estados.')';

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS
$rowcount = $vobj_factura->get_financiamientos('COUNT', 0, 0, $filtros,'');

//#############################################################
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'facturas/finan_xestado.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Financiamientos
    </div>
    
    <!--================================================== 
    ========================== FILTROS -->
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="finan_xestado.php">
        <ul>
            <li><span class="icon-filter"></span> Estados de FINANCIAMIENTOS:</li>
        </ul>

        <ul>
<?php
    // PINTO LOS ESTADOS
    for ($i=0; $i<count($arrestados); $i++){
        if (in_array($arrestados[$i]['id'], $varr_estados_f))
            echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="'.$arrestados[$i]['id'].'" checked><label style="padding-left:5px;padding-right:5px;">'.$arrestados[$i]['nombre'].'</label></li>';
        else 
            echo '
            <li><input type="checkbox" class="frminput_text" name="estados[]" value="'.$arrestados[$i]['id'].'"><label style="padding-left:5px;padding-right:5px;">'.$arrestados[$i]['nombre'].'</label></li>';
    }
        
?>
            <li>
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-right: 10px;" onclick="filtrar()"><i class="fa-solid fa-filter"></i> Filtrar</button>
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
                        <th scope="col" class="sort asc">OPERACION ID</th>          <th scope="col" class="sort asc">EMISOR</th>
                        <th scope="col" class="sort asc">PAGADOR</th>               <th scope="col" class="sort asc">FACTURA</th>
                        <th scope="col" class="sort asc">F VTO</th>                 <th scope="col" class="sort asc">DIAS FINAN</th>
                        <th scope="col" class="sort asc">MONEDA</th>                <th scope="col" class="sort asc">MONTO FINAN</th>    
                        <th scope="col" class="sort asc">MONTO FACTURA</th>         <th scope="col" class="sort asc">DIAS XVENCER</th>    
                        <th scope="col" class="sort asc">PAGO CONFIRMA</th>         <th scope="col" class="sort asc">F CONFIRMA</th>
                        <th scope="col" class="sort asc">ACCION</th>
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

    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido -->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:600px;float:left;"><h5 id="exampleModalLabel" style="color:var(--color-azulv2);font-weight: bold;font-size:20px;">Detalle del Financiamiento</h5></li>
                        <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
                    </ul>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
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

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            
            fetch("finan_xestado_load.php", {
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

    <script>
        function filtrar(){
            document.frm.submit();
        }

        function verDetalle(p_finan_id){
                $('.modal-title').text('DETALLE INVERSOR');
                $('.modal-body').load('detalle_financiamiento.php?ffid='+p_finan_id,function(){
                    $('#myModal').modal({show:true});
                });
        }
    </script>
    
</BODY>
</HTML>