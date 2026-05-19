<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'PERFILINV';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function acciones(accion){
            var enviar, previo;
            
            document.frm.action = 'empresa_gestion_proceso.php';
            document.frm.accion.value = accion;
            previo = document.frm.previo.value;
            
            if (accion == 'cerrar') location.href = previo;
            else if (accion == 'aprobar'){
                var direccion = document.frm.direccion.value;
                var actividad = document.frm.actividad.value;
                var t_empresaid = document.frm.t_empresaid.value;

                if (direccion == '' || actividad == ''){
                    alert('Debe completar la informacion de la empresa');
                } else{
                    if (t_empresaid == 48 || t_empresaid == 50 || t_empresaid == 51 || t_empresaid == 52){  // algun tipo relacionado a OP
                        var emp_score = document.frm.emp_riesgoid.value;
                        var nivel_score = document.frm.nivel_score_riesgoid.value;
                        var justi_nivel_score = document.frm.score_riesgo_justi.value;
                        var nivel_factu = document.frm.nivel_factureate_riesgoid.value;
                        var justi_nivel_factu = document.frm.factureate_riesgo_justi.value;
                        var path_score = document.frm.path_score.value;
                        var path_score_file = document.frm.informe_score_file.value;
                        var path_factu = document.frm.path_factu.value;
                        var path_factu_file = document.frm.informe_factu_file.value;
                        
                        if (emp_score == 0 || nivel_score == 0 || nivel_factu == 0) alert('Debe completar los niveles de riesgo');
                        else if (justi_nivel_score == '' || justi_nivel_factu == '') alert('Debe completar las justificaciones de riesgos');
                        else if ((path_score == '' && path_score_file == '') || (path_factu == '' && path_factu_file == '')) alert('Debe registrar los informes de riesgos');
                        else enviar = 1;
                    } else enviar = 1;
                }
            } else if (accion == 'update') enviar = 1;

            if (enviar == 1) document.frm.submit();
        }
        function valida_informe(tipo){
            document.frm.action = 'update_riesgo_proceso.php';
            document.frm.accion.value = tipo;

            if (tipo == 'score'){
                var archivo = document.getElementById('informe_score_file');
                
                if (archivo.value == ""){
                    alert("Debe cargar el informe del Score de Credito");
                } else{
                    document.frm.submit();
                }
            } else{
                var archivo = document.getElementById('informe_factu_file');

                if (archivo.value == ""){
                    alert("Debe cargar el informe del Score de Credito");
                } else{
                    document.frm.submit();
                }
            }
        }
        function closemodal(){
            $('.modalclase').fadeOut();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_mae = new maestros;
$arr_empresa = $obj_mae->get_datos_empresa($_GET['id']);

/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'inversionistas/perfil_inversionista.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
    <input type="hidden" name="accion">
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding: 5px;">
        Perfil de inversi&oacute;n
    </div>
    <div class="frmtransaccion">
        <ul style="margin:0px;padding:0px;">
            <li style="font-size: 12px;font-weight: bold;margin:0px 3px;padding:0px;width:135px;">Automatizar inversiones?:</li>
            <li><select class="frminput_text" name="automatico">
                    <option value="0">NO</option>
                    <option value="1">SI</option>
                </select>
            </li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Id Empresa:</li>
            <li style="margin:0px 3px;padding:0px;width:315px;">Nombre Empresa:</li>
            <li style="margin:0px 3px;padding:0px;width:135px;">Estado:</li>
            <li style="margin:0px 3px;padding:0px;width:130px;">Tipo:</li>
        </ul>
        <ul>
            <li><input type="text" name="ruc" value="<?=$arr_empresa['identificacion']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="nombre_empresa" size="50" value="<?=$arr_empresa['nombre']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="estado_empresa" size="20" value="<?=$arr_empresa['e_empresa']?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="tipo_empresa" size="20" value="<?=$arr_empresa['t_empresa']?>" class="frminput_text_off" readonly></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:455px;">Direcci&oacute;n:</li>
            <li style="margin:0px 3px;padding:0px;width:135px;">Tama&ntilde;o de empresa:</li>
        </ul>
        <ul>
            <li><input type="text" name="direccion" size="72" value="<?=$arr_empresa['direccion']?>" class="frminput_text"></li>
            <li>
                <select class="frminput_text" name="tamanoid">
                    <? 
                    $arr_ttamano = $obj_mae->get_tipos('TAMANHOEMP');
                    if ($arr_empresa['tamanoid'] == 0) echo '<option value="0" selected>Elija una opcion</option>';

                    for ($i=0; $i<count($arr_ttamano); $i++){
                        if ($arr_ttamano[$i]['id'] == $arr_empresa['tamanoid']) echo '<option value="'.$arr_ttamano[$i]['id'].'" selected>'.$arr_ttamano[$i]['nombre'].'</option>';
                        else echo '<option value="'.$arr_ttamano[$i]['id'].'">'.$arr_ttamano[$i]['nombre'].'</option>';
                    }
                    ?>
                </select>
            </li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:300px;">Actividad econ&oacute;mica:</li>
            <li style="margin:0px 3px;padding:0px;width:180px;">Descripci&oacute;n actividad:</li>
        </ul>
        <ul>
            <li>
                <select class="frminput_text" name="sectorid">
                    <? 
                    $arr_sector = $obj_mae->get_tipos('SECTORECO');
                    if ($arr_empresa['sectorid'] == 0) echo '<option value="0" selected>Elija una opcion</option>';

                    for ($i=0; $i<count($arr_sector); $i++){
                        if ($arr_sector[$i]['id'] == $arr_empresa['sectorid']) echo '<option value="'.$arr_sector[$i]['id'].'" selected>'.$arr_sector[$i]['nombre'].'</option>';
                        else echo '<option value="'.$arr_sector[$i]['id'].'">'.$arr_sector[$i]['nombre'].'</option>';
                    }
                    ?>
                </select>
            </li>
            <li><textarea name="actividad" <?=$readonly?> cols="70" rows="5" class="frminput_text"><?echo $arr_empresa['actividad'];?></textarea></li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!-- ============== ZONA EMISOR E INVERSIONISTA =========== -->
        <?
        if ($t_empresa == 'emisor' || $t_empresa == 'inversionista'){
        ?>
        <ul><li style="font-size:16px; font-weight:bold; color:#606060;">Representante Legal</li></ul>
        <ul>
            <li>Nombre: </li>
            <li><input type="text" name="nombre_repre" <?=$readonly?> style="width:300px;" value="<?=$arr_empresa['nombre_repre']?>"></li>
            <li>E-mail: </li>
            <li><input type="text" name="email_repre" <?=$readonly?> style="width:200px;" value="<?=$arr_empresa['email_repre']?>"></li>
            <li>Documento: </li>
            <li><select name="tdoc_repre" <?=$readonly?>>
            <?
                $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

                for ($i=0; $i<count($arr_tdoc); $i++){
                    if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_repre']) echo '<option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
                    else echo '<option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
                }
            ?>
                </select>
            </li>
            <li><input type="text" name="nrodoc_repre" <?=$readonly?> style="width:100px;" value="<?=$arr_empresa['nrodoc_repre']?>"></li>
        </ul>
        <ul>
            <li>DNI: </li>
            <?
            if ($arr_empresa['docrepre_path'] != '' && !is_null($arr_empresa['docrepre_path']))
                echo '<li><a class="pdfdni" href="#" path="../archivos/empresa_'.$arr_empresa['identificacion'].'/'.$arr_empresa['docrepre_path'].'">'.$arr_empresa['docrepre_path'].'</a></li>';
            ?>
            <li>Vigencia de Poderes : </li>
            <?
            if ($arr_empresa['vigencia_path'] != '' && !is_null($arr_empresa['vigencia_path']))
                echo '<li><a class="pdfpod" href="#" pathpod="../archivos/empresa_'.$arr_empresa['identificacion'].'/'.$arr_empresa['vigencia_path'].'">'.$arr_empresa['vigencia_path'].'</a></li>';
            ?>
        </ul>
        <?
        }
        ?>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!-- ============== CONTACTO =========== -->
        <ul><li style="font-size:16px; font-weight:bold; color:#606060;">Contacto</li></ul>
        <ul>
            <li>Nombre: </li>
            <li><input type="text" name="nombre_contacto" <?=$readonly?> style="width:300px;" value="<?=$arr_empresa['nombre_contacto']?>"></li>
            <li>E-mail: </li>
            <li><input type="text" name="email_contacto" <?=$readonly?> style="width:200px;" value="<?=$arr_empresa['email_contacto']?>"></li>
        </ul>
        <ul>
            <li>Documento: </li>
            <li><select name="tdoc_contacto" <?=$readonly?>>
            <?
                $arr_tdoc = $obj_mae->get_tipos('TIPOIDENTIF');

                for ($i=0; $i<count($arr_tdoc); $i++){
                    if ($arr_tdoc[$i]['id'] == $arr_empresa['tdoc_contacto']) echo '<option value="'.$arr_tdoc[$i]['id'].'" selected>'.$arr_tdoc[$i]['nombre'].'</option>';
                    else echo '<option value="'.$arr_tdoc[$i]['id'].'">'.$arr_tdoc[$i]['nombre'].'</option>';
                }
            ?>
                </select>
            </li>
            <li><input type="text" name="nrodoc_contacto" <?=$readonly?> style="width:100px;" value="<?=$arr_empresa['nrodoc_contacto']?>"></li>
            <li>Telefono: </li>
            <li><input type="text" name="telefono_contacto" <?=$readonly?> style="width:100px;" value="<?=$arr_empresa['telf_contacto']?>"></li>
        </ul>
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <!-- ============== ZONA DE CALIFICACION DE RIESGOS =========== -->
        <?
        if ($arr_empresa['t_empresaid'] == 48 || $arr_empresa['t_empresaid'] == 50 || $arr_empresa['t_empresaid'] == 51 || $arr_empresa['t_empresaid'] == 52){
            $v_arr_riesgos = $obj_mae->get_riesgo_obpago($_GET['id']);

            if (is_null($v_arr_riesgos['f_evaluacion'])) $f_riesgo = '';
            else {
                $f_riesgo_t = strtotime($v_arr_riesgos['f_evaluacion']);
                $f_riesgo = date('d-m-Y',$f_riesgo_t);
                $arr_dias_min = $obj_mae->dias_vigencia_riesgos(7); //cantidad de dias de vigencia del riesgo factureate
                $arr_dias_minfact = $obj_mae->dias_vigencia_riesgos(23); //cantidad de dias de alerta para modificacion de evaluacion

                $date1_t = date('Y-m-d');
                $date1 = new DateTime($date1_t);
                $date2 = new DateTime($v_arr_riesgos['f_evaluacion']);
                $v_diasvencimiento = $date1->diff($date2);      // hoy - fecha evaluacion
                $diasvencimiento = $v_diasvencimiento->days;    // dias transcurrido
                $v_dias_alerta = $arr_dias_min['valor'] - $arr_dias_minfact['valor'];   // dias para alertar
                $v_dias_xvencer_factu = $arr_dias_min['valor'] - $diasvencimiento;

                if ($arr_dias_min['valor'] >= $diasvencimiento){    // esta vigente
                    if ($v_dias_alerta <= $diasvencimiento){    // alertar
                        $mensaje_vencimiento = $arr_dias_min['descripcion'].' urgente los riesgos';
                        $v_bg = "background-color:#b30a1f;color:#ffffff;";
                    } else{
                        $mensaje_vencimiento = 'calificacion VIGENTE';
                        $v_bg = "color:#000000;";
                    }
                } else{    // vencido
                    $mensaje_vencimiento = 'calificacion VENCIDA';
                    $v_bg = "background-color:#b30a1f;color:#ffffff;";
                    $v_dias_xvencer_factu = $v_dias_xvencer_factu * -1;
                }

                $v_feval_score_t = strtotime($v_arr_riesgos['f_evalua_score']);
                $v_feval_score = date('d-m-Y',$v_feval_score_t);
                $arr_dias_score = $obj_mae->dias_vigencia_riesgos(22); //cantidad de dias de vigencia de evaluacion

                $v_dt_feval_score = new DateTime($v_arr_riesgos['f_evalua_score']);
                $v_obj_dias_transcurridos = $date1->diff($v_dt_feval_score);
                $v_dias_transcurridos_score = $v_obj_dias_transcurridos->days;
                $v_dias_alerta_score = $arr_dias_score['valor'] - $arr_dias_minfact['valor'];
                $v_dias_xvencer_score = $arr_dias_score['valor'] - $v_dias_transcurridos_score;

                if ($arr_dias_score['valor'] >= $v_dias_transcurridos_score){    // esta vigente
                    if ($v_dias_alerta_score <= $v_dias_transcurridos_score){    // alertar
                        $mensaje_vencimiento_score = $arr_dias_score['descripcion'].' urgente los riesgos';
                        $v_bg_score = "background-color:#b30a1f;color:#ffffff;";
                    } else{
                        $mensaje_vencimiento_score = 'calificacion VIGENTE';
                        $v_bg_score = "color:#000000;";
                    }
                } else{    // vencido
                    $mensaje_vencimiento_score = 'calificacion VENCIDA';
                    $v_bg_score = "background-color:#b30a1f;color:#ffffff;";
                    $v_dias_xvencer_score = -1 * $v_dias_xvencer_score;
                }
            }
        ?>
        <input type="hidden" name="riesgo_id" value="<?=$v_arr_riesgos['riesgo_id']?>">
        <div style="overflow:hidden;background-color:#555555;height:1px;"></div>
        <ul><li style="font-size: 12px;font-weight: bold;">Riesgos del Obligado al Pago:</li></ul>
        <ul><li style="font-size: 11px;font-weight: bold;">Riesgo Score de Credito:</li></ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Fecha de calificaci&oacute;n:</li>
            <li style="margin:0px 3px;padding:0px;width:315px;">Dias x vencer:</li>
        </ul>
        <ul>
            <li><input type="text" name="f_evaluacion_score" value="<?=$v_feval_score?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="dias_xvencer_score" size="10" value="<?=$v_dias_xvencer_score?>" class="frminput_text_off" style="<?=$v_bg_score?>" readonly></li>
            <li><input type="text" name="mensaje" size="35" value="<?=$mensaje_vencimiento_score?>" class="frminput_text_off" readonly></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Fuente de Riesgo Externo:</li>
            <li style="margin:0px 3px;padding:0px;width:400px;">Nivel:</li>
        </ul>
        <ul>
            <li><select name="emp_riesgoid" class="frminput_text" style="width:130px;">
            <?
                $arr_emp_riesgos = $obj_mae->empresas_riesgo();
                echo '<option value="0"><-- Seleccionar --></option>';

                for ($i=0; $i<count($arr_emp_riesgos); $i++){
                    if ($arr_emp_riesgos[$i]['id'] == $v_arr_riesgos['empresa_scoreid']) 
                        echo '<option value="'.$arr_emp_riesgos[$i]['id'].'" selected>'.$arr_emp_riesgos[$i]['nombre'].'</option>';
                    else echo '<option value="'.$arr_emp_riesgos[$i]['id'].'">'.$arr_emp_riesgos[$i]['nombre'].'</option>';
                }
            ?>
                </select>
            </li>
            <input type='hidden' name="emp_riesgoid_old" value="<?=$v_arr_riesgos['empresa_scoreid']?>">
            <li><select name="nivel_score_riesgoid" class="frminput_text">
            <?
                $arr_niveles_riesgo = $obj_mae->niveles_riesgo();
                echo '<option value="0"><-- Seleccionar --></option>';

                for ($i=0; $i<count($arr_niveles_riesgo); $i++){
                    if ($arr_niveles_riesgo[$i]['id'] == $v_arr_riesgos['riesgo_scoreid']) 
                        echo '<option value="'.$arr_niveles_riesgo[$i]['id'].'" selected>['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.
                            $arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
                    else echo '<option value="'.$arr_niveles_riesgo[$i]['id'].'">['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.
                            $arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
                }
            ?>
                </select>
            </li>
            <input type='hidden' name="nivel_score_riesgoid_old" value="<?=$v_arr_riesgos['riesgo_scoreid']?>">
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:450px;">Justificaci&oacute;n:</li>
            <li style="margin:0px 3px;padding:0px;width:300px;">Informe:</li>
            <li style="width:150px;" class="botontransaccion"><a href=javascript:valida_informe("score")><span class="icon-download"></span> Grabar Score</a></li>
        </ul>
        <ul>            
            <li><textarea name="score_riesgo_justi" cols="70" rows="5" class="frminput_text"><?echo $v_arr_riesgos['desc_riesgoscore'];?></textarea></li>
            <input type='hidden' name="score_riesgo_justi_old" value="<?=$v_arr_riesgos['desc_riesgoscore']?>">
            <?
            echo '<li><a href="mostrar_archivo.php?path='.$v_arr_riesgos['path_informe_score'].'&retorno=empresa_gestion_detalle.php&id='.$_GET['id'].'&previo='.$_GET['previo'].'">Ver informe de riresgos del Score</a></li>';
            ?>
            <input type="hidden" name="path_score" value="<?=$v_arr_riesgos['path_informe_score']?>">
            <li><input name="informe_score_file" id="informe_score_file" type="file"></li>
        </ul>
        <!--========================= RIESGO FACTUREATE =====================-->
        <ul><li style="font-size: 11px;font-weight: bold;">Riesgo Factureate: </li></ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:135px;">Fecha de calificaci&oacute;n:</li>
            <li style="margin:0px 3px;padding:0px;width:315px;">Dias x vencer:</li>
        </ul>
        <ul>
            <li><input type="text" name="f_evaluacion" value="<?=$f_riesgo?>" class="frminput_text_off" readonly></li>
            <li><input type="text" name="dias_xvencer" size="10" value="<?=$v_dias_xvencer_factu?>" class="frminput_text_off" style="<?=$v_bg?>" readonly></li>
            <li><input type="text" name="mensaje" size="35" value="<?=$mensaje_vencimiento?>" class="frminput_text_off" readonly></li>
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:400px;">Nivel:</li>
        </ul>
        <ul>
            <li><select name="nivel_factureate_riesgoid" class="frminput_text">
            <?
                $arr_niveles_riesgo = $obj_mae->niveles_riesgo();
                echo '<option value="0"><-- Seleccionar --></option>';

                for ($i=0; $i<count($arr_niveles_riesgo); $i++){
                    if ($arr_niveles_riesgo[$i]['id'] == $v_arr_riesgos['riesgo_factid']) 
                        echo '<option value="'.$arr_niveles_riesgo[$i]['id'].'" selected>['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.
                            $arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
                    else echo '<option value="'.$arr_niveles_riesgo[$i]['id'].'">['.$arr_niveles_riesgo[$i]['calificacion'].'] ['.
                            $arr_niveles_riesgo[$i]['nombre'].'] '.$arr_niveles_riesgo[$i]['descripcion'].'</option>';
                }
            ?>
                </select>
            </li>
            <input type='hidden' name="nivel_factureate_riesgoid_old" value="<?=$v_arr_riesgos['riesgo_factid']?>">
        </ul>
        <ul style="margin:0px;padding:0px;">
            <li style="margin:0px 3px;padding:0px;width:450px;">Justificaci&oacute;n:</li>
            <li style="margin:0px 3px;padding:0px;width:300px;">Informe:</li>
            <li style="width:150px;" class="botontransaccion"><a href=javascript:valida_informe("factureate")><span class="icon-download"></span> Grabar Factureate</a></li>
        </ul>
        <ul>
            <li><textarea class="frminput_text" name="factureate_riesgo_justi" cols="70" rows="5"><?echo $v_arr_riesgos['desc_riesgofact'];?></textarea></li>
            <input type='hidden' name="factureate_riesgo_justi_old" value="<?=$v_arr_riesgos['desc_riesgofact']?>">
            <?
            echo '<li><a href="mostrar_archivo.php?path='.$v_arr_riesgos['path_informe_factu'].'&retorno=empresa_gestion_detalle.php&id='.$_GET['id'].'&previo='.$_GET['previo'].'">Ver informe de riresgos Factureate</a></li>';
            ?>
            <input type="hidden" name="path_factu" value="<?=$v_arr_riesgos['path_informe_factu']?>">
            <li><input name="informe_factu_file" id="informe_factu_file" type="file"></li>
        </ul>
        <?
        }
        ?>
        <!--====================== botonera =======================-->
        <ul>
        <?
        if ($arr_empresa['estado'] == 2){      // en validacion
            echo '<li style="width:150px;" class="botontransaccion"><a href=javascript:acciones("aprobar")><span class="icon-upload"></span> Aprobar</a></li>';
            // obligados al pago
            if ($arr_empresa['t_empresaid'] == 48) 
                echo '<li style="width:150px;" class="botontransaccion"><a href=javascript:acciones("update")><span class="icon-download"></span> Grabar</a></li>';
        } elseif ($arr_empresa['estado'] == 3 || $arr_empresa['estado'] == 22){     // aprobada
            echo '<li style="width:150px;" class="botontransaccion"><a href=javascript:acciones("anular")>Anular</a></li>
                    <li style="width:150px;" class="botontransaccion"><a href=javascript:acciones("bloquear")><span class="icon-point-down"></span> Bloquear</a></li>';
        } elseif ($arr_empresa['estado'] == 4 || $arr_empresa['estado'] == 5){
            echo '<li style="width:150px;" class="botontransaccion"><a href=javascript:acciones("reactivar")>Re-activar</a></li>';
        }
        
        echo '<li class="botontransaccionrojo" style="width:150px;"><a href=javascript:acciones("cerrar")><span class="icon-point-right"></span> Cerrar</a></li>';
        ?>
        </ul>
    </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>