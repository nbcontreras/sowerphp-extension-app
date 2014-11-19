<h1>Iniciar sesión</h1>
<?php
$form = new sowerphp\general\View_Helper_Form();
echo $form->begin(array('focus'=>'usuario', 'onsubmit'=>'Form.check()'));
echo $form->input([
    'name'=>'usuario',
    'label'=>'Usuario',
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
    echo $form->input([
        'type'=>'div',
        'label'=>'Captcha',
        'value'=>recaptcha_get_html($public_key, null, true),
        'check'=>'notempty',
    ]);
}
echo $form->end('Ingresar');

if ($self_register) :
?>
<p>¿Desea obtener una cuenta de usuario?, <a href="<?php echo $_base;
?>/usuarios/registrar">click aquí para registrarse</a>.</p>
<?php endif; ?>

<p>¿No recuerda su usuario o contraseña?, <a href="<?php echo $_base;
?>/usuarios/contrasenia/recuperar">click aquí para recuperar</a>.</p>
