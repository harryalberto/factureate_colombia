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
$obj_cuenta = new cuentas;
$obj_mae = new maestros;

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <input type="hidden" id="inversor_id" value="<?=$_GET['inversor_id']?>">

    <div class="form-group">
        <label for="motivo">Motivo:</label>
        <textarea class="form-control" id="motivo" rows="5"></textarea>
    </div>

    <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="guardarSolicitud()">
        <i class="fa-solid fa-floppy-disk"></i> Guardar Solicitud
    </button>
  
<!--################ ZONA JS ####################-->
<script>
    function guardarSolicitud(){
        var v_inversor_id = $('#inversor_id').val();
        var v_motivo = $('#motivo').val();
        
        if (v_motivo != ''){
            $.ajax({
                url:"solicita_anular_inversor_proceso.php",
                type:'post',
                data:{
                    "inversor_id":v_inversor_id,
                    "motivo":v_motivo,
                    "accion":'solicitud'
                },
                success:function(data,status){
                    $('#PerfilInversor').fadeIn(1000).html(data);
                    $('#PerfilInversor').modal('hide');
                    refresh_page();
                }
            });
        } else alert('Debe ingresar el motivo de su solicitud');
    }
</script>
<!--#############################################-->
</BODY>
</HTML>