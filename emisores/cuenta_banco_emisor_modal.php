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
<?
    require("../lib/head.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//@@@@@@@@@@@@@@@@@@ LOGICA
$vobj_mae = new maestros;
$vobj_cuenta = new cuentas;

if ($_GET['moneda_id'] != 0){
    $varr_cuenta = $vobj_cuenta->get_cuenta_banco_emisor($_GET['emisor_id'], $_GET['moneda_id'], $_GET['id']);
    $v_banco_id = $varr_cuenta['banco_id'];
    $v_moneda_id = $_GET['moneda_id'];
    $v_tcuenta_id = $varr_cuenta['tcuenta_id'];
    $v_nro_cuenta = $varr_cuenta['nro_cuenta'];
} else{
    $v_banco_id = 0;
    $v_moneda_id = 0;
    $v_tcuenta_id = 0;
    $v_nro_cuenta = '';
}
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
        <input type="hidden" name="emisor_id" id="emisor_id" value="<?=$_SESSION['user']['empresaid']?>">
        <input type="hidden" name="moneda_id_ref" id="moneda_id_ref" value="<?=$v_moneda_id?>">

    <div id="principal" style="display: block;padding-left: 10px;height: 60%;">
        <div class="contenedor_formulario">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 500px;">
                    <label for="banco_id">BANCO:</label>
                    <select name="banco_id" id="banco_id" class="formulario_control">
<?php
    if ($v_banco_id == 0)
        echo '          <option value="0" selected>Elija un Banco</option>';
    $varr_bancos = $vobj_mae->get_bancos();

    for ($i=0; $i<count($varr_bancos); $i++){
        if ($varr_bancos[$i]['banco_id'] == $v_banco_id)
            echo '      <option value="'.$varr_bancos[$i]['banco_id'].'" selected>'.$varr_bancos[$i]['banco_nombre'].'</option>';
        else
            echo '      <option value="'.$varr_bancos[$i]['banco_id'].'">'.$varr_bancos[$i]['banco_nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="moneda_id">MONEDA:</label>
                    <select name="moneda_id" id="moneda_id" class="formulario_control">
<?php
    if ($v_moneda_id == 0)
        echo '          <option value="0" selected>Elija una moneda</option>';

    $varr_monedas = $vobj_mae->get_tipos('MONEDA');

    for ($i=0; $i<count($varr_monedas); $i++){
        if ($varr_monedas[$i]['id'] == $v_moneda_id)
            echo '      <option value="'.$varr_monedas[$i]['id'].'" selected>'.$varr_monedas[$i]['nombre'].'</option>';    
        else
            echo '      <option value="'.$varr_monedas[$i]['id'].'">'.$varr_monedas[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 300px;">
                    <label for="tcuenta_id">TIPO CUENTA:</label>
                    <select name="tcuenta_id" id="tcuenta_id" class="formulario_control">
<?php
    if ($v_tcuenta_id == 0)
        echo '          <option value="0" selected>Elija un tipo de cuenta</option>';

    $varr_tcuenta = $vobj_mae->get_tipos('TIPO CUENTA BANCO');

    for ($i=0; $i<count($varr_tcuenta); $i++){
        if ($varr_tcuenta[$i]['id'] == $v_tcuenta_id)
            echo '      <option value="'.$varr_tcuenta[$i]['id'].'" selected>'.$varr_tcuenta[$i]['nombre'].'</option>';
        else
            echo '      <option value="'.$varr_tcuenta[$i]['id'].'">'.$varr_tcuenta[$i]['nombre'].'</option>';
    }
?>
                    </select>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width: 400px;">
                    <label for="nro_cuenta">NRO CUENTA:</label>
                    <input type="text" name="nro_cuenta" id="nro_cuenta" class="formulario_control" value="<?=$v_nro_cuenta?>">
                </div>
            </div>

            <div style="width:100%; float:left;margin-bottom:5px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="grabar()" id="btn_grabar">
                    <i class="fa-solid fa-floppy-disk"></i> Grabar
                </button>
            </div>

        </div>
    </div>
    </form>    
    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        @@@@@@@@@@@@@ ZON JS -->
    <script type="text/javascript">
        function grabar(){
            var v_banco_id = $('#banco_id').val();
            var v_moneda_id = $('#moneda_id').val();
            var v_tcuenta_id = $('#tcuenta_id').val();
            var v_nro_cuenta = $('#nro_cuenta').val();
            var v_emisor_id = $('#emisor_id').val();
            var v_moneda_id_ref = $('#moneda_id_ref').val();
            var v_accion;
            var v_btn_grabar = document.getElementById('btn_grabar');

            if (v_moneda_id_ref == 0) v_accion = 'registrar';
            else v_accion = 'update';

            if (v_banco_id == 0) alert("Debe elegir un Banco");
            else
                if (v_moneda_id == 0) alert("Debe elegir una moneda");
                else
                    if (v_tcuenta_id == 0) alert("Debe elegir un tipo de cuenta");
                    else
                        if (v_nro_cuenta == '') alert("Debe ingresar un numero de cuenta");
                        else{
                            v_btn_grabar.disabled = 'true';

                            $.ajax({
                                url:"cuenta_banco_emisor_proceso.php",
                                type:'post',
                                data:{
                                    "banco_id":v_banco_id,
                                    "moneda_id":v_moneda_id,
                                    "tcuenta_id":v_tcuenta_id,
                                    "nro_cuenta":v_nro_cuenta,
                                    "emisor_id":v_emisor_id,
                                    "accion":v_accion
                                }/*,
                                success:function(data,status){
                                    $('#EmisorModal').fadeIn(1000).html(data);
                                    $('#EmisorModal').modal('hide');
                                    refresh_page();
                                }*/
                            })
                            .done(function(rpta){
                                if (rpta == 1) alert('La cuenta fue creada con éxito, ahora cuenta con más de una cuenta de la misma moneda, si no elimina alguna, en las transacciones usaremos la más antigua');
                                else alert('La cuenta fue guardada con éxito');

                                getCuentasBanco();
                                refresh_page();
                            });
                        }
        }
    </script>
</BODY>
</HTML>