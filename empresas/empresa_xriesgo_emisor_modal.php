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

<?php
    require("../lib/head.php");
    $acceso = 'GESTEMP';
    require("../lib/valida-acceso.php");
?>

</HEAD>

<?php
/*===================================================
===== LOGICA
=====================================================*/
$obj_mae = new maestros;
$vobj_seg_modal = new seguridad;

$arr_empresa = $obj_mae->get_datos_empresa($_GET['id']);
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <input type="hidden" name="empresa_id" id="empresa_id" value="<?=$_GET['id']?>">

    <div id="principal" style="padding-left: 10px;overflow: hidden;">
        <div class="contenedor_formulario">
            <!--+++++++++++++++++++++++++++++++++++++++++++++++++
            ++++ bloque informacion de la empresa-->

            <div class="contenedor_formulario_column">
                <p style="font-size: 12px;font-weight: bold; margin-top: 10px; padding-top: 10px;">INFORMACION DE LA EMPRESA:</p>
            </div>

            <!--++++ bloque 1 de informacion de la empresa -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="ruc">ID EMPRESA</label>
                    <input type="text" name="ruc" id="ruc" class="formulario_control" value="<?=$arr_empresa['identificacion']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 250px;">
                    <label for="nombre_empresa">NOMBRE EMPRESA</label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" class="formulario_control" value="<?=$arr_empresa['nombre']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 100px;">
                    <label for="estado_empresa">ESTADO</label>
                    <input type="text" name="estado_empresa" id="estado_empresa" class="formulario_control" value="<?=$arr_empresa['e_empresa']?>" readonly>
                </div>
                <div class="formulario_grupo_row" style="width: 120px;">
                    <label for="tipo_empresa">TIPO</label>
                    <input type="text" name="tipo_empresa" id="tipo_empresa" class="formulario_control" value="<?=$arr_empresa['t_empresa']?>" readonly>
                </div>
            </div>

            <!--++++ bloque 3 informacion de la empresa -->
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="sectorid">ACTIVIDAD ECONOMICA</label>
<?php
    if ($_SESSION['user']['perfilid'] == 14 || $_SESSION['user']['perfilid'] == 10 || $_SESSION['user']['perfilid'] == 12 || $_SESSION['user']['perfilid'] == 13 || $_SESSION['user']['perfilid'] == 15){
            //CFO, ANALISTA FINANCIERO, CLO, ANALISTA LEGAL
        echo '      <input type="text" name="sector" id="sector" class="formulario_control" value="'.$arr_empresa['sector'].'" readonly>
                    <input type="hidden" name="sectorid" id="sectorid" value="'.$arr_empresa['sectorid'].'">';
    } else{
        $arr_sector = $obj_mae->get_tipos_xnom('SECTORECO');
        echo '      <select class="formulario_control" name="sectorid" id="sectorid">';
                        
        if ($arr_empresa['sectorid'] == 0) 
            echo '      <option value="0" selected>Elija una opcion</option>';

        for ($i=0; $i<count($arr_sector); $i++){
            if ($arr_sector[$i]['id'] == $arr_empresa['sectorid']) 
                echo '  <option value="'.$arr_sector[$i]['id'].'" selected>'.$arr_sector[$i]['nombre'].'</option>';
            else echo ' <option value="'.$arr_sector[$i]['id'].'">'.$arr_sector[$i]['nombre'].'</option>';
        }
                        
        echo '      </select>';
    }
?>
                </div>
                <div class="formulario_grupo_row" style="width: 300px;">
                    <label for="actividad">DESCRIPCION ACTIVIDAD</label>
                    <textarea name="actividad" id="actividad" cols="70" rows="3" class="formulario_control" readonly><?php echo $arr_empresa['actividad'];?></textarea>
                </div>
            </div>

            <!--+++++++++++++++++++++++++++++++++++++++++++++++++
            ++++ FIN bloque informacion de la empresa-->

            <!--+++++++++++++++++++++++++++++++++++++++++
            +++++++++++ EVALUACION DE VARIABLES DE RIESGOS DIMENSION 2 -->
            <div style="font-weight: bold;color:var(--color-azulv2);font-size: 14px;margin-top: 20px;width: 100%; float: left;">EVALUACION DE RIESGOS</div>

