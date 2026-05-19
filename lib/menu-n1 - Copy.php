<div class="menu_izquierda">
    <ul>
        <?
        $styleli = 'style="background-color:#232E3B;"';
        $menuid = 0;
        echo '  <li style="height:50px;"></li>
                <li style="height:1px;background-color:#3F5165;"></li>';

        for ($i=0; $i<count($_SESSION['menu']); $i++){
            if ($_SESSION['menu'][$i]['pagina'] == $menu){
                echo '  <li style="background-color:#e1a700;"><a href="../'.$_SESSION['menu'][$i]['pagina'].'" style="color:#003955;font-weight: bold;">'.$_SESSION['menu'][$i]['menu'].'</a></li>';
                $menuid = $_SESSION['menu'][$i]['menuid'];
            } else
                echo '<li><a href="../'.$_SESSION['menu'][$i]['pagina'].'">'.$_SESSION['menu'][$i]['menu'].'</a></li>';
            
            echo '<li style="height:1px;background-color:#3F5165;"></li>';
        }
        ?>
    </ul>
</div>