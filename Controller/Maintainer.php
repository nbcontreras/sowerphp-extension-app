<?php

/**
 * SowerPHP: Minimalist Framework for PHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 */

namespace sowerphp\app;

/**
 * Clase que implementa los métodos básicos de un mantenedor, métodos CRUD.
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-05-04
 */
class Controller_Maintainer extends \Controller_App
{

    private $model; ///< Atributo con el namespace y clase del modelo singular
    private $models; ///< Atributo con el namespace y clase del modelo plural
    private $module_url; ///< Atributo con la url para acceder el módulo

    /**
     * Constructor del controlador
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-22
     */
    public function __construct (\sowerphp\core\Network_Request $request, \sowerphp\core\Network_Response $response)
    {
        parent::__construct ($request, $response);
        $this->setModelName();
        $this->module_url = $this->setModuleUrl ($this->request->params['module']);
    }

    /**
     * Método que asigna los namespaces y nombres de los modelos tanto singular
     * como plural usados por este controlador
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-22
     */
    private function setModelName ()
    {
        $this->models = \sowerphp\core\Utility_Inflector::camelize($this->request->params['controller']);
        $this->model = \sowerphp\core\Utility_Inflector::singularize($this->models);
        $this->set('models', $this->models);
        $this->set('model', $this->model);
        $this->model = '\\'.$this->namespace.'\Model_'.$this->model;
        $this->models = '\\'.$this->namespace.'\Model_'.$this->models;
    }

    /**
     * Método que asigna la url del módulo que se usa en el controlador
     * @param modulo Nombre del módulo donde se generarán los archivos
     * @return URL que se usa para acceder al módulo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-22
     */
    private function setModuleUrl ($modulo = '')
    {
        $partes = explode('.', $modulo);
        $module_url = '';
        foreach ($partes as &$p) {
            $module_url .= \sowerphp\core\Utility_Inflector::underscore($p).'/';
        }
        return '/'.$module_url;
    }

    /**
     * Acción para listar los registros de la tabla
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-26
     */
    public function listar ($page = 1, $orderby = null, $order = 'A')
    {
        $model = $this->model;
        // crear objeto
        $Objs = new $this->models();
        // si se debe buscar se agrega filtro
        $searchUrl = null;
        $search = array();
        if (!empty($_GET['search'])) {
            $searchUrl = '?search='.$_GET['search'];
            $filters = explode(',', $_GET['search']);
            $where = array();
            foreach ($filters as &$filter) {
                list($var, $val) = explode(':', $filter);
                $search[$var] = $val;
                // dependiendo del tipo de datos se ve como filtrar
                if (in_array($model::$columnsInfo[$var]['type'], array('char', 'character varying')))
                    $where[] = $Objs->like($Objs->sanitize($var), $Objs->sanitize('%'.$val.'%'));
                else
                    $where[] = $Objs->sanitize($var)." = '".$Objs->sanitize($val)."'";
            }
            // agregar condicion a la busqueda
            $Objs->setWhereStatement(implode(' AND ', $where));
        }
        // si se debe ordenar se agrega
        if ($orderby) {
            $Objs->setOrderByStatement($orderby.' '.($order=='D'?'DESC':'ASC'));
        }
        // total de registros
        $registers_total = $Objs->count();
        // paginar si es necesario
        if ((integer)$page>0) {
            $registers_per_page = \sowerphp\core\Configure::read('app.registers_per_page');
            $pages = ceil($registers_total/$registers_per_page);
            $Objs->setLimitStatement($registers_per_page, ($page-1)*$registers_per_page);
            if ($page != 1 && $page > $pages) {
                $this->redirect(
                    $this->module_url.$this->request->params['controller'].'/listar/1'.($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl
                );
            }
        }
        // crear variable con las columnas para la vista
        if (!empty($this->columnsView['listar'])) {
            $columns = [];
            foreach ($model::$columnsInfo as $col => &$info) {
                if (in_array($col, $this->columnsView['listar'])) {
                    $columns[$col] = $info;
                }
            }
        } else {
            $columns = $model::$columnsInfo;
        }
        // setear variables
        $this->set(array(
            'module_url' => $this->module_url,
            'controller' => $this->request->params['controller'],
            'page' => $page,
            'orderby' => $orderby,
            'order' => $order,
            'searchUrl' => $searchUrl,
            'search' => $search,
            'Objs' => $Objs->getObjects(),
            'columns' => $columns,
            'registers_total' => $registers_total,
            'pages' => isset($pages) ? $pages : 0,
            'linkEnd' => ($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl,
            'fkNamespace' => $model::$fkNamespace,
            'comment' => $model::$tableComment,
            '_header_extra' => ['js'=>['/js/mantenedor.js']],
            'listarFilterUrl' => '?listar='.base64_encode('/'.$page.($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl),
        ));
        $this->autoRender = false;
        $this->render('Maintainer/listar', 'sowerphp/app');
    }

    /**
     * Acción para crear un registro en la tabla
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-05-04
     */
    public function crear ()
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            $Obj = new $this->model();
            $Obj->set($_POST);
            $msg = $Obj->save() ? 'Registro creado' : 'Registro no creado';
            \sowerphp\core\Model_Datasource_Session::message($msg);
            $this->redirect(
                $this->module_url.$this->request->params['controller'].'/listar'.$filterListar
            );
        }
        // setear variables
        $model = $this->model;
        $this->set(array(
            'columnsInfo' => $model::$columnsInfo,
            'fkNamespace' => $model::$fkNamespace,
            'accion' => 'Crear',
            'columns' => $model::$columnsInfo,
            'listarUrl' => $this->module_url.$this->request->params['controller'].'/listar'.$filterListar,
        ));
        // renderizar
        $this->autoRender = false;
        $this->render('Maintainer/crear_editar', 'sowerphp/app');
    }

    /**
     * Acción para editar un registro de la tabla
     * @param pk Parámetro que representa la PK, pueden ser varios parámetros los pasados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-05-04
     */
    public function editar ($pk)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Obj = new $this->model(func_get_args());
        // si el registro que se quiere editar no existe error
        if(!$Obj->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro ('.implode(', ', func_get_args()).') no existe, no se puede editar'
            );
            $this->redirect(
                $this->module_url.$this->request->params['controller'].'/listar'.$filterListar
            );
        }
        // si no se ha enviado el formulario se mostrará
        if(!isset($_POST['submit'])) {
            $model = $this->model;
            $this->set(array(
                'Obj' => $Obj,
                'columns' => $model::$columnsInfo,
                'fkNamespace' => $model::$fkNamespace,
                'accion' => 'Editar',
                'listarUrl' => $this->module_url.$this->request->params['controller'].'/listar'.$filterListar,
            ));
            // renderizar
             $this->autoRender = false;
            $this->render('Maintainer/crear_editar', 'sowerphp/app');
        }
        // si se envió el formulario se procesa
        else {
            $Obj->set($_POST);
            $msg = $Obj->save() ? 'Registro ('.implode(', ', func_get_args()).') editado' : 'Registro ('.implode(', ', func_get_args()).') no editado';
            \sowerphp\core\Model_Datasource_Session::message($msg);
            $this->redirect(
                $this->module_url.$this->request->params['controller'].'/listar'.$filterListar
            );
        }
    }

    /**
     * Acción para eliminar un registro de la tabla
     * @param pk Parámetro que representa la PK, pueden ser varios parámetros los pasados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-24
     */
    public function eliminar ($pk)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Obj = new $this->model(func_get_args());
        // si el registro que se quiere eliminar no existe error
        if(!$Obj->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro ('.implode(', ', func_get_args()).') no existe, no se puede eliminar'
            );
            $this->redirect(
                $this->module_url.$this->request->params['controller'].'/listar'.$filterListar
            );
        }
        $Obj->delete();
        \sowerphp\core\Model_Datasource_Session::message(
            'Registro ('.implode(', ', func_get_args()).') eliminado'
        );
        $this->redirect(
            $this->module_url.$this->request->params['controller'].'/listar'.$filterListar
        );
    }

    /**
     * Método para subir los archivos de un formulario a la base de datos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    protected function u ($pk)
    {
/*        ${class} = new Model_{class}({pk_parameter});
        $files = array({files});
        foreach($files as &$file) {
            if(isset($_FILES[$file]) && !$_FILES[$file]['error']) {
                $archivo = \sowerphp\general\Utility_File::upload($_FILES[$file]);
                if(is_array($archivo)) {
                    ${class}->saveFile($file, $archivo);
                }
            }
        }*/
    }

    /**
     * Método para descargar un archivo desde la base de datos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    public function d ($file, $pk)
    {
/*        ${class} = new Model_{class}({pk_parameter});
        $this->response->sendFile(array(
            'name' => ${class}->{$campo.'_name'},
            'type' => ${class}->{$campo.'_type'},
            'size' => ${class}->{$campo.'_size'},
            'data' => pg_unescape_bytea(${class}->{$campo.'_data'}),
        ));*/
    }

}
