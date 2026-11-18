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


    </script>

</BODY>
</HTML>