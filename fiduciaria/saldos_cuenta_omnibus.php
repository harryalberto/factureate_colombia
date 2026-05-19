<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");
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

$varr_monedas = $vobj_mae->get_tipos('MONEDA');
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'fiduciaria/cuenta_omnibus.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA BODY -->

    <!--==================================================
    ========================== TITLE -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:500px;margin:auto;">
        Saldos Cuenta Omnibus
    </div>

    <!--================================================== 
    ========================== BOTONES DE SECCIONES -->
    <nav>
        <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-right: 10px;margin-left: 10px;" onclick="cuentaOmnibus()"><i class="fa-solid fa-vault"></i> Cuenta Omnibus</button>
        <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;margin-right: 10px;"><i class="fa-solid fa-money-check-dollar"></i> Saldos Cuenta</button>
    </nav>
    
    <!--==================================================
    ========================== CONTENIDO -->

    <!--==== FILTROS Y HEADER -->
    <div class="contenedor_formulario">
        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_row" style="width: 100px;">
                <label for="moneda">MONEDA</label>
                <select name="moneda" id="moneda" class="formulario_control" onchange="cambiaMoneda()">
<?php
    for ($i = 0; $i < count($varr_monedas); $i++){
        if ($i == 0) echo '
                    <option value="'.$varr_monedas[$i]['id'].'" selected>'.$varr_monedas[$i]['dato1'].'</option>';
        else echo ' <option value="'.$varr_monedas[$i]['id'].'">'.$varr_monedas[$i]['dato1'].'</option>';
    }
?>                  
                </select>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label for="s_contable"><abbr title="Efectivo en la cuenta">SALDO CONTABLE</abbr></label>
                <input type="text" name="s_contable" id="s_contable" class="formulario_control" style="text-align:right;" readonly>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label for="s_inversor"><abbr title="Saldo que le pertenece al conjunto de inversores">SALDO INVERSOR</abbr></label>
                <input type="text" name="s_inversor" id="s_inversor" class="formulario_control" style="text-align:right;" readonly>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label for="s_disponible"><abbr title="Ganancias de Factureate del cual puede disponer">SALDO DISPONIBLE</abbr></label>
                <input type="text" name="s_disponible" id="s_disponible" class="formulario_control" style="text-align:right;" readonly>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label for="s_vendedor"><abbr title="Efectivo pendiente de transferirle a los vendedores por adelanto o remanente, debe ser cero">SALDO VENDEDOR</abbr></label>
                <input type="text" name="s_vendedor" id="s_vendedor" class="formulario_control" style="text-align:right;" readonly>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label for="s_transito"><abbr title="Efectivo que ingreso a la cuenta y no esta identificado, no puede durar mas de 24 la validacion">SALDO TRANSITO</abbr></label>
                <input type="text" name="s_transito" id="s_transito" class="formulario_control" style="text-align:right;" readonly>
            </div>
        </div>
    </div>

    <!--==== TABLA CONTENIDO -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID CUENTA</th>         <th scope="col" class="sort asc">NOMBRE</th>
                        <th scope="col" class="sort asc">APELLIDO</th>          <th scope="col" class="sort asc">SALDO CONTABLE</th>
                        <th scope="col" class="sort asc"><abbr title="Saldo que se encuentra comprometido en el proceso de compra de una factura">SALDO COMPROMETIDO</abbr></th>    
                        <th scope="col" class="sort asc"><abbr title="Saldo que dispone el inversor de poder retirarlo o reinvertirlo">SALDO DISPONIBLE</abbr></th>
                        <th scope="col" class="sort asc"><abbr title="Efectivo que no forma parte de la cuenta pero que en el futuro retornara mas las ganancias">SALDO INVERTIDO</abbr></th>
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
        </div>

    <!--==== DATOS -->
    <input type="hidden" id="pagina" value="1">
    <input type="hidden" id="orderCol" value="0">
    <input type="hidden" id="orderType" value="asc">
    <input type="hidden" id="num_registros" value="10">
    <input type="hidden" id="rowcount" value="0">
    </div>
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    @@@@@@@@@@@@@@@@@ ZONA MODAL -->
    

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
            let moneda = document.getElementById("moneda").value
            
            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('moneda', moneda)
            
            fetch("load_saldos_cuenta_omnibus.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = data.data
                    document.getElementById("lbl-total").innerHTML = `Mostrando ${data.totalFiltro} de ${data.totalRegistros} registros`;
                    document.getElementById("nav-paginacion").innerHTML = data.paginacion

                    //actualizo los saldos del omnibus
                    document.getElementById("s_contable").value = data.saldo_contable
                    document.getElementById("s_inversor").value = data.saldo_inversor
                    document.getElementById("s_disponible").value = data.saldo_disponible
                    document.getElementById("s_vendedor").value = data.saldo_vendedor
                    document.getElementById("s_transito").value = data.saldo_transito

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
        function cuentaOmnibus(){
            location.href = "cuenta_omnibus.php";
        }

        function cambiaMoneda(){
            getData()
        }
    </script>
</BODY>
</HTML>