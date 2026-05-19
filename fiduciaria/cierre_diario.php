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
date_default_timezone_set($_SESSION['user']['zona_horaria']);

$v_fhoy = date('Y-m-d');
$v_fcierre = strtotime('-1 day', strtotime($v_fhoy)); $v_fcierre = date('Y-m-d', $v_fcierre);

$vobj_mae = new maestros;

$varr_monedas = $vobj_mae->get_tipos('MONEDA');
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    $menu = 'fiduciaria/cierre_diario.php';
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
        Cierre diario Cuenta Omnibus
    </div>

    <!--==================================================
    ========================== CONTENIDO -->

    <!--==== TABLA CONTENIDO -->
    <div style="overflow:hidden;margin:5px;padding:5px;">

        <!--==== DATOS DE LA CUENTA -->
        <nav style="font-size:12px;">
            <label style="margin-right: 5px;">Día Cierre:</label>
            <input type="date" name="dia_cierre" id="dia_cierre" class="formulario_control" style="margin-right:10px;" value="<?=$v_fcierre?>">
            <button type="button" class="btn btn-primary" style="font-size:10px;background-color:var(--color-azulv2);border:none;" onclick="cambiaDia()"><i class="fa-solid fa-filter"></i></button>

            <label style="margin-right:5px;">Moneda: </label>
            <select id="moneda" class="formulario_control" onchange="cambiaMoneda()">
<?php
    for ($i = 0; $i < count($varr_monedas); $i++){
        if ($i == 0) echo '
                <option value="'.$varr_monedas[$i]['id'].'" selected>'.$varr_monedas[$i]['dato1'].'</option>';
        else echo '
                <option value="'.$varr_monedas[$i]['id'].'">'.$varr_monedas[$i]['dato1'].'</option>';
    }
?>
            </select>

            <label style="margin-right:5px;">Cuenta: </label>
            <input type="text" name="cuenta" id="cuenta" class="formulario_control" style="margin-right:10px;" readonly>

            <label style="margin-right:5px;">Banco: </label>
            <input type="text" name="banco" id="banco" class="formulario_control" readonly>
        </nav>

        <!--==== SALDOS INICIALES -->
        <nav id="saldo_inicial" style="font-size:12px;margin-top: 10px;">
            
        </nav>
        <!--==== TABLA DE DATOS -->
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col" class="sort asc">ID MOVIMIENTO</th>        <th scope="col" class="sort asc">MOTIVO</th>
                        <th scope="col" class="sort asc">ORIGEN / DESTINO</th>     <th scope="col" class="sort asc">TIPO MOV</th>
                        <th scope="col" class="sort asc">MONTO</th>
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

        <!--==== SALDO FINAL -->
        <nav id="saldo_final" style="font-size:12px;margin-top: 10px;">
            
        </nav>

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
            let saldo_inicial = document.getElementById("saldo_inicial")
            let saldo_final = document.getElementById("saldo_final")
            let pagina = document.getElementById("pagina").value || 1;
            let orderCol = document.getElementById("orderCol").value
            let orderType = document.getElementById("orderType").value
            let rowcount = document.getElementById("rowcount").value
            let dia_cierre = document.getElementById("dia_cierre").value
            let moneda = document.getElementById("moneda").value
            
            let formaData = new FormData()
            //formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            formaData.append('rowcount', rowcount)
            formaData.append('dia_cierre', dia_cierre)
            formaData.append('moneda', moneda)
            
            fetch("load_cierre_diario.php", {
                    method: "POST",
                    body: formaData
                })
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = data.data
                    document.getElementById("lbl-total").innerHTML = `Mostrando ${data.totalFiltro} de ${data.totalRegistros} registros`;
                    document.getElementById("cuenta").value = data.numero_cuenta
                    document.getElementById("banco").value = data.banco
                    saldo_inicial.innerHTML = data.saldo_inicial
                    saldo_final.innerHTML = data.saldo_final
                    //document.getElementById("nav-paginacion").innerHTML = data.paginacion

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
        function cambiaDia(){
            getData()
        }

        function cambiaMoneda(){
            getData()
        }
    </script>
</BODY>
</HTML>