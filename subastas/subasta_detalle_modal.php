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
        function validar(accion,retorno){
            var monto = Number(document.frm.monto.value);
            var tia = Number(document.frm.tia.value);
            var tipofinancia = Number(document.frm.tipofinancia.value);

            if (accion == 'grabar' || accion == 'ofertar'){
                if (monto <= 0){ 
                    alert('Debe ingresar un monto de inversión validao');
                    document.frm.monto.value = 0;
                } else{
                    if (tia <= 0){ 
                        alert('De ingresar una TEA valida');
                        document.frm.tia.value = 0;
                    } else{
                        document.frm.action = '../inversionistas/propuesta_detalle_proceso.php';
                        document.frm.accion.value = accion;
                        document.frm.submit();
                    }
                }
            }
            if (accion == 'anular'){
                    var rpta = confirm("Esta seguro de anular su propuesta?");
                    if (rpta == true){
                        document.frm.action = '../inversionistas/propuesta_detalle_proceso.php';
                        document.frm.accion.value = accion;
                        document.frm.submit();
                    }
            }
        }

        function validapropuesta(accion){
            if (accion == 'tia'){
                var tia = Number(document.frm.tia.value);
                var monto = Number(document.frm.monto.value);
                var dias = Number(document.frm.dias.value);

                if (tia < 0){
                    alert('El valor de la TEA no puede ser menor a CERO, ingrese in valor correcto');
                    document.frm.tia.value = 0;
                }

                var tim = Number(Math.pow((1 + (tia / 100)),Number(1/12)) - 1);
                var tid = Number(Math.pow((1 + tim),Number(1/30)) - 1);
                var ganancia = Number(tid * monto * dias);
                document.frm.ganancia.value = ganancia.toFixed(2);
            }
        }

        function acciones(accion){
            document.frm.accion.value = accion;
            
            if (accion == 'envio_contrato'){
                if (document.frm.link_envio.value == '') alert('Debe ingresar la referencia del envio del contrato al vendedor');
                else{
                    /*var btn_envio = getElementById('');*/
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            if (accion == 'contrato'){
                if (document.frm.contrato.value == '') alert('Debe agregar el archivo del contrato con el vendedor');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            if (accion == 'endoso'){
                if (document.frm.endoso.value == '') alert('Debe agregar el archivo de Endoso');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            
            if (accion == 'liquidar'){
                if (document.frm.transferenciafile.value == '' && document.frm.transferenciapath.value == '') alert('Debe agregar el archivo de transferencia de titularidad');
                else{
                    if (document.frm.fondosfile.value == '' && document.frm.fondospath.value == '') alert('Debe agregar el archivo de transferencia de fondos');
                    else{
                        document.frm.action = 'subasta_gestion_proceso.php';
                        document.frm.submit();
                    }
                }
            }
            if (accion == 'fondos'){
                if (document.frm.fondosfile.value == '') alert('Debe agregar el archivo de transferencia de fondos');
                else{
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
            if (accion == 'terminar' || accion == 'anular'){
                document.frm.action = 'subasta_gestion_proceso.php';
                document.frm.submit();
            }
            
            if (accion == 'cerrar'){ 
                var estados = document.frm.estados.value;
                location.href = 'subastas.php?estados='+estados;
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
$vobj_seg = new seguridad;

$arrsubasta = $objsubasta->get_subasta($_GET['subastaid']);
$arrpropuestas = $objsubasta->get_subasta_posiciones($_GET['subastaid']);
$varr_parametros = $obj_mae->get_parametros();
$varr_factura = $obj_factura->get_datos_factura($arrsubasta['facturaid']);

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    //------ PARTE SUPERIOR ------
    
    //------ PARTE IZQUIERDA ------
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
        <input type="hidden" name="retorno" value="<?=$_GET['retorno']?>">
        <input type="hidden" name="pagina" value="<?=$_GET['pagina']?>">
        <input type="hidden" name="rowcount" value="<?=$_GET['rowcount']?>">
        <input type="hidden" name="subastaid" value="<?=$_GET['subastaid']?>">
        <input type="hidden" name="grupowin" id="grupowin" value="<?=$arrsubasta['grupowinid']?>">
        <input type="hidden" name="accion">
        <input type="hidden" name="cliente_id" id="cliente_id" value="<?=$arrsubasta['clienteid']?>">
        <input type="hidden" name="estado_compensa" id="estado_compensa" value="<?= $arrsubasta['estado_compensa_id'] ?>">
        <input type="hidden" name="con_endoso" id="con_endoso" value="<?= $varr_parametros['REQUERIMIENTO DE ENDOSO']['valornum'] ?>">

    <div class="frmtransaccion" style="font-size:12px;">
        <ul>
            <li style="margin-left:32px;font-weight: bold;width:300px;">ID OPERACION</li>
            <li style="font-weight: bold;width:200px;">NRO FACTURA</li>
            <li style="font-weight: bold;width:200px;">PAGADOR</li>
        </ul>
        <ul>
            <li><span class="icon-file-text" style="font-size:25px;color:#1F9A8E;"></span></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:10px;"><?php echo $arrsubasta['facturaid'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:300px;"><?php echo $arrsubasta['facnumero'];?></li>
            <li style="padding-left:5px;padding-right:5px;margin-left:100px;"><?php echo $arrsubasta['cliente'];?></li>
            <input type="hidden" name="factura_id" value="<?=$arrsubasta['facturaid']?>">
            <input type="hidden" name="factura_numero" value="<?=$arrsubasta['facnumero']?>">
            <input type="hidden" name="cliente_nombre" value="<?=$arrsubasta['cliente']?>">
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">DATOS DEL EMISOR:</li>
        </ul>
        <ul>
            <li style="font-weight:bold;width:115px;padding-left:5px;padding-right:5px;">RNC EMISOR:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:280px;">EMISOR:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:210px;">EMAIL:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;">TELEFONO:</li>
        </ul>
        <ul>
            <li><input type="text" name="emisor_identificacion" size="15" value="<?=$arrsubasta['emisordoc']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="emisor" size="40" value="<?=$arrsubasta['emisor']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="emisor_correo" size="30" value="<?=$arrsubasta['emisor_correo']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="emisor_telefono" size="20" value="<?=$arrsubasta['emisor_telefono']?>" class="frminput_text_off" readonly></li>
            <input type="hidden" name="emisor_id" value="<?=$arrsubasta['emisorid']?>">
            <input type="hidden" name="emisor_correo" value="<?=$arrsubasta['emisor_correo']?>">
        </ul>
        <ul>
            <li style="font-weight:bold;width:280px;padding-left:5px;padding-right:5px;">REPRESENTANTE EMISOR:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:210px;">TIPO DOC:</li>
            <li style="font-weight:bold;padding-left:5px;padding-right:5px;width:210px;">NRO DOC:</li>
        </ul>
        <ul>
            <li><input type="text" name="repre_emisor_nombre" size="40" value="<?=$arrsubasta['repre_emisor_nombre']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="repre_emisor_tipodoc" size="30" value="<?=$arrsubasta['repre_emisor_tipodoc']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="repre_emisor_nrodoc" size="30" value="<?=$arrsubasta['repre_emisor_nrodoc']?>" class="frminput_text_off" readonly></li>
        </ul>

        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">DATOS DE LA FACTURA:</li>
        </ul>
        <ul>
            <li style="font-weight:bold;width:115px;padding-left:5px;padding-right:5px;">MONEDA:</li>
            <li style="font-weight:bold;width:115px;padding-left:5px;padding-right:5px;">MONTO FACTURA:</li>
            <li style="font-weight:bold;width:100px;padding-left:5px;padding-right:5px;">TASA INTERES ANUAL (%):</li>
            <li style="font-weight:bold;width:115px;padding-left:5px;padding-right:5px;">MONTO ADELANTO:</li>
            <li style="font-weight:bold;width:100px;padding-left:5px;padding-right:5px;">F EMISION:</li>
            <li style="font-weight:bold;width:110px;padding-left:5px;padding-right:5px;">F VENCIMIENTO:</li>
        </ul>
    <?php
        $tiagrupo = $arrpropuestas[0]['tiafinal']*100;
    ?>
        <ul>
            <li><input type="text" name="moneda" size="15" value="<?=$arrsubasta['moneda']?>" class="frminput_text_off" readonly></li>
            <input type="hidden" name="moneda_id" value="<?=$arrsubasta['monedaid']?>">
            <li><input type="text" name="monto_factura" size="15" value="<?=number_format($arrsubasta['total'],2,'.',',')?>" class="frminput_text_off" readonly></li>
            <li style="margin-right:20px;"><input type="text" name="tia" size="10" value="<?=number_format($tiagrupo,2,'.',',')?> %" class="frminput_text_off" readonly></li>
            <li style="margin-right:5px;"><input type="text" name="monto_adelanto_f" size="15" value="<?=number_format($arrsubasta['montofin'],2,'.',',')?>" class="frminput_text_off" readonly></li>
            <input type="hidden" name="monto_adelanto" value="<?=$arrsubasta['montofin']?>">
            <input type="hidden" name="monto_factura_orig" value="<?=$arrsubasta['total']?>">
    <?php
        $femision_t = strtotime($arrsubasta['factura_femision']);
        $femision = date('d-m-Y',$femision_t);
        $fvencimiento_t = strtotime($arrsubasta['fvencimiento']);
        $fvencimiento = date('d-m-Y',$fvencimiento_t);
    ?>
            <li style="margin-right:25px;"><input type="text" name="femision" size="10" value="<?=$femision?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="fvencimiento" size="10" value="<?=$fvencimiento?>" class="frminput_text_off" readonly></li>
        </ul>
        
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
                <tbody>
    <?php
        for ($i=0; $i<count($arrpropuestas); $i++){
            if ($arrpropuestas[$i]['grupofinal'] == $arrsubasta['grupowinid']){
                $porcentaje = $arrpropuestas[$i]['posicion_porc']*100;
                echo '          
                    <tr>
                        <td data-label="ID">'.$arrpropuestas[$i]['propuestaid'].'</td>
                        <td data-label="MONTO POSICION">'.number_format($arrpropuestas[$i]['posicion'],2,'.',',').' '.$arrsubasta['moneda'].'</td>
                        <td data-label="PARTICIPACION">'.number_format($porcentaje,2,'.',',').' %</td>
                    </tr>';
            }
        }
    ?>
                </tbody>
            </table>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul style="margin-top:10px;">
            <li style="font-weight:bold;">DATOS DE LA LIQUIDACION DE LA SUBASTA:</li>
        </ul>
    <?php
        $varr_permisos = $vobj_seg->get_permisos($_SESSION['user']['perfilid']);

        if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'COMP-LEG')){
            if ($arrsubasta['estado_compensa_id'] == 40){  // contrato x enviar
                echo '
            <ul>
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">LINK DEL CONTRATO ENDOSO:</li>
                <li><input type="text" name="link_envio" size="80" class="frminput_text"></li>
                <li> <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="enviarContrato()" id="btn_enviar_contrato">
                    <i class="fa-solid fa-floppy-disk"></i> Save Link Contrato</button></li>
            </ul>';
            } elseif ($arrsubasta['estado_compensa_id'] == 43){    // contrato enviado
                echo '
            <ul>
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">LINK DEL CONTRATO ENDOSO:</li>
                <li><a href="'.$arrsubasta['ref_envio_contrato'].'" style="text-decoration:none;color:#064677;" target="_blank"><span class="icon-link"></span> Ver Contrato Emisor</a></li>
                <li> <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="enviarContrato()" id="btn_enviar_contrato">
                    <i class="fa-solid fa-share"></i> Re-enviar Contrato</button>
                    <input type="hidden" name="link_envio" id="link_envio" value="'.$arrsubasta['ref_envio_contrato'].'">
                </li>
            </ul>
            <ul>
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">ADJUNTAR CONTRATO FIRMADO:</li>
                <li><input type="file" name="contrato" id="contrato"></li>
            </ul>';

                //== verifico si necesita endoso
                if ($varr_parametros['REQUERIMIENTO DE ENDOSO']['valornum'] == 1){
                    echo '
            <ul>
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">ADJUNTAR ENDOSO:</li>
                <li><input type="file" name="endoso" id="endoso"></li>
            </ul>';
                }
            } elseif ($arrsubasta['estado_compensa_id'] == 44 || $arrsubasta['estado_compensa_id'] == 45){    // contrato recibido o firmado    / ENDOSADO
                echo '
            <ul>
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">LINK DEL CONTRATO ENDOSO:</li>
                <li><a href="'.$arrsubasta['ref_envio_contrato'].'" style="text-decoration:none;color:#064677;" target="_blank"><span class="icon-link"></span> Ver Contrato Emisor</a></li>
            </ul>
            <ul style="margin:0px;padding:0px;">
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">CONTRATO FIRMADO:</li>
                <li><a href="'.$arrsubasta['path_contrato'].'" style="text-decoration:none;color:#064677;" target="_blank"><span class="icon-link"></span> Ver Contrato Firmado</a></li>
            </ul>';

                // verificacion si se necesita endoso
                if ($varr_parametros['REQUERIMIENTO DE ENDOSO']['valornum'] == 1){
                    echo '
            <ul style="margin:0px;padding:0px;">
                <li style="font-weight:bold;width:200px;padding-left:5px;padding-right:5px;">ENDOSO:</li>
                <li><a href="'.$varr_factura['acpath'].'" style="text-decoration:none;color:#064677;" target="_blank"><i class="fa-solid fa-paperclip"></i> Ver Endoso</a></li>
            </ul>';
                }
            }
        }
        if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'COMP-FINAN')){   //  ANALISTA FINANCIERO / CFO / CLEVEL
            if ($arrsubasta['estado_compensa_id'] == 45){   //ENDOSADO
                $varr_emisor = $obj_mae->get_datos_emisor_full($arrsubasta['emisorid']);
                
                echo '
            <ul style="margin-top:10px;">
                <li style="font-weight:bold;">DATOS PARA LA TRANSFERENCIA DEL ADELANTO AL EMISOR:</li>
            </ul>
            <ul>
                <li style="font-weight:bold;width:150px;padding-left:5px;padding-right:5px;">BANCO:</li>
                <li style="font-weight:bold;width:110px;padding-left:5px;padding-right:5px;">TIPO CUENTA:</li>
                <li style="font-weight:bold;width:110px;padding-left:5px;padding-right:5px;">NRO CUENTA:</li>
                <li style="font-weight:bold;width:160px;padding-left:5px;padding-right:5px;">MONTO A TRANSFERIR:</li>
                <li style="font-weight:bold;width:115px;padding-left:5px;padding-right:5px;">MONEDA:</li>
            </ul>
            <ul>
                <li><input type="text" name="banco" size="20" value="'.$varr_emisor['banco_nombre'].'" class="frminput_text_off" readonly></li>
                <li><input type="text" name="tcuenta" size="15" value="'.$varr_emisor['tcuenta_banco'].'" class="frminput_text_off" readonly></li>
                <li><input type="text" name="nro_cuenta" size="15" value="'.$varr_emisor['nro_cuenta_banco'].'" class="frminput_text_off" readonly></li>
                <li style="margin-right:50px;"><input type="text" name="monto_transferencia" size="15" value="'.number_format($arrsubasta['montofin'],2,'.',',').'" class="frminput_text_off" readonly></li>
                <li><input type="text" name="moneda_transferencia" size="15" value="'.$arrsubasta['moneda'].'" class="frminput_text_off" readonly></li>
            </ul>';
            
            }
        }
    ?>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!--#######################################################
        ##################### BOTONERA
        ###########################################################-->
        <ul style="margin-top:10px;">
    <?php
        if ($obj_mae->busca_arreglo_bidi($varr_permisos, 'codigo', 'COMP-LEG')){
            /*if ($arrsubasta['estado_compensa_id'] == 40)    // CONTRATO PENDIENTE DE ENVIAR AL EMISOR
            echo '
                <li> <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="enviarContrato()" id="btn_enviar_contrato">
                <i class="fa-solid fa-paper-plane"></i> Enviar Contrato Emisor</button></li>';*/
            if ($arrsubasta['estado_compensa_id'] == 43)    // CONTRATO ENVIADO AL EMISOR
            echo '
                <li> <button type="button" class="btn btn-primary" style="font-size:11px;background-color:var(--color-azulv2);border:none;" onclick="recibirContrato()" id="btn_recibir_contrato">
                <i class="fa-solid fa-file-invoice"></i> Recibir Contrato Emisor</button></li>';
        }
        
    ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <script>
        function enviarContrato(){
            var estado_compensa = document.getElementById('estado_compensa').value;

            if (estado_compensa == 40){     //CONTRATO POR ENVIAR
                if (document.frm.link_envio.value == '') alert('Debe ingresar la referencia del envio del contrato al vendedor');
                else{
                    var btn_envio = document.getElementById('btn_enviar_contrato');
                    btn_envio.disabled = "true";
                    document.frm.accion.value = 'envio_contrato';
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            } else {
                if (estado_compensa == 43){     // CONTRATO ENVIADO SIRVE PARA REENVIAR
                    var btn_envio = document.getElementById('btn_enviar_contrato');
                    btn_envio.disabled = "true";
                    document.frm.accion.value = 'reenvio_contrato';
                    document.frm.action = 'subasta_gestion_proceso.php';
                    document.frm.submit();
                }
            }
        }
        
        function recibirContrato(){
            var contrato = document.getElementById('contrato').value;
            var con_endoso = document.getElementById('con_endoso').value;
            var procede = 1;

            if (contrato == ''){
                procede = 0;
                alert('Debe agregar el archivo del contrato con el vendedor');
            }

            if (procede == 1 && con_endoso == 1){
                var endoso = document.getElementById('endoso').value;

                if (endoso == ''){
                    procede = 0;
                    alert('Debe agregar el comprobante del endoso');
                }
            }

            if (procede == 1){
                var btn_recibe = document.getElementById('btn_recibir_contrato');
                
                btn_recibe.disabled = "true";
                document.frm.accion.value = 'contrato';
                document.frm.action = 'subasta_gestion_proceso.php';
                document.frm.submit();
            }
        }
        
    </script>
</BODY>
</HTML>