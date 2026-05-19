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
        Cuenta Omnibus
    </div>

    <!--================================================== 
    ========================== BOTONES DE SECCIONES -->
    <nav>
        <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-verde);border:none;margin-right: 10px;margin-left: 10px;"><i class="fa-solid fa-vault"></i> Cuenta Omnibus</button>
        <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;margin-right: 10px;" onclick="saldosCuenta()"><i class="fa-solid fa-money-check-dollar"></i> Saldos Cuenta</button>
    </nav>
    
    <!--==================================================
    ========================== CONTENIDO -->
    <!--==== TABLA HEADER -->
    <div style="overflow:hidden;margin:5px;padding:5px;">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col">ID</th>                <th scope="col">NRO CUENTA</th>
                        <th scope="col">TIPO</th>              <th scope="col">BANCO</th>
                        <th scope="col">MONEDA</th>            
                        <th scope="col"><abbr title="Efectivo en la cuenta">SALDO CONTABLE</abbr></th>
                        <th scope="col"><abbr title="Saldo que le pertenece al conjunto de inversores">SALDO INVERSOR</abbr></th>    
                        <th scope="col"><abbr title="Ganancias de Factureate del cual puede disponer">SALDO DISPONIBLE</abbr></th>
                        <th scope="col"><abbr title="Efectivo pendiente de transferirle a los vendedores por adelanto o remanente, debe ser cero">SALDO VENDEDOR</abbr></th>    
                        <th scope="col"><abbr title="Efectivo que ingreso a la cuenta y no esta identificado, no puede durar mas de 24 la validacion">SALDO TRANSITO</abbr></th>
                        <th scope="col">MOVIMIENTOS ING</th>   <th scope="col">MOVIMIENTOS SAL</th>
                        <th scope="col">MOVIMIENTOS TODOS</th>
                    </tr>
                </thead>
                <tbody id="content">
                        
                </tbody>
            </table>
        </div>
        <!--==== EL DETALLE DE UNA CUENTA -->
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize_trans">
                <thead id="detalle_cuenta_header">
                    
                </thead>
                <tbody id="detalle_cuenta_content">
                        
                </tbody>
            </table>
        </div>
        <!--==== PAGINACION DEL DETALLE DE CUENTA -->
        <div class="row justify-content-between">
            <div class="col-12 col-md-4">
                <label id="lbl-total" style="font-size: 10px;"></label>
            </div>

            <div class="col-12 col-md-4" id="nav-paginacion"></div>        
        </div>
        <!--==== CUENTAS SIN DETALLE -->
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead id="cuenta_header">
                    
                </thead>
                <tbody id="cuenta_content">
                        
                </tbody>
            </table>
        </div>
    </div>

    <input type="hidden" id="pagina" value="1">
    <input type="hidden" id="orderCol" value="0">
    <input type="hidden" id="orderType" value="asc">
    <input type="hidden" id="num_registros" value="10">
    <input type="hidden" id="rowcount" value="0">
    <input type="hidden" id="filtros" value="">
    <input type="hidden" id="cuenta_id" value="0">
    <input type="hidden" id="accion" value="nada">
    <input type="hidden" id="filtros_fecha" value="0">
    
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
            let detalle_cuenta_header = document.getElementById("detalle_cuenta_header")
            let detalle_cuenta_content = document.getElementById("detalle_cuenta_content")
            let cuenta_header = document.getElementById("cuenta_header")
            let cuenta_content = document.getElementById("cuenta_content")
            let pagina = document.getElementById("pagina").value || 1;
            let orderCol = document.getElementById("orderCol").value
            let orderType = document.getElementById("orderType").value
            let rowcount = document.getElementById("rowcount").value
            let filtros = document.getElementById("filtros").value
            let cuenta = document.getElementById("cuenta_id").value
            let accion = document.getElementById("accion").value
            let filtros_fecha = document.getElementById("filtros_fecha").value

            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('filtros', filtros)
            formaData.append('cuenta', cuenta)
            formaData.append('accion', accion)

            if (parseInt(filtros_fecha) != 0){
                let fini = document.getElementById("fini").value
                let ffin = document.getElementById("ffin").value
                formaData.append('fini', fini)
                formaData.append('ffin', ffin)
            }
            
            fetch("load_cuenta_omnibus.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = data.data

                    //  variacion de la seleccion del detalle de una cuenta
                    if (parseInt(cuenta) != 0) {
                        detalle_cuenta_header.innerHTML = data.header_detalle
                        detalle_cuenta_content.innerHTML = data.detalle
                        cuenta_header.innerHTML = data.header_otras_cuentas
                        cuenta_content.innerHTML = data.detalle_otras_cuentas
                    }

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
        function verIngresos(p_cuenta_id){
            document.getElementById('cuenta_id').value = p_cuenta_id;
            document.getElementById('accion').value = 'ingresos';
            getData()
        }

        function verSalidas(p_cuenta_id){
            document.getElementById('cuenta_id').value = p_cuenta_id;
            document.getElementById('accion').value = 'salidas';
            getData()
        }

        function verTodo(p_cuenta_id){
            document.getElementById('cuenta_id').value = p_cuenta_id;
            document.getElementById('accion').value = 'todo';
            getData()
        }

        function filtrarMovimientos(){
            document.getElementById('filtros_fecha').value = "1";
            getData()
        }

        function saldosCuenta(){
            location.href = "saldos_cuenta_omnibus.php";
        }
    </script>

</BODY>
</HTML>