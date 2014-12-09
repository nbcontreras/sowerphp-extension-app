<h1>Mi perfil de usuario (<?=$_Auth->User->usuario?>)</h1>

<h2>Mis datos personales</h2>
<img src="<?=$_base?>/exportar/qrcode/<?=$qrcode?>" alt="hash" class="fright" style="width:100px" title="Código QR para autenticación en la aplicación"/>
<?php
$form = new \sowerphp\general\View_Helper_Form();
echo $form->begin(array(
    'id' => 'datosUsuario',
    'onsubmit' => 'Form.check(\'datosUsuario\')'
));
echo $form->input(array(
    'name' => 'nombre',
    'label' => 'Nombre',
    'value' => $_Auth->User->nombre,
    'help' => 'Nombre real del usuario',
    'check' => 'notempty',
    'attr' => 'style="width:350px"',
));
if ($changeUsername) {
    echo $form->input(array(
        'name' => 'usuario',
        'label' => 'Usuario',
        'value' => $_Auth->User->usuario,
        'help' => 'Nombre de usuario',
        'check' => 'notempty',
        'attr' => 'style="width:350px"',
    ));
}
echo $form->input(array(
    'name' => 'email',
    'label' => 'Email',
    'value' => $_Auth->User->email,
    'help' => 'Correo electrónico para uso dentro del sistema',
    'check' => 'notempty email',
    'attr' => 'style="width:350px"',
));
echo $form->input(array(
    'name' => 'hash',
    'label' => 'Hash',
    'value' => $_Auth->User->hash,
    'help' => 'Hash único para identificar el usuario (32 caracteres).<br />Si desea uno nuevo, borrar este y automáticamente se generará uno nuevo al guardar los cambios',
    'attr' => 'style="width:350px"',
));
echo $form->end(array(
    'name' => 'datosUsuario',
    'value' => 'Guardar cambios',
));
?>

<h2>Cambiar mi contraseña</h2>
<?php
echo $form->begin(array(
    'id' => 'cambiarContrasenia',
    'onsubmit' => 'Form.check(\'cambiarContrasenia\')'
));
echo $form->input(array(
    'type' => 'password',
    'name' => 'contrasenia1',
    'label' => 'Contraseña',
    'help' => 'Contraseña que se quiere utilizar',
    'check' => 'notempty',
));
echo $form->input(array(
    'type' => 'password',
    'name' => 'contrasenia2',
    'label' => 'Repetir contraseña',
    'help' => 'Repetir la contraseña que se haya indicado antes',
    'check' => 'notempty',
));
echo $form->end(array(
    'name' => 'cambiarContrasenia',
    'value'=>'Cambiar contraseña',
));
?>

<?php
if ($auth2) {
    if (!isset($_Auth->User->token[0])) {
        echo '<h2>Crear token</h2>',"\n";
        echo '<p>Aquí podrá crear su token para autorizar el ingreso a la aplicación con el sistema secundario <a href="',$auth2['url'],'" target="_blank">',$auth2['name'],'</a>.</p>',"\n";
        echo $form->begin([
            'id' => 'crearToken',
            'onsubmit' => 'Form.check(\'crearToken\')'
        ]);
        echo $form->input(array(
            'name' => 'codigo',
            'label' => 'Código',
            'help' => 'Código para generar token y parear dispositivo',
            'check' => 'notempty',
        ));
        echo $form->end([
            'name' => 'crearToken',
            'value' => 'Crear token',
        ]);
    } else {
        echo '<h2>Destruir token</h2>',"\n";
        echo '<p>Aquí podrá eliminar su token de <a href="',$auth2['url'],'" target="_blank">',$auth2['name'],'</a>.</p>',"\n";
        echo $form->begin([
            'onsubmit' => 'Form.checkSend(\'¿Está seguro de querer destruir su token?\')'
        ]);
        echo $form->end([
            'name' => 'destruirToken',
            'value' => 'Destruir token',
        ]);
    }
}

// mostrar grupos si el usuario pertenece a alguno
$grupos = $_Auth->User->groups();
if ($grupos) {
    echo '<h2>Grupos a los que pertenezco</h2>',"\n";
    echo '<ul>',"\n";
    foreach ($grupos as &$grupo)
        echo '<li>',$grupo,'</li>';
    echo '</ul>',"\n";
}
