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
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@ LOGICA
$vobj_cuenta_modal = new cuentas;

if ($_GET['tipo'] == 'EMISOR') $varr_cuenta = $vobj_cuenta_modal->get_cuenta_banco_emisor_v2($_GET['id'], $_GET['moneda_id'], $_GET['cta'], $_GET['banco_id']);
else $varr_cuenta = $vobj_cuenta_modal->get_cta_banco_inversor($_GET['id'], $_GET['moneda_id'], $_GET['cta'], $_GET['banco_id']);
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="accion" id="accion">
        <input type="hidden" name="tipo" id="tipo" value="<?=$_GET['tipo']?>">
        <input type="hidden" name="id" id="id" value="<?=$_GET['id']?>">
        <input type="hidden" name="moneda_id" id="moneda_id" value="<?=$_GET['moneda_id']?>">
        <input type="hidden" name="banco_id" id="banco_id" value="<?=$_GET['banco_id']?>">
        
    <div id="principal" style="display: block;padding-left: 10px;height: 60%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="banco">BANCO:</label>
                    <input type="text" name="banco" id="banco" class="formulario_control" value="<?=$varr_cuenta['banco_nombre']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="tcuenta">TIPO CTA:</label>
                    <input type="text" name="tcuenta" id="tcuenta" class="formulario_control" value="<?=$varr_cuenta['tcuenta_nombre']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="cuenta">CUENTA:</label>
                    <input type="text" name="cuenta" id="cuenta" class="formulario_control" value="<?=$varr_cuenta['nro_cuenta']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="moneda">MONEDA:</label>
                    <input type="text" name="moneda" id="moneda" class="formulario_control" value="<?=$varr_cuenta['nombre_moneda']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="certificado">CERTIFICADO:</label>
                    <span name="certificado" id="certificado"><a href="<?=$varr_cuenta['certificado']?>" target="_blank"><i class="fa-solid fa-file-pdf" style="font-size:18px;"></i></a></span>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="motivo">MOTIVO RECHAZO:</label>
                    <textarea name="motivo" id="motivo" class="formulario_control" rows="3" cols="60"></textarea>
                </div>
            </div>

            <div style="overflow:hidden;background-color:#555555;height:1px;width:100%; float:left;margin-top:10px;"></div>

            <div style="width:100%; float:left;margin-bottom:5px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" id="boton_aprobar" class="btn btn-primary" onclick="aprobar()">
                    <i class="fa-solid fa-floppy-disk"></i> Aprobar
                </button>
                <button style="font-size:12px;background-color:var(--color-rojo);border:none;margin-top: 5px;" type="button" id="boton_rechazo" class="btn btn-primary" onclick="rechazar()">
                    <i class="fa-solid fa-circle-xmark"></i> Rechazar
                </button>
            </div>

        </div>  <!-- formulario -->
    </div>  <!-- principal -->
    </form>    
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">
        function aprobar(){
            var btn_aprobar = document.getElementById("boton_aprobar");
            var btn_rechazar = document.getElementById("boton_rechazo");
            btn_aprobar.disabled = true;
            btn_rechazar.disabled = true;

            document.frm_modal.accion.value = 'aprobar';

            var formData = new FormData(document.getElementById("frm_modal"));
            
            $.ajax({
                url:"cuenta_banco_pend_modal_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                if (rpta == -1){
                    alert('Ocurrio un error en la aprobacion de la cuenta de banco');
                } else {
                    alert('La cuenta de banco fue aprobada con exito');
                    location.href = 'cuentas_banco_pendiente.php';
                }
            });
        }

        function rechazar(){
            var v_motivo = $('#motivo').val();
            var btn_rechazar = document.getElementById("boton_rechazo");
            btn_rechazar.disabled = true;

            document.frm_modal.accion.value = 'rechazar';

            if (v_motivo == ""){
                alert('Debe ingresar el motivo de rechazo');
            } else{
                var formData = new FormData(document.getElementById("frm_modal"));
            
                $.ajax({
                    url:"cuenta_banco_pend_modal_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    if (rpta == -1){
                        alert('Ocurrio un error en el rechazo de la cuenta de banco');
                    } else {
                        alert('La cuenta de banco fue rechazada y enviado un correo al creador de la cuenta');
                        location.href = 'cuentas_banco_pendiente.php';
                    }
                });
            }
        }
    </script>
</BODY>
</HTML>