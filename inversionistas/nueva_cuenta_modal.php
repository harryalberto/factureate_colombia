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
$obj_cuenta_modal = new cuentas;
$obj_mae = new maestros;

$varr_bancos = $obj_mae->get_bancos();
$varr_tcuenta = $obj_mae->get_tipos('TIPO CUENTA BANCO');
$varr_monedas = $obj_mae->get_tipos('MONEDA');

// OBTENER LAS CUENTAS YA REGISTRADAS Y CREAR LA VARIABLE DE IMPRESION
$varr_cuentas_modal = $obj_cuenta_modal->get_cuentas_banco_inversor($_GET['inversor_id']);
$v_tabla_cuentas = "<table class='tabla_resize'>
                        <thead>
                            <tr>
                                <th scope='col' class='sort asc'>ID CUENTA</th>     <th scope='col' class='sort asc'>BANCO</th>
                                <th scope='col' class='sort asc'>TIPO CUENTA</th>   <th scope='col' class='sort asc'>MONEDA</th>
                                <th scope='col' class='sort asc'>CUENTA</th>        <th scope='col' class='sort asc'>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody id='content'>";

for ($i = 0; $i < count($varr_cuentas_modal); $i++){
    if ($varr_cuentas_modal[$i]['estado_id'] == 1) $v_icono_estado = "<i class='fa-solid fa-circle-check' style='margin-left:5px;color:var(--color-verde);''></i>";
    else $v_icono_estado = "<abbr title='Pendiente de verificar por Factureate'><i class='fa-solid fa-triangle-exclamation' style='margin-left:5px;color:var(--color-amarillo);''></i></abbr>";

    $v_tabla_cuentas .= "   <tr>    
                                <td data-label='ID CUENTA'>".$varr_cuentas_modal[$i]['cuenta_banco_id']."</td>
                                <td data-label='BANCO'>".$varr_cuentas_modal[$i]['banco_nombre']."</td>
                                <td data-label='TIPO CUENTA'>".$varr_cuentas_modal[$i]['tcuenta_nombre']."</td>
                                <td data-label='MONEDA'>".$varr_cuentas_modal[$i]['moneda_nombre']."</td>
                                <td data-label='CUENTA'>".$varr_cuentas_modal[$i]['cuenta']."</td>
                                <td data-label='ESTADO'>".$varr_cuentas_modal[$i]['estado_nombre'].$v_icono_estado."</td>
                            </tr>";
}

/*$v_tabla_cuentas .= '   </tbody>
                    </table>';*/
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<div class="contenedor_principal">
    <form name='frm_modal' method='post' id='frm_modal' enctype="multipart/form-data">
    <input type="hidden" id="inversor_id" name="inversor_id" value="<?=$_GET['inversor_id']?>">
    <input type="hidden" id="empresa_id" name="empresa_id" value="<?=$_GET['empresa_id']?>">
    
    <input type="hidden" name="nro_doc" id="nro_doc" value="<?=$_GET['nro_doc']?>">
    
    <input type="hidden" name="tabla" id="tabla" value="<?=$v_tabla_cuentas?>">

    <input type="hidden" name="banco_nombre" id="banco_nombre">
    <input type="hidden" name="tcuenta_nombre" id="tcuenta_nombre">
    <input type="hidden" name="moneda_nombre" id="moneda_nombre">   

    <div class="contenedor_formulario">
        <div class="contenedor_formulario_block">
            <div class="formulario_grupo_row" style="width: 500px;">
                <label for="banco_id">Banco:</label>
                <select id="banco_id" name="banco_id" class="form-control" onchange="focusBanco()">
                    <option value="0" selected>Seleccione Banco</option>
    <?php
        for ($i=0; $i<count($varr_bancos); $i++){
            echo '  <option value="'.$varr_bancos[$i]['banco_id'].'">'.$varr_bancos[$i]['banco_nombre'].'</option>';
        }
    ?>
                </select>
    <?php
        for ($i=0; $i<count($varr_bancos); $i++){
            echo '  <input type="hidden" name="banco'.$varr_bancos[$i]['banco_id'].'" id="banco'.$varr_bancos[$i]['banco_id'].'" value="'.$varr_bancos[$i]['banco_nombre'].'">';
        }
    ?>
            </div>
        </div>

        <div class="contenedor_formulario_block">
            <div class="formulario_grupo_row" style="width: 500px;">
                <label for="tcuenta">Tipo Cuenta:</label>
                <select id="tcuenta" name="tcuenta" class="form-control" onchange="focusTCuenta()">
                    <option value="0" selected>Tipo cuenta</option>
    <?php
        for ($i=0; $i<count($varr_tcuenta); $i++){
            echo '  <option value="'.$varr_tcuenta[$i]['id'].'">'.$varr_tcuenta[$i]['nombre'].'</option>';
        }
    ?>
                </select>
    <?php
        for ($i=0; $i<count($varr_tcuenta); $i++){
            echo '  <input type="hidden" name="tcuenta'.$varr_tcuenta[$i]['id'].'" id="tcuenta'.$varr_tcuenta[$i]['id'].'" value="'.$varr_tcuenta[$i]['nombre'].'">';
        }
    ?>
            </div>
        </div>

        <div class="contenedor_formulario_block">
            <div class="formulario_grupo_row" style="width: 500px;">
                <label for="cuenta">Nro Cuenta:</label>
                <input type="text" id="cuenta" name="cuenta" placeholder="Nro de cuenta" class="form-control">
            </div>
        </div>

        <div class="contenedor_formulario_block">
            <div class="formulario_grupo_row" style="width: 500px;">
                <label for="moneda_id">Moneda:</label>
                <select id="moneda_id" name="moneda_id" class="form-control" onchange="focusMoneda()">
                    <option value="0" selected>Seleccione</option>
