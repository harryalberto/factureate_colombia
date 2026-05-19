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
<?php
    require("../lib/head.php");
    $acceso = 'EMPRESAS';
    require("../lib/valida-acceso.php");
?>
    
</HEAD>

<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@@ LOGICA
$objmaestro = new maestros;

//==== CALCULO LOS FILTROS
$arrestados = $objmaestro->get_estados('EMPRESA');

if (!isset($_POST['estadoid'])) $v_estado_id = 2;
else $v_estado_id = $_POST['estadoid'];

if (!isset($_POST['t_empresa_id'])) $v_tempresa_id = 0;
else $v_tempresa_id = $_POST['t_empresa_id'];

//==== CALCULO LOS FILTROS
$filtros = 'empresa.estado = '.$v_estado_id;

if ($v_tempresa_id == 46) $filtros .= ' and empresa.t_empresa in (46, 49, 50, 52)';
elseif ($v_tempresa_id == 47) $filtros .= ' and empresa.t_empresa in (47, 49, 51, 52)';
elseif ($v_tempresa_id == 48) $filtros .= ' and empresa.t_empresa in (48, 50, 51, 52)';
elseif ($v_tempresa_id == 0) $filtros .= '';
else $filtros .= ' and empresa.t_empresa = '.$v_tempresa_id;

//==== CALCULO LA CANTIDAD DE REGISTROS CONSIDERANDO FILTROS
$rowcount = $objmaestro->get_empresas('COUNT', 0, 0, $filtros, '');

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'empresas/empresas.php';
    $pagina = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Empresas
    </div>

    <!--================================================== 
    ========================== FILTROS -->
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="empresas.php">
        <ul>
            <li><span class="icon-filter"></span> Filtros:</li>
        </ul>
        <ul>
            <li>
                <select name='estadoid' class="formulario_control">
<?php
            for ($i=0; $i<count($arrestados); $i++){
                if ($v_estado_id == $arrestados[$i]['id']) 
                    echo '
                    <option value="'.$arrestados[$i]['id'].'" selected>'.$arrestados[$i]['nombre'].'</option>';
                else 
                    echo '
                    <option value="'.$arrestados[$i]['id'].'">'.$arrestados[$i]['nombre'].'</option>';
            }
?>
                </select>
            </li> 

            <li>
                <select name="t_empresa_id" id="t_empresa_id" class="formulario_control">
<?php
    if ($v_tempresa_id == 0)
        echo '      <option value="0" selected>Todos los tipos</option>';
    else echo '     <option value="0">Todos los tipos</option>';

    if ($v_tempresa_id == 46)
        echo '      <option value="46" selected>Emisor</option>';
    else echo '     <option value="46">Emisor</option>';

    if ($v_tempresa_id == 48)
        echo '      <option value="48" selected>Obligado al pago</option>';
    else echo '     <option value="48">Obligado al pago</option>';
?>
                    
                </select>
            </li>

            <li><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="filtrar()"><i class="fa-solid fa-filter"></i> Filtrar</button></li>
            <li><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;" onclick="nuevoOP(48)"><i class="fa-solid fa-users"></i> Nuevo Obligado al Pago</button></li>
            <li><button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;" onclick="nuevoEmisor()"><i class="fa-regular fa-building"></i> Nuevo Emisor</button></li>
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
                        <th scope="col" class="sort asc">TELEFONO</th>   <th scope="col" class="sort asc">ESTADO</th>
                        <th scope="col" class="sort asc">TIPO</th>       <th scope="col" class="sort asc">CATEGORIA</th>    <th scope="col" class="sort asc">CONTRATO</th>
                        <th scope="col" class="sort asc">DETALLE</th>    
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

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA SCRIPT -->
    <script type="text/javascript">
        // Llamando a la función getData() al cargar la página
        document.addEventListener("DOMContentLoaded", getData);

        // Función para obtener datos con AJAX
        function getData() {
            let num_registros = document.getElementById("num_registros").value
            let content = document.getElementById("content")
            let pagina = document.getElementById("pagina").value || 1;
            let orderCol = document.getElementById("orderCol").value
            let orderType = document.getElementById("orderType").value
            let rowcount = document.getElementById("rowcount").value
            let filtros = document.getElementById("filtros").value

            let formaData = new FormData()
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            
            fetch("empresas_load.php", {
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
        function nuevoOP(p_tipo){
            location.href = "nueva_empresa.php?tipo="+p_tipo;
        }

        function verDetalle(p_id){
            location.href = "empresa_gestion_detalle.php?id="+p_id+"&previo=empresas";
        }

        function nuevoEmisor(){
            location.href = 'registra_emisor_manual.php';
        }

        function filtrar(){
            document.frm.submit();
        }
    </script>
</BODY>
</HTML>