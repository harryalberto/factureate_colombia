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
    $acceso = 'EMPRESAS';
    require("../lib/valida-acceso.php");
?>

</HEAD>

<?php
//===============================================
//============= LOGICA
$vobj_mae = new maestros;
?>

<BODY bottommargin=0 leftmargin=0 topmargin=0>

<?php
    $menu = 'maestros/notificaciones.php';
    //$pagina = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>

    <!-- ================= Titulo -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:300px;margin:auto;">
        Notificaciones
    </div>

    <!--============== div principal -->
    <form name="frm" id="frm" method="post" action="notificaciones.php" style="display: flex; gap: 20px; align-items: flex-start;">
    <div style="display: flex; flex-direction: column; gap: 20px; align-items: flex-start; flex: 1;">

        <!--================== gestion por permiso -->
        <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
            <li>Notificaciones:</li>
            <li>
                <select name="notificacion" id="notificacion" class="formulario_control" onchange="cambia_noti()">

<?php
    $varr_permisos = $vobj_mae->get_notificaciones();
    $v_noti_id = 0;
    $v_descripcion = '';
    $v_subject = '';
    $v_body = '';

    if (isset($_POST['notificacion'])) $v_noti_id = $_POST['notificacion'];

    for ($i = 0; $i < count($varr_permisos); $i++){
        if ($v_noti_id == 0) $v_noti_id = $varr_permisos[$i]['id'];

        if ($v_noti_id == $varr_permisos[$i]['id']) {
            echo '  <option value="'.$varr_permisos[$i]['id'].'" selected>'.$varr_permisos[$i]['nombre'].'</option>';
            $v_descripcion = $varr_permisos[$i]['descripcion'];
            $v_subject = $varr_permisos[$i]['subject'];
            $v_body = $varr_permisos[$i]['body'];
        } else
            echo '  <option value="'.$varr_permisos[$i]['id'].'">'.$varr_permisos[$i]['nombre'].'</option>';
    }
?>

                </select>
            </li>
        </ul>

        <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
            <li>Descripcion:</li>
            <li>
                <textarea id="descripcion" name="descripcion" rows="3" cols="100"><?php echo $v_descripcion; ?></textarea>
            </li>
        </ul>

        <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
            <li>Subject:</li>
            <li>
                <textarea id="subject" name="subject" rows="3" cols="100"><?php echo $v_subject; ?></textarea>
            </li>
        </ul>

        <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
            <li>Body:</li>
            <li>
                <textarea id="body" name="body" rows="3" cols="100"><?php echo $v_body; ?></textarea>
            </li>
        </ul>

        <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
            <li>Perfiles:</li>
            <li>
                <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                    <tr style="background-color:#064677;color:#ffffff;">
                        <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                        <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE</td>
                        <td style="border: 1px solid;padding:5px;text-align:center;">C</td>
                    </tr>

<?php
    $varr_perfiles_fact = $vobj_mae->get_perfiles_factureate();
    $varr_perfil_xnoti = $vobj_mae->get_noti_xperfil($v_noti_id);

    for ($i = 0; $i < count($varr_perfiles_fact); $i++){
        $v_encontrado = 0;

        for ($j = 0; $j < count($varr_perfil_xnoti); $j++){
            if ($varr_perfiles_fact[$i]['id'] == $varr_perfil_xnoti[$j]['id']){
                $v_encontrado = 1;
                break;
            }
        }

        if ($v_encontrado == 1) $v_checked = 'checked';
        else $v_checked = '';

        echo '      <tr>
                        <td>'.$varr_perfiles_fact[$i]['id'].'</td>
                        <td>'.$varr_perfiles_fact[$i]['nombre'].'</td>
                        <td><input type="checkbox" name="perfil_noti[]" value="'.$varr_perfiles_fact[$i]['id'].'" '.$v_checked.'></td>
                        <input type="hidden" name="chkperfil-'.$varr_perfiles_fact[$i]['id'].'" id="chkperfil-'.$varr_perfiles_fact[$i]['id'].'" value="'.$v_encontrado.'">
                    </tr>';
    }
?>

                </table>
            </li>
        </ul>

        <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; margin-top: 20px;">
            <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="grabar()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
        </ul>

    </div>
    </form>

    <script>
        function cambia_noti(){
            document.getElementById('frm').submit();
        }

        function grabar(){
            //document.getElementById('accion').value = 'permisos';
            var formData = new FormData(document.getElementById("frm"));

            $.ajax({
                url:"notificaciones_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                alert('Se guardaron las asignaciones');
                document.getElementById('frm').submit();
            });
        }
    </script>

</BODY>
</HTML>