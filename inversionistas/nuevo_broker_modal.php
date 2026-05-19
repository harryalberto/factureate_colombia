<?php
session_start();
require("../conn/conn_db.inc");
require("../conn/conn_db_param.inc");
require("../conn/conn_db_trans.inc");
require("../conn/conn_db_param_trans.inc");
require("../lib-seg/seguridad-acceso.php");
require("../lib-trans/maestros.php");
require("../lib-trans/c_cuentas.php");
?>
<HTML>
<HEAD>
<?
    require("../lib/head.php");
    $acceso = 'CUENTAS';
    require("../lib/valida-acceso.php");
?>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cuenta = new cuentas;
$obj_mae = new maestros;

$varr_tipodoc_mod = $obj_mae->get_tipos('TIPOIDENTIF');
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <div class="contenedor_formulario">
        
        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_column" style="width: 500px;">
                <label for="nombre_broker">Nombre:</label>
                <input type="text" class="formulario_control" id="nombre_broker" placeholder="Nombre del broker">
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_column" style="width: 500px;">
                <label for="apellido_broker">Apellido:</label>
                <input type="text" class="formulario_control" id="apellido_broker" placeholder="Apellido del broker">
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_column" style="width: 500px;">
                <label for="telefono_broker">Telefono:</label>
                <input type="text" class="formulario_control" id="telefono_broker" placeholder="Telefono">
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_column" style="width: 500px;">
                <label for="email_broker">Email:</label>
                <input type="text" class="formulario_control" id="email_broker" placeholder="Email">
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_column" style="width: 500px;">
                <label for="tipodoc_broker">Tipo Doc:</label>
                <select id="tipodoc_broker" class="formulario_control">
                    <option value="0" selected>Seleccione tipo de documento</option>

<?php
    for ($i=0; $i<count($varr_tipodoc_mod); $i++){
        echo '      <option value="'.$varr_tipodoc_mod[$i]['id'].'">'.$varr_tipodoc_mod[$i]['nombre'].'</option>';
    }
?>
                </select>
            </div>
        </div>

        <div class="contenedor_formulario_column">
            <div class="formulario_grupo_column" style="width: 500px;">
                <label for="nrodoc_broker">Nro Doc:</label>
                <input type="text" class="formulario_control" id="nrodoc_broker" placeholder="Nro Documento">
            </div>
        </div>

    </div> <!-- div principal -->
    
    <button style="font-size:12px;background-color:var(--color-azulv2);border:none; margin-top: 10px;" type="button" class="btn btn-primary" onclick="guardarBroker()">
        <i class="fa-solid fa-floppy-disk"></i> Guardar
    </button>
  
    <!--################ ZONA JS ####################-->
    <script>
        function guardarBroker(){
            var v_nombre = $('#nombre_broker').val();
            var v_apellido = $('#apellido_broker').val();
            var v_telefono = $('#telefono_broker').val();
            var v_email = $('#email_broker').val();
            var v_tipodoc = $('#tipodoc_broker').val();
            var v_nrodoc = $('#nrodoc_broker').val();

            var v_procede = 1;

            if (v_nombre == ""){
                v_procede = 0;
                $('#nombre_broker').focus();
                alert('Debe ingresar el nombre del Broker');
            }

            if (v_apellido == "" && v_procede == 1){
                v_procede = 0;
                $('#apellido_broker').focus();
                alert('Debe ingresar el Apellido del Broker');
            }

            if (v_telefono == "" && v_procede == 1){
                v_procede = 0;
                $('#teledono_broker').focus();
                alert('Debe ingresar el Telefono del Broker');
            }

            if (v_email == "" && v_procede == 1){
                v_procede = 0;
                $('#email_broker').focus();
                alert('Debe ingresar el Email del Broker');
            }

            if (v_tipodoc == 0 && v_procede == 1){
                v_procede = 0;
                $('#tipodoc_broker').focus();
                alert('Debe elegir el Tipo de documento del Broker');
            }

            if (v_nrodoc == "" && v_procede == 1){
                v_procede = 0;
                $('#nrodoc_broker').focus();
                alert('Debe ingresar el Numero de documento del Broker');
            }

            if (v_procede == 1){
                var formData = new FormData();

                formData.append('accion', 'nuevo')
                formData.append('nombre', v_nombre)
                formData.append('apellido', v_apellido)
                formData.append('telefono', v_telefono)
                formData.append('email', v_email)
                formData.append('tipodoc', v_tipodoc)
                formData.append('nrodoc', v_nrodoc)

                $.ajax({
                    url:"nuevo_broker_proceso.php",
                    type:'post',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(data){
                        //alert('data='+data);
                        if (data > 0) {
                            alert('El nuevo broker fue registrado con éxito, ahora será revisado por nuestros analistas');
                            refresh_page();
                        } else {
                            alert('El broker ya existe, revise la información ingresada');
                            //refresh_page();
                        }
                        
                    }
                });
            }
        }
    </script>
    <!--#############################################-->
</BODY>
</HTML>