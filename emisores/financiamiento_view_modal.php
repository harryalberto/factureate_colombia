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
?>
<HTML>
<HEAD>
<?php
    require("../lib/head.php");
    $acceso = 'FINANCIAMIENTO';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(p_accion){
            var validacion = 0;
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_subasta = new subasta;

$arr_factura = $obj_factura->get_datos_financiamiento($_GET['factura_id']);
$arr_propuestas = $obj_subasta->get_subasta_posiciones($arr_factura['subasta_id']);

$fvencimiento_t = strtotime($arr_factura['facfvencimiento']);
$fvencimiento = date('d-m-Y',$fvencimiento_t);
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <div class="contenedor_formulario" style="height: 70%;">

        <!--################### CABECERA -->
        <div class="contenedor_formulario_column">
            <span class="icon-coin-dollar" style="font-size:30px;color:var(--color-verde);margin-right: 10px;"></span></li>
            <div class="formulario_grupo_row" style="width:100px;">
                <label>ID OPERACION</label>
                <label style="color:var(--color-azulv2);font-size: 12px;"><?php echo $_GET['factura_id']?></label>
            </div>
            <div class="formulario_grupo_row" style="width:200px;">
                <label>CLIENTE</label>
                <label style="color:var(--color-azulv2);font-size: 12px;"><?php echo $arr_factura['faccliente'];?></label>
            </div>
            <div class="formulario_grupo_row" style="width:200px;">
                <label>ESTADO</label>
                <label style="color:var(--color-azulv2);font-size: 12px;"><?php echo $arr_factura['e_financiamiento'];?></label>
            </div>
        </div>

        <div style="overflow:hidden;background-color:#555555;height:1px;width: 100%;"></div>

        <!--#################### INFORMACION DEL FINANCIAMIENTO -->
<?php
    if ($arr_factura['e_financiamiento_id'] == 18){
        $label_fecha = 'F SUBASTA';
        $f_finan = date('d-m-Y',strtotime($arr_factura['subfcreacion']));
    } else {
        $label_fecha = 'F FINAN';
        $f_finan = date('d-m-Y',strtotime($arr_factura['f_financiamiento']));
    }
?>
        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="factura">FACTURA</label>
                <input type="text" id="factura" class="formulario_control" value="<?=$arr_factura['facnumero']?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="f_vencimiento">F VCTO</label>
                <input type="text" id="f_vencimiento" class="formulario_control" value="<?=$fvencimiento?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="f_finan"><?php echo $label_fecha;?></label>
                <input type="text" id="f_finan" class="formulario_control" value="<?=$f_finan?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:100px;">
                <label for="moneda">MONEDA</label>
                <input type="text" id="moneda" class="formulario_control" value="<?=$arr_factura['facmoneda']?>" readonly>
            </div>
        </div>

        <div class="contenedor_formulario_column" style="margin-bottom: 10px;">
            <div class="formulario_grupo_row" style="width:150px;">
                <label for="monto_factura">MONTO FACTURA</label>
                <input type="text" id="monto_factura" style="text-align: right;" class="formulario_control" value="<?=number_format($_GET['monto'],2,'.',',')?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:150px;">
                <label for="monto_adelanto">MONTO ADELANTO</label>
                <input type="text" id="monto_adelanto" style="text-align: right;" class="formulario_control" value="<?=number_format($arr_factura['submonto_fin'],2,'.',',')?>" readonly>
            </div>
            <div class="formulario_grupo_row" style="width:150px;">
                <label for="remanente">REMANENTE APROX</label>
                <input type="text" id="remanente" style="text-align: right;" class="formulario_control" value="<?=number_format($arr_factura['submonto_rem'],2,'.',',')?>" readonly>
            </div>
        </div>

        <div style="overflow:hidden;background-color:#555555;height:1px;width: 100%;"></div>

        <!--############### PROPUESTAS RECIBIDAS -->
        <div style="margin-top: 20px;width:100%;float:left;font-weight: bold;color:var(--color-azulv2);font-size: 12px;width:300px;float:left;">PROPUESTAS RECIBIDAS</div>

        <div style="margin-top: 10px;width:100%;float:left;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">MONTO</th>
                    <th scope="col">PORCENTAJE</th>
                    <th scope="col">% INTERES ANUAL</th>
                </tr></thead>
                <tbody>
<?php
    for ($i=0; $i<count($arr_propuestas); $i++){
        $porcentaje = number_format(100 * $arr_propuestas[$i]['posicion_porc'],0,'.',',');
        $tia = number_format(100 * $arr_propuestas[$i]['tia'],2,'.',',');
        $monto = number_format($arr_propuestas[$i]['posicion'],2,'.',',');

        echo '      <tr>
                        <td data-label="ID">'.$arr_propuestas[$i]['propuestaid'].'</td>
                        <td data-label="MONTO">'.$monto.'</td>
                        <td data-label="PORCENTAJE">'.$porcentaje.' %</td>
                        <td data-label="% INTERES ANUAL">'.$tia.' %</td>
                    </tr>';
    }
?>
                </tbody>
            </table>
        </div>
    </div>
    <!------ END CUERPO VARIABLE ------>
    
</BODY>
</HTML>