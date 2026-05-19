<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/factura.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'FINANCIAMIENTO';
    require("../lib/valida-acceso.php");
?>
    <!-- FUNCIONES DE JS -->
     
</HEAD>
<?
/*####################################################
########################## LOGICA */
$obj_finan = new factura;
$obj_mae = new maestros;

if ($_GET['ffid'] > 0){ 
    $v_arr_finan = $obj_finan->get_financiamiento_detalle($_GET['ffid']);
    $v_arr_emp = $obj_mae->get_datos_empresa_full($v_arr_finan['cliente_id']);
    $v_arr_com = $obj_finan->comunica_pagador($v_arr_finan['cliente_id'], $v_arr_finan['factura_id']);
    $varr_parametros = $obj_mae->get_parametros();
    
    $t_fpago = strtotime($v_arr_finan['fpago']);
    $v_fvencimiento = date('d-m-Y',$t_fpago);
    $v_fhoy_en = date('Y-m-d');
    $v_dt_hoy = new DateTime($v_fhoy_en);

    if ($v_arr_finan['confirma_pago'] == 'SI'){
        $t_fcompromiso = strtotime($v_arr_finan['f_confirmacion']);
        $v_fcompromiso = date('d-m-Y',$t_fcompromiso);
        // calculo la diferencia
        $v_fcompromiso_en = date('Y-m-d',$t_fcompromiso);
        $v_dt_compromiso = new DateTime($v_fcompromiso_en);
        $arr_diff_xvencer = $v_dt_hoy->diff($v_dt_compromiso);
        $v_dias_xvencer = $arr_diff_xvencer->days;
    } else{
        $v_fpago_en = date('Y-m-d',$t_fpago);
        $v_dt_fpago = new DateTime($v_fpago_en);
        $arr_diff_xvencerxpago = $v_dt_hoy->diff($v_dt_fpago);
        $v_dias_xvencer = $arr_diff_xvencerxpago->days;
        $t_fcompromiso = strtotime($v_arr_finan['fpago']);
        $v_fcompromiso_en = date('Y-m-d',$t_fcompromiso);
        $v_dt_compromiso = new DateTime($v_fcompromiso_en);
    }
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Santo_Domingo");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO PRINCIPAL ------>
    <div class="frmtransaccion" style="font-size:12px;">
        <form name='frm_modal' method='post' id='frm' enctype="multipart/form-data">
            <input type="hidden" name="facturaid" id="facturaid" value="<?=$v_arr_finan['factura_id']?>">
            <input type="hidden" name="finan_id" id="finan_id" value="<?=$_GET['ffid']?>">
            <input type="hidden" name="empresaid" id="empresaid" value="<?=$v_arr_finan['cliente_id']?>">
            <input type="hidden" name="fvencimiento" id="fvencimiento" value="<?=$v_arr_finan['fpago']?>">
            <input type="hidden" name="retorno" value="finan_xestado.php?estado_id=27">
            <input type="hidden" name="accion" id="accion">
            <input type="hidden" name="maximo_prorroga" id="maximo_prorroga" value="<?=$varr_parametros['MAXIMO PRORROGA']['valornum']?>">
            <input type="hidden" name="dif_monto_porc" id="dif_monto_porc" value="<?=$varr_parametros['DIFERENCIA X RETENCION']['valornum']?>">
            <input type="hidden" name="monto_pagado" id="monto_pagado" value="<?=$v_arr_finan['monto_pagado']?>">
            <input type="hidden" name="monto_factura" id="monto_factura" value="<?= $v_arr_finan['monto_factura']?>">
            <input type="hidden" name="estado_finan_id" id="estado_finan_id" value="<?= $v_arr_finan['estado_finan_id']?>">
            <input type="hidden" name="perfil_id" id="perfil_id" value="<?= $_SESSION['user']['perfilid']?>">
        <!-- datos del financiamiento -->
        <ul>
            <li><span class="icon-coin-dollar" style="font-size:35px;color:#1F9A8E;"></span></li>
            <li>
                <ul>
                    <li style="margin-left:10px;font-weight: bold;width:180px;">ID OPERACION</li>
                    <li style="font-weight: bold;width:290px;">VENDEDOR</li>
                    <li style="font-weight: bold;width:200px;">PAGADOR</li>
                </ul>
                <ul>
                    <li style="margin-left:10px;width:180px;"><?echo $v_arr_finan['factura_id'];?></li>
                    <li style="width:290px;"><?echo $v_arr_finan['emisor_nombre'];?></li>
                    <li style="width:200px;"><?echo $v_arr_finan['cliente_nombre'];?></li>
                    <input type="hidden" name="cliente_nombre" id="cliente_nombre" value="<?=$v_arr_finan['cliente_nombre']?>">
                    <input type="hidden" name="emisor_nombre" id="emisor_nombre" value="<?=$v_arr_finan['emisor_nombre']?>">
                </ul>
            </li>
        </ul>
        
        <hr>

        <ul style="margin-top:0px;">
            <li style="font-weight:bold;">INFORMACION DE LA FACTURA:</li>
        </ul>
    <?php
        if ($v_arr_finan['confirma_pago'] == 'SI') $v_fconfirmacion = $v_fcompromiso;
        else $v_fconfirmacion = 'PENDIENTE';
    ?>
        <ul style="margin:0px;padding:0px;">
            <li style="font-weight:bold;margin-left:5px;width:150px;">FACTURA NRO:</li>
            <li style="font-weight:bold;margin-left:5px;width:205px;">MONTO FACTURA:</li>
            <li style="font-weight:bold;margin-left:5px;width:130px;">F VENCIMIENTO:</li>
            <li style="font-weight:bold;margin-left:5px;width:135px;">CONFIRMA PAGO:</li>
        </ul>
        <ul>
            <li style="margin-left:5px;width:150px;"><input type="text" name="factura_nro" id="factura_nro" value="<?=$v_arr_finan['factura_numero']?>" class="frminput_text_off" style="text-align:center;" readonly></li>
            <li style="margin-left:5px;width:205px;"><input type="text" name="monto_factura_view" id="monto_factura_view" size="30" value="<?echo $v_arr_finan['simbolo'].' '.number_format($v_arr_finan['monto_factura'],2,'.',',');?>" class="frminput_text_off" style="text-align:right;" readonly></li>
            <li style="margin-left:5px;width:130px;"><input type="date" name="f_vencimiento" id="f_vencimiento" size="15" value="<?=$v_arr_finan['fpago']?>" class="frminput_text_off" style="text-align:center;margin-right:20px;" readonly></li>
            <li style="margin-left:5px;width:135px;"><input type="text" name="factura_id" size="15" value="<?=$v_fconfirmacion?>" class="frminput_text_off" style="text-align:center;" readonly></li>
    <?
        if ($v_dias_xvencer == 0) 
            echo '
            <li style="width:80px;background-color: #b30a1f;color:#ffffff;text-align:center;">VENCE HOY</li>';
        elseif ($v_dt_compromiso < $v_dt_hoy) 
            echo '
            <li style="width:80px;background-color: #b30a1f; color: #ffffff;text-align:center;">VENCIDO</li>';
    ?>
        </ul>

        <hr>
        <!--========= DATOS DEL PAGADOR -->
        <ul style="margin-top:0px;">
            <li style="font-weight:bold;">INFORMACION DEL OBLIGADO AL PAGO:</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:280px;">REPRESENTANTE LEGAL:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:130px;">E-MAIL:</li>
        </ul>
        <ul>
            <li><input type="text" name="representante" value="<?=$v_arr_emp['nombre_repre']?>" class="frminput_text_off" size="40" readonly></li>
            <li><input type="text" name="mail_repre" value="<?=$v_arr_emp['email_repre']?>" class="frminput_text_off" size="25" readonly></li>
        </ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:280px;">CONTACTO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:173px;">E-MAIL:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:143px;">TELEFONO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:135px;">PAGINA WEB:</li>
        </ul>
        <ul>
            <li><input type="text" name="contacto" value="<?=$v_arr_emp['nombre_contacto']?>" class="frminput_text_off" size="40" readonly></li>
            <li><input type="text" name="mail_contacto" value="<?=$v_arr_emp['email_contacto']?>" class="frminput_text_off" size="25" readonly></li>
            <li><input type="text" name="telefono_contacto" value="<?=$v_arr_emp['telf_contacto']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="pagina_web" value="<?=$v_arr_emp['paginaweb']?>" class="frminput_text_off" size="25" readonly></li>
        </ul>
        
        <hr>
        <!-- ========= ACUERDO DE PAGO -->
        <ul style="margin-top:0px;">
            <li style="font-weight:bold;">ACUERDO DE PAGO:</li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:200px;">FECHA DE ACUERDO DE PAGO:</li>
            <li style="padding-left:5px;padding-right:5px;width:100px;"><input type='date' name='fpago' id="fpago" value='<?=$v_fhoy_en?>' min='1900-01-01' class="frminput_text"></li>
        </ul>
        <!-- relacion de comunicaciones -->
        <ul>
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">FECHA</th>
                    <th scope="col">NOMBRE</th>
                    <th scope="col">COMUNICACION</th>
                </tr></thead>
                <tbody>
        <?
        if (count($v_arr_com) == 0) echo '
                    <tr>
                        <td colspan="4" style="border: 1px solid;padding:5px;text-align:center;">No hay registros</td>
                    </tr>';
        else{ 
            for ($i=0; $i<count($v_arr_com); $i++){
                $t_fecha_com = strtotime($v_arr_com[$i]['fcomunica']);
                $v_fecha_com = date('d-m-Y',$t_fecha_com).' '.$v_arr_com[$i]['hcomunica'];
                echo ' 
                    <tr>
                        <td data-label="ID">'.$v_arr_com[$i]['comunica_id'].'</td>
                        <td data-label="FECHA">'.$v_fecha_com.'</td>
                        <td data-label="NOMBRE">'.$v_arr_com[$i]['nombre'].'</td>
                        <td data-label="COMUNICACION">'.$v_arr_com[$i]['comunicacion'].'</td>
                    </tr>';
            }
        }
        ?>
                </tbody></table>
        </ul>            
        
        <hr>
        <!-- ========= ACUERDO DE PAGO -->
        <ul style="margin-top:0px;">
            <li style="font-weight:bold;">PAGOS REALIZADOS POR EL PAGADOR:</li>
        </ul>
        <ul style="color:var(--color-rojo);font-weight: bold;"><li>MONTO PAGADO: <?php echo $v_arr_finan['simbolo'].' '.number_format($v_arr_finan['monto_pagado'],2,'.',',');?></li></ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">NRO OPERACION:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">MONEDA:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:150px;">MONTO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:100px;">F PAGO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:135px;">H PAGO:</li>
        </ul>
    <?php
        $v_monto_pendiente = $v_arr_finan['monto_factura'] - $v_arr_finan['monto_pagado'];
    ?>
        <ul>
            <li><input type="text" name="nro_operacion" id="nro_operacion" placeholder="# operacion banco" class="frminput_text"></li>
            <li><input type="text" name="moneda" id="moneda" value="<?=$v_arr_finan['moneda']?>" class="frminput_text_off" readonly></li>
            <li><input type="number" name="monto_pago" id="monto_pago" value="<?=$v_monto_pendiente?>" class="frminput_text" style="text-align:right;"></li>
            <li><input type="date" name="fecha_pago" id="fecha_pago" value="<?=$v_fhoy_en?>" class="frminput_text"></li>
            <li><input type="text" name="hora_pago" id="hora_pago" value="00:00:00" class="frminput_text" style="text-align:center" size="10"></li>
            <input type="hidden" name="moneda_id" id="moneda_id" value="<?=$v_arr_finan['moneda_id']?>">
        </ul>

    <?
    //============== BOTONERA
    if ($v_arr_finan['estado_finan_id'] == 27){     // EN PROCESO
    ?>
        <hr>
        <ul style="margin-top:10px;">
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraAcuerdo('acuerdo')"><span class="icon-point-up"></span> Acuerdo</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraPago('pago')"><i class="fa-solid fa-credit-card"></i> RegPago</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraLiquidar('liquidar')"><span class="icon-coin-dollar"></span> Liquidar</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraCobranza('cobranza')"><span class="icon-point-down"></span> Cobranza</button>
        </ul>
    <?
    } elseif($v_arr_finan['estado_finan_id'] == 51){    // PRE LIQUIDADA
        if ($_SESSION['user']['perfilid'] == 14 || $_SESSION['user']['perfilid'] == 15){        //CFO o CEO
    ?>            
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraAcuerdo('acuerdo')"><span class="icon-point-up"></span> Acuerdo</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraPago('pago')"><i class="fa-solid fa-credit-card"></i> RegPago</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraLiquidar('liquidar')"><span class="icon-coin-dollar"></span> Liquidar</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraCobranza('cobranza')"><span class="icon-point-down"></span> Cobranza</button>
    <?php
        } else{
    ?>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraAcuerdo('acuerdo')"><span class="icon-point-up"></span> Acuerdo</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraPago('pago')"><i class="fa-solid fa-credit-card"></i> RegPago</button>
            <button type="button" class="btn btn-primary" style="background-color:var(--color-azulv2);" onclick="registraCobranza('cobranza')"><span class="icon-point-down"></span> Cobranza</button>
    <?php
        }
    }
    ?>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <script>
        function registraAcuerdo(parametro){
            var v_accion=parametro;
            var v_fpago=$('#fpago').val();
            var v_fvencimiento=$('#f_vencimiento').val();
            var v_empresaid=$('#empresaid').val();
            var v_facturaid=$('#facturaid').val();
            var v_fvencimiento=$('#fvencimiento').val();
            var v_finan_id=$('#finan_id').val();
            
            var acuerdo = new Date(v_fpago);
            var vencimiento = new Date(v_fvencimiento);
            var maximo = $('#maximo_prorroga').val();
            
            if (vencimiento > acuerdo){
                var diff = vencimiento.getTime() - acuerdo.getTime();
                var diferencia = Math.round(diff / (1000 * 60 * 60 * 24));
                    
                if (diferencia > 7){
                    var confirma = confirm("Esta anticipando el pago mas de 7 dias, esta seguro?");

                    if (confirma == true){
                        validacion = 1;
                    }
                } else validacion = 1;
            } else{
                var diff = acuerdo.getTime() - vencimiento.getTime();
                var diferencia = Math.round(diff / (1000 * 60 * 60 * 24));
                    
                if (diferencia > maximo){
                    var texto = "No esta permitido postergar el pago tantos dias";
                    alert(texto);
                } else validacion = 1;
            }

            if (validacion > 0){
                $.ajax({
                        url:"detalle_finan_proceso.php",
                        type:'post',
                        data:{
                            "accion":v_accion,
                            "fpago":v_fpago,
                            "empresaid":v_empresaid,
                            "facturaid":v_facturaid,
                            "fvencimiento":v_fvencimiento,
                            "finan_id":v_finan_id
                        },
                        success:function(data,status){
                            $('#myModal').fadeIn(1000).html(data);
                            $('#myModal').modal('hide');
                            filtrar();
                        }
                });
            }
        }

        function registraCobranza(parametro){
            var v_accion=parametro;
            var v_fvencimiento=$('#f_vencimiento').val();
            var v_fhoy = new Date();
            var vencimiento = new Date(v_fvencimiento);

            if (vencimiento < v_fhoy) alert('No puede enviar a COBRANZA una factura que aun no vence');
            else {
                $.ajax({
                        url:"detalle_finan_proceso.php",
                        type:'post',
                        data:{
                            "accion":v_accion
                        },
                        success:function(data,status){
                            $('#myModal').fadeIn(1000).html(data);
                            $('#myModal').modal('hide');
                            filtrar();
                        }
                });
            }
        }

        function registraLiquidar(parametro){
            var v_accion=parametro;
            var v_facturaid=$('#facturaid').val();
            var v_operacion=$('#nro_operacion').val();
            var v_monedaid=$('#moneda_id').val();
            var v_monto_pago=$('#monto_pago').val();
            var v_fecha_pago=$('#fecha_pago').val();
            var v_hora_pago=$('#hora_pago').val();

            var v_diferencia_porc=Number($('#dif_monto_porc').val());
            var v_monto_pagado=Number($('#monto_pagado').val());
            var v_monto_factura=Number($('#monto_factura').val());
            var v_estado_finan_id=$('#estado_finan_id').val();
            var v_perfil_id=$('#perfil_id').val();
            var v_no_cumple, v_liquidar;

            if ((v_monto_factura * v_diferencia_porc) > v_monto_pagado) v_no_cumple = 1;
            else v_no_cumple = 0;

            if (v_perfil_id == 14 || v_perfil_id == 15){
                if (confirm("El monto pagado es menor al permitido, aun asi esta seguro de continuar con la liquidación?") == true) v_liquidar = 1;
                else v_liquidar = 0;
            } else{
                if (confirm("Esta tratando de liquidar una operacion por un monto menor al permitido, si continua la operacion quedara PRE LIQUIDADA y el CFO debe aprobar la liquidacion, desea continuar?") == true) v_liquidar = 1;
                else v_liquidar = 0;
            }

            if (v_liquidar > 0){
                $.ajax({
                        url:"detalle_finan_proceso.php",
                        type:'post',
                        data:{
                            "accion":v_accion,
                            "facturaid":v_facturaid,
                            "nro_operacion":v_operacion,
                            "moneda_id":v_monedaid,
                            "monto_pago":v_monto_pago,
                            "fecha_pago":v_fecha_pago,
                            "hora_pago":v_hora_pago
                        },
                        success:function(data,status){
                            $('#myModal').fadeIn(1000).html(data);
                            $('#myModal').modal('hide');
                            filtrar();
                        }
                });
            }
        }

        function registraPago(parametro){
            var v_accion=parametro;
            var v_facturaid=$('#facturaid').val();
            var v_operacion=$('#nro_operacion').val();
            var v_monedaid=$('#moneda_id').val();
            var v_monto_pago=Number($('#monto_pago').val());
            var v_fecha_pago=$('#fecha_pago').val();
            var v_hora_pago=$('#hora_pago').val();
            var v_diferencia_porc=Number($('#dif_monto_porc').val());
            var v_monto_pagado=Number($('#monto_pagado').val());
            var v_monto_factura=Number($('#monto_factura').val());
            
            var v_cliente_nombre = $('#cliente_nombre').val();
            var v_moneda = $('#moneda').val();
            var v_emisor_nombre = $('#emisor_nombre').val();
            var v_factura_nro = $('#factura_nro').val();
            var v_finan_id = $('#finan_id').val();
            
            var nro_operacion = document.frm_modal.nro_operacion.value;
            var monto_pago = document.frm_modal.monto_pago.value;

            if (v_operacion == '' || v_monto_pago == 0) alert('Debe completar los datos del pago realizado por el OP');
            else{
                var v_exceso = v_monto_factura - (v_monto_pagado + v_monto_pago);

                if (v_exceso < 0) alert('El monto pagado hasta la fecha sumado con el monto que desea registrar supera el monto de la factura lo cual es un error, verifique por favor');
                else {
                    $.ajax({
                            url:"detalle_finan_proceso.php",
                            type:'post',
                            data:{
                                "accion":v_accion,
                                "facturaid":v_facturaid,
                                "nro_operacion":v_operacion,
                                "moneda_id":v_monedaid,
                                "monto_pago":v_monto_pago,
                                "fecha_pago":v_fecha_pago,
                                "hora_pago":v_hora_pago,
                                "diferencia":v_diferencia_porc,
                                "monto_pagado":v_monto_pagado,
                                "monto_factura":v_monto_factura,
                                "cliente_nombre" : v_cliente_nombre,
                                "moneda" : v_moneda,
                                "emisor_nombre" : v_emisor_nombre,
                                "factura_nro" : v_factura_nro,
                                "finan_id" : v_finan_id
                            },
                            success:function(data,status){
                                $('#myModal').fadeIn(1000).html(data);
                                $('#myModal').modal('hide');
                                filtrar();
                            }
                    });
                }
            }
        }
    </script>
</BODY>
</HTML>