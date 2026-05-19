<header>
    <nav>
        <ul>
            <li><img src="../img/transparente.png" style="width:150px;height:1px;"></li>
            <li style="font-size:12px;color:##064677;padding:5px;">
                    <?
                    echo $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];
                    if ($_SESSION['user']['empresaid'] > 0) echo ', '.$_SESSION['user']['empresa'];
                    ?>
                    <span class="icon-user" style="color:#a9a9a9;font-size:20px;font-weight:bold;"></span>
            </li>
            <!--<li style="background-color:#b30a1f;"><a href="../bo-index.php" style="background-color:#b30a1f;color:#ffffff;"><span class="icon-switch"></span>Salir</a></li>-->
            <li><abbr title="Mail a FACTUREATE"><a href="mailto:info-rd@factureate.com"><span class="icon-mail3" style="color:var(--color-gris-oscuro);font-size:20px;font-weight:bold;"></span></a></abbr></li>
            <li><a href="../bo-index.php"><span class="icon-exit" style="color:var(--color-rojo);font-size:20px;font-weight: bold;"></span></a></li>
        </ul>
    </nav>
    <div>
        <a href="#" class="logo-menu"><img src="../img/logo.png" class="logo-menu_img"></a>
    </div>
</header>
<div style="overflow:hidden;margin:auto;font-size:12px;color:#ffffff;background-color:#064677;padding:0px 20px;height:2px;"></div>
