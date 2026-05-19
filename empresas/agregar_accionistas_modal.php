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

$varr_tiposdoc = $obj_mae->get_tipos('TIPOIDENTIF');
$v_tipos_view = '<option value="0" selected>- Tipo Doc -</option>';
$v_nro_registros = 10;

for ($i=0; $i<count($varr_tiposdoc); $i++){
    $v_tipos_view .= '<option value="'.$varr_tiposdoc[$i]['id'].'">'.$varr_tiposdoc[$i]['nombre'].'</option>';
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form method="post" name="frm_accionistas" id="frm_accionistas">
    <div class="contenedor_formulario">
        <input type="hidden" name="nro_registros" id="nro_registros" value="<?=$v_nro_registros?>">
        <input type="hidden" name="empresa2_id" id="empresa2_id" value="<?=$_GET['empresa_id']?>">
        
        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_row" style="width: 200px;">
                <label>NOMBRE</label>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label>TIPO DOC</label>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <label>NRO DOC</label>
            </div>
        </div>

<?php
    for ($i=0; $i<$v_nro_registros; $i++){
        echo '
        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_row" style="width: 200px;">
                <input type="text" name="nombre'.$i.'" id="nombre'.$i.'" class="formulario_control">
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <select name="area'.$i.'" id="area'.$i.'" class="formulario_control">'.$v_tipos_view.'</select>
            </div>
            <div class="formulario_grupo_row" style="width: 100px;">
                <input type="text" name="nro_doc'.$i.'" id="nro_doc'.$i.'" class="formulario_control">
            </div>
        </div>';
    }
?>
        <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="guardarAccionistas()">
            <i class="fa-solid fa-floppy-disk"></i> Guardar
        </button>
    </div>
    </form>
  
<!--################ ZONA JS ####################-->
    <script>
        function guardarAccionistas(){
            var formData = new FormData(document.getElementById("frm_accionistas"));
            var v_empresa_id = $('#empresa2_id').val();

            $.ajax({
                url:"empresa_accionistas_procesar.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html",
                success:function(data,status){
                    $('#MasAccionistas').fadeIn(1000).html(data);
                    $('#MasAccionistas').modal('hide');
                    refresh_page(v_empresa_id);
                }
            });
        }
    </script>
<!--#############################################-->
</BODY>
</HTML>