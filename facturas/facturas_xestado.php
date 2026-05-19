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
    $acceso = 'FACTURAS';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<!--#####################################################
##################### PARA USO DEL ANALISTA DE FACTUREATE
#########################################################
#########################################################-->
<?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA

$objmaestro = new maestros;
$vobj_factura = new factura;

$arrestados = $objmaestro->get_estados('FACTURA');

if ($_SESSION['user']['tipousuario'] == 2 || $_SESSION['user']['tipousuario'] == 5){ //usuario global o pertenece a factureate
    $filtroestado = 'on';
    $estadoid = 12;
} elseif ($_SESSION['user']['tipousuario'] == 3){   // emisores
    $filtroestado = 'on';
    $estadoid = 0;
}

if (isset($_POST['estadoid'])) $estadoid = $_POST['estadoid'];
if (isset($_GET['estadoid'])) $estadoid = $_GET['estadoid'];

//==== CALCULO LOS FILTROS
$filtros = 'factura.estado ='.$estadoid;

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS

$rowcount = $vobj_factura->get_facturas('COUNT', 0, 0, $filtros,'');
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'facturas/facturas_xestado.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Instrumentos
    </div>

    <!--================================================== 
    ========================== FILTROS -->

    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="facturas_xestado.php">
        <ul>
            <?
            if ($filtroestado == 'on'){
                echo '<li>Estado Factura:</li>
                    <li><select name="estadoid" class="formulario_control">';
                                
                for ($i=0; $i<count($arrestados); $i++){
                    if ($arrestados[$i]['id'] == 13) $v_nombre = $arrestados[$i]['nombre'].' (Anotada / En Subasta / Financiamiento / Liquidada)';
                    else $v_nombre = $arrestados[$i]['nombre'];

                    if ($estadoid == $arrestados[$i]['id']) echo '<option value="'.$arrestados[$i]['id'].'" selected>'.$v_nombre.'</option>';
                    else echo '<option value="'.$arrestados[$i]['id'].'">'.$v_nombre.'</option>';
                }

                echo '  </select>
                    </li>';
            }
            ?>
            <button style="font-size:12px;background-color:var(--color-azulv2);" type="button" class="btn btn-primary" onclick="filtrar()"><span class="icon-filter" style="font-size:16px;"></span> Filtrar</button>
        </ul>
        </form>
    </div>
    
    <!--==== TABLA HEADER -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">OPERACION ID</th>  <th scope="col" class="sort asc">EMISOR</th>
                        <th scope="col" class="sort asc">PAGADOR</th>       <th scope="col" class="sort asc">FACTURA</th>
                        <th scope="col" class="sort asc">F EMISION</th>     <th scope="col" class="sort asc">F VTO</th>
                        <th scope="col" class="sort asc">MONEDA</th>        <th scope="col" class="sort asc">MONTO FACTURA</th>
                        <th scope="col" class="sort asc">ESTADO</th>        <th scope="col" class="sort asc">F ENVIO</th>
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
    <div class="modal fade" id="FacturaDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Factura Detalle</h5></li>
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

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            
            fetch("facturas_xestado_load.php", {
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
        $('.openBtn2').on('click',function(){
            var eid = $(this).attr('eid');
            var ret = $(this).attr('ret');
            var tipo = $(this).attr('tipo');
            var id = $(this).attr('id');
            
            location.href = 'factura_detalle.php?eid='+eid+'&ret='+ret+'&tipo='+tipo+'&id='+id+'';
        });
    </script>

    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }

        function verDetalle(p_factura_id){
            $('.modal-title').text('FACTURA DETALLE');
            $('.modal-body').load('factura_detalle_modal.php?facturaid='+p_factura_id,function(){
                $('#FacturaDetalle').modal({show:true});
            });
        }

        function refresh_page(){
            location.href = "facturas_xestado.php";
        }
    </script>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>