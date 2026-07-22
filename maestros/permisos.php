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
    $menu = 'maestros/permisos.php';
    //$pagina = 'empresas/empresas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>

    <!-- ================= Titulo -->
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;width:300px;margin:auto;">
        Permisos
    </div>

    <!--============== div principal -->
    <div style="display: flex; gap: 20px; align-items: flex-start;">
    <form name="frm" id="frm" method="post" action="permisos.php" style="display: flex; gap: 20px; align-items: flex-start;">
        <input type="hidden" name="accion" id="accion">
        <!--================== gestion por permiso -->
        <div id="permisos_xperfil" style="flex: 1;">
            <!--==== permisos -->
            <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
                <li>Permisos:</li>
                <li>
                    <select name="permisos" id="permisos" class="formulario_control" onchange="cambia_permiso()">

<?php
    $varr_permisos = $vobj_mae->get_permisos();

    if (isset($_POST['permisos'])) $v_permiso_id = $_POST['permisos'];
    else $v_permiso_id = 0;

    for ($i = 0; $i < count($varr_permisos); $i++){
        if ($v_permiso_id == 0) $v_permiso_id = $varr_permisos[$i]['id'];

        if ($v_permiso_id == $varr_permisos[$i]['id'])
            echo '      <option value="'.$varr_permisos[$i]['id'].'" selected>'.$varr_permisos[$i]['nombre'].' '.$varr_permisos[$i]['codigo'].'</option>';
        else
            echo '      <option value="'.$varr_permisos[$i]['id'].'">'.$varr_permisos[$i]['nombre'].' '.$varr_permisos[$i]['codigo'].'</option>';
    }
?>

                    </select>
                </li>
            </ul>

            <!--==== perfiles asignados -->
            <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; margin-top: 20px;">
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
    $varr_perfil_permiso = $vobj_mae->get_perfil_xpermiso($v_permiso_id);

    for ($i = 0; $i < count($varr_perfiles_fact); $i++){
        $v_encontrado = 0;

        for ($j= 0; $j < count($varr_perfil_permiso); $j++){
            if ($varr_perfiles_fact[$i]['id'] == $varr_perfil_permiso[$j]['perfil_id']){
                $v_encontrado = 1;
                break;
            }
        }

        if ($v_encontrado == 1) $v_checked = 'checked';
        else $v_checked = '';

        echo '          <tr>
                            <td>'.$varr_perfiles_fact[$i]['id'].'</td>
                            <td>'.$varr_perfiles_fact[$i]['nombre'].'</td>
                            <td><input type="checkbox" name="perfil_permiso[]" value="'.$varr_perfiles_fact[$i]['id'].'" '.$v_checked.'></td>
                            <input type="hidden" name="chkperfil-'.$varr_perfiles_fact[$i]['id'].'" id="chkperfil-'.$varr_perfiles_fact[$i]['id'].'" value="'.$v_encontrado.'">
                        </tr>';
    }
?>

                    </table>
                </li>
            </ul>

            <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; margin-top: 20px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="grabaPermisos()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Permisos
                </button>
            </ul>
        </div>

        <!--================== permisos por usuario -->
        <div id="perfil_xpermiso" style="flex: 1;">
            <!--==== perfiles -->
            <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0;">
                <li>Perfiles:</li>
                <li>
                    <select name="perfiles" id="perfiles" class="formulario_control" onchange="cambia_perfil()">

<?php
    if (isset($_POST['perfiles'])) $v_perfil_id = $_POST['perfiles'];
    else $v_perfil_id = 0;

    for ($i = 0; $i < count($varr_perfiles_fact); $i++){
        if ($v_perfil_id == 0) $v_perfil_id = $varr_perfiles_fact[$i]['id'];

        if ($v_perfil_id == $varr_perfiles_fact[$i]['id'])
            echo '      <option value="'.$varr_perfiles_fact[$i]['id'].'" selected>'.$varr_perfiles_fact[$i]['nombre'].'</option>';
        else
            echo '      <option value="'.$varr_perfiles_fact[$i]['id'].'">'.$varr_perfiles_fact[$i]['nombre'].'</option>';
    }
