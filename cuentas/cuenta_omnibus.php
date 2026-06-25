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
<?php
    require("../lib/head.php");
    $acceso = 'CUENTA OMNIBUS';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cta = new cuentas;
$v_arr_detalle_cta = $obj_cta->get_cuenta_omnibus_detalle();
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set("America/Lima");
    $menu = 'cuentas/cuenta_omnibus.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;">
        Cuenta Efectivo Omnibus
    </div>
<?php
    echo '
    <div style="overflow:hidden;margin:1px;padding:2px;">';

    if (count($v_arr_detalle_cta) == 0) echo '
        <ul style="overflow:hidden;list-style:none;">
            <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 14px;">La cuenta OMNIBUS no cuenta con saldo en este momento</li>
        </ul>';
    else {
        $v_moneda_id = $v_arr_detalle_cta[0]['moneda_id'];
        $v_total = 0;
        $v_titulo_cuenta = '';
        $v_detalle_cuenta = '';

        for ($i=0; $i<count($v_arr_detalle_cta); $i++){
            if ($i == 0 || $v_arr_detalle_cta[$i]['moneda_id'] != $v_moneda_id){    //inicio o cambio de moneda
                if ($v_arr_detalle_cta[$i]['moneda_id'] != $v_moneda_id){   // cambio de moneda
                    $v_titulo_cuenta .= number_format($v_total,2,'.',',');
                    $v_detalle_cuenta .= '</tbody></table></ul>';

                    echo '  <ul style="overflow:hidden;list-style:none;">
                                <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 14px;">'.$v_titulo_cuenta.'</li>
                            </ul>'.$v_detalle_cuenta;

                    $v_total = 0;
                }
                //========= TITULO DE LA CUENTA
                $v_titulo_cuenta = 'MONEDA: '.$v_arr_detalle_cta[$i]['moneda'].'  TOTAL: ';
                $v_detalle_cuenta = '<ul style="overflow:hidden;list-style:none;">
                                        <table class="tabla_resize">
                                            <thead><tr>
                                                <th scope="col">ID TITULAR</th>
                                                <th scope="col">NOMBRE</th>
                                                <th scope="col">IDENTIFICACION</th>
                                                <th scope="col">CUENTA ID</th>
                                                <th scope="col">SALDO COMPROMETIDO</th>
                                                <th scope="col">SALDO DISPONIBLE</th>
                                                <th scope="col">TOTAL</th>
                                            </tr></thead>
                                            <tbody>';
            }

            $v_total = $v_total + $v_arr_detalle_cta[$i]['saldo_comprometido'] + $v_arr_detalle_cta[$i]['saldo_disponible'];
            $v_saldo_efectivo = $v_arr_detalle_cta[$i]['saldo_comprometido'] + $v_arr_detalle_cta[$i]['saldo_disponible'];
            if ($v_arr_detalle_cta[$i]['inversionista_id'] == 0) $v_nombre = 'FACTUREATE';
            else $v_nombre = $v_arr_detalle_cta[$i]['nombre'];
            // pinto el detalle
            $v_detalle_cuenta .= '          <tr>
                                                <td data-label="ID TITULAR">'.$v_arr_detalle_cta[$i]['inversionista_id'].'</td>
                                                <td data-label="NOMBRE">'.$v_nombre.'</td>
                                                <td data-label="IDENTIFICACION">'.$v_arr_detalle_cta[$i]['identificacion'].'</td>
                                                <td data-label="CUENTA ID">'.$v_arr_detalle_cta[$i]['cuenta_id'].'</td>
                                                <td data-label="SALDO COMPROMETIDO">'.number_format($v_arr_detalle_cta[$i]['saldo_comprometido'],2,'.',',').'</td>
                                                <td data-label="SALDO DISPONIBLE">'.number_format($v_arr_detalle_cta[$i]['saldo_disponible'],2,'.',',').'</td>
                                                <td data-label="TOTAL">'.number_format($v_saldo_efectivo,2,'.',',').'</td>
                                            </tr>';
        }

        $v_detalle_cuenta .= '</tbody></table></ul>';
        //==== PINTO EL ULTIMO REGISTRO
        $v_titulo_cuenta .= number_format($v_total,2,'.',',');
        echo '  <ul style="overflow:hidden;list-style:none;">
                    <li style="display:block;margin:1px 5px;width:80%;float:left;padding:0px 5px;font-weight: bold;FONT-SIZE: 14px;">'.$v_titulo_cuenta.'</li>
                </ul>'.$v_detalle_cuenta;
    }
?>
    </div>
    <!------ END CUERPO VARIABLE ------>
</BODY>
</HTML>