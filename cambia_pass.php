<?php
session_destroy();
session_start();
?>
<HTML>
<HEAD>
    <title>Factureate</title>
    <meta content='text/html; charset=UTF-8' http-equiv='content-type'>
    <meta name='description' content='Factureate'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' type='text/css' href='css/factureate.css'>
    <link rel='stylesheet' type='text/css' href='css/style.css'>
    <link rel='shortcut icon' href='img/factureate.ico'>
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
    
    <link href='css/all.min.css' rel='stylesheet'>
    
</HEAD>
<BODY bottommargin=0 leftmargin=0 topmargin=0>
    <div class="login-container" style="height:400px;">
        <h1 class="title-login" style="padding-left: 2px;padding-right: 2px;">Cambia Password</h1>
        <div class="img-login">
            <img src="img/logo.png" alt="Factureate">
        </div>
        <form class="form-login" name='frm' method='post'>
            <input class="input-login-on" style="margin-bottom:5px;" type="text" name="usuario" id="usuario" placeholder="Documento identificacion" autofocus>
            <input class="input-login-on" type="text" name="password" id="password" placeholder="Password Actual" autofocus>
            <p style="font-size:10px;">El nuevo password ingresado solo debe contener numeros y letras, no se permiten caracteres especiales!!</p>
            <input class="input-login-on" type="password" name="new_password" id="new_password" placeholder="Password Nuevo" autofocus>
            <input class="input-login-on" type="password" name="renew_password" id="renew_password" placeholder="Re Password Nuevo" autofocus>
            <!--<button class="btn-login" onclick=recuperar_pass()>Recuperar password</button>-->
            <button type="button" class="btn btn-primary" style="background-color: var(--color-amarillo);border-color: var(--color-amarillo);" onclick="cambiar_pass()">
                <i class="fa-solid fa-lock"></i> Cambiar password
            </button>
        </form>
    </div>

    <!--#################################
    ############ FUNCIONES JS -->
    <script>
        function cambiar_pass(){
            var v_documento = $('#usuario').val();
            var v_password = $('#password').val();
            var v_new_password = $('#new_password').val();
            var v_renew_password = $('#renew_password').val();
            var i, caracter, contador = 0;
            var v_procede = 1;

            //VERIFICA USUARIO
            if (v_documento == ''){
                alert('El documento de identificacion no puede estar vacio');
                v_procede = 0;
            }
            
            // VERIFICACION QUE SOLO CONTENGA NUMEROS Y LETRAS
            if (v_procede > 0){
                for (i = 0; i < v_new_password.length; i++){
                    caracter = v_new_password.charCodeAt(i);

                    if (caracter < 48) contador = contador + 1;
                    if (caracter > 57 && caracter < 65) contador = contador + 1;
                    if (caracter > 90 && caracter < 97) contador = contador + 1;
                    if (caracter > 122) contador = contador + 1;
                }

                if (contador > 0){
                    alert('El nuevo password no puede contener caracteres especiales solo letras mayusculas, minusculas y numeros');
                    v_procede = 0;
                } else {
                    if (v_new_password != v_renew_password){
                        alert('El nuevo password ingresado no coincide');
                        v_procede = 0;
                    }
                }
            }
            
            if (v_procede > 0){
                $.ajax({
                    url:"cambia_pass_proceso.php",
                    type:'post',
                    data: {
                        "documento" : v_documento,
                        "password" : v_password,
                        "new_password" : v_new_password,
                        "renew_password" : v_renew_password
                    }
                })
                .done(function(rpta){
                    if (rpta == 1) alert('El password fue cambiado satisfactoriamente, identifiquese nuevamente con el nuevo password');
                    else alert('Lo sentimos los datos del usuario ingresado no son validos');
                    
                    location.href = 'bo-index.php';
                });
            }
        }
    </script>
</BODY>
</HTML>