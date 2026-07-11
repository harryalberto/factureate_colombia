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
require("../lib-trans/c_cuentas.php");
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
	<div id="principal" style="padding-left: 10px; overflow: hidden;">
        <div class="contenedor_formulario" style="width: 100%; overflow: hidden;">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="notificacion">NOTIFICACION:</label>
                    <input type="file" name="notificacion" id="notificacion" class="formulario_control" style="background-color:#fff;">
                    <input type="hidden" name="factura_id" id="factura_id" value="<?= $_GET['fid'] ?>">
                </div>
            </div>

            <div class="contenedor_formulario_column">
            	<button style="font-size:12px;background-color:var(--color-azul);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="registrarNoti()">
                    <i class="fa-solid fa-circle-xmark"></i> Registrar notificacion
                </button>
            </div>
        </div>
    </div>

    <!--=================================================
    ============ zona JS -->
    <script>
    	function registrarNoti(){
    		var notificacion = document.getElementById('notificacion');
    		var factura_id = document.getElementById('factura_id').value;

    		if (notificacion.value == "") alert('Debe adjuntar la notificacion');
    		else {
    			var formData = new FormData();

    			formData.append("notificacion", $("#notificacion")[0].files[0]);
    			formData.append("factura_id", factura_id);

    			$.ajax({
                    url:"registra_notifica_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    refresh_page();
                });
    		}
    	}
    </script>
    <!--=================================================-->
</BODY>