?>

                    </select>
                </li>
            </ul>

            <!--==== permisos por perfiles -->
            <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; margin-top: 20px;">
                <li>Permisos:</li>
                <li>
                    <table style="border: 1px solid; border-collapse: collapse;font-size:10px;width:100%;">
                        <tr style="background-color:#064677;color:#ffffff;">
                            <td style="border: 1px solid;padding:5px;text-align:center;">ID</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">NOMBRE</td>
                            <td style="border: 1px solid;padding:5px;text-align:center;">C</td>
                        </tr>

<?php
    $varr_permiso_xperfil = $vobj_mae->get_permiso_xperfil($v_perfil_id);

    for ($i = 0; $i < count($varr_permisos); $i++){
        $v_encontrado = 0;

        for ($j= 0; $j < count($varr_permiso_xperfil); $j++){
            if ($varr_permisos[$i]['id'] == $varr_permiso_xperfil[$j]['permiso_id']){
                $v_encontrado = 1;
                break;
            }
        }

        if ($v_encontrado == 1) $v_checked = 'checked';
        else $v_checked = '';

        echo '          <tr>
                            <td>'.$varr_permisos[$i]['id'].'</td>
                            <td>'.$varr_permisos[$i]['nombre'].' | '.$varr_permisos[$i]['codigo'].'</td>
                            <td><input type="checkbox" name="permiso_perfil[]" value="'.$varr_permisos[$i]['id'].'" '.$v_checked.'></td>
                            <input type="hidden" name="chkpermiso-'.$varr_permisos[$i]['id'].'" id="chkpermiso-'.$varr_permisos[$i]['id'].'" value="'.$v_encontrado.'">
                        </tr>';
    }
?>

                    </table>
                </li>
            </ul>

            <ul style="display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; margin-top: 20px;">
                <button style="font-size:12px;background-color:var(--color-azulv2);border:none;margin-top: 5px;" type="button" class="btn btn-primary" onclick="grabaPerfiles()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar perfiles
                </button>
            </ul>
        </div>
    </form>
    </div>
    <!--================== ZONA MODAL -->
    <div class="modal fade" id="InversorDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <ul style="list-style:none;overflow:hidden;">
                <li style="display:block;width:200px;float:left;"><h5 class="modal-title fs-5" id="exampleModalLabel" style="color:#064677;font-weight: bold;">Detalle de la Subasta</h5></li>
                <li style="display:block;width:50px;float:right;"><button type="button" class="btn btn-default" data-dismiss="modal">X</button></li>
            </ul>
        </div>
        <div class="modal-body">
        </div>
        <!--<div class="modal-footer">-->
            <!--<p class="botontransaccionazul" id="btn_grabar_accionistas"><span class="icon-floppy-disk"></span><a href="javascript:guarda_accionistas('accionistas')" style=""> Guardar</a></p>-->
        <!--</div>-->
        </div>
    </div>
    </div>

    <!--=================== ZONA SCRIPT -->
    <script>
        function cambia_permiso(){
            document.getElementById('frm').submit();
        }

        function cambia_perfil(){
            document.getElementById('frm').submit();
        }

        function grabaPermisos(){
            document.getElementById('accion').value = 'permisos';
            var formData = new FormData(document.getElementById("frm"));

            $.ajax({
                url:"permisos_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                alert('Los perfiles fueron asignados');
                document.getElementById('frm').submit();
            });
        }

        function grabaPerfiles(){
            document.getElementById('accion').value = 'perfil';
            var formData = new FormData(document.getElementById("frm"));

            $.ajax({
                url:"permisos_proceso.php",
                type:'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: "html"
            })
            .done(function(rpta){
                alert('Los permisos fueron asignados');
                document.getElementById('frm').submit();
            });
        }
    </script>

</BODY>
</HTML>