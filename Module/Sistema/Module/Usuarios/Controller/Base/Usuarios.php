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

// namespace del controlador
namespace sowerphp\app\Sistema\Usuarios;

/**
 * Clase abstracta para el controlador asociado a la tabla usuario de la base
 * de datos
 * Comentario de la tabla: Usuarios de la aplicación
 * Esta clase permite controlar las acciones básicas entre el modelo y vista
 * para la tabla usuario, o sea implementa métodos CRUD
 * @author SowerPHP Code Generator
 * @version 2014-04-05 17:32:18
 */
abstract class Controller_Base_Usuarios extends \Controller_App
{

    /**
     * Controlador para listar los registros de tipo Usuario
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function listar ($page = 1, $orderby = null, $order = 'A')
    {
        // crear objeto
        $Usuarios = new Model_Usuarios();
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
                if (in_array(Model_Usuario::$columnsInfo[$var]['type'], array('char', 'character varying')))
                    $where[] = $Usuarios->like($var, $val);
                else
                    $where[] = $Usuarios->sanitize($var)." = '".$Usuarios->sanitize($val)."'";
            }
            // agregar condicion a la busqueda
            $Usuarios->setWhereStatement(implode(' AND ', $where));
        }
        // si se debe ordenar se agrega
        if ($orderby) {
            $Usuarios->setOrderByStatement($orderby.' '.($order=='D'?'DESC':'ASC'));
        }
        // total de registros
        $registers_total = $Usuarios->count();
        // paginar si es necesario
        if ((integer)$page>0) {
            $registers_per_page = \sowerphp\core\Configure::read('app.registers_per_page');
            $pages = ceil($registers_total/$registers_per_page);
            $Usuarios->setLimitStatement($registers_per_page, ($page-1)*$registers_per_page);
            if ($page != 1 && $page > $pages) {
                $this->redirect(
                    $this->module_url.'usuarios/listar/1'.($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl
                );
            }
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
            'Usuarios' => $Usuarios->getObjects(),
            'columnsInfo' => Model_Usuario::$columnsInfo,
            'registers_total' => $registers_total,
            'pages' => isset($pages) ? $pages : 0,
            'linkEnd' => ($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl,
            'fkNamespace' => Model_Usuario::$fkNamespace,
        ));
    }
    
    /**
     * Controlador para crear un registro de tipo Usuario
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function crear ()
    {
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            $Usuario = new Model_Usuario();
            $Usuario->set($_POST);
            $Usuario->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario creado'
            );
            $this->redirect(
                $this->module_url.'usuarios/listar'
            );
        }
        // setear variables
        $this->set(array(
            'columnsInfo' => Model_Usuario::$columnsInfo,
            'fkNamespace' => Model_Usuario::$fkNamespace,
            'accion' => 'Crear',
        ));
        // renderizar
        $this->autoRender = false;
        $this->render('Usuarios/crear_editar');
    }
    
    /**
     * Controlador para editar un registro de tipo Usuario
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function editar ($id)
    {
        $Usuario = new Model_Usuario($id);
        // si el registro que se quiere editar no existe error
        if(!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') no existe, no se puede editar'
            );
            $this->redirect(
                $this->module_url.'usuarios/listar'
            );
        }
        // si no se ha enviado el formulario se mostrará
        if(!isset($_POST['submit'])) {
            $this->set(array(
                'Usuario' => $Usuario,
                'columnsInfo' => Model_Usuario::$columnsInfo,
                'fkNamespace' => Model_Usuario::$fkNamespace,
                'accion' => 'Editar',
            ));
            // renderizar
            $this->autoRender = false;
            $this->render('Usuarios/crear_editar');
        }
        // si se envió el formulario se procesa
        else {
            $Usuario->set($_POST);
            $Usuario->save();
            if(method_exists($this, 'u')) {
                $this->u($id);
            }
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') editado'
            );
            $this->redirect(
                $this->module_url.'usuarios/listar'
            );
        }
    }

    /**
     * Controlador para eliminar un registro de tipo Usuario
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function eliminar ($id)
    {
        $Usuario = new Model_Usuario($id);
        // si el registro que se quiere eliminar no existe error
        if(!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') no existe, no se puede eliminar'
            );
            $this->redirect(
                $this->module_url.'usuarios/listar'
            );
        }
        $Usuario->delete();
        \sowerphp\core\Model_Datasource_Session::message(
            'Registro Usuario('.implode(', ', func_get_args()).') eliminado'
        );
        $this->redirect($this->module_url.'usuarios/listar');
    }



}
