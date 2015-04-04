<h1>Sistema &raquo; Usuarios &raquo; Enviar email a grupos</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'id' => 'grupos',
    'label' => 'Destinatarios',
    'titles' => ['Grupo'],
    'table' => $grupos,
    'display-key' => false,
]);
echo $f->input([
    'type' => 'select',
    'name' => 'enviar_como',
    'label' => 'Enviar como',
    'options' => ['bcc'=>'BCC: copia oculta', 'cc'=>'CC: copia'],
]);
echo $f->input([
    'name' => 'asunto',
    'label' => 'Asunto',
    'help' => 'Se incluirá automáticamente "['.$page_title.'] " al inicio del asunto',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'textarea',
    'name' => 'mensaje',
    'label' => 'Mensaje',
    'check' => 'notempty',
    'help' => 'Se adjuntará listado de grupos y firma (con nombre y correo) de forma automática al final del mensaje',
]);
echo $f->end('Enviar email a usuarios de grupos seleccionados');
