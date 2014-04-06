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
 * Clase abstracta para el controlador asociado a la tabla grupo de la base
 * de datos
 * Comentario de la tabla: Grupos de la aplicación
 * Esta clase permite controlar las acciones básicas entre el modelo y vista
 * para la tabla grupo, o sea implementa métodos CRUD
 * @author SowerPHP Code Generator
 * @version 2014-04-05 17:32:18
 */
abstract class Controller_Base_Grupos extends \Controller_App
{

    /**
     * Controlador para listar los registros de tipo Grupo
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function listar ($page = 1, $orderby = null, $order = 'A')
    {
        // crear objeto
        $Grupos = new Model_Grupos();
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
                if (in_array(Model_Grupo::$columnsInfo[$var]['type'], array('char', 'character varying')))
                    $where[] = $Grupos->like($var, $val);
                else
                    $where[] = $Grupos->sanitize($var)." = '".$Grupos->sanitize($val)."'";
            }
            // agregar condicion a la busqueda
            $Grupos->setWhereStatement(implode(' AND ', $where));
        }
        // si se debe ordenar se agrega
        if ($orderby) {
            $Grupos->setOrderByStatement($orderby.' '.($order=='D'?'DESC':'ASC'));
        }
        // total de registros
        $registers_total = $Grupos->count();
        // paginar si es necesario
        if ((integer)$page>0) {
            $registers_per_page = \sowerphp\core\Configure::read('app.registers_per_page');
            $pages = ceil($registers_total/$registers_per_page);
            $Grupos->setLimitStatement($registers_per_page, ($page-1)*$registers_per_page);
            if ($page != 1 && $page > $pages) {
                $this->redirect(
                    $this->module_url.'grupos/listar/1'.($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl
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
            'Grupos' => $Grupos->getObjects(),
            'columnsInfo' => Model_Grupo::$columnsInfo,
            'registers_total' => $registers_total,
            'pages' => isset($pages) ? $pages : 0,
            'linkEnd' => ($orderby ? '/'.$orderby.'/'.$order : '').$searchUrl,
            'fkNamespace' => Model_Grupo::$fkNamespace,
        ));
    }
    
    /**
     * Controlador para crear un registro de tipo Grupo
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function crear ()
    {
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            $Grupo = new Model_Grupo();
            $Grupo->set($_POST);
            $Grupo->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Grupo creado'
            );
            $this->redirect(
                $this->module_url.'grupos/listar'
            );
        }
        // setear variables
        $this->set(array(
            'columnsInfo' => Model_Grupo::$columnsInfo,
            'fkNamespace' => Model_Grupo::$fkNamespace,
            'accion' => 'Crear',
        ));
        // renderizar
        $this->autoRender = false;
        $this->render('Grupos/crear_editar');
    }
    
    /**
     * Controlador para editar un registro de tipo Grupo
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function editar ($id)
    {
        $Grupo = new Model_Grupo($id);
        // si el registro que se quiere editar no existe error
        if(!$Grupo->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Grupo('.implode(', ', func_get_args()).') no existe, no se puede editar'
            );
            $this->redirect(
                $this->module_url.'grupos/listar'
            );
        }
        // si no se ha enviado el formulario se mostrará
        if(!isset($_POST['submit'])) {
            $this->set(array(
                'Grupo' => $Grupo,
                'columnsInfo' => Model_Grupo::$columnsInfo,
                'fkNamespace' => Model_Grupo::$fkNamespace,
                'accion' => 'Editar',
            ));
            // renderizar
            $this->autoRender = false;
            $this->render('Grupos/crear_editar');
        }
        // si se envió el formulario se procesa
        else {
            $Grupo->set($_POST);
            $Grupo->save();
            if(method_exists($this, 'u')) {
                $this->u($id);
            }
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Grupo('.implode(', ', func_get_args()).') editado'
            );
            $this->redirect(
                $this->module_url.'grupos/listar'
            );
        }
    }

    /**
     * Controlador para eliminar un registro de tipo Grupo
     * @author SowerPHP Code Generator
     * @version 2014-04-05 17:32:18
     */
    public function eliminar ($id)
    {
        $Grupo = new Model_Grupo($id);
        // si el registro que se quiere eliminar no existe error
        if(!$Grupo->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Grupo('.implode(', ', func_get_args()).') no existe, no se puede eliminar'
            );
            $this->redirect(
                $this->module_url.'grupos/listar'
            );
        }
        $Grupo->delete();
        \sowerphp\core\Model_Datasource_Session::message(
            'Registro Grupo('.implode(', ', func_get_args()).') eliminado'
        );
        $this->redirect($this->module_url.'grupos/listar');
    }



}
