<h1>Ejecutar consulta SQL</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form ();
echo $f->begin(array('onsubmit'=>'Form.check()'));
echo $f->input (array(
    'type'=>'select',
    'name'=>'database',
    'label'=>'Base de datos',
    'options'=>$databases,
    'check'=>'notempty',
    'help'=>'Nombre de la base de datos definida dentro de la configuración en Config/core.php',
));
echo $f->input([
    'type'=>'textarea',
    'name'=>'query',
    'label'=>'Consulta SQL',
    'check'=>'notempty',
    'help'=>'Consulta SQL que se desea ejecutar en la base de datos seleccionada',
    'rows'=>20,
    'attr'=>'style="width:500px"',
]);
echo $f->end('Ejecutar consulta SQL');

if (isset($data)) {
    new \sowerphp\general\View_Helper_Table ($data, 'query_'.$database.'_'.date('U'), true);
}
