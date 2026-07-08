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
    <script type="text/javascript">
        function acciones(accion){
            if (document.frm.mensaje.value == '') alert("El mensaje no puede estar vacio");
            else{
                document.frm.action = 'detalle_comunica_proceso.php';
                document.frm.accion.value = accion;
                document.frm.submit();
            }
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_mae = new maestros;

$v_arr_empresa = $obj_mae->get_datos_empresa_full($_GET['eid']);
$v_arr_comunica = $obj_factura->comunica_pagador($_GET['eid'], $_GET['fid']);
$v_arr_factura = $obj_factura->get_datos_factura($_GET['fid']);

$v_fvence_t = strtotime($v_arr_factura['fvencimiento']);
$v_fvence = date('d-m-Y',$v_fvence_t);
$v_fvence_en = date('Y-m-d',$v_fvence_t);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <!--<div style="overflow:hidden;font-size: 12px;color:#000000;padding:5px;margin:auto;">-->
    <div class="frmtransaccion" style="color:#000000;">
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:50px;display: block;padding:5px;">Pagador:</li>
            <li class="frm_input_off" style="width:500px;display: block;"><?php echo $v_arr_empresa['nombre'];?></li>
            <li style="width:50px;display: block;padding:5px;">RNC:</li>
            <li class="frm_input_off" style="width:100px;display: block;"><?php echo $v_arr_empresa['identificacion'];?></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:50px;display: block;padding:5px;">Direcci&oacute;n:</li>
            <li class="frm_input_off" style="width:500px;display: block;"><?php echo $v_arr_empresa['direccion'];?></li>
            <li style="width:50px;display: block;padding:5px;">Web:</li>
            <li class="frm_input_off" style="width:200px;display: block;"><?php echo $v_arr_empresa['paginaweb'];?></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:50px;display: block;padding:5px;">Sector:</li>
            <li class="frm_input_off" style="width:300px;display: block;"><?php echo $v_arr_empresa['sector'];?></li>
            <li style="width:50px;display: block;padding:5px;">Actividad:</li>
            <li class="frm_input_off" style="width:400px;display: block;"><?php echo $v_arr_empresa['actividad'];?></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:80px;display: block;padding:5px;">Representante:</li>
            <li class="frm_input_off" style="width:300px;display: block;"><?php echo $v_arr_empresa['nombre_repre'];?></li>
            <li style="width:50px;display: block;padding:5px;">Email:</li>
            <li class="frm_input_off" style="width:150px;display: block;"><?php echo $v_arr_empresa['email_repre'];?></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:80px;display: block;padding:5px;">Contacto:</li>
            <li class="frm_input_off" style="width:300px;display: block;"><?php echo $v_arr_empresa['nombre_contacto'];?></li>
            <li style="width:50px;display: block;padding:5px;">Email:</li>
            <li class="frm_input_off" style="width:150px;display: block;"><?php echo $v_arr_empresa['email_contacto'];?></li>
            <li style="width:50px;display: block;padding:5px;">Telefono:</li>
            <li class="frm_input_off" style="width:100px;display: block;"><?php echo $v_arr_empresa['telf_contacto'];?></li>
        </ul>
        <!-- datos de la factura -->
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:50px;display: block;padding:5px;">Factura:</li>
            <li class="frm_input_off" style="width:100px;display: block;"><?php echo $v_arr_factura['factura'];?></li>
            <li style="width:80px;display: block;padding:5px;">F. Vencimiento:</li>
            <li class="frm_input_off" style="width:100px;display: block;"><?php echo $v_fvence;?></li>
            <li style="width:50px;display: block;padding:5px;">Moneda:</li>
            <li class="frm_input_off" style="width:100px;display: block;"><?php echo $v_arr_factura['moneda'];?></li>
            <li style="width:50px;display: block;padding:5px;">Monto:</li>
            <li class="frm_input_off" style="width:100px;display: block;"><?php echo number_format($v_arr_factura['total'],2,'.',',');?></li>
        </ul>
        <!-- comunicaciones -->
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:50px;display: block;padding:5px;font-size: 12px;font-weight: bold;">Mensajes:</li>
        </ul>
        <?php
        if (count($v_arr_comunica) > 0){
            for ($i=0; $i<count($v_arr_comunica); $i++){
                $v_fcomu_t = strtotime($v_arr_comunica[$i]['fcomunica']);
                $v_fcomu = date('d-m-Y',$v_fcomu_t).' '.$v_arr_comunica[$i]['hcomunica'];

                echo '<ul style="list-style:none;overflow:hidden;">
                        <li style="width:50px;display: block;padding:5px;">Fecha:</li>
                        <li class="frm_input_off" style="width:150px;display: block;">'.$v_fcomu.'</li>
                        <li style="width:50px;display: block;padding:5px;">Nombre:</li>
                        <li class="frm_input_off" style="width:150px;display: block;">'.$v_arr_comunica[$i]['nombre'].'</li>
                        <li style="width:50px;display: block;padding:5px;">Mensaje:</li>
                        <li class="frm_input_off" style="width:300px;display: block;">'.$v_arr_comunica[$i]['comunicacion'].'</li>
                      </ul>';
            }
        } else echo '<ul style="list-style:none;overflow:hidden;"> No hay comunicaciones </ul>';
        ?>
        <form name='frm' method='post' id='frm' enctype="multipart/form-data">
            <input type="hidden" name="facturaid" value="<?=$_GET['fid']?>">
            <input type="hidden" name="empresaid" value="<?=$_GET['eid']?>">
            <input type="hidden" name="fvencimiento" value="<?=$v_arr_factura['fvencimiento']?>">
            <input type="hidden" name="retorno" value="facturasxvencer">
            <input type="hidden" name="accion" value="">
        <ul style="list-style:none;overflow:hidden;">
            <li style="width:50px;display: block;padding:5px;">Nuevo Mensaje:</li>
            <li style="width:300px;display: block;"><input type="text" name="mensaje" style="width:300px;"></li>
            <li style="width:50px;display: block;padding:5px;">Contacto:</li>
            <li style="width:150px;display: block;"><input type="text" name="contacto" style="width:150px;"></li>
            <li style="width:50px;display: block;padding:5px;">F. Pago:</li>
            <li style="width:100px;display: block;"><input type='date' name='fpago' value='<?=$v_fvence_en?>' min='1900-01-01'></li>
        </ul>
        <ul style="list-style:none;overflow:hidden;">
            <li class="botontransaccion" style="width:100px;display: block;"><a href=javascript:acciones("envia")>Registrar</a></li>
            <li class="botontransaccion" style="width:100px;display: block;"><a href=javascript:acciones("confirma")>Confirmar</a></li>
        </ul>
        </form>
    </div>
    <!------ END CUERPO VARIABLE ------>
    
</BODY>
</HTML>