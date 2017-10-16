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
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#contrasenia" aria-controls="contrasenia" role="tab" data-toggle="tab">Contraseña</a></li>
        <li role="presentation"><a href="#auth" aria-controls="auth" role="tab" data-toggle="tab">Auth</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="datos">
            <div class="row">
                <div class="col-sm-9">
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
    'attr' => 'maxlength="50"',
));
if ($changeUsername) {
    echo $form->input(array(
        'name' => 'usuario',
        'label' => 'Usuario',
        'value' => $_Auth->User->usuario,
        'help' => 'Nombre de usuario',
        'check' => 'notempty',
        'attr' => 'maxlength="30"',
    ));
}
echo $form->input(array(
    'name' => 'email',
    'label' => 'Email',
    'value' => $_Auth->User->email,
    'help' => 'Correo electrónico para uso dentro del sistema',
    'check' => 'notempty email',
    'attr' => 'maxlength="50"',
));
echo $form->input(array(
    'name' => 'hash',
    'label' => 'Hash',
    'value' => $_Auth->User->hash,
    'help' => 'Hash único para identificar el usuario (32 caracteres).<br />Si desea uno nuevo, borrar este y automáticamente se generará uno nuevo al guardar los cambios',
    'attr' => 'maxlength="32" onclick="this.select()" onmouseover="this.type=\'text\'" onmouseout="this.type=\'password\'"',
));
echo $form->input(array(
    'name' => 'api_key',
    'label' => 'API key',
    'value' => base64_encode($_Auth->User->hash.':X'),
    'help' => 'Valor de la cabecera Authorization de HTTP para autenticar en la API usando sólo la API key, la cual está basada en el hash del usuario',
    'attr' => 'readonly="readonly" onclick="this.select()" onmouseover="this.type=\'text\'" onmouseout="this.type=\'password\'"',
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
                <div class="col-sm-3">
                    <a href="https://gravatar.com" title="Cambiar imagen en Gravatar" target="_blank">
                        <img src="<?=$_Auth->User->getAvatar(200)?>" alt="Avatar" class="center img-responsive thumbnail" />
                    </a>
                    <div class="text-center small" style="margin-top:0.5em">
                        <a href="https://gravatar.com" title="Cambiar imagen en Gravatar" target="_blank">[cambiar imagen]</a>
                    </div>
                </div>
            </div>
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
        <div role="tabpanel" class="tab-pane" id="auth">
<?php if ($auth2) : ?>
            <div class="panel panel-default">
                <div class="panel-heading">Autenticación secundaria con <?=$auth2['name']?></div>
                <div class="panel-body">
<?php
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
?>
                </div>
            </div>
<?php endif; ?>
            <div class="panel panel-default">
                <div class="panel-heading">Código QR autenticación</div>
                <div class="panel-body">
                    <p>El siguiente código QR provee la dirección de la aplicación junto con su <em>hash</em> de usuario para autenticación.</p>
                    <img src="<?=$_base?>/exportar/qrcode/<?=$qrcode?>" alt="auth_qr" class="center img-responsive thumbnail" style="display:none" id="auth_qr" />
                    <a href="#" onclick="$('#auth_qr').show(); $('#auth_qr_show').hide(); $('#auth_qr_hide').show(); return false;" class="btn btn-default" id="auth_qr_show">Ver código QR</a>
                    <a href="#" onclick="$('#auth_qr').hide(); $('#auth_qr_hide').hide(); $('#auth_qr_show').show(); return false;" class="btn btn-default" style="display:none" id="auth_qr_hide">Ocultar código QR</a>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Grupos y permisos</div>
                <div class="panel-body">
<?php
$grupos = $_Auth->User->groups();
if ($grupos) {
    echo '<p>Los siguientes son los grupos a los que usted pertenece:</p>',"\n";
    echo '<ul>',"\n";
    foreach ($grupos as &$grupo)
        echo '<li>',$grupo,'</li>';
    echo '</ul>',"\n";
    echo '<p>A través de estos grupos, tiene acceso a los siguientes recursos:</p>',"\n";
    echo '<ul>',"\n";
    foreach ($_Auth->User->auths() as &$auth)
        echo '<li>',$auth,'</li>';
    echo '</ul>',"\n";
} else {
    echo '<p>No pertenece a ningún grupo.</p>',"\n";
}
?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
$(function() {
    $('#hashField').attr('type', 'password');
    $('#api_keyField').attr('type', 'password');
});
</script>
