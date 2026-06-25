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
//############# LOGICA PAGINA ##################
$vobj_mae = new maestros;

$varr_param = $vobj_mae->get_parametros();
$varr_tmoneda = $vobj_mae->get_tipos('MONEDA');

for ($i=0; $i<count($varr_tmoneda); $i++){
    if ($varr_tmoneda[$i]['id'] == 20) $v_moneda_simb = $varr_tmoneda[$i]['dato1'];
}
//##############################################
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php
    $menu = 'maestros/factureate_info.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>

    <div class="contenedor_principal">

        <div style="text-align:center;font-size: 18px;font-weight: bold;color:var(--color-azulv2);max-width:700px;margin: 0px auto;padding: 10px;">
            Información FACTUREATE
        </div>

        <div class="contenedor_formulario" id="formulario" style="margin-bottom:20px;">
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="id_empresa">NIT:</label>
                    <input type="text" class="formulario_control" id="id_empresa" value="<?=$varr_param['ID FACTUREATE']['valorchar']?>" readonly>
                </div>

                <div class="formulario_grupo_column" style="width:500px;">
                    <label for="nombre_empresa">RAZON SOCIAL:</label>
                    <input type="text" class="formulario_control" id="nombre_empresa" value="<?=$varr_param['RAZON SOCIAL FACTUREATE']['valorchar']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="telefono">TELEFONO:</label>
                    <input type="text" class="formulario_control" id="telefono" value="<?=$varr_param['telefono factureate']['valorchar']?>" readonly>
                </div>

                <div class="formulario_grupo_column" style="width:500px;">
                    <label for="direccion">DIRECCION:</label>
                    <input type="text" class="formulario_control" id="direccion" value="<?=$varr_param['direccion factureate']['valorchar']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width:400px;">
                    <label for="correo_op">EMAIL OPERACIONES:</label>
                    <input type="text" class="formulario_control" id="correo_op" value="<?=$varr_param['correo factureate operacion']['valorchar']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width:400px;">
                    <label for="correo_sop">EMAIL SOPORTE:</label>
                    <input type="text" class="formulario_control" id="correo_sop" value="<?=$varr_param['correo factureate soporte']['valorchar']?>" readonly>
                </div>
            </div>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="cuenta_nac">CUENTA:</label>
                    <input type="text" class="formulario_control" id="cuenta_nac" value="<?=$varr_param['CUENTA NACIONAL']['valorchar']?>" readonly>
                </div>
                <div class="formulario_grupo_column" style="width:200px;">
                    <label for="moneda_nac">MONEDA:</label>
                    <input type="text" class="formulario_control" id="moneda_nac" value="<?=$v_moneda_simb?>" readonly>
                </div>

<?php
    $varr_tcuenta = $vobj_mae->get_tipos('TIPO CUENTA BANCO');
    $varr_bancos = $vobj_mae->get_bancos();

    for ($i=0; $i<count($varr_tcuenta); $i++){
        if ($varr_tcuenta[$i]['id'] == $varr_param['CUENTA NACIONAL']['valornum']) $v_tcuenta_nac = $varr_tcuenta[$i]['nombre'];
    }

    for ($j=0; $j<count($varr_bancos); $j++){
        if ($varr_bancos[$j]['banco_id'] == $varr_param['BANCO CTA NACIONAL']['valornum']) $v_banco_nac = $varr_bancos[$j]['banco_nombre'];
    }
?>
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="tipocta_nac">TIPO:</label>
                    <input type="text" class="formulario_control" id="tipocta_nac" value="<?=$v_tcuenta_nac?>" readonly>
                </div>
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="banco_nac">BANCO:</label>
                    <input type="text" class="formulario_control" id="banco_nac" value="<?=$v_banco_nac?>" readonly>
                </div>
            </div>

<?php
    if ($varr_param['CUENTA DOL']['valorchar'] != ''){
        for ($i=0; $i<count($varr_tcuenta); $i++){
            if ($varr_tcuenta[$i]['id'] == $varr_param['CUENTA DOL']['valornum']) $v_tcuenta_dol = $varr_tcuenta[$i]['nombre'];
        }

        for ($j=0; $j<count($varr_bancos); $j++){
            if ($varr_bancos[$j]['banco_id'] == $varr_param['BANCO CTA DOL']['valornum']) $v_banco_dol = $varr_bancos[$j]['banco_nombre'];;
        }
?>

            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="cuenta_dol">CUENTA:</label>
                    <input type="text" class="formulario_control" id="cuenta_dol" value="<?=$varr_param['CUENTA DOL']['valorchar']?>" readonly>
                </div>
                <div class="formulario_grupo_column" style="width:200px;">
                    <label for="moneda_dol">MONEDA:</label>
                    <input type="text" class="formulario_control" id="moneda_dol" value="US$" readonly>
                </div>
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="tipocta_dol">TIPO:</label>
                    <input type="text" class="formulario_control" id="tipocta_dol" value="<?=$v_tcuenta_dol?>" readonly>
                </div>
                <div class="formulario_grupo_column" style="width:300px;">
                    <label for="banco_dol">BANCO:</label>
                    <input type="text" class="formulario_control" id="banco_dol" value="<?=$v_banco_dol?>" readonly>
                </div>
            </div>

<?php
    }
?>

        </div>

    </div>

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    ZONA MODAL 
    @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->

    <!--@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@-->

    <!--#################### ZONA JS ###################-->

    <!--################################################-->

</BODY>
</HTML>