<?php
    for ($i=0; $i<count($varr_monedas); $i++){
        echo '      <option value="'.$varr_monedas[$i]['id'].'">'.$varr_monedas[$i]['nombre'].'</option>';
    }
?>
                </select>
<?php
    for ($i=0; $i<count($varr_monedas); $i++){
        echo '      <input type="hidden" name="moneda'.$varr_monedas[$i]['id'].'" id="moneda'.$varr_monedas[$i]['id'].'" value="'.$varr_monedas[$i]['nombre'].'">';
    }
?>
            </div>
        </div>

        <div class="contenedor_formulario_block">
            <!--<div class="formulario_grupo_row" style="width: 700px;">-->
            <div style="width: 700px;">
                <label for="certificado" style="display: inline;margin-right: 36px;">Certificado:</label>
                <input type="file" name="certificado" id="certificado" class="form-control" style="display: inline; width: calc(100% - 300px); font-size: 12px;">
            </div>
        </div>

        <div style="margin-top: 50px;width: 100%; float: left;">
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;" type="button" class="btn btn-primary" onclick="guardarCuenta()" id="btn_guardar">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </div>
    </form>
</div>
      
<!--################ ZONA JS ####################-->
<script>
    function guardarCuenta(){
        var v_banco_id = $('#banco_id').val();
        var v_tcuenta_id = $('#tcuenta').val();
        var v_cuenta = $('#cuenta').val();
        var v_moneda_id = $('#moneda_id').val();
        var v_inversor_id = $('#inversor_id').val();
        var v_certificado = $('#certificado').val();
        var accion = "nueva_cuenta";
        var v_nro_doc = $('#nro_doc').val();
        var v_tabla = $('#tabla').val();
        var certificado = document.getElementById("certificado");
        var btn_guardar = document.getElementById("btn_guardar");

        btn_guardar.disabled = true;

        var procede = 1;
        
        if (v_banco_id == 0) {
            alert("Debe seleccionar un banco");
            procede = 0;
            $('#banco_id').focus();
        }

        if (v_tcuenta_id == 0 && procede == 1){
            alert("Debe seleccionar un tipo de cuenta");
            procede = 0;
            $('#tcuenta').focus();
        }
        
        if (v_cuenta == "" && procede == 1){
            alert("Debe ingresar un numero de cuenta");
            procede = 0;
            $('#cuenta').focus();
        }

        if (v_moneda_id == 0 && procede == 1){
            alert("Debe seleccionar una moneda");
            procede = 0;
            $('#moneda_id').focus();
        }
        
        if (v_certificado == "" && procede == 1){
            alert("Debe seleccionar un certificado bancario");
            procede = 0;
            $('#certificado').focus();
        }
        
        if (procede == 1){
            //var formData = new FormData(document.getElementById("frm_modal"));
            var formData = new FormData();

            formData.append('accion', accion)
            formData.append('banco_id', v_banco_id)
            formData.append('tcuenta_id', v_tcuenta_id)
            formData.append('cuenta', v_cuenta)
            formData.append('moneda_id', v_moneda_id)
            formData.append('nro_doc', v_nro_doc)
            formData.append('inversor_id', v_inversor_id)

            var fileCertificado = certificado.files[0];
            formData.append('certificado', fileCertificado)
            
            $.ajax({
                url:"perfil_inversor_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success:function(data){
                    //alert('data='+data);
                    if (data > 0) {
                        alert('La cuenta fue registrada con exito, ahora sera revisada por nuestros analistas');

                        // PREPARO LA INFORMACION QUE SERA IMPRESA CON LAS CUENTAS
                        var banco_nombre = $('#banco_nombre').val();
                        var tcuenta_nombre = $('#tcuenta_nombre').val();
                        var moneda_nombre = $('#moneda_nombre').val();

                        var estado = 'REGISTRADO<abbr title="Pendiente de verificar por Factureate"><i class="fa-solid fa-triangle-exclamation" style="margin-left:5px;color:var(--color-amarillo);"></i></abbr>';

                        v_tabla = v_tabla + '<tr><td data-label="ID CUENTA">'+data+'</td><td data-label="BANCO">'+banco_nombre+'</td><td data-label="TIPO CUENTA">'+tcuenta_nombre+'</td><td data-label="MONEDA">'+moneda_nombre+'</td><td data-label="CUENTA">'+v_cuenta+'</td><td data-label="ESTADO">'+estado+'</td></tr></tbody></table>';

                        // CIERRO EL MODAL
                        pintaCuentas(v_tabla);
                        //$('#PerfilInversor').modal('hide');
                    } else alert('La cuenta ya existe, verifique por favor');
                    //refresh_page();
                    
                }
            });
        }
    }

    function focusBanco(){
        var v_banco_id = $('#banco_id').val();
        var v_banco_nombre = document.getElementById("banco"+v_banco_id).value;
        var banco = document.getElementById("banco_nombre");
        banco.value = v_banco_nombre;
    }

    function focusTCuenta(){
        var v_tcuenta_id = $('#tcuenta').val();
        var v_tcuenta_nombre = document.getElementById("tcuenta"+v_tcuenta_id).value;
        var tcuenta = document.getElementById("tcuenta_nombre");
        tcuenta.value = v_tcuenta_nombre;
    }

    function focusMoneda(){
        var v_moneda_id = $('#moneda_id').val();
        var v_moneda_nombre = document.getElementById("moneda"+v_moneda_id).value;
        var moneda = document.getElementById("moneda_nombre");
        moneda.value = v_moneda_nombre;
    }
</script>
<!--#############################################-->
</BODY>
</HTML>