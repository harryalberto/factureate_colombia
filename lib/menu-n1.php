<?php
    if (count($_SESSION['menu']) > 10) $v_heigth_menu = 'style="height: 60px;"';
    else $v_heigth_menu = '';
?>

<div class="menu_superior" <?echo $v_heigth_menu;?>>
    <ul>

<?php
    //$styleli = 'style="background-color:#232E3B;"';
    $menuid = 0;
        
    for ($i=0; $i<count($_SESSION['menu']); $i++){
        if ($_SESSION['menu'][$i]['pagina'] == $menu){
            echo '  
        <li style="background-color:var(--color-oro);"><a href="../'.$_SESSION['menu'][$i]['pagina'].'" style="color:var(--color-azulv2);font-weight: bold;">'.$_SESSION['menu'][$i]['menu'].'</a></li>';
            $menuid = $_SESSION['menu'][$i]['menuid'];
        } else
            echo '
        <li><a href="../'.$_SESSION['menu'][$i]['pagina'].'">'.$_SESSION['menu'][$i]['menu'].'</a></li>';

        echo '
        <li style="widht:1px;padding-left:1px;padding-right:1px;background-color:var(--color-gris-claro);"></li>';
    }
?>
    </ul>
</div>