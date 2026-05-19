<header style="background-color: var(--color-azulv2);margin-bottom: 1px;">
    
    <nav style="background-color: var(--color-azulv2);">
        <!-- icono del menu para mobile -->
        <input type="checkbox" id="check_mm">
        <label for="check_mm" class="checkmenu">
            <i class="fas fa-bars"></i>
        </label>

        <!-- logo en version MM -->
        <a href="#" class="logo-menu_mm">
            <img src="../img/logo_blank.png" class="logo-menu_img_mm">
        </a>

        <!-- nombre de usuario version MM -->
        <span class="nombre_usuario_superior">
            <?php
            echo $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];
            
            if ($_SESSION['user']['empresaid'] > 0 && $_SESSION['user']['empresaid'] != $_SESSION['user']['usuarioid']) 
                echo '('.$_SESSION['user']['empresa'].')';
            ?>
            <span class="icon-user"></span>
        </span>

        <!-- para version grande -->
        <ul id="texto_superior">
            <li id="transparente_superior">
                <img src="../img/transparente.png" class="transparente_superior_img">
            </li>
            <li style="font-size:12px;color:##064677;padding:5px;color: var(--color-gris-claro);" id="nombre_superior">
                    <?php
                    echo $_SESSION['user']['nombre'].' '.$_SESSION['user']['apellido'];
                    if ($_SESSION['user']['empresaid'] > 0 && $_SESSION['user']['empresaid'] != $_SESSION['user']['usuarioid']) 
                        echo '('.$_SESSION['user']['empresa'].')';
                    ?>
                    <span class="icon-user" style="color:var(--color-gris-claro);font-size:20px;font-weight:bold;"></span>
            </li>
            <li id="correo_superior">
                <!--<abbr title="Mail a FACTUREATE"><a href="mailto:info-rd@factureate.com"><span class="icon-mail3" style="color:var(--color-gris-claro);font-size:20px;font-weight:bold;"></span></a></abbr>-->
                <abbr title="Mail a FACTUREATE"><a href="#" onclick="contacta(); return false;"><span class="icon-mail3" style="color:var(--color-gris-claro);font-size:20px;font-weight:bold;"></span></a></abbr>
            </li>
            <li id="salir_superior">
                <a href="../bo-index.php" style="text-decoration: none;"><span class="icon-exit" style="color:var(--color-rojo);font-size:20px;font-weight: bold;"></span></a>
            </li>
        </ul>

        <!-- menu para version MM -->
        <ul id="menu_mm">

<?php
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

            <li>
               <a href="#" onclick="contacta(); return false;"><span class="icon-mail3" style="color:var(--color-gris-claro);font-size:20px;font-weight:bold;"></span> Mail a Factureate
               </a>
            </li>
            <li>
                <a href="../bo-index.php" style="text-decoration: none;">
                    <span class="icon-exit" style="color:var(--color-rojo);font-size:20px;font-weight: bold;"></span> Cerrar Sesion
                </a>
            </li>
            
        </ul>
    </nav>

    <div class="logo-menu">
        <a href="#" class="logo-menu"><img src="../img/logo_blank.png" class="logo-menu_img"></a>
    </div>
</header>

<script>
    function contacta(){
        var respuesta = confirm("El correo de Factureate es info-rd@factureate.com , si desea enviarlo con su gestor de correos presione Aceptar, o si desea enviarlo manualmente presione Cancelar");

        if (respuesta){
            location.href = "mailto:info-rd@factureate.com";
        }
    }
</script>