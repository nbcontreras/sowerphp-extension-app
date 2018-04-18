<div class="page-header"><h1>Recuperación de contraseña</h1></div>
<p>Las instrucciones para recuperar su contraseña serán envíadas a su correo
electrónico, por favor ingrese su usuario o email a continuación:</p>
<?php
$f = new \sowerphp\general\View_Helper_Form ();
echo $f->begin(array('focus'=>'idField', 'onsubmit'=>'Form.check()'));
echo $f->input (array(
    'name'=>'id',
    'label'=>'Usuario o email',
    'check'=>'notempty'
));
echo $f->end('Solicitar email');