<?php
    // variables de dimension 2
    $varr_variables = $obj_mae->get_risk_variables_empresa($_GET['id'],2);
    $v_variables = '';

    for ($i = 0; $i < count($varr_variables); $i++){
        // variable de valorizacion del BS
        if ($varr_variables[$i]['variable_id'] == 13){
            $varr_parametros = $obj_mae->get_risk_parametros(0,'VALORIZACION BS');

            if ($varr_variables[$i]['impacto_id'] == 0) $v_calificacion_id = 35;
            else $v_calificacion_id = $varr_variables[$i]['impacto_id'];
        }

        // variable de tipo de producto
        if ($varr_variables[$i]['variable_id'] == 14){
            $varr_parametros = $obj_mae->get_risk_parametros(0,'N PRODUCTO DEVUELTO');

            if ($varr_variables[$i]['impacto_id'] == 0) $v_calificacion_id = 36;
            else $v_calificacion_id = $varr_variables[$i]['impacto_id'];
        }

        echo '
            <div class="contenedor_formulario_column">
                <div class="formulario_grupo_row" style="width: 150px;">
                    <label for="variable'.$varr_variables[$i]['variable_id'].'">'.$varr_variables[$i]['nombre'].'</label>
                    <select name="variable'.$varr_variables[$i]['variable_id'].'" id="variable'.$varr_variables[$i]['variable_id'].'" class="formulario_control">';

        for ($j = 0; $j < count($varr_parametros); $j++){
            if ($varr_parametros[$j]['parametro_id'] == $v_calificacion_id)
                echo '  <option value="'.$varr_parametros[$j]['parametro_id'].'-'.$varr_parametros[$j]['valor_num'].'" selected>'.$varr_parametros[$j]['nombre'].'</option>';
            else
                echo '  <option value="'.$varr_parametros[$j]['parametro_id'].'-'.$varr_parametros[$j]['valor_num'].'">'.$varr_parametros[$j]['nombre'].'</option>';
        }

        echo '
                    </select>
                </div>
                <div class="formulario_grupo_row" style="width: 200px;">
                    <label for="variable_justi'.$varr_variables[$i]['variable_id'].'">Justificación</label>
                    <textarea name="variable_justi'.$varr_variables[$i]['variable_id'].'" id="variable_justi'.$varr_variables[$i]['variable_id'].'" cols="50" rows="3" class="formulario_control">'.$varr_variables[$i]['justificacion'].'</textarea>
                </div>
            </div>';

        $v_variables .= $varr_variables[$i]['variable_id'].'-';
    }
