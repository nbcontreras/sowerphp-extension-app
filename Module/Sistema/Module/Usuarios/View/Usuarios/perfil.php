<h1>Mi perfil de usuario (<?=$Usuario->usuario?>)</h1>

<h2>Mis datos personales</h2>
<?php
$form = new \sowerphp\general\View_Helper_Form();
echo $form->begin(array(
    'id' => 'datosUsuario',
    'onsubmit' => 'Form.check(\'datosUsuario\')'
));
echo $form->input(array(
    'name' => 'nombre',
    'label' => 'Nombre',
    'value' => $Usuario->nombre,
    'help' => 'Nombre real del usuario',
    'check' => 'notempty',
));
echo $form->input(array(
    'name' => 'usuario',
    'label' => 'Usuario',
    'value' => $Usuario->usuario,
    'help' => 'Nombre de usuario',
    'check' => 'notempty',
));
echo $form->input(array(
    'name' => 'email',
    'label' => 'Email',
    'value' => $Usuario->email,
    'help' => 'Correo electrónico para uso dentro del sistema',
    'check' => 'notempty email',
));
echo $form->input(array(
    'name' => 'hash',
    'label' => 'Hash',
    'value' => $Usuario->hash,
    'help' => 'Hash único para identificar el usuario (32 caracteres).<br />Si desea uno nuevo, borrar este y automáticamente se generará uno nuevo al guardar los cambios',
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

<h2>Grupos a los que pertenezco</h2>
<ul>
<?php
foreach ($grupos as &$grupo)
    echo '<li>',$grupo,'</li>';
?>
</ul>
