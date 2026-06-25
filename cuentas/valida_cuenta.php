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
    $acceso = 'CTAEFE';
    require("../lib/valida-acceso.php");
?>
    <script type="text/javascript">
        function filtrar(){
            document.frm.submit();
        }
        function closemodal(){
            $('.modalclase').fadeOut();
        }
        function acciones(accion){
            document.frm.action = "confirmar_saldo.php";
            document.frm.submit(); 
        }
    </script>
</HEAD>
<?php
/*--------------------------------------------------------*/
//------ LOGICA NO VISIBLE ------
$obj_cuenta = new cuentas;
$obj_usuario = new seguridad;
$obj_empresa = new maestros;

$arr_transito = $obj_cuenta->get_saldos_transito();
/*--------------------------------------------------------*/
?>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
<?php
    date_default_timezone_set($_SESSION['user']['zona_horaria']);
    $menu = 'cuentas/valida_cuenta.php';
    //------ PARTE SUPERIOR ------
    require("../lib/superior.php");
    //------ PARTE IZQUIERDA ------
    require("../lib/menu-n1.php");
?>
    <!------ CUERPO VARIABLE ------>
    <div style="overflow:hidden;text-align:center;font-size: 18px;font-weight: bold;color:#064677;padding:10px;max-width:700px;margin:auto;">
        Depositos de Inversores en transito por confirmar
    </div>

    <hr>

    <form name='frm' method='post' id='frm' enctype="multipart/form-data">
        <div style="overflow:hidden;margin:5px;padding:5px;">
            <table class="tabla_resize">
                <thead>
                    <tr>
                        <th scope="col">TRANSF ID</th>
                        <th scope="col">FECHA</th>
                        <th scope="col">INVERSOR</th>
                        <th scope="col">BANCO</th>
                        <th scope="col">MONEDA</th>
                        <th scope="col">MONTO</th>
                        <th scope="col">COMPROBANTE</th>
                        <th scope="col">CONFIRM</th>
                        <th scope="col">OBS</th>
                        <th scope="col">REJECT</th>
                    </tr>
                </thead>
                <tbody>
<?php
    for ($j=0; $j<count($arr_transito); $j++){
        $fecha = date('d-m-Y',strtotime($arr_transito[$j]['f_creacion']));
        $v_comp_path = $arr_transito[$j]['comp_path'];

        echo '      <tr>
                        <td data-label="TRANSF ID">'.$arr_transito[$j]['saldo_id'].'</td>
                        <td data-label="FECHA">'.$fecha.'</td>
                        <td data-label="INVERSOR">'.$arr_transito[$j]['nombre_inversor'].'</td>
                        <td data-label="BANCO">'.$arr_transito[$j]['nombre_banco'].'</td>
                        <td data-label="MONEDA">'.$arr_transito[$j]['moneda_simbolo'].'</td>
                        <td data-label="MONTO">'.number_format($arr_transito[$j]['monto'],2,'.',',').'</td>
                        <td data-label="COMPROBANTE"><a href="'.$v_comp_path.'" target="_blank"><i class="fa-solid fa-file-invoice" style="font-size:16px;"></i></a></td>
                        <td data-label="CONFIRM"><button type="button" class="btn btn-primary" onclick="confirmaSaldo('.$arr_transito[$j]['saldo_id'].')" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
                            <i class="fa-solid fa-circle-check" style="font-size:14px;"></i></button>
                        </td>
                        <td data-label="OBS"><button type="button" class="btn btn-primary" onclick="observaSaldo()" style="font-size:12px;background-color:var(--color-azulv2);border:none;">
                            <i class="fa-solid fa-hand-pointer" style="font-size:14px;"></i></button>
                        </td>
                        <td data-label="REJECT"><button type="button" class="btn btn-primary" onclick="rechazaSaldo()" style="font-size:12px;background-color:var(--color-rojo);border:none;">
                            <i class="fa-solid fa-ban" style="font-size:14px;"></i></button>
                        </td>
                    </tr>';
    }
?>
                </tbody>
            </table>
        </div>
    </form>
    <!------ END CUERPO VARIABLE ------>
    <!--###################################
    ########### ZONA MODAL -->
    <div class="modal fade" id="SaldosModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="display:block;float:left;font-weight:bold;"></h5>
            <button type="button" class="btn btn-default" style="display:block;float:right;" data-dismiss="modal">X</button>
          </div>
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
    <!-- LLAMADA AL MODAL -->
     <script>
        function confirmaSaldo(p_st_id){
            $('.modal-title').text('CONFIRMA DEPOSITO INVERSOR');
            $('.modal-body').load('confirma_deposito_modal.php?st_id='+p_st_id,function(){
                $('#SaldosModal').modal({show:true});
            });
        }

        function observaSaldo(){
            alert('Comuniquese directamente con el Inversor');
        }

        function rechazaSaldo(){
            alert('Comuniquese directamente con el Inversor');
        }

        function refresh_page(){
            location.href = 'valida_cuenta.php';
        }
     </script>
</BODY>
</HTML>