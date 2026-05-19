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

//==== CALCULO LOS FILTROS
$filtros = 'inversionista.estado in (64)';

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS

$rowcount = $vobj_mae->get_inversores_v2('COUNT', 0, 0, $filtros,'');
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'inversionistas/inversores_finanzas.php';
    //$pagina = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:300px;margin:auto;">
        Relaci&oacute;n de Inversores
    </div>

    <!--================================================== 
    ========================== FILTROS -->
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="inversores_finanzas.php">
            <ul>
                <li><span class="icon-filter"></span> Nombre:</li>
            </ul>
            <ul>
                <li><input type="text" name="filtro_nombre" id="filtro_nombre" style="width:300px;" class="formulario_control"></li>
                <li>
                    <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-right: 10px;" onclick="filtrar()"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-rojo);border:none;margin-right: 10px;" onclick="filtraPendientes()"><i class="fa-solid fa-triangle-exclamation" style="color:var(--color-amarillo);"></i> Con Pendientes</button>
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
                        <th scope="col" class="sort asc">ID</th>            <th scope="col" class="sort asc">NOMBRE</th>
                        <th scope="col" class="sort asc">DOCUMENTO</th>     <th scope="col" class="sort asc">EMAIL</th>
                        <th scope="col" class="sort asc">TELEFONO</th>      <th scope="col" class="sort asc">ESTADO</th>
                        <th scope="col" class="sort asc">TIPO</th>          <th scope="col" class="sort asc">CATEGORIA</th>
                        <th scope="col" class="sort asc">CTA BANCO</th>     <th scope="col" class="sort asc">DETALLE</th>
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
            
            fetch("inversores_finanzas_load.php", {
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
        function filtrar(){
            document.frm.submit();
        }

        function verDetalle(p_inversor, p_tipo_inversor){
            //if (p_tipo_inversor == 85){
            //persona natural
                $('.modal-title').text('DETALLE INVERSOR');
                $('.modal-body').load('inversor_upd_modal.php?inversor_id='+p_inversor+'&tipo_inv='+p_tipo_inversor,function(){
                    $('#InversorDetalle').modal({show:true});
                });
            //} else {
                //persona juridica
            //}
        }
    </script>
</BODY>
</HTML>