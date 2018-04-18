<div class="page-header"><h1>Registro de nuevo usuario</h1></div>
<?php
$form = new sowerphp\general\View_Helper_Form();
echo $form->begin(array('focus'=>'nombreField', 'onsubmit'=>'Form.check()'));
echo $form->input([
    'name'=>'nombre',
    'label'=>'Nombre',
    'check'=>'notempty',
    'attr'=>'maxlength="50"',
]);
echo $form->input([
    'name'=>'usuario',
    'label'=>'Usuario',
    'check'=>'notempty',
    'attr'=>'maxlength="30"',
]);
echo $form->input([
    'name'=>'email',
    'label'=>'Email',
    'check'=>'notempty email',
    'attr'=>'maxlength="50"',
    'help'=>'La contraseña será enviada a su email'
]);
if (isset($terms)) {
    echo $form->input([
        'type'=>'checkbox',
        'name'=>'terms_ok',
        'label'=>'Términos',
        'check'=>'notempty',
        'help'=>'Acepto los <a href="'.$terms.'" target="_blank">términos y condiciones de uso</a> del servicio',
        'attr'=>'onclick="this.value = this.checked ? 1 : 0"',
    ]);
}
if (!empty($public_key)) {
    $captcha = '<div class="g-recaptcha" data-sitekey="'.$public_key.'"></div>';
    $captcha .= '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl='.$language.'"></script>';
    echo $form->input([
        'type'=>'div',
        'label'=>'Captcha',
        'value'=>$captcha,
    ]);
}
echo $form->end('Registrar');
