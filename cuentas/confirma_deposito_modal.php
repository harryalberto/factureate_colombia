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
<?php
    require("../lib/head.php");
    $acceso = 'CUENTAS';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cuenta = new cuentas;
$varr_st = $obj_cuenta->get_st($_GET['st_id']);
$v_monto_label = $varr_st['moneda_simbolo'].' '.number_format($varr_st['monto'],2,'.',',');
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm_modal' enctype="multipart/form-data">
        <div class="form-group">
            <label for="st_id">TRANSITO ID</label>
            <input type="text" class="form-control" id="st_id" name="st_id" value="<?=$_GET['st_id']?>" readonly>
        </div>
        <div class="form-group">
            <label for="inversor">INVERSOR</label>
            <input type="text" class="form-control" id="inversor" name="inversor" value="<?=$varr_st['nombre_inversor']?>" readonly>
        </div>
        <div class="form-group">
            <label for="banco">BANCO</label>
            <input type="text" class="form-control" id="banco" name="banco" value="<?=$varr_st['nombre_banco']?>" readonly>
        </div>
        <div class="form-group">
            <label for="monto">MONTO</label>
            <input type="text" class="form-control" id="monto" name="monto" value="<?=$v_monto_label?>" readonly>
        </div>
        <div class="form-group">
            <label for="remitente">REMITENTE</label>
            <input type="text" class="form-control" id="remitente" name="remitente">
        </div>
        <div class="form-group">
            <label for="nro_op">NRO OPERACION</label>
            <input type="text" class="form-control" id="nro_op" name="nro_op">
        </div>

        <hr>
    
        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
        <div>
            <button type="button" class="btn btn-primary" id="btn_confirma" onclick="confirmaDeposito()" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
            <i class="fa-solid fa-check-double" style="font-size:16px;"></i> Confirmar Deposito
            </button>
        </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <script>
        function confirmaDeposito(){
            var v_st_id = $('#st_id').val();
            var v_remitente = $('#remitente').val();
            var v_nro_op = $('#nro_op').val();
            var btn_confirma = document.getElementById('btn_confirma');

            btn_confirma.disabled = true;

            $.ajax({
                url:"confirma_saldo_transito.php",
                type:'post',
                data:{
                    "st_id":v_st_id,
                    "remitente":v_remitente,
                    "nro_op":v_nro_op
                },
                success:function(data,status){
                    $('#SaldosModal').fadeIn(1000).html(data);
                    $('#SaldosModal').modal('hide');
                    refresh_page();
                }
            });
        }
    </script>
</BODY>
</HTML>