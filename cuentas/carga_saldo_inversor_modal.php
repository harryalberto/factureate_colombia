<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");
require("../lib-trans/c_inversiones.php");
require("../lib-trans/c_subasta.php");
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
$obj_inv = new inversiones;
$objsubasta = new subasta;

$vn_cuenta_id = $_GET['cuenta_id'];
$vn_moneda_id = $_GET['moneda_id'];

$varr_cuenta = $obj_cuenta->get_cuenta_detalle($_GET['cuenta_id']);
$varr_cuentas_full = $obj_cuenta->get_cuentas_inversor($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
$varr_bancos = $obj_mae->get_bancos();

$arr_saldos = $obj_cuenta->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
$varr_prop = $obj_inv->get_propuestas_pre_xinversor('SELEC',$_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid'],0,1000);

$v_monto_enpropuestas = $objsubasta->monto_propuestas_subasta($_SESSION['user']['usuarioid'], $_SESSION['user']['empresaid'],$_GET['moneda_id']);
$v_monto_preliminares = $obj_cuenta->get_monto_total_preliminar($vn_cuenta_id,$vn_moneda_id,$varr_prop,$arr_saldos,$v_monto_enpropuestas);

/*--------------------------------------------------------*/

?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?
    //date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm_modal' enctype="multipart/form-data">

        <div id="content">

            <div class="form-group">
                <label for="cuenta_id">CUENTA ID</label>
                <input type="text" class="form-control" id="cuenta_id" name="cuenta_id" value="<?=$_GET['cuenta_id']?>" readonly>
            </div>

            <div class="form-group">
                <label for="banco_id">BANCO</label>
                <select id="banco_id" class="form-control" name="banco_id" onchange="CambiaBanco()">
                    <option value="0" selected>Seleccione un Banco</option>
<?php
            for ($i=0; $i<count($varr_bancos); $i++){
                echo '
                    <option value="'.$varr_bancos[$i]['banco_id'].'">'.$varr_bancos[$i]['banco_nombre'].'</option>';
            }
?>
                </select>
            </div>

            <div class="form-group">
                <label for="moneda_id">MONEDA</label>
<?php
            for ($i=0; $i<count($varr_cuentas_full); $i++){
                if ($varr_cuentas_full[$i]['cuenta_id'] == $_GET['cuenta_id']){
                    echo '
                <input type="text" class="form-control" id="moneda_nombre" name="moneda_nombre" value="'.$varr_cuentas_full[$i]['moneda'].'" readonly>
                <input type="hidden" id="moneda_id" name="moneda_id" value="'.$varr_cuentas_full[$i]['moneda_id'].'">';
                }
                
                echo '<input type="hidden" id="cta'.$i.'" value="'.$varr_cuentas_full[$i]['banco_id'].'-'.$varr_cuentas_full[$i]['moneda_id'].'">
                    <input type="hidden" id="cuenta'.$varr_cuentas_full[$i]['moneda_id'].'" value="'.$varr_cuentas_full[$i]['cuenta_id'].'">';
            }

            echo '<input type="hidden" id="num_cuentas" value="'.count($varr_cuentas_full).'">';
?>
                </select>
            </div>

            <div class="form-group">
                <label for="monto">MONTO</label>
                <input type="number" id="monto" class="form-control" name="monto">
            </div>

            <div class="form-group">
                <label for="comprobante">COMPROBANTE</label>
                <input type="file" class="form-control-file" id="comprobante" name="comprobante">
            </div>

        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
            <div>
                <button type="button" class="btn btn-primary" onclick="GuardarDeposito()" style="font-size:12px;background-color:var(--color-azulv2);border:none;" id="btn_registra_deposito">
                    <i class="fa-solid fa-piggy-bank" style="font-size:16px;"></i> Registrar Deposito
                </button>
            </div>

        </div>

    </form>

    <!------ END CUERPO VARIABLE ------>
    
    <script>
        function CambiaBanco(){
            var v_moneda = $('#moneda_id').val();
            var v_cuenta = $('#cuenta_id').val();
            var v_banco = $('#banco_id').val();
            var v_num_cuentas = Number($('#num_cuentas').val());
            var v_proceder, i, v_cta;
            var cta_moneda = v_banco + '-' + v_moneda;
            
            v_proceder = 0;

            if (v_num_cuentas > 1){
                for (i=0; i<v_num_cuentas; i++){
                    v_cta = $('#cta'+i).val();

                    if (v_cta == cta_moneda) v_proceder = 1;
                }

                if (v_proceder == 0)
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            } else{
                v_cta = $('#cta0').val();
                
                if (v_cta == cta_moneda) v_proceder = 1;
                else 
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            }
        }

        function CambiaMoneda(){
            var v_moneda = $('#moneda_id').val();
            var v_cuenta = $('#cuenta_id').val();
            var v_banco = $('#banco_id').val();
            var v_num_cuentas = Number($('#num_cuentas').val());
            var v_proceder, i, v_cta;
            var cta_moneda = v_banco + '-' + v_moneda;

            v_proceder = 0;

            if (v_num_cuentas > 1){
                for (i=0; i<v_num_cuentas; i++){
                    v_cta = $('#cta'+i).val();

                    if (v_cta == cta_moneda) v_proceder = 1;
                }

                if (v_proceder == 0)
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            } else{
                v_cta = $('#cta0').val();

                if (v_cta == cta_moneda) v_proceder = 1;
                else 
                    alert("Usted no tiene registrado con nosotros cuenta en ese Banco, considere que la cuenta de deposito debe estar a su nombre");
            }
        }

        function GuardarDeposito(){
            var v_banco = $('#banco_id').val();
            var v_monto = Number($('#monto').val());
            var v_moneda = $('#moneda_id').val();
            var v_cuenta = $('#cuenta'+v_moneda).val();
            var v_file = document.getElementById('comprobante');
            var v_ruta = v_file.value;
            
            if (v_banco == 0) alert("Debe seleccionar un Banco");
            else 
                if (v_monto == 0 || v_monto == '') alert('Debe ingresar un monto valido');
                else 
                    if (v_ruta == '') alert('Debe seleccionar un comprobante de transferencia');
                    else{
                        document.getElementById('btn_registra_deposito').disabled = true;

                        var formData = new FormData(document.getElementById("frm_modal"));
                        $.ajax({
                            url:"cargar_saldo_inversor_transito.php",
                            type:'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: "html",
                            /*data:{
                                "cuenta_id":v_cuenta,
                                "banco_id":v_banco,
                                "monto":v_monto,
                                "moneda_id":v_moneda,
                                "comprobante":v_file
                            },*/
                            success:function(data,status){
                                $('#SaldosModal').fadeIn(1000).html(data);
                                $('#SaldosModal').modal('hide');
                                refresh_page();
                            }
                        });
                    }
        }
    </script>
</BODY>
</HTML>