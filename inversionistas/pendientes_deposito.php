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
    require("../lib/head_v2.php");
    $acceso = 'INVERSIONES';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function graba_deposito(){
            var v_monto = document.frm.monto_depositado.value;
            var v_moneda = document.frm.tipo_moneda.value;
            var v_archivo = document.getElementById('comprobante');
            var v_ruta = v_archivo.value;
            
            if (v_moneda == 0) alert('Debe registrar una moneda');
            else {
                if (v_monto == 0) alert('Debe ingresar un monto mayor a CERO');
                else {
                    if (v_ruta == "") alert('Debe ingresar un documento del deposito realizado');
                    else {
                        var v_cuenta_ref = document.getElementById('cuenta_id'+v_moneda);
                        var v_cuenta = v_cuenta_ref.value;
                        document.frm.cuenta_id.value = v_cuenta;
                        //document.frm.submit();
                        alert("llego");
                    }
                }
            }
        }
        function cambia_moneda(){
            var v_moneda_id = document.frm.tipo_moneda.value;
            var v_cuenta_obj = document.getElementById("cuenta_id"+v_moneda_id);
            var v_cuenta_id = v_cuenta_obj.value;
            document.frm.cuenta_id.value = v_cuenta_id;
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
date_default_timezone_set("America/Lima");
$v_hoy = date('Y-m-d');
$v_time_hoy = date('H:i:s');

$obj_inversiones = new inversiones;
$obj_maestros = new maestros;
$obj_cuentas = new cuentas;

$v_arr_pendientes = $obj_inversiones->get_pendientes_deposito($_SESSION['user']['usuarioid']);
$v_arr_parametros = $obj_maestros->get_parametros();
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    //if ($_GET['ret'] == 'inversiones') $menu = 'inversionistas/facturas_inversion.php';
    $menu = 'inversionistas/facturas_inversion.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Pendientes de Deposito
    </div>
    <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
    <form name='frm' method='post' id='frm' action="">
    <div style="font-size: 10px;overflow:hidden;margin:5px auto;padding: 10px 40px;">
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 10px;">Relaci&oacute;n de pendientes de deposito producto de las subastas ganadas por el inversionista</li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:5px;width:80%;float:left;padding:5px;">
                <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                    <tr style="background-color:#252525;color:#ffffff;">
                            <td style="border: 1px solid;padding:5px;text-align:center;"></td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">PAGADOR</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">FACTURA</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">F VENCIMIENTO</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">MONTO INVERSION</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">TASA ANUAL</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"><abbr title="Ganancia es estimada hasta el momento en que se entregue el financiamiento">GANANCIA EST</abbr></td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">PENDIENTE DEPOSITO</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">MONEDA</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"><abbr title="Fecha en que realizo su propuesta para comprar el instrumento">F PROPUESTA</abbr></td>
                            <td style="border: 1px solid;padding:5px;text-align:center;"><abbr title="Horas transcurridas desde que se le informo que debe realizar el deposito">HORAS</abbr></td>
                    </tr>
        <?php
        $v_monto_total_pendiente = 0;

        for ($i=0; $i<count($v_arr_pendientes); $i++){
            $v_ganancia = $obj_maestros->calcula_ganancia($v_hoy, $v_arr_pendientes[$i]['factura_fvencimiento'], $v_arr_pendientes[$i]['tia'], $v_arr_pendientes[$i]['monto_inversion']);
            $v_horas_maximo = $v_arr_parametros['HORAS MAXIMO DEPOSITO']['valornum'];
            $v_arr_dif_fechas = $obj_maestros->calcula_dif_fechas($v_arr_pendientes[$i]['subasta_ffin'],$v_arr_pendientes[$i]['subasta_hfin'],$v_hoy,$v_time_hoy);
            $v_horas_trans = ($v_arr_dif_fechas['dias']*24) + $v_arr_dif_fechas['horas'];
            $v_fvencimiento_formato = date('d-m-Y',strtotime($v_arr_pendientes[$i]['factura_fvencimiento']));
            $v_tia = $v_arr_pendientes[$i]['tia'] * 100;
            $v_monto_total_pendiente = $v_monto_total_pendiente + $v_arr_pendientes[$i]['fondo_pendiente'];
            
            if ($v_horas_trans > $v_horas_maximo) $v_bg_color = 'background-color:#b30a1f;color:#ffffff;';
            elseif ($v_horas_trans > ($v_horas_maximo/2)) $v_bg_color = 'background-color:#fdd15a;';
            else $v_bg_color = '';

            if ($_GET['fid'] == $v_arr_pendientes[$i]['factura_id']) $v_select = 'checked';
            else $v_select = '';

            echo '   <tr>
                         <td style="border: 1px solid;padding:5px;text-align:right;"><input type="checkbox" class="frminput_text" name="pendientes[]" value="'.$v_arr_pendientes[$i]['propuesta_id'].'" '.$v_select.'></td>
                         <td style="border: 1px solid;padding:5px;text-align:right;">'.$v_arr_pendientes[$i]['factura_id'].'</td>
                         <td style="border: 1px solid;padding:5px;text-align:left;">'.$v_arr_pendientes[$i]['pagador'].'</td>
                         <td style="border: 1px solid;padding:5px;text-align:left;">'.$v_arr_pendientes[$i]['factura_nro'].'</td>
                         <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_fvencimiento_formato.'</td>
                         <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_pendientes[$i]['monto_inversion'],2,'.',',').'</td>
                         <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_tia,2,'.',',').' %</td>
                         <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_ganancia,2,'.',',').'</td>
                         <td style="border: 1px solid;padding:5px;text-align:right;">'.number_format($v_arr_pendientes[$i]['fondo_pendiente'],2,'.',',').'</td>
                         <td style="border: 1px solid;padding:5px;text-align:right;">'.$v_arr_pendientes[$i]['moneda'].'</td>
                         <td style="border: 1px solid;padding:5px;text-align:center;">'.$v_arr_pendientes[$i]['fpropuesta_formato'].'</td>
                         <td style="border: 1px solid;padding:5px;text-align:right;'.$v_bg_color.'">'.number_format($v_horas_trans,2,'.',',').'</td>
                     </tr>';
        }
        ?>
                </table>
            </li>
        </ul>
    </div>
    <div class="frmtransaccion">
        <ul>
            <li>
                <button style="font-size:10px;width: 150px;" type="button" data-toggle="modal" data-target="#grabarChildrenDeposito" class="botontransaccionazul"><span class="icon-download"></span> Registrar Deposito</button>
            </li>
        </ul>
        <?php include('registro_deposito_inversor.php'); ?>
    </div>
    </form>
    <!------ END CUERPO VARIABLE btn btn-danger------>
    <!--==== ZONA Modal ====-->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        //Ocultar mensaje
        setTimeout(function() {
          $("#contenMsjs").fadeOut(1000);
        }, 3000);

        $('.btnGrabar').click(function(e) {
            e.preventDefault();
            var monto = $("#monto_depositado").val();
            var comprobante = $("#comprobante").val();
            var moneda = $("#tipo_moneda").val();
            var cuenta_id = $("#cuenta_id").val();
            var retorno = $("#retorno").val();
            var formData = new FormData();
            var compro = $("#comprobante")[0].files[0];
            
            formData.append("monto_depositado",monto);
            formData.append("tipo_moneda",moneda);
            formData.append("cuenta_id",cuenta_id);
            formData.append("retorno",retorno);
            formData.append("comprobante",compro);

            if (moneda == 0) alert ("Debe seleccionar una moneda");
            else {
                if (monto <= 0) alert("Debe ingresar un monto mayor que CERO");
                else {
                    if (comprobante == "") alert("Debe ingresar un comprobante de deposito");
                    else {
                        $.ajax({
                            type: "POST",
                            url: "graba_deposito.php",
                            //async: true,
                            //data: {monto_depositado:monto, tipo_moneda:moneda, comprobante:archivo, cuenta_id:cuenta_id, retorno:retorno},
                            data: formData,
                            contentType: false,
                            processData: false,
                        
                            success: function(response){
                                console.log(response);
                                window.location.href = "pendientes_deposito.php?ret="+retorno;
                                $('#respuesta').html(data);
                            },
                            error: function(error){
                                console.log(error);
                            }
                        });
                    }
                }
            }
        });
          
        return false;
    });
    </script>
</BODY>
</HTML>