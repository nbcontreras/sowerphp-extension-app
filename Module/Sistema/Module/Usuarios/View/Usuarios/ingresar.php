<div class="container">
    <div class="text-center mt-4 mb-4">
        <a href="<?=$_base?>/"><img src="<?=$_base?>/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px" /></a>
    </div>
    <div class="row">
        <div class="offset-md-3 col-md-6">
<?php
$messages = \sowerphp\core\Model_Datasource_Session::message();
foreach ($messages as $message) {
    $icons = [
        'success' => 'ok',
        'info' => 'info-sign',
        'warning' => 'warning-sign',
        'danger' => 'exclamation-sign',
    ];
    echo '<div class="alert alert-',$message['type'],'" role="alert">',"\n";
    echo '    <span class="glyphicon glyphicon-',$icons[$message['type']],'" aria-hidden="true"></span>',"\n";
    echo '    <span class="visually-hidden">',$message['type'],': </span>',$message['text'],"\n";
    echo '    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="Cerrar">&times;</a>',"\n";
    echo '</div>'."\n";
}
?>
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4">Ingresar</h1>
                    <form action="<?=$_base?>/usuarios/ingresar" method="post" onsubmit="return Form.check()" class="mb-4">
                        <div class="mb-3">
                            <label for="user" class="visually-hidden">Usuario</label>
                            <input type="text" name="usuario" id="user" class="form-control form-control-lg" required="required" placeholder="Usuario o correo electrónico">
                        </div>
                        <div class="mb-3">
                            <label for="pass" class="visually-hidden">Contraseña</label>
                            <input type="password" name="contrasenia" id="pass" class="form-control form-control-lg" required="required" placeholder="Contraseña">
                        </div>
<?php if ($auth2_token_enabled) : ?>
                        <div class="mb-3">
                            <label for="auth2" class="visually-hidden">Token 2FA</label>
                            <input type="text" name="auth2_token" id="auth2" class="form-control form-control-lg" placeholder="Token 2FA si es necesario">
                        </div>
<?php endif; ?>
<?php if (!empty($public_key)) : ?>
                        <div class="g-recaptcha mb-3" data-sitekey="<?=$public_key?>" style="width:304px;margin:0 auto"></div>
                        <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?=$language?>"></script>
<?php endif; ?>
                        <input type="hidden" name="redirect" value="<?=$redirect?>" />
                        <div class="d-grid gap-2 col-6 mx-auto">
                            <button type="submit" class="btn btn-primary btn-lg">Iniciar sesión</button>
                        </div>
                    </form>
                    <p class="text-center small"><a href="<?=$_base?>/usuarios/contrasenia/recuperar">¿perdió su contraseña?</a></p>
                </div>
            </div>
<?php if ($self_register) : ?>
            <p class="text-center small mt-4">¿no tiene cuenta? <a href="<?=$_base?>/usuarios/registrar">¡regístrese!</a></p>
<?php endif; ?>
        </div>
    </div>
    <script> $(function() { $("#user").focus(); }); </script>
</div>
