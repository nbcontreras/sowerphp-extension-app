<h1>Recuperación de contraseña del usuario <em><?=$usuario?></em></h1>
<p>Aquí podrá crear una nueva contraseña para su cuenta de usuario.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form ();
echo $f->begin(array('focus'=>'codigoField', 'onsubmit'=>'Form.check()'));
echo $f->input (array(
    'type'=>'hidden',
    'name'=>'codigo',
    'value'=>$codigo,
));
echo $f->input (array(
    'type'=>'password',
    'name'=>'contrasenia1',
    'label'=>'Contraseña',
    'check'=>'notempty'
));
echo $f->input (array(
    'type'=>'password',
    'name'=>'contrasenia2',
    'label'=>'Repetir contraseña',
    'check'=>'notempty'
));
echo $f->end('Cambiar contraseña');
