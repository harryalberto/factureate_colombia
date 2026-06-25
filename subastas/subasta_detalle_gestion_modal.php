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
            document.frm_modal.accion.value = accion;
            
            if (accion == 'terminar' || accion == 'anular' || accion == 'extender'){
                document.frm_modal.action = 'subasta_gestion_proceso.php';
                document.frm_modal.submit();
            }
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_factura = new factura;
$obj_mae = new maestros;
$objsubasta = new subasta;

$arrsubasta = $objsubasta->get_subasta($_GET['subastaid']);
$arrpropuestas = $objsubasta->get_subasta_posiciones($_GET['subastaid']);
$varr_parametros = $obj_mae->get_parametros();

if ($arrsubasta['estadosubastaid'] == 31) $v_estado_compensa = $arrsubasta['estadosubasta'].' - '.$arrsubasta['estado_compensa'];
else $v_estado_compensa = $arrsubasta['estadosubasta'];
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Santo_Domingo");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
        <input type="hidden" name="subastaid" value="<?=$_GET['subastaid']?>">
        <input type="hidden" name="accion">
        <input type="hidden" name="emisor_id" value="<?=$arrsubasta['emisorid']?>">
        <input type="hidden" name="emisor_correo" value="<?=$arrsubasta['emisor_correo']?>">
        <input type="hidden" name="porc_minimo" id="porc_minimo" value="<?=$varr_parametros['MINIMO FINANCIA']['valornum']?>">
    <div class="frmtransaccion" style="font-size:12px;">
        <ul>
            <li style="margin-left:32px;font-weight: bold;width:180px;">ID OPERACION</li>
            <li style="font-weight: bold;width:200px;">NRO FACTURA</li>
            <li style="font-weight: bold;width:200px;">PAGADOR</li>
        </ul>
        <ul>
            <li><span class="icon-file-text" style="font-size:25px;color:#1F9A8E;"></span></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?php echo $arrsubasta['facturaid'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:150px;"><?php echo $arrsubasta['facnumero'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:100px;"><?php echo $arrsubasta['cliente'];?></li>
            <input type="hidden" name="factura_id" value="<?=$arrsubasta['facturaid']?>">
            <input type="hidden" name="factura_numero" value="<?=$arrsubasta['facnumero']?>">
            <input type="hidden" name="cliente_nombre" value="<?=$arrsubasta['cliente']?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">INFORMACION DE LA SUBASTA:</li>
        </ul>
        <ul>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:280px;">EMISOR:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:210px;">ESTADO COMPENSADO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:180">TIPO:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;">T SUBASTA:</li>
        </ul>
    <?php
        $v_fecha_hoy = date('Y-m-d H:i:s');
        $v_dt_hoy = new DateTime($v_fecha_hoy);
        $v_dt_subasta = new DateTime($arrsubasta['f_subasta'].' '.$arrsubasta['h_subasta']);
        $v_diferencia = $v_dt_subasta->diff($v_dt_hoy);
    ?>
        <ul>
            <li><input type="text" name="emisor" size="40" value="<?=$arrsubasta['emisor']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="estado_compensa" size="30" value="<?=$v_estado_compensa?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo_financiamiento" size="20" value="<?=$arrsubasta['tipofinanciamientonom']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="t_subasta" size="20" value="<?=$v_diferencia->days.' dias '.$v_diferencia->h.' horas'?>" class="frminput_text_off" readonly></li>
            <input type="hidden" name="emisor_id" value="<?=$arrsubasta['emisorid']?>">
        </ul>
        <ul>
            <li style="font-weight:bold;width:280px;padding-left:5px;padding-right:5px;">MONEDA:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:210px;">MONTO SUBASTA:</li>
        </ul>
        <ul>
            <li><input type="text" name="moneda" size="40" value="<?=$arrsubasta['moneda']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="monto_subasta_l" size="30" value="<?=number_format($arrsubasta['montofin'],2,'.',',')?>" class="frminput_text_off" readonly></li>
            <input type="hidden" name="monto_subasta" value="<?=$arrsubasta['montofin']?>">
        </ul>
<?php
    if ($arrsubasta['estadosubastaid'] == 24){      //==== SUBASTA ACTIVA
        echo '
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">INVERSORES:</li>
        </ul>
        <ul style="overflow:hidden;list-style:none;">
            <table class="tabla_resize">
                <thead><tr>
                    <th scope="col">ID</th>
                    <th scope="col">MONTO POSICION</th>
                    <th scope="col">PARTICIPACION</th>
                </tr></thead>
                <tbody>';

        $v_porc_propuestas = 0;

        for ($i=0; $i<count($arrpropuestas); $i++){
            //($arrpropuestas[$i]['grupofinal'] == $arrsubasta['grupowinid'])
            if ($arrpropuestas[$i]['estadoid'] == 1){
                $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;
                echo '          
                    <tr>
                        <td data-label="ID">'.$arrpropuestas[$i]['propuestaid'].'</td>
                        <td data-label="MONTO POSICION">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                        <td data-label="PARTICIPACION">'.number_format($porcentaje,2,'.',',').' %</td>
                    </tr>';
            }

            $v_porc_propuestas = $v_porc_propuestas + $arrpropuestas[$i]['posicion_porc'];
        }

        echo '<input type="hidden" name="porc_propuestas" id="porc_propuestas" value="'.$v_porc_propuestas.'">';

        echo '  </tbody>
            </table>
        </ul>';
        //==================== PARA LA EXTENCION DE TIEMPO DE LA SUBASTA
        $v_extencion = '
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">EXTENSION DE SUBASTA:</li>
        </ul>
        <ul>
            <li style="font-weight:bold;width:150px;padding-left:5px;padding-right:5px;">EXTENDER SUBASTA:</li>
            <li><input type="number" name="dias_extension" class="frminput_text" min="1" step="1" style="width:50px;"></li>
            <li style="font-weight:bold;width:50px;padding-left:5px;padding-right:5px;">DIAS:</li>
        </ul>';
    } else $v_extencion = '';

    if ($arrsubasta['estadosubastaid'] == 31 || $arrsubasta['estadosubastaid'] == 26 || $arrsubasta['estadosubastaid'] == 25){      //==== SUBASTA EN COMPENSACION O COMPENSADA O LIQUIDADA
        $v_count_perdedores = 0;

        $v_print_ganadores = '
                <ul style="overflow:hidden;list-style:none;">
                    <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">ID</th>
                        <th scope="col">MONTO POSICION</th>
                        <th scope="col">MONTO DISPONIBLE</th>
                        <th scope="col">PARTICIPACION</th>
                        <th scope="col">TIA</th>
                        <th scope="col">ESTADO</th>
                    </tr></thead>
                    <tbody>';
        $v_print_perdedores = '
                <ul style="overflow:hidden;list-style:none;">
                    <table class="tabla_resize">
                    <thead><tr>
                        <th scope="col">ID</th>
                        <th scope="col">MONTO POSICION</th>
                        <th scope="col">MONTO DISPONIBLE</th>
                        <th scope="col">PARTICIPACION</th>
                        <th scope="col">TIA</th>
                        <th scope="col">ESTADO</th>
                        <th scope="col">TURNO</th>
                    </tr></thead>
                    <tbody>';

        for ($i=0; $i<count($arrpropuestas); $i++){
            $v_tia_l = $arrpropuestas[$i]['tia'] * 100;
            $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;

            if ($arrpropuestas[$i]['grupofinal'] == $arrsubasta['grupowinid']){     // PROPUESTA QUE PERTENECE AL GRUPO GANADOR
                $v_tia_final = $arrpropuestas[$i]['tiafinal'];
                $v_tia_final_l = $v_tia_final * 100;
                
                $v_print_ganadores .= '          
                    <tr>
                        <td data-label="ID">'.$arrpropuestas[$i]['propuestaid'].'</td>
                        <td data-label="MONTO POSICION">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                        <td data-label="MONTO DISPONIBLE">'.number_format($arrpropuestas[$i]['fondo_disponible'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                        <td data-label="PARTICIPACION">'.$porcentaje.' %</td>
                        <td data-label="TIA">'.$v_tia_l.' %</td>
                        <td data-label="ESTADO">'.$arrpropuestas[$i]['estado'].'</td>
                    </tr>';
            } else {
                $v_count_perdedores++;
                $v_print_perdedores .= '          
                    <tr>
                        <td data-label="ID">'.$arrpropuestas[$i]['propuestaid'].'</td>
                        <td data-label="MONTO POSICION">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                        <td data-label="MONTO DISPONIBLE">'.number_format($arrpropuestas[$i]['fondo_disponible'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                        <td data-label="PARTICIPACION">'.$porcentaje.' %</td>
                        <td data-label="TIA">'.$v_tia_l.' %</td>
                        <td data-label="ESTADO">'.$arrpropuestas[$i]['estado'].'</td>
                        <td data-label="TURNO">'.$arrpropuestas[$i]['turno'].'</td>
                    </tr>';
            }
        }

        $v_print_ganadores .= '</tbody></table></ul>';
        $v_print_perdedores .= '</tbody></table></ul>';

        echo '
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">PROPUESTAS GANADORAS: (TIA GLOBAL = '.$v_tia_final_l.' %)</li>
        </ul>'.$v_print_ganadores;

        if ($v_count_perdedores > 0)
        echo '
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">PROPUESTAS QUE NO GANARON:</li>
        </ul>'.$v_print_perdedores;
    }
    echo $v_extencion;
?>        
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
        <ul style="margin-top:10px;">
    <?php
        if ($arrsubasta['estadosubastaid'] == 24){  // SUBASTA ACTIVA
            echo '
                <li><button type="button" id="btn_terminar" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="terminarSubasta()">
                <i class="fa-solid fa-flag-checkered"></i> Terminar Subasta</button></li>
                <li><button type="button" id="btn_extender" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="extenderSubasta()">
                <i class="fa-solid fa-plus-minus"></i> Extender Subasta</button></li>';
        }
    ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <script>
        function terminarSubasta(){
            var porc_propuestas = Number($('#porc_propuestas').val());
            var porc_minimo = Number($('#porc_minimo').val());
            var v_continuar = 0;
            document.frm_modal.accion.value = 'terminar';

            if (porc_propuestas < 1){
                if (porc_minimo > porc_propuestas){
                    if (confirm("Si usted termina la subasta esta sera anulada asi como las propuestas porque no alcanzo el minimo a financiar, esta seguro de continuar?") == true) v_continuar = 1;
                } else{
                    if (confirm("Si usted termina la subasta, se enviara una notificacion al EMISOR para que confirme el monto conseguido porque no se consiguio el 100%, esta seguro de continuar?") == true) v_continuar = 1;
                }
            } else v_continuar = 1;

            if (v_continuar == 1){
                var formData = new FormData(document.getElementById("frm_modal"));
                var btn_terminar = document.getElementById("btn_terminar");

                btn_terminar.disabled = true;

                $.ajax({
                    url:"subasta_gestion_proceso_v2.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "html"
                })
                .done(function(rpta){
                    if (rpta == 'ANULADO'){
                        alert('La subasta fue anulada!!');
                    } else{
                        alert('La subasta fue terminada!!');
                    }

                    refresh();
                });
            }
        }

        function extenderSubasta(){
            document.frm_modal.accion.value = 'extender';
            document.frm_modal.action = 'subasta_gestion_proceso.php';
            document.frm_modal.submit();
        }
    </script>
</BODY>
</HTML>