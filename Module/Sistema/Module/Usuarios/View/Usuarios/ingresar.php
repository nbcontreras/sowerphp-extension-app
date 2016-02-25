<h1>Iniciar sesión</h1>
<?php
$form = new sowerphp\general\View_Helper_Form();
echo $form->begin(array('focus'=>'usuarioField', 'onsubmit'=>'Form.check()'));
echo $form->input([
    'name'=>'usuario',
    'label'=>'Usuario o email',
    'check'=>'notempty',
]);
echo $form->input([
    'type'=>'password',
    'name'=>'contrasenia',
    'label'=>'Contraseña',
    'check'=>'notempty'
]);
echo $form->input([
    'type'=>'hidden',
    'name'=>'redirect',
    'value'=>$redirect,
]);
if (!empty($public_key)) {
    $captcha = '<div class="g-recaptcha" data-sitekey="'.$public_key.'"></div>';
    $captcha .= '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl='.$language.'"></script>';
    echo $form->input([
        'type'=>'div',
        'label'=>'Captcha',
        'value'=>$captcha,
    ]);
}
echo $form->end('Ingresar');
?>

<?php if ($self_register) : ?>
<p>¿Desea obtener una cuenta de usuario?, <a href="<?=$_base?>/usuarios/registrar">click aquí para registrarse</a>.</p>
<?php endif; ?>
<p>¿No recuerda su usuario o contraseña?, <a href="<?=$_base?>/usuarios/contrasenia/recuperar">click aquí para recuperar</a>.</p>
