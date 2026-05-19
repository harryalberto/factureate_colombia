<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$v_arr_cuentas = $obj_cuentas->get_saldos($_SESSION['user']['usuarioid'],$_SESSION['user']['empresaid']);
/*--------------------------------------------------------*/
?>
<!-- Ventana modal -->
<div class="modal fade" id="grabarChildrenDeposito" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">
                Registro de deposito de Inversor
            </h4>
        </div>

        <div class="modal-body">
            <div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">
                <ul style="overflow:hidden;list-style:none;">
                    <li style="display:block;margin:5px;width:80%;float:left;padding:5px;">
                        <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                            <tr style="background-color:#252525;color:#ffffff;">
                                <td style="border: 1px solid;padding:5px;text-align:center;">MONEDA</td>
                                <td style="border: 1px solid;padding:5px;text-align:center;">SALDO CONTABLE</td>
                                <td style="border: 1px solid;padding:5px;text-align:center;">SALDO COMPROMETIDO</td>
                                <td style="border: 1px solid;padding:5px;text-align:center;">SALDO DISPONIBLE</td>
                                <td style="border: 1px solid;padding:5px;text-align:center;">SALDO INVERTIDO</td>
                                <td style="border: 1px solid;padding:5px;text-align:center;">SALDO TRANSITO</td>
                            </tr>
        <?
        for ($i=0; $i<count($v_arr_cuentas); $i++){
            echo '          <tr>
                                <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_cuentas[$i]['moneda'].'</td>
                                <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_cuentas[$i]['saldo_contable'],2,'.',',').'</td>
                                <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_cuentas[$i]['saldo_comprometido'],2,'.',',').'</td>
                                <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_cuentas[$i]['saldo_disponible'],2,'.',',').'</td>
                                <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_cuentas[$i]['saldo_invertido'],2,'.',',').'</td>
                                <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_cuentas[$i]['saldo_transito'],2,'.',',').'</td>
                            </tr>';
        }
        ?>
                        </table>
                    </li>
                </ul>
            </div>
            <form name='frm' method='post' id='frm' enctype="multipart/form-data" action="" onsubmit="event.preventDefault(); sendData();">
            <div class="frmtransaccion">
                <ul style="margin:0px;padding:0px;">
                    <li style="margin:0px 3px;padding:0px;width:135px;">Moneda:</li>
                    <li style="margin:0px 3px;padding:0px;width:130px;">Monto Depositado:</li>
                    <li style="margin:0px 3px;padding:0px;width:130px;">Comprobante:</li>
                </ul>
                <ul>
                    <li>
                        <select name="tipo_moneda" class="frminput_text" id="tipo_moneda" onChange="cambia_moneda();">
                        <?
                        $arrtipomoneda = $obj_maestros->get_tipos('MONEDA');
                        echo '<option value="0" selected>Monedas Disponibles</option>';

                        for ($j=0; $j<count($arrtipomoneda); $j++){
                            echo '
                              <option value="'.$arrtipomoneda[$j]['id'].'">'.$arrtipomoneda[$j]['nombre'].'</option>';
                        }
                        ?>
                        </select>
                        <?
                        for ($j=0; $j<count($arrtipomoneda); $j++){
                            $v_encontrado = 0;
                            
                            for ($z=0; $z<count($v_arr_cuentas); $z++){
                                if ($v_arr_cuentas[$z]['moneda_id'] == $arrtipomoneda[$j]['id']){
                                    echo '<input type="hidden" name="cuenta_id'.$v_arr_cuentas[$z]['moneda_id'].'" id="cuenta_id'.$v_arr_cuentas[$z]['moneda_id'].'" value="'.$v_arr_cuentas[$z]['cuenta_id'].'">';
                                    $v_encontrado = 1;
                                }
                            }
                            if ($v_encontrado == 0) echo '<input type="hidden" name="cuenta_id'.$v_arr_cuentas[$z]['moneda_id'].'" id="cuenta_id'.$v_arr_cuentas[$z]['moneda_id'].'" value="0">';
                        }
                        ?>
                    </li>
                    <li><input type="number" name="monto_depositado" style="text-align:right;" value="0.00" placeholder="Monto depositado" class="frminput_text" id="monto_depositado" required></li>
                    <li><input type="file" name="comprobante" id="comprobante" required></li>
                </ul>
            </div>
            <input type="hidden" name="cuenta_id" value="0" id="cuenta_id">
            <input type="hidden" name="retorno" value="<?=$_GET['ret']?>" id="retorno">
        </div>
        
        <div class="modal-footer">
          <ul style="list-style:none;">
            <button type="submit" class="botontransaccionazul btnGrabar" data-dismiss="modal"><span class="icon-floppy-disk"></span> Guardar</button>
            <!--<li class="botontransaccionazul" style="height:27px;padding: 5px 10px;"><a style="text-decoration: none;color:#ffffff;" href=javascript:graba_deposito()><span class="icon-floppy-disk"></span> Guardar</a></li>-->
            <button type="button" class="botontransaccionrojo btn-default" data-dismiss="modal">Cerrar</button>
          </ul>
        </div>
        </form>
        
        </div>
      </div>
</div>