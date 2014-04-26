<h1><?=$accion?> <?=$model?></h1>

<?php

// crear formulario
$form = new \sowerphp\general\View_Helper_Form ();
echo $form->begin(array('onsubmit'=>'Form.check()'));

// opciones para select en caso que sea un campo boolean
$optionsBoolean = array(
    array('', 'Seleccione una opción'),
    array('1', 'Si'),
    array('0', 'No')
);

// agregar campos del formulario
foreach ($columns as $column => &$info) {
    // se genera campo de input solo si no es una columna automática
    if (!$info['auto']) {
        // configuración base para campo
        $input = array(
            'name'  => $column,
            'label' => $info['name'],
            'help'  => $info['comment'],
            'check' => (!$info['null']?['notempty']:[])
        );
        // si es un archivo
        if ($info['type']=='file') {
            $input['type'] = 'file';
            echo $form->input($input);
        }
        // si es de tipo text se muestra un textarea
        else if ($info['type']=='text') {
            $input['type'] = 'textarea';
            if (isset($Obj)) $input['value'] = $Obj->{$column};
            echo $form->input($input);
        }
        // si es de tipo boolean se muestra lista desplegable
        else if ($info['type']=='boolean') {
            $input['type'] = 'select';
            $input['options'] = $optionsBoolean;
            if (isset($Obj)) $input['selected'] = $Obj->{$column};
            echo $form->input($input);
        }
        // si es de tipo date se muestra calendario
        else if ($info['type']=='date') {
            $input['type'] = 'date';
            $input['check'][] = 'date';
            if (isset($Obj)) $input['value'] = $Obj->{$column};
            echo $form->input($input);
        }
        // si es llave foránea
        else if ($info['fk']) {
            $class = 'Model_'.\sowerphp\core\Utility_Inflector::camelize(
                $info['fk']['table']
            );
            $classs = $fkNamespace[$class].'\Model_'.\sowerphp\core\Utility_Inflector::camelize(
                \sowerphp\core\Utility_Inflector::pluralize($info['fk']['table'])
            );
            $options = (new $classs())->getList();
            array_unshift($options, array('', 'Seleccione una opción'));
            $input['type'] = 'select';
            $input['options'] = $options;
            if (isset($Obj)) $input['selected'] = $Obj->{$column};
            echo $form->input($input);
        }
        // si el nombre de la columna es contrasenia o clave o password o pass
        else if (in_array($column, array('contrasenia', 'clave', 'password', 'pass'))) {
            $input['type'] = 'password';
            echo $form->input($input);
        }
        // si es cualquier otro tipo de datos
        else {
            if (!empty($info['check']))
                $input['check'] = array_merge($input['check'], $info['check']);
            if (isset($Obj)) $input['value'] = $Obj->{$column};
            echo $form->input($input);
        }
    }
}

// terminar formulario
echo $form->end('Guardar');
?>
<p><span style="color:red">* campo es obligatorio</span></p>
