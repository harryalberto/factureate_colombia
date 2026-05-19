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
    $acceso = 'FACTURAS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
    </script>
</HEAD>
<?
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$objmaestro = new maestros;
$arrestados = $objmaestro->get_estados('FACTURA');
//seleccion de los filtros y valores iniciales
if ($_SESSION['user']['tipousuario'] == 2 || $_SESSION['user']['tipousuario'] == 5){ //usuario global o pertenece a factureate
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 12;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
} elseif ($_SESSION['user']['tipousuario'] == 3){   // emisores
    $filtrofecha = 'on';
    $filtroestado = 'on';
    $estadoid = 0;
    $ffin = date('Y-m-d');
    $t_fini = strtotime('-180 day', strtotime($ffin));
    $fini = date('Y-m-d', $t_fini);
}
if (isset($_POST['estadoid'])){
    $estadoid = $_POST['estadoid'];
}
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?
    date_default_timezone_set("America/Lima");
    $menu = 'facturas/facturas.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Relaci&oacute;n de Facturas
    </div>
    <div class="frmtransaccion">
        <form name='frm' method='post' id='frm' action="facturas.php">
        <ul>
            <?
            if ($filtroestado == 'on'){
                echo '<li>Estado Factura:</li>
                    <li><select name="estadoid">';
                
                for ($i=0; $i<count($arrestados); $i++){
                    if ($estadoid == 0 && $i == 0) echo '<option value="0" selected>---- Todos ----</option>';
                    if ($arrestados[$i]['id'] == 13) $v_nombre = $arrestados[$i]['nombre'].' (Anotada / En Subasta / Financiamiento / Liquidada)';
                    else $v_nombre = $arrestados[$i]['nombre'];

                    if ($estadoid == $arrestados[$i]['id']) echo '<option value="'.$arrestados[$i]['id'].'" selected>'.$v_nombre.'</option>';
                    else echo '<option value="'.$arrestados[$i]['id'].'">'.$v_nombre.'</option>';
                }

                echo '  </select>
                    </li>';
            }
            ?>
            <li class="botontransaccion" style="width:50px;"><a href="javascript:filtrar()">Filtrar</a></li>
        </ul>
        </form>
    </div>
    <div class="listpag"><? require('pagina_facturas.php'); ?></div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>