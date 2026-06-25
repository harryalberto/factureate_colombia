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
    $acceso = 'FACTURAS';
    require("../lib/valida-acceso.php");
?>
    <script src="https://code.jquery.com/jquery-3.2.1.js"></script>

    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;
$objfactura = new factura;

$arrestados = $objmaestro->get_estados('FACTURA');
$varr_estadofin = $objmaestro->get_estados('FACTFIN');

date_default_timezone_set($_SESSION['user']['zona_horaria']);

//seleccion de los filtros y valores iniciales
if ($_SESSION['user']['tipousuario'] == 2 || $_SESSION['user']['tipousuario'] == 5){ //usuario global o pertenece a factureate
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 12;
    $estado_id_filtro = 12;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
} elseif ($_SESSION['user']['tipousuario'] == 3){   // emisores
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 0;
    $estado_id_filtro = 0;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
}

if (isset($_POST['estadoid'])){
    $estado_id_filtro = $_POST['estadoid'];
    $estadoid = 21;
}

$v_tipousuario = $_SESSION['user']['tipousuario'];
$v_empresaid = $_SESSION['user']['empresaid'];

//DATOS PARA EL DETALLE
if ($estado_id_filtro == 0) $filtros = '';
else {
    if ($estado_id_filtro > 100) $filtros = 'factura.estadofinanciamiento = '.$estado_id_filtro;
    else $filtros = 'factura.estado = '.$estado_id_filtro;
}

$rowcount = $objfactura->get_facturas_activas_xemisor('COUNT', 0, 0, $filtros,'', $_SESSION['user']['empresaid']);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'emisores/facturas_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Facturas
    </div>

    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="facturas_emisor.php">
        <ul>
<?php
    if ($filtroestado == 'on'){
        echo '
            <li>Estado Factura:</li>
            <li><select name="estadoid" class="formulario_control" id="estadoid">';
                
        if ($estadoid == 0) echo '
                    <option value="0" selected>---- Todos ----</option>';
        else echo '
                    <option value="0">---- Todos ----</option>';
                
        for ($i=0; $i<count($arrestados); $i++){
            if ($arrestados[$i]['id'] == 21){
                // FACTURAS APROBADAS, INCLUYO LOS ESTADOS DE FINANCIAMIENTO
                for ($j=0; $j<count($varr_estadofin); $j++){
                    if ($varr_estadofin[$j]['id'] != 36){   // EXCLUYO LAS PAGADAS
                        $v_id_option = 100 + $varr_estadofin[$j]['id'];

                        if ($estado_id_filtro == $v_id_option)
                            echo '
                    <option value="'.$v_id_option.'" selected>'.$varr_estadofin[$j]['nombre'].'</option>';
                        else
                            echo '
                    <option value="'.$v_id_option.'">'.$varr_estadofin[$j]['nombre'].'</option>';
                    }
                }
            } else {
                if ($estado_id_filtro == $arrestados[$i]['id'])
                    echo '
                    <option value="'.$arrestados[$i]['id'].'" selected>'.$arrestados[$i]['nombre'].'</option>';
                else
                    echo '
                    <option value="'.$arrestados[$i]['id'].'">'.$arrestados[$i]['nombre'].'</option>';
            }
        }

        echo '
                </select>
            </li>';
    }
?>
            <li><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="filtrar()">
                    <i class="fa-solid fa-filter"></i> Filtrar</button></li>
        </ul>
        </form>
    </div>

    <!-- ########## DETALLE DE LAS FACTURAS ##############-->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">OPERACION ID</th>      <th scope="col" class="sort asc">CLIENTE</th>
                        <th scope="col" class="sort asc">DOCUMENTO</th>         <th scope="col" class="sort asc">FACTURA</th>
                        <th scope="col" class="sort asc">MONTO</th>             <th scope="col" class="sort asc">MONEDA</th>
                        <th scope="col" class="sort asc"><abbr title="Fecha de vencimiento">F VTO</abbr></th>
                        <th scope="col" class="sort asc">TIPO</th>              <th scope="col" class="sort asc">ESTADO</th>
                        <th scope="col" class="sort asc">DETALLE</th>           <th scope="col" class="sort asc">ACCION</th>
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
            <input type="hidden" id="num_registros" value="1000">
            <input type="hidden" id="rowcount" value="<?=$rowcount?>">
            <input type="hidden" id="filtros" value="<?=$filtros?>">
        </div>
    </div>
    <!------ END CUERPO VARIABLE ------>

    <!--#####################################################
    ########### ZONA MODAL
    #########################################################-->
    <div class="modal fade" id="FINANVIEW" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido-->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">FINANCIAMIENTO</h5></li>
                        <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
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
    <!--######################################################-->

    <!--======================= FUNCIONES DEL LISTADO =======================-->
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

            fetch("panel_emisor_load.php", {
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
        function verDetalle(p_factura_id, p_estado_id, p_estadofin_id){
            var url;

            if (p_estado_id == 11) url = 'registro_factura_fisica_modal.php?tipo=upd&id='+p_factura_id;         /*REGISTRADA*/
            else {
                if (p_estado_id == 12) url = 'registro_factura_fisica_modal.php?tipo=view&id='+p_factura_id;    /*EN SOLICITUD*/
                else {
                    if (p_estado_id == 21) url = 'factura_view_modal.php?id='+p_factura_id;                     /*REVISADA*/
                    else alert('Estado de la factura desconocido');
                }
            }

            $('.modal-title').text('Factura ID '+p_factura_id);
            $('.modal-body').load(url,function(){
                $('#FINANVIEW').modal({show:true});
            });
        }

        function solicitaFinan(p_factura_id){
            var formaData = new FormData();

            formaData.append('factura_id', p_factura_id);
            formaData.append('accion', 'envia_express');

            $.ajax({
                url:"registro_factura_fisica_proc.php",
                type:'post',
                data: formaData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                if (rpta == 1){
                    alert('Su factura ha sido enviada para la evaluación de nuestros analistas, inmediatamente supere la evaluación le enviaremos un correo de confirmación');
                    refresh_page();
                } else {
                    if (rpta == -1){
                        alert('Ocurrio un error al enviar la Factura, su factura fue rechazada');
                        refresh_page();
                    } else{
                        if (rpta == -2){
                            alert('Faltan datos, ingrese al detalle de la factura para completar la informacion');
                            refresh_page();
                        }
                    }
                }
            });
        }

        function refresh_page(){
            location.href = "facturas_emisor.php";
        }
    </script>
</BODY>
</HTML>