?>

            <input type="hidden" name="cadena_variables" id="cadena_variables" value="<?=$v_variables?>">

            <!--+++++++++++++++++++++++++++++++++++++++++
            ++++ FIN EVALUACION DE VARIABLES DE RIESGOS DIMENSION 2 -->

            <!--+++++++++++++++++++++++++++++++++++++++++
            +++++++++++ BOTONERA -->
            <div class="contenedor_formulario_column">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" id="boton_grabar" onclick="grabar()">
                    <i class="fa-solid fa-floppy-disk"></i> Grabar
                </button>
            </div>
        </div>  <!-- div del formulario -->
    </div>  <!-- div principal -->

    <script type="text/javascript">
        function grabar(){
            const cadena_variables = document.getElementById("cadena_variables").value;
            const arr_variables = cadena_variables.split('-').filter(Boolean).map(Number);   // arreglo con las variables
            let cadena_datos_variables = '';
            let justificacion = '';
            let procede = 'SI';

            arr_variables.forEach(function(variable) {
                justificacion = document.getElementById("variable_justi"+variable).value;

                if (procede == 'SI'){
                    if (justificacion == ''){
                        procede = 'NO';
                        alert('Debe ingresar las justificaciones');
                    }

                    cadena_datos_variables = cadena_datos_variables + variable + '-' + document.getElementById("variable"+variable).value + '-' + document.getElementById("variable_justi"+variable).value + '-';
                }
            });

            if (procede == 'SI'){
                const empresa_id = document.getElementById("empresa_id").value;
                let formData = new FormData();

                formData.append('tipo_riesgo', 'EMISOR')
                formData.append('cadena_datos_variables', cadena_datos_variables)
                formData.append('empresa_id', empresa_id)

                fetch("registra_riesgo_proceso.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.respuesta == 'ERROR') alert('Ocurrio un error al registrar las variables');
                    else {
                        alert('Las variables fueron registradas');
                    }
                })
                .catch(err => console.log(err))
            }
        }

        function guardarRiesgoFactureate(){
            let procede = 'SI';

            //++++ validacion de buro
            const emp_riesgoid = document.getElementById("emp_riesgoid").value;
            const puntaje_buro = document.getElementById("puntaje_buro").value;
            const puntaje_buro_old = document.getElementById("puntaje_buro_old").value;

            if (puntaje_buro > 0){
                //++++ hay cambios en los riesgos del buro
                if (puntaje_buro !== puntaje_buro_old){
                    alert("Idenficamos cambios en los riesgos de Buro, debe guardar los riesgos del Buro antes de guardar los riesgos Factureate");
                    procede = 'NO';
                }
            }

            //++++ variable de cumplimiento de pago
            const historia_pago = document.getElementById("historia_pago").value;
            const calificacion_pago = document.getElementById("calificacion_pago").value;
            const dias_prom_pago = document.getElementById("dias_prom_pago").value;

            //++++ variable perfil empresa
            const anhos_operando = document.getElementById("anhos_operando").value;
            const calificacion_perfil = document.getElementById("calificacion_perfil").value;
            const calificacion_tamanho = document.getElementById("calificacion_tamanho").value;
            const tamanho = document.getElementById("tamanho").value;

            //++++ variables macro
            const grupo_variables = document.getElementById("grupo_variables").value;   // cadena con las variables macro
            const variables = grupo_variables.split('-').filter(Boolean).map(Number);   // arreglo con las variables macro
            const q_variables_macro = document.getElementById("q_variables_macro").value;
            const factor_macro = 1 / Number(q_variables_macro);
            let cadena_datos_macro = '';
            let puntaje_macro = 0;
            let puntaje_macro_total = 0;

            variables.forEach(function(variable) {
                cadena_datos_macro = cadena_datos_macro + 'variable'+variable+'-'+document.getElementById("impactomacro-"+variable).value+'-'+document.getElementById("nivelmacro-"+variable).value+'-';
                puntaje_macro = document.getElementById("calificacion_macro-"+variable).value;
                puntaje_macro_total = Number(puntaje_macro_total) + (Number(puntaje_macro) * Number(factor_macro));
            });

            puntaje_macro_total = 100 - puntaje_macro_total;

            //++++ llamo al calculo de riesgo
            calculaRiesgoF();

            //++++ datos del calulo del riesgo factureate
            const puntaje_factureate = document.getElementById("puntaje_factureate").value;
            const puntaje_factureate_old = document.getElementById("puntaje_factureate_old").value;
            const informe_factureate = document.getElementById('informe_factu_file');
            const msg_factu = document.getElementById('factureate_riesgo_justi').value;

            //++++ validacion de informacion ingresada
            if (informe_factureate.value == ""){
                alert("Debe cargar el informe de analisis de Riesgo de Factureate");
                procede = "NO";
            }

            if (procede == 'SI' && msg_factu == "") {
                alert("Debe ingresar un texto de justificacion del nivel de riesgo Factureate");
                procede = "NO";
            }

            //++++ validacion de resumen macro
            const cadena_tipos = document.getElementById("cadena_tipos").value;   // cadena con los resumenes
            const arr_tipos = cadena_tipos.split('-').filter(Boolean).map(Number);   // arreglo con los tipos
            let resumen_riesgo = '';
            let resultado_resumen = '';

            arr_tipos.forEach(function(varTipo) {
                resumen_riesgo = document.getElementById("resumen-"+varTipo).value;
                
                if (resumen_riesgo == '') resultado_resumen = 'fallo';
            });

            if (resultado_resumen == 'fallo'){
                procede = 'NO';
                alert("Debe ingresar todos los resumenes de riesgo");
            }

            if (procede == "SI"){
                let formData = new FormData();

                const fileInforme = informe_factureate.files[0];
                const v_ruc = $('#ruc').val();
                const v_feval = $('#f_evaluacion').val();
                const v_empresa_id = $('#empresaid').val();
                const v_dias = $('#dias_xvencer').val();
                const v_riesgo_sec_id = document.getElementById("riesgo_id").value;
                const v_nombre_emp = $('#nombre_empresa').val();

                formData.append('origen', 'factureate')

                //datos de la variable de cumplimiento de pagos
                formData.append('historia_pago', historia_pago)
                formData.append('calificacion_pago', calificacion_pago)
                formData.append('dias_prom_pago', dias_prom_pago)

                //datos de perfil de empresa
                formData.append('tamanho', tamanho)
                formData.append('anhos_operando', anhos_operando)
                formData.append('calificacion_perfil', calificacion_perfil)

                //datos macro
                formData.append('cadena_datos_macro', cadena_datos_macro)
                formData.append('puntaje_macro_total', puntaje_macro_total)

                //datos del riesgo factureate
                formData.append('informe_factu_file', fileInforme)
                formData.append('ruc', v_ruc)
                formData.append('factureate_riesgo_justi', msg_factu)
                formData.append('f_evaluacion', v_feval)
                formData.append('empresaid', v_empresa_id)
                formData.append('dias_xvencer', v_dias)
                formData.append('riesgo_sec_id', v_riesgo_sec_id)
                formData.append('cadena_tipos', cadena_tipos)
                formData.append('puntaje_factureate', puntaje_factureate)
                formData.append('puntaje_factureate_old', puntaje_factureate_old)
                formData.append('nombre_empresa', v_nombre_emp)

                arr_tipos.forEach(function(varTipo) {
                    resumen_riesgo = document.getElementById("resumen-"+varTipo).value;
                    formData.append('resumen-'+varTipo, resumen_riesgo)
                });

                fetch("update_riesgo_proceso.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.respuesta == '0') alert('Ocurrio un error con los informes adjuntados');
                    else {
                        alert('La evaluación de riesgos fue registrada');
                        var content_pdf = document.getElementById("pdf_factu");

                        content_pdf.innerHTML = data.respuesta;

                        if (v_riesgo_id == 0){
                            var obj_riesgo_id = document.getElementById('riesgo_id');
                            obj_riesgo_id.value = data.resultado;
                        }
                    }
                })
                .catch(err => console.log(err))
            }
        }
    </script>

</BODY>
</HTML>