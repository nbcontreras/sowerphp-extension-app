<h1>Listado de <?=$models?></h1>
<p><?=$comment?></p>

<?php

// preparar títulos de columnas (con link para ordenar por dicho campo)
$titles = array();
foreach ($columns as $column => &$info) {
    $titles[] = $info['name'].'<br />'.
        '<a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/D'.$searchUrl.'" title="Ordenar descendentemente por '.$info['name'].'"><img src="'.$_base.'/img/icons/16x16/actions/down.png" alt="" /></a>'.
        '<a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/A'.$searchUrl.'" title="Ordenar ascendentemente por '.$info['name'].'"><img src="'.$_base.'/img/icons/16x16/actions/up.png" alt="" /></a>'
    ;
}
$titles[] = 'Acciones';

// crear arreglo para la tabla y agregar títulos de columnas
$data = array($titles);

// agregar fila para búsqueda mediante formulario
$row = array();
$form = new \sowerphp\general\View_Helper_Form ('normal');
$optionsBoolean = array(array('', 'Seleccione una opción'), array('1', 'Si'), array('0', 'No'));
foreach ($columns as $column => &$info) {
    // si es un archivo
    if ($info['type']=='file') {
        $row[] = '';
    }
    // si es de tipo boolean se muestra lista desplegable
    else if ($info['type']=='boolean' || $info['type']=='tinyint(1)') {
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $optionsBoolean, 'selected' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es llave foránea
    else if ($info['fk']) {
        $class = 'Model_'.\sowerphp\core\Utility_Inflector::camelize(
            $info['fk']['table']
        );
        $classs = $fkNamespace[$class].'\Model_'.\sowerphp\core\Utility_Inflector::camelize(
            \sowerphp\core\Utility_Inflector::pluralize($info['fk']['table'])
        );
        $objs = new $classs();
        $options = $objs->getList();
        array_unshift($options, array('', 'Seleccione una opción'));
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $options, 'selected' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es cualquier otro tipo de datos
    else {
        $row[] = $form->input(array('name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
    }
}
$row[] = '<input type="image" src="'.$_base.'/img/icons/16x16/actions/search.png" alt="Buscar" title="Buscar" />';
$data[] = $row;

// crear filas de la tabla
foreach ($Objs as &$obj) {
    $row = array();
    foreach ($columns as $column => &$info) {
        // si es un archivo
        if ($info['type']=='file') {
            if ($obj->{$column.'_size'})
                $row[] = '<a href="'.$_base.$module_url.$controller.'/d/'.$column.'/'.urlencode($obj->id).'"><img src="'.$_base.'/img/icons/16x16/actions/download.png" alt="" /></a>';
            else
                $row[] = '';
        }
        // si es boolean se usa Si o No según corresponda
        else if ($info['type']=='boolean' || $info['type']=='tinyint(1)') {
            $row[] = $obj->{$column}=='t' || $obj->{$column}=='1' ? 'Si' : 'No';
        }
        // si es llave foránea
        else if ($info['fk']['table']) {
            // si no es vacía la columna
            if (!empty($obj->{$column})) {
                $method = 'get'.\sowerphp\core\Utility_Inflector::camelize($info['fk']['table']);
                $row[] = $obj->$method($obj->$column)->{$info['fk']['table']};
            } else {
                $row[] = '';
            }
        }
        // si es cualquier otro tipo de datos
        else {
            $row[] = $obj->{$column};
        }
    }
    $pkValues = $obj->getPkValues();
    $pkURL = implode('/', array_map('urlencode', $pkValues));
    $actions = '<a href="'.$_base.$module_url.$controller.'/editar/'.$pkURL.$listarFilterUrl.'" title="Editar"><img src="'.$_base.'/img/icons/16x16/actions/edit.png" alt="" /></a>';
    if ($deleteRecord) {
        $actions .= ' <a href="'.$_base.$module_url.$controller.'/eliminar/'.$pkURL.$listarFilterUrl.'" title="Eliminar" onclick="return eliminar(\''.$model.'\', \''.implode(', ', $pkValues).'\')"><img src="'.$_base.'/img/icons/16x16/actions/delete.png" alt="" /></a>';
    }
    $row[] = $actions;
    $data[] = $row;
}

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer ([
    'link' => $_base.$module_url.$controller,
    'linkEnd' => $linkEnd,
    'listarFilterUrl' => $listarFilterUrl
]);
$maintainer->setId($model);
echo $maintainer->listar ($data, $pages, $page);
