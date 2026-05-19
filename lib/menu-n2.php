<?
$obj_submenu = new seguridad;
$arr_menu2 = $obj_submenu->get_submenu($menuid, $_SESSION['user']['perfilid']);
?>
<div style="overflow:hidden;text-align:center;font-size: 14px;color:#ffffff;background-color:#189bd8;height:30px;">
    <ul style="list-style:none;overflow:hidden;">
        <?
        $styleliselect = 'style="background-color:#aaaaaa;color:#000000;text-decoration: none;"';
        $styleli = 'style="text-decoration: none;color:#ffffff;"';

        for ($i=0; $i<count($arr_menu2); $i++){
            if ($arr_menu2[$i]['pagina'] == $pagina) echo '<li><a href="../'.$arr_menu2[$i]['pagina'].'" '.$styleliselect.'>'.$arr_menu2[$i]['nombre'].'</a></li>';
            else echo '<li style="float:left;display: block;padding:5px 5px;width:200px;text-align:center;"><a href="../'.$arr_menu2[$i]['pagina'].'" '.$styleli.'>'.$arr_menu2[$i]['nombre'].'</a></li>';
        }
        ?>
    </ul>
</div>