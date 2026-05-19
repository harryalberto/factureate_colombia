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

<?
    require("../lib/head.php");
    $acceso = 'PENDIENTES PAGO';
    require("../lib/valida-acceso.php");
?>

</HEAD>
<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA
$vobj_factura = new factura;

date_default_timezone_set($_SESSION['user']['zona_horaria']);

//==== CALCULO LOS FILTROS
$filtros = 'financiamiento.estado not in (28,29,37) and factura.clienteid = '.$_SESSION['user']['empresaid'];

if (isset($_POST['proveedores_id'])) {
    if ($_POST['proveedores_id'] > 0) $filtros .= ' and factura.emisorid = '.$_POST['proveedores_id'];
}

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS
$rowcount = $vobj_factura->get_financiamientos('COUNT', 0, 0, $filtros,'');

//#############################################################
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'op/pendientes_pago.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Facturas en factoring Pendientes de Pago
    </div>

    <!--==================================================
    ========================== FILTROS -->
    <form id="frmFiltros" method="post">
        <input type="hidden" name="monto_seleccionado" id="monto_seleccionado" value="0">
        <input type="hidden" name="q_seleccion" id="q_seleccion" value="0">

    <div class="frmtransaccion">
        <ul>
            <li>Proveedores:</li>
            <li>
                <select id="proveedores_id" name="proveedores_id" class="formulario_control" onchange="refresh_page_form()">

<?php
    $varr_emisores = $vobj_factura->get_proveedores_finan_xcliente($_SESSION['user']['empresaid']);
    $v_emisor_selec = 0;

    if (isset($_POST['proveedores_id'])) {
        $v_emisor_selec = $_POST['proveedores_id'];

        if ($v_emisor_selec > 0)
            echo '  <option value="0">Todos</option>';
        else
            echo '  <option value="0" selected>Todos</option>';
    } else echo '   <option value="0" selected>Todos</option>';

    for ($i = 0; $i < count($varr_emisores); $i++){
        if ($v_emisor_selec == $varr_emisores[$i]['emisor_id'])
            echo '  <option value="'.$varr_emisores[$i]['emisor_id'].'" selected>'.$varr_emisores[$i]['emisor'].'</option>';
        else
            echo '  <option value="'.$varr_emisores[$i]['emisor_id'].'">'.$varr_emisores[$i]['emisor'].'</option>';
    }

?>

                </select>
            </li>
            <li style="margin-left: 10px; font-weight: bold;">Total selección:</li>
            <li id="seleccion" style="font-size: 16px; font-weight: bold; margin-right: 10px;">0.00</li>
            <li>
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;" onclick="pagarSeleccion()"><i class="fa-solid fa-money-check-dollar"></i> Pagar Selección</button>
            </li>
        </ul>
    </div>
    <!--==== TABLA HEADER -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">[]</th>
                        <th scope="col" class="sort asc">FACTURA</th>       <th scope="col" class="sort asc">PROVEEDOR</th>
                        <th scope="col" class="sort asc">MONEDA</th>        <th scope="col" class="sort asc">MONTO</th>
                        <th scope="col" class="sort asc">F VTO</th>         <th scope="col" class="sort asc">DIAS XVENCER</th>
                        <th scope="col" class="sort asc">F PAGO PROG</th>   <th scope="col" class="sort asc">F PAGO</th>
                        <th scope="col" class="sort asc">DETALLE</th>       <th scope="col" class="sort asc">PAGAR</th>
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
            <input type="hidden" id="orderCol" value="4">
            <input type="hidden" id="orderType" value="desc">
            <input type="hidden" id="num_registros" value="10">
            <input type="hidden" id="rowcount" value="<?=$rowcount?>">
            <input type="hidden" id="filtros" value="<?=$filtros?>">
        </div>
    </div>

    </form>
    <!--#####################################################
    ########### ZONA MODAL
    #########################################################-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle</h5></li>
                        <li style="width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
                    </ul>
                </div>
                <div class="modal-body">
                </div>
                <!--<div class="modal-footer">-->
                    <!--<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>-->
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

            fetch("pendientes_pago_load.php", {
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

        function verDetalle(p_factura_id){
            $('.modal-title').text('DETALLE FACTURA');
            $('.modal-body').load('detalle_factura_op.php?fid='+p_factura_id,function(){
                $('#myModal').modal({show:true});
            });
        }

        function progPago(p_factura_id){
            /*$('.modal-title').text('PROG PAGO');
            $('.modal-body').load('programa_pago_op.php?fid='+p_factura_id,function(){
                $('#myModal').modal('show');
            });*/
            $('#myModal .modal-body').load('programa_pago_op.php?fid='+p_factura_id, function(){
                $('#myModal').modal('show');
            });
        }

        function pagar(p_factura_id){
            $('.modal-title').text('PAGAR FACTURA');
            $('.modal-body').load('boton_pago_factura_op.php?fid='+p_factura_id,function(){
                $('#myModal').modal({show:true});
            });
        }

        function botonPago(p_factura_id){
            $('#myModal').modal({show:false});

            $('.modal-title').text('PAGO DE FACTURA');
            $('.modal-body').load('boton_pago_factura_op.php?fid='+p_factura_id,function(){
                $('#myModal').modal({show:true});
            });
        }

        function cierraModal(){
            //$('#myModal').modal('hide');
            $('#myModal').removeClass('in');
            $('#myModal').hide();
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }

        function seleccion(obj_check){
            var monto_seleccionado = Number(document.getElementById("monto_seleccionado").value);
            var factura_id = obj_check.value;
            var monto_factura = Number(document.getElementById("monto"+factura_id).value);

            if (obj_check.checked) {
                monto_seleccionado = monto_seleccionado + monto_factura;
                document.getElementById("monto_seleccionado").value = monto_seleccionado;
                monto_seleccionado_format = new Intl.NumberFormat('en-US', {minimumFractionDigits: 2,maximumFractionDigits: 2}).format(monto_seleccionado);
                document.getElementById("seleccion").innerHTML = monto_seleccionado_format;
            } else {
                monto_seleccionado = monto_seleccionado - monto_factura;
                document.getElementById("monto_seleccionado").value = monto_seleccionado;
                monto_seleccionado_format = new Intl.NumberFormat('en-US', {minimumFractionDigits: 2,maximumFractionDigits: 2}).format(monto_seleccionado);
                document.getElementById("seleccion").innerHTML = monto_seleccionado_format;
            }
        }

        function pagarSeleccion(){
            var monto_seleccionado = document.getElementById("monto_seleccionado").value;

            if (monto_seleccionado == 0) alert('Debe elegir al menos una factura para usar esta opción');
            else {
                let seleccionados = document.querySelectorAll('input[name="slc[]"]:checked');
                var variables = "?";
                var contador = 0;

                seleccionados.forEach(cb => {
                    if (contador == 0) variables = variables + 'fid' + contador + '=' + cb.value + '&monto' + contador + '=' + document.getElementById("monto"+cb.value).value;
                    else variables = variables + '&fid' + contador + '=' + cb.value + '&monto' + contador + '=' + document.getElementById("monto"+cb.value).value;

                    contador ++;
                });

                variables = variables + '&contador=' + contador;

                $('.modal-title').text('PAGO DE VARIAS FACTURA');
                $('.modal-body').load('boton_pago_factura_op.php'+variables,function(){
                    $('#myModal').modal({show:true});
                });
            }
        }

        function refresh_page_form(){
            let form = document.getElementById('frmFiltros');

            form.action = 'pendientes_pago.php';
            form.submit();
        }

    </script>

</BODY>
</HTML>