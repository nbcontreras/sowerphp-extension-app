<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<h1>Mi perfil de usuario (<?=$_Auth->User->usuario?>)</h1>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos personales</a></li>
        <li role="presentation"><a href="#contrasenia" aria-controls="contrasenia" role="tab" data-toggle="tab">Contraseña</a></li>
        <li role="presentation"><a href="#grupos" aria-controls="grupos" role="tab" data-toggle="tab">Grupos</a></li>
        <li role="presentation"><a href="#auth2" aria-controls="auth2" role="tab" data-toggle="tab">Auth2</a></li>
        <li role="presentation"><a href="#qr" aria-controls="qr" role="tab" data-toggle="tab">QR</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="datos">
            <p>Aquí puede modificar los datos de su usuario.</p>
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
));
if ($changeUsername) {
    echo $form->input(array(
        'name' => 'usuario',
        'label' => 'Usuario',
        'value' => $_Auth->User->usuario,
        'help' => 'Nombre de usuario',
        'check' => 'notempty',
    ));
}
echo $form->input(array(
    'name' => 'email',
    'label' => 'Email',
    'value' => $_Auth->User->email,
    'help' => 'Correo electrónico para uso dentro del sistema',
    'check' => 'notempty email',
));
echo $form->input(array(
    'name' => 'hash',
    'label' => 'Hash',
    'value' => $_Auth->User->hash,
    'help' => 'Hash único para identificar el usuario (32 caracteres).<br />Si desea uno nuevo, borrar este y automáticamente se generará uno nuevo al guardar los cambios',
));
if ($_Auth->User->getLdapPerson() and $_Auth->User->getLdapPerson()->uid != $_Auth->User->usuario) {
    echo $form->input(array(
        'type' => 'div',
        'label' => 'Usuario LDAP',
        'value' => $_Auth->User->getLdapPerson()->uid,
        'help' => 'Usuario LDAP asociado a la cuenta de usuario',
    ));
}
if ($_Auth->User->getEmailAccount() and $_Auth->User->getEmailAccount()->getEmail() != $_Auth->User->email) {
    echo $form->input(array(
        'type' => 'div',
        'label' => 'Email oficial',
        'value' => $_Auth->User->getEmailAccount()->getEmail(),
        'help' => 'Correo electrónico oficial del usuario',
    ));
}
echo $form->end(array(
    'name' => 'datosUsuario',
    'value' => 'Guardar cambios',
));
?>
        </div>
        <div role="tabpanel" class="tab-pane" id="contrasenia">
            <p>A través del siguiente formulario puede cambiar su contraseña.</p>
<?php
echo $form->begin(array(
    'id' => 'cambiarContrasenia',
    'onsubmit' => 'Form.check(\'cambiarContrasenia\')'
));
echo $form->input(array(
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña actual',
    'help' => 'Contraseña actualmente usada por el usuario',
    'check' => 'notempty',
));
echo $form->input(array(
    'type' => 'password',
    'name' => 'contrasenia1',
    'label' => 'Contraseña nueva',
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
        </div>
        <div role="tabpanel" class="tab-pane" id="grupos">
<?php
$grupos = $_Auth->User->groups();
if ($grupos) {
    echo '<p>Los siguientes son los grupos a los que usted pertenece.</p>',"\n";
    echo '<ul>',"\n";
    foreach ($grupos as &$grupo)
        echo '<li>',$grupo,'</li>';
    echo '</ul>',"\n";
} else {
    echo '<p>No pertenece a ningún grupo.</p>',"\n";
}
?>
        </div>
        <div role="tabpanel" class="tab-pane" id="auth2">
<?php
if ($auth2) {
    if (!isset($_Auth->User->token[0])) {
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
?>
        </div>
        <div role="tabpanel" class="tab-pane" id="qr">
            <p>El siguiente código QR provee la dirección de la aplicación junto con su <em>hash</em> de usuario para autenticación.</p>
            <div style="text-align:center">
                <img src="<?=$_base?>/exportar/qrcode/<?=$qrcode?>" alt="hash" />
            </div>
        </div>
  </div>
</div>
