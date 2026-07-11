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
//==============================================
//=========== LOGICA
$vobj_factura = new factura;

$v_estados = '73';

if (isset($_POST['enviado'])){
    if ($v_estados != '') $v_estados .= ',72';
    else $v_estados = '72';
}

if (isset($_POST['re_enviado'])){
    if ($v_estados != '') $v_estados .= ',74';
    else $v_estados = '74';
}

//==== CALCULO LOS FILTROS
$filtros = 'endoso_notifica.estado_id in ('.$v_estados.')';

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS
$rowcount = $vobj_factura->get_noti_endosos('COUNT', 0, 0, $filtros,'');
//==============================================
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    $menu = 'facturas/notifica_endoso.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--==========================================
    =============== ZONA BODY -->

    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Notificaciones de Endoso
    </div>

    <!--==========================================
    ============== FILTROS -->
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="notifica_endoso.php">
        <ul>
            <li><i class="fa-solid fa-filter"></i> Estados de NOTIFICACION:</li>
            <li><input type="checkbox" class="formulario_control" name="no_enviado" value="73" checked disabled><label style="padding-left:5px;padding-right:5px;">No Enviado</label></li>

<?php
    if (isset($_POST['enviado'])) echo '
            <li><input type="checkbox" class="formulario_control" name="enviado" value="72" checked><label style="padding-left:5px;padding-right:5px;">Enviado</label></li>';
    else echo '
            <li><input type="checkbox" class="formulario_control" name="enviado" value="72"><label style="padding-left:5px;padding-right:5px;">Enviado</label></li>';

    if (isset($_POST['re_enviado'])) echo '
            <li><input type="checkbox" class="formulario_control" name="re_enviado" value="74" checked><label style="padding-left:5px;padding-right:5px;">RE Enviado</label></li>';
    else echo '
            <li><input type="checkbox" class="formulario_control" name="re_enviado" value="74"><label style="padding-left:5px;padding-right:5px;">RE Enviado</label></li>';
?>

            <li>
                <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-right: 10px;" onclick="filtrar()">
                    <i class="fa-solid fa-filter"></i> Filtrar</button>
            </li>
        </ul>
        </form>
    </div>
    <!--==========================================-->

    <!--==========================================
    =========== TABLA HEADER -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">OPERACION ID</th>          <th scope="col" class="sort asc">OBLIGADO AL PAGO</th>
                        <th scope="col" class="sort asc">FECHA</th>                 <th scope="col" class="sort asc">HORA</th>
                        <th scope="col" class="sort asc">ESTADO</th>
                        <th scope="col" class="sort asc">TIPO</th>                  <th scope="col" class="sort asc">EMAIL OP</th>
                        <th scope="col" class="sort asc">ACCION</th>                <th scope="col" class="sort asc">OTRAS</th>
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
    <!--==========================================-->

    <!--==========================================
    ============== ZONA MODAL
    ========================================== -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">                   <!---- se agrega "modal-lg modal-dialog-centered" si se quiere mas grande --->
        <!-- Modal contenido -->
            <div class="modal-content">
                <div class="modal-header">
                    <ul style="list-style:none;overflow:hidden;">
                        <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Subasta</h5></li>
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

    <!--==========================================-->

    <!--==========================================
    ============== ZONA SCRIPT
    ========================================== -->
    <script>
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

            fetch("notifica_endoso_load.php", {
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

        let columns = document.querySelectorAll(".sort");
        columns.forEach(column => {
            column.addEventListener("click", ordenar);
        });

        function filtrar(){
            document.frm.submit();
        }

        function enviar_notifica(p_factura_id, p_email){
            let formData = new FormData()

            formData.append('factura_id', p_factura_id)
            formData.append('accion', 'envia')
            formData.append('email', p_email)

            $.ajax({
                    url:"notifica_endoso_envia.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
            })
            .done(function(rpta){
                    if (rpta == -1) alert('El OP no cuenta con mail registrado');
                    if (rpta == 1) alert('La notificacion fue enviada');
            });
        }

        function registra_notifica(p_factura_id){
            $('.modal-title').text('REGISTRO NOTIFICACION');
            $('.modal-body').load('registra_notifica_modal.php?fid='+p_factura_id,function(){
                $('#myModal').modal({show:true});
            });
        }

        function verNotiOld(p_factura_id){
            alert('old');
        }

        function refresh_page(){
            location.href = 'notifica_endoso.php';
        }
    </script>
</BODY>
</HTML>