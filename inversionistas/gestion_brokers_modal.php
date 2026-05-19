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
    $acceso = 'CUENTAS';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae_modal = new maestros;

$varr_broker = $obj_mae_modal->get_broker_detalle($_GET['broker_id']);
$v_qrepresentados = $obj_mae_modal->get_q_brokeraje($varr_broker['identificacion']);

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <div id="principal" style="display: block;height: 50%;">
        <div class="contenedor_formulario" style="overflow: hidden;">
            
            <div class="contenedor_formulario_column">

                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="id">ID:</label>
                    <input type="text" name="id" id="id" class="formulario_control" value="<?=$_GET['broker_id']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="nombre">NOMBRE:</label>
                    <input type="text" name="nombre" id="nombre" class="formulario_control" value="<?=$varr_broker['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="apellido">APELLIDO:</label>
                    <input type="text" name="apellido" id="apellido" class="formulario_control" value="<?=$varr_broker['apellido']?>" readonly>
                </div>

            </div>

            <div class="contenedor_formulario_column">

                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="tipodoc">TIPO DOC:</label>
                    <input type="text" name="tipodoc" id="tipodoc" class="formulario_control" value="<?=$varr_broker['tipodoc']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="documento">DOCUMENTO:</label>
                    <input type="text" name="documento" id="documento" class="formulario_control" value="<?=$varr_broker['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="email">EMAIL:</label>
                    <input type="text" name="email" id="email" class="formulario_control" value="<?=$varr_broker['email']?>" readonly>
                </div>

            </div>

            <div class="contenedor_formulario_column">

                <div class="formulario_grupo_row" style="width: 315px;">
                    <label for="estado">ESTADO:</label>
                    <input type="text" name="estado" id="estado" class="formulario_control" value="<?=$varr_broker['estado_activo']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="otros_repre">REPRESENTADOS:</label>
                    <input type="text" name="otros_repre" id="otros_repre" class="formulario_control" value="<?=$v_qrepresentados?>" readonly>
                </div>
                

            </div>

            <!-- informacion no visible que se pasara -->
            <input type="hidden" name="empresa_id" id="empresa_id" value="<?=$varr_broker['empresa_id']?>">

<?php
    if ($varr_broker['estado_activo_id'] == 2){
?>
            <div class="contenedor_formulario_column">

                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="informe">INFORME LEGAL:</label>
                    <input type="file" name="informe" id="informe" class="formulario_control" style="background-color:#fff;">
                </div>

            </div>

            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none; margin-top: 10px;" type="button" class="btn btn-primary" onclick="aprobarBroker()">
                    <i class="fa-solid fa-circle-check"></i> Aprobar
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none; margin-top: 10px;margin-left: 10px;" type="button" class="btn btn-primary" onclick="rechazarBroker()">
                    <i class="fa-solid fa-circle-xmark"></i> Rechazar
                </button>
            </div>
<?php
    }
?>

        </div> <!-- div formulario -->

    </div> <!-- div principal -->
  
    <!--################ ZONA JS ####################-->
    <script>
        function aprobarBroker(){
            var v_id = $('#id').val();
            var v_informe = $('#informe').val();
            var v_empresa_id = $('#empresa_id').val();
            var v_email = $('#email').val();
            var v_nombre = $('#nombre').val();
            var v_apellido = $('#apellido').val();
            var v_documento = $('#documento').val();

            if (v_informe == ""){
                alert('Debe adjuntar el informe antes de aprobar');
            } else {
                var formaData = new FormData();

                formaData.append('id', v_id)
                formaData.append('accion', 'aprueba_broker')
                formaData.append('empresa_id', v_empresa_id)
                formaData.append('email', v_email)
                formaData.append('nombre', v_nombre)
                formaData.append('apellido', v_apellido)
                formaData.append('documento', v_documento)

                var inputFile_informe = document.getElementById("informe");
                var file_informe = inputFile_informe.files[0];
                formaData.append('informe', file_informe)

                $.ajax({
                    url: "nuevo_broker_proceso.php",
                    type: "POST",
                    data: formaData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data)
                    {
                        alert('El Broker fue aprobado !!');
                        refresh_page();
                    }
                });
            }
        }

        function rechazarBroker(){
            var v_id = $('#id').val();
            var v_informe = $('#informe').val();
            var v_empresa_id = $('#empresa_id').val();

            if (v_informe == ""){
                alert('Debe adjuntar el informe antes de rechazar');
            } else {
                var formaData = new FormData();

                formaData.append('id', v_id)
                formaData.append('accion', 'rechazar_broker')
                formaData.append('empresa_id', v_empresa_id)

                var inputFile_informe = document.getElementById("informe");
                var file_informe = inputFile_informe.files[0];
                formaData.append('informe', file_informe)

                $.ajax({
                    url: "nuevo_broker_proceso.php",
                    type: "POST",
                    data: formaData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data)
                    {
                        alert('El Broker fue rechazado !!');
                        refresh_page();
                    }
                });
            }
        }
    </script>
    <!--#############################################-->
</BODY>
</HTML>