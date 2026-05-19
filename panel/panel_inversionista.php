<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
require("../lib-trans/c_subasta.php");
require("../lib-trans/c_inversiones.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PANELINV';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
        function refresh_page(){
            location.href = 'panel_inversionista.php';
        }
    </script>
</HEAD>
<?
/*#####################################################
#################### LOGICA */
$objmaestro = new maestros;
$obj_inversiones = new inversiones;
$vobj_subastas = new subasta;

$triesgoid = 0;
$seconomicoid = 0;
$origen = 'panel';

//==== EVALUA EL ESTADO DEL INVERSOR SI AUN NO ESTA AUTORIZADO
if ($_SESSION['user']['empresaid'] > 0) $v_inversor_id = $_SESSION['user']['empresaid'];
elseif ($_SESSION['user']['empresaid'] < 0) $v_inversor_id = $_SESSION['user']['empresaid'] * -1;
else $v_inversor_id = $_SESSION['user']['usuarioid'];

$varr_inversor = $objmaestro->get_datos_inversor($v_inversor_id);

if ($varr_inversor['estado_id'] == 58)
    echo '<script>
            location.href = "panel_inversor_pre.php";
        </script>';

//==== obtencion de los indicadores del inversor
$v_q_propuestas = $obj_inversiones->get_count_inversiones($_SESSION['user']['usuarioid'], 'en_propuesta', $_SESSION['user']['empresaid']);
$v_arr_propuestas = $obj_inversiones->get_montos_inversion($_SESSION['user']['usuarioid'], 'en_propuesta', $_SESSION['user']['empresaid']);
$v_q_encomp = $obj_inversiones->get_count_inversiones($_SESSION['user']['usuarioid'], 'no_liquidada', $_SESSION['user']['empresaid']);
$v_arr_encomp = $obj_inversiones->get_montos_inversion($_SESSION['user']['usuarioid'], 'no_liquidada', $_SESSION['user']['empresaid']);
$v_q_inv = $obj_inversiones->get_count_inversiones($_SESSION['user']['usuarioid'], 'liquidada', $_SESSION['user']['empresaid']);
$v_arr_inv = $obj_inversiones->get_montos_inversion($_SESSION['user']['usuarioid'], 'liquidada', $_SESSION['user']['empresaid']);
$v_arr_pagada = $obj_inversiones->get_montos_inversion($_SESSION['user']['usuarioid'], 'pagada', $_SESSION['user']['empresaid']);
$v_arr_xpagar = $obj_inversiones->get_montos_inversion($_SESSION['user']['usuarioid'], 'xpagar', $_SESSION['user']['empresaid']);

//==== verifico si envian parametros de acceso express
if (isset($_GET['fid'])) {
    $v_fid = $_GET['fid'];

    if ($_SESSION['user']['empresaid'] > 0) $v_tipo_inv = 86;
    else $v_tipo_inv = 85;

    $varr_subasta = $vobj_subastas->get_subasta_xfactura_inversor($v_fid, $v_inversor_id, $v_tipo_inv);
} else $v_fid = 0;

//==== CALCULO LOS FILTROS
$filtros = '';

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS
$rowcount = $vobj_subastas->get_subastas_inversor('COUNT', 0, 0, $filtros, '', $v_inversor_id);

//############# FIN DE LOGICA
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'panel/panel_inversionista.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--###### BODY PRINCIPAL -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Panel de Inversionista
    </div>

    <!-- AQUI SE DEBE PINTAR LOS INDICADORES PARA EL INVERSIONISTA COMO LAS FACTURAS DISPONIBLES, DONDE INVIRTIO, DONDE ESTA EN COMPENSACION -->
    <!--==== container de los indicadores -->
    <div class="frmtransaccion">

        <!-- row de los indicadores del inversor -->
        <ul style="overflow:hidden;list-style:none;">

            <!---- indicador EN PROPUESTA -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro)">
                    <abbr title="Propuestas que ha realizado, pero aún no son una inversión hasta que se termine la subasta y usted resulte elegido para la inversión">
                    <span class="icon-eye" style="color:#000000;font-size: 14px;"></span> En Propuesta
                    </abbr></p>
                <?
                if ($v_q_propuestas > 0){
                    $v_label_propuestas = '';

                    for ($i=0; $i<count($v_arr_propuestas); $i++){
                        $v_label_propuestas .= $v_arr_propuestas[$i]['simbolo'].' '.number_format($v_arr_propuestas[$i]['monto'],2,'.',',').'<br>';
                    }

                    if (count($v_arr_propuestas) == 1) $v_label_propuestas .= '-';
                }
                ?>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight: bold;">
                    <? echo $v_label_propuestas;?>
                </p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro)">
                    <? echo '<b>'.$v_q_propuestas.'</b> propuesta(s) activa(s)';?>
                </p>
            </li>
            
            <!---- indicador EN COMPENSACION -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro);">
                    <abbr title="Usted fue elegido para la inversión pero aún se está dejando todo a punto para ejecutar la inversión">
                    <span class="icon-eye" style="color:#000000;font-size: 14px;"></span> En Compensaci&oacute;n
                    </abbr></p>
                <?
                if ($v_q_encomp > 0){
                    $v_label_encomp = '';

                    for ($i=0; $i<count($v_arr_encomp); $i++){
                        $v_label_encomp .= $v_arr_encomp[$i]['simbolo'].' '.number_format($v_arr_encomp[$i]['monto'],2,'.',',').'<br>';
                    }

                    if (count($v_arr_encomp) == 1) $v_label_encomp .= '-';
                } else $v_q_encomp = 0;
                ?>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight: bold;">
                    <? echo $v_label_encomp;?>
                </p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro);">
                    <? echo '<b>'.$v_q_encomp.'</b> propuesta(s) en compensaci&oacute;n';?>
                </p>
            </li>
            
            <!---- indicador EN INVERSION -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro);">
                    <abbr title="Es la relación de instrumentos en los que usted ha invertido y se encuentran vivos hasta el momento del vencimiento de su inversión">
                    <span class="icon-eye" style="color:#000000;font-size: 14px;"></span> En Inversi&oacute;n
                    </abbr></p>
                <?
                if ($v_q_inv > 0){
                    $v_label_inv = '';

                    for ($i=0; $i<count($v_arr_inv); $i++){
                        $v_label_inv .= $v_arr_inv[$i]['simbolo'].' '.number_format($v_arr_inv[$i]['monto'],2,'.',',').'<br>';
                    }

                    if (count($v_arr_inv) == 1) $v_label_inv .= '-';
                } else $v_q_inv = 0;
                ?>
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight:bold;">
                    <? echo $v_label_inv;?>
                </p>
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro);">
                    <? echo '<b>'.$v_q_inv.'</b> inversiones activas';?>
                </p>
            </li>
            
            <!---- indicador GANANCIAS -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro);">
                    <abbr title="Son las ganancias que hasta el momento ha conseguido con FACTUREATE">
                    <span class="icon-eye" style="color:#000000;font-size: 14px;"></span> Ganancias
                    </abbr></p>
                <?
                $v_ganancias = 0;
                $v_label_ganancias = '';

                for ($i=0; $i<count($v_arr_pagada); $i++){
                    $v_label_ganancias .= $v_arr_pagada[$i]['simbolo'].' '.number_format($v_arr_pagada[$i]['monto'],2,'.',',').'<br>';
                    $v_ganancias = $v_ganancias + $v_arr_pagada[$i]['monto'];
                }

                if (count($v_arr_pagada) == 1) $v_label_ganancias .= '-';
                if ($v_ganancias > 0) $v_label_gana = '';
                else $v_label_gana = 'Usted aun no cuenta con ganancias';

                if (count($v_arr_pagada) > 0)
                    echo '
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight:bold;">'.$v_label_gana.$v_label_ganancias.'</p>';
                else 
                    echo '
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro);">'.$v_label_gana.$v_label_ganancias.'</p>';
                ?>
                
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro);">
                    <? echo '<b>Ganancias conseguidas</b>';?>
                </p>
            </li>
            
            <!---- indicador GANANCIAS FORWARD -->
            <li style="display:block;margin:2px;width:200px;float:left;padding:10px;">
                <p style="text-align:left;color:var(--color-gris-oscuro);">
                    <abbr title="Son las ganancias que usted percibirá cuando sus inversiones terminen o sean liquidadas">
                    <span class="icon-eye" style="color:#000000;font-size: 14px;"></span> Ganancias Futuras
                    </abbr></p>
                <?
                $v_futuros = 0;
                $v_label_futuros = '';

                for ($i=0; $i<count($v_arr_xpagar); $i++){
                    $v_label_futuros .= $v_arr_xpagar[$i]['simbolo'].' '.number_format($v_arr_xpagar[$i]['monto'],2,'.',',').'<br>';
                    $v_futuros = $v_futuros + $v_arr_xpagar[$i]['monto'];
                }

                if (count($v_arr_xpagar) == 1) $v_label_futuros .= '-';
                if ($v_futuros > 0) $v_label_fw = '';
                else $v_label_fw = 'Usted aun no cuenta con inversiones';

                if (count($v_arr_xpagar) > 0)
                    echo '
                <p style="margin:10px 0px;text-align:left;font-size:16px;color:#000000;font-weight:bold;">'.$v_label_fw.$v_label_futuros.'</p>';
                else 
                    echo '
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro);">'.$v_label_fw.$v_label_futuros.'</p>';
                ?>
                
                <p style="margin:10px 0px;text-align:left;color:var(--color-gris-oscuro);">
                    <? echo '<b>Ganancias futuras por cobrar</b>';?>
                </p>
            </li>

        </ul> <!-- end row indicadores -->

    </div> <!-- end contenedor de los indicadores -->

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
                        <th scope="col" class="sort asc">ACCION</th>        
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
            <input type="hidden" id="fid" value="<?=$v_fid?>">
            <input type="hidden" id="subasta_id" value="<?=$varr_subasta['subasta_id']?>">
            <input type="hidden" id="propuesta_id" value="<?=$varr_subasta['propuesta_id']?>">
            <input type="hidden" id="estado_prop" value="<?=$varr_subasta['estado_propuesta']?>">
            <input type="hidden" id="estado_sub" value="<?=$varr_subasta['estado_subasta']?>">
        </div>
    </div>
    <!--###### END BODY PRINCIPAL -->

    <!--###### ZONA MODAL -->
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
    <!--###### END ZONA MODAL -->

    <!--==== Funciones del LOAD del listado y paginacion -->
    <script type="text/javascript">
        // Llamando a la función getData() al cargar la página
        document.addEventListener("DOMContentLoaded", getData);
        document.addEventListener("DOMContentLoaded", inversionExpress);

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

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            formaData.append('inversor_id', inversor_id)
            
            fetch("../subastas/subastas_inversor_load.php", {
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

        // funcion transaccional
        function inversionExpress(){
            let fid = document.getElementById("fid").value

            if (fid > 0){
                // si hubo llamada express
                let subasta_id = document.getElementById("subasta_id").value
                let estado_sub = document.getElementById("estado_sub").value

                if (estado_sub == 24){
                    let propuesta_id = document.getElementById("propuesta_id").value

                    $('.modal-title').text('PROPUESTA');
                    $('.modal-body').load('posicion_inversion_modal.php?fid='+fid+'&pid='+propuesta_id+'&subastaid='+subasta_id,function(){
                        $('#PropuestaDetalle').modal({show:true});
                    });
                } else alert('La factura ya no esta disponible para inversión!!');
            }
        }
    </script>

    <!--==== Funciones de transaccion -->
    <script>
        function verDetalle(p_factura_id, p_subasta_id, p_propuesta_id){
            $('.modal-title').text('PROPUESTA');
            $('.modal-body').load('posicion_inversion_modal.php?fid='+p_factura_id+'&pid='+p_propuesta_id+'&subastaid='+p_subasta_id,function(){
                $('#PropuestaDetalle').modal({show:true});
            });
        }
    </script>

</BODY>
</HTML>