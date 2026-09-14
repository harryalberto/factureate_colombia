<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_subasta.php");
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'SUBASTAS';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;
$vobj_subastas = new subasta;

$arrestados = $objmaestro->get_estados('SUBASTA');
$seconomicoid = 0;
$triesgoid = 0;
//seleccion de los filtros y valores iniciales
if ($_SESSION['user']['tipousuario'] == 2 || $_SESSION['user']['tipousuario'] == 5){ //usuario global o pertenece a factureate
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 31;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
}
if (isset($_POST['seconomicoid'])){
    $seconomicoid = $_POST['seconomicoid'];
    $triesgoid = $_POST['triesgoid'];
} else {
    $seconomicoid = 0;
    $triesgoid = 0;
}

//==== ID DEL INVERSOR
if ($_SESSION['user']['empresaid'] > 0) $v_inversor_id = $_SESSION['user']['empresaid'];
elseif ($_SESSION['user']['empresaid'] < 0) $v_inversor_id = $_SESSION['user']['empresaid'] * -1;
else $v_inversor_id = $_SESSION['user']['usuarioid'];

//==== CALCULO LOS FILTROS
$filtros = '';

if ($seconomicoid > 0) $filtros .= ' empresa.sectoreconomicoid = '.$seconomicoid;
if ($triesgoid > 0){
    if ($filtros != '') $filtros .= ' and factura.riesgofacturaid = '.$triesgoid;
    else $filtros .= 'factura.riesgofacturaid = '.$triesgoid;
}

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS
$rowcount = $vobj_subastas->get_subastas_inversor('COUNT', 0, 0, $filtros, '', $v_inversor_id);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'subastas/subastas_disponibles_inver.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Subastas Disponibles para inversi&oacute;n
    </div>

    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="subastas_disponibles_inver.php">
        <ul>
            <li>Sector econ&oacute;mico:</li>
            <li>
                <select name="seconomicoid" class="frminput_text">
                <?php
                    $arrsectores = $objmaestro->get_tipos('SECTORECO');

                    if ($seconomicoid == 0) echo '<option value = "0" selected>Todos los sectores</option>';
                    else echo '<option value = "0">Todos los sectores</option>';

                    for ($i=0; $i<count($arrsectores); $i++){
                        if ($seconomicoid == $arrsectores[$i]['id']) 
                            echo '<option value = "'.$arrsectores[$i]['id'].'" selected>'.$arrsectores[$i]['nombre'].'</option>';
                        else echo '<option value = "'.$arrsectores[$i]['id'].'">'.$arrsectores[$i]['nombre'].'</option>';
                    }
                ?>
                </select>
            </li>
            <li>Riesgo Pagador:</li>
            <li>
                <select name="triesgoid" class="frminput_text">
                <?php
                    $arrtriesgo = $objmaestro->get_triesgopagador();

                    if ($triesgoid == 0) echo '<option value = "0" selected>Todos los tipos</option>';
                    else echo '<option value = "0">Todos los tipos</option>';

                    for ($i=0; $i<count($arrtriesgo); $i++){
                        if ($triesgoid == $arrtriesgo[$i]['id'])
                            echo '<option value = "'.$arrtriesgo[$i]['id'].'" selected>'.$arrtriesgo[$i]['calificacion'].' - '.$arrtriesgo[$i]['nombre'].'</option>';
                        else echo '<option value = "'.$arrtriesgo[$i]['id'].'">'.$arrtriesgo[$i]['calificacion'].' - '.$arrtriesgo[$i]['nombre'].'</option>';
                    }
                ?>
                </select>
            </li>
            <!--<li class="botontransaccion" style="margin-top:5px;"><a href="javascript:filtrar()"><span class="icon-filter"></span> Filtrar</a></li>-->
            <button type="button" class="btn btn-primary" style="font-size:12px;background-color:var(--color-azulv2);border:none;" onclick=filtrar()>
                <span class="icon-filter" style="font-size:16px;"></span> Filtrar
            </button>
        </ul>
        </form>
    </div>

    <!--==== contenedor del listado de oportunidades -->
    <div style="overflow:hidden;margin:5px;padding:5px;">

        <!--==== DIV HEADER -->
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID</th>            <th scope="col" class="sort asc">PAGADOR</th>
                        <th scope="col" class="sort asc">MONTO FACTURA</th> <th scope="col" class="sort asc">FINANCIAMIENTO</th>
                        <th scope="col" class="sort asc">MONEDA</th>        <th scope="col" class="sort asc">DIAS X COBRAR</th>
                        <th scope="col" class="sort asc">F VENCIMIENTO</th> <th scope="col" class="sort asc">RIESGO</th>
                        <th scope="col" class="sort asc">TIPO</th>          <th scope="col" class="sort asc">ACCION</th>
                    </tr>
                </thead>
                <tbody id="content">

                </tbody>
            </table>
        </div>

        <!--==== DIV PAGINACION -->
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

            <!-- datos de negocio -->
            <input type="hidden" id="inversor_id" value="<?=$v_inversor_id?>">
            <input type="hidden" id="sector_id" value="<?=$seconomicoid?>">
            <input type="hidden" id="riesgo_id" value="<?=$triesgoid?>">
        </div>
    </div>

    <!------ END CUERPO VARIABLE ------>
    <!--#####################################################
    ########### ZONA MODAL 
    #########################################################-->
    <div class="modal fade" id="PropuestaDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Propuesta</h5></li>
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
    <!--############# FIN ZONA MODAL ##############-->

    <!--==== Funciones del LOAD del listado y paginacion -->
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
            let inversor_id = document.getElementById("inversor_id").value
            let sector_id = document.getElementById("sector_id").value
            let riesgo_id = document.getElementById("riesgo_id").value

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            formaData.append('inversor_id', inversor_id)
            formaData.append('sector_id', sector_id)
            formaData.append('riesgo_id', riesgo_id)


            fetch("subastas_inversor_load.php", {
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

        // Funciones de transaccion
        function verDetalle(p_factura_id, p_subasta_id, p_propuesta_id){
            $('.modal-title').text('PROPUESTA');
            $('.modal-body').load('propuesta_detalle_modal.php?fid='+p_factura_id+'&pid='+p_propuesta_id+'&subastaid='+p_subasta_id,function(){
                $('#PropuestaDetalle').modal({show:true});
            });
        }

        function filtrar(){
            document.frm.submit();
        }

        function refresh_page(){
            document.frm.submit();
        }
    </script>

</BODY>
</HTML>