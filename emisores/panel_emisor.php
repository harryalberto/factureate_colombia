<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");

if ($_SESSION['user']['tipousuario'] == 3){     //emisor
    $obj_mae = new maestros;
    $arr_emisor = $obj_mae->get_datos_emisor($_SESSION['user']['empresaid']);

    if ($arr_emisor['estado'] == 1) $redir = "<meta http-equiv=refresh content='0;url=registra_emisor.php?estado=1'>";
    elseif ($arr_emisor['estado'] == 2) $redir = "<meta http-equiv=refresh content='0;url=registra_emisor.php?estado=2'>";
    else $redir = '';
}

?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'PANELEMI';
    require("../lib/valida-acceso.php");

    echo $redir;
?>

</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objfactura = new factura;
$vobj_mae = new maestros;

//$arrfacturas = $objfactura->get_facturas_xemisor($_SESSION['user']['empresaid']);
$varr_fe = $vobj_mae->get_parametro_detalle(54);
/*--------------------------------------------------------*/

//==== CALCULO LOS FILTROS
$filtros = '';

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS

$rowcount = $objfactura->get_facturas_activas_xemisor('COUNT', 0, 0, $filtros,'', $_SESSION['user']['empresaid']);
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'emisores/panel_emisor.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    
    <!--================== INDICADORES EMISOR
    ==================================================== -->
<?php
    $v_registradas = $objfactura->get_count_facturas_xestadoemisor($_SESSION['user']['empresaid'],11,0);
    $v_ensolicitud = $objfactura->get_count_facturas_xestadoemisor($_SESSION['user']['empresaid'],12,0);
    $v_subastadas = $objfactura->get_count_facturas_xestadoemisor($_SESSION['user']['empresaid'],-1,18);
    $v_financiadas = $objfactura->get_count_facturas_xestadoemisor($_SESSION['user']['empresaid'],-1,19) + $objfactura->get_count_facturas_xestadoemisor($_SESSION['user']['empresaid'],-1,15);
?>
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <ul style="overflow:hidden;list-style:none;font-size:11px;margin-left: 20px;">

            <!-- REGISTRADAS -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro)">
                    <abbr title="Facturas que se encuentran registradas, están listas para que solicite financiamiento"><span class="icon-eye" style="color:#000000;font-size: 14px;"></span> REGISTRADAS
                    </abbr></p>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight: bold;"><?php echo $v_registradas;?></p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro)">Factura(s)</p>
            </li>

            <!-- EN SOLICITUD DE FINANCIAMIENTO (cuendo es enviada) -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro)">
                    <abbr title="Facturas que se encuentran siendo analizadas por nuestro equipo"><span class="icon-eye" style="color:#000000;font-size: 14px;"></span> EN SOLICITUD
                    </abbr></p>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight: bold;"><?php echo $v_ensolicitud;?></p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro)">Factura(s)</p>
            </li>

            <!-- EN SUBASTA -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro)">
                    <abbr title="Facturas que se encuentran en subasta, en cualquien momento le conseguimos financiamiento"><span class="icon-eye" style="color:#000000;font-size: 14px;"></span> EN SUBASTA
                    </abbr></p>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight: bold;"><?php echo $v_subastadas;?></p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro)">Consiguiendo financiamiento</p>
            </li>

            <!-- FINANCIADAS -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro)">
                    <abbr title="Facturas que se entregó el adelanto, y si ya pagó su cliente ya recibió el remanente"><span class="icon-eye" style="color:#000000;font-size: 14px;"></span> FINANCIADAS
                    </abbr></p>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight: bold;"><?php echo $v_financiadas;?></p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro)">Obtuvieron financiamiento</p>
            </li>
        </ul>

        <!--================== OPCIONES ==================== -->
        <ul style="overflow:hidden;list-style:none;padding-left: 5px;margin-left: 20px;">
            <li style="display:block;float:left;margin-left: 10px;">
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="registrarFF()"><i class="fa-solid fa-file-pen"></i> Registrar factura manual</button>
            </li>
<?php
    if ($varr_fe['valornum'] > 0) echo '
            <li style="display:block;float:left;margin-left: 20px; font-size: 12px; background-color:var(--color-verde); padding: 5px; color:#fff;">
                <span class="icon-upload"></span> Cargar Factura electronica
            </li>

            <li style="display:block;float:left;margin-left: 10px;">
                <input type="hidden" name="tipo" value="enew">
                <input name="xmlfile" type="file" id="xmlfile" style="font-size:12px;color:#555555;width:200px; padding:5px;" onchange="javascript:subirxml()">
            </li>';
?>
        </ul>
    </div>

        <!--========== RELACION DE FACTURAS VIVAS ============-->
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
    <!--=====================================================================-->

    <!-- llamada al modal -->
    <script>
        $('.openBtn2').on('click',function(){
            var factura_id = $(this).attr('factura_id');
            var monto = $(this).attr('monto');
            var titulo = $(this).attr('titulo');
            
            if (titulo == 'SUBASTA') $('.modal-title').text('SUBASTA');
            if (titulo == 'FINAN') $('.modal-title').text('FINANCIAMIENTO');

            $('.modal-body').load('financiamiento_view_modal.php?factura_id='+factura_id+'&monto='+monto,function(){
                $('#FINANVIEW').modal({show:true});
            });
        });

        /*function verDetalle(p_tipo, p_factura_id){
            location.href = 'registro_factura.php?tipo='+p_tipo+'&id='+p_factura_id;
        }*/

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

        function registrarFF(){
            /*location.href = "../emisores/registro_factura.php?tipo=new";*/
            $('.modal-title').text('Nueva factura fisica');
            $('.modal-body').load('registro_factura_fisica_modal.php?tipo=new',function(){
                $('#FINANVIEW').modal({show:true});
            });
        }

        function refresh_page(){
            location.href = "panel_emisor.php";
        }

        function subirxml(){
            let archivo = document.getElementById("xmlfile").files[0];
            var procede = 1;

            if(!archivo){
                alert('Debe seleccionar un archivo XML');
                procede = 0;
            }

            // validando la extension
            const nombre = archivo.name.toLowerCase();

            if(!nombre.endsWith('.xml') && !nombre.endsWith('.xsd')){
                alert('Debe seleccionar un archivo XML');
                e.target.value = '';
                procede = 0;
            }

            if (procede == 1){
                const formData = new FormData();

                formData.append('xml', archivo);

                $.ajax({
                    url: 'leer_factura_electronica.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function(rpta){ 
                    if (rpta <= 0){
                        if (rpta == 0) alert('El contenido del archivo no es valido, verifique por favor');
                        if (rpta == -1) alert('Sus datos como Emisor no coinciden con los de la factura');
                        if (rpta == -2) alert('Los datos de su cliente no coincide con los datos que tenemos en nuestra base de datos, comuniquese con operaciones por favor');
                        if (rpta == -3) alert('La factura que intenta cargar no cumple con los minimos de montos minimos de Factureate');
                        if (rpta == -4) alert('El tipo de documento electronico que intenta cargar no es admitido por Factureate');
                        if (rpta == -5) alert('El indicador de produccion no es el que recomienda la DIAN');
                        if (rpta == -6) alert('La moneda de la factura no es admitida');
                        if (rpta == -7) alert('El cliente de la factura debe ser una empresa');
                    } else {
                        $('.modal-title').text('Nueva factura electrónica');
                        $('.modal-body').load('registro_factura_electronica_modal.php?idtemp='+rpta,function(){
                            $('#FINANVIEW').modal({show:true});
                        });
                    }
                });
            }
        }

        function cambia_modal_factura(factura_id){
            // CIERRA MODEL DE FELECTRONICA
            $('#FINANVIEW').removeClass('in');
            $('#FINANVIEW').hide();
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();

            //APERTURA DEL MODAL CON LA FACTURA YA GUARDADA
            verDetalle(factura_id, 11, 0);
        }

    </script>
    <!---=============== end modal ==============--->
</BODY>
</HTML>