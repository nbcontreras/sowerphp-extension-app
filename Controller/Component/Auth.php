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
 * Componente para proveer de un sistema de autenticación y autorización
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-03-29
 */
class Controller_Component_Auth extends \sowerphp\core\Controller_Component
{

    public $settings = array( ///< Opciones por defecto
        'session' => array(
            'key' => 'auth',
        ),
        'redirect' => array(
            'login' => '/',
            'logout' => '/',
            'error' => '/',
            'form' => '/usuarios/ingresar',
        ),
        'messages' => array(
            'ok' => array(
                'login' => 'Usuario <em>%s</em> ha iniciado su sesión',
                'lastlogin' => 'Último ingreso fue el <em>%s</em> desde <em>%s</em>',
                'logout' => 'Usuario <em>%s</em> ha cerrado su sesión',
                'logged' => 'Usuario <em>%s</em> tiene su sesión abierta'
            ),
            'error' => array(
                'nologin' => 'Debe iniciar sesión para tratar de acceder a <em>%s</em>',
                'auth' => 'No dispone de permisos para acceder a <em>%s</em>',
                'empty' => 'Debe especificar usuario y clave',
                'invalid' => 'Usuario o clave inválida',
                'inactive' => 'Cuenta de usuario no activa',
                'newlogin' => 'Sesión cerrada, usuario <em>%s</em> tiene una más nueva en otro lugar',
            ),
        ),
        'model' => array(
            'user' => array(
                'class' => '\sowerphp\app\Sistema\Usuarios\Model_Usuario',
                'table' => 'usuario',
                'columns' => array(
                    'id' => 'id',
                    'user' => 'usuario',
                    'pass' => 'contrasenia',
                    'active' => 'activo',
                    'lastlogin_timestamp' => 'ultimo_ingreso_fecha_hora',
                    'lastlogin_from' => 'ultimo_ingreso_desde',
                    'lastlogin_hash' => 'ultimo_ingreso_hash',
                ),
                'hash' => 'sha256',
            ),
        )
    );
    public $session = null; ///< Información de la sesión del usuario
    public $allowedActions = array(); ///< Acciones sin login
    public $allowedActionsWithLogin = array(); ///< Acciones con login

    /**
     * Método que inicializa el componente y carga la sesión activa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function __construct(\sowerphp\core\Controller_Component_Collection $Components, $settings = array())
    {
        // ejecutar el constructor padre
        parent::__construct($Components, $settings);
        // Recuperar sesión
        $this->session = \sowerphp\core\Model_Datasource_Session::read(
            $this->settings['session']['key']
        );
    }

    /**
     * Método que verifica si el usuario tiene permisos o bien da error
     * Wrapper para el método que hace la validación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function beforeFilter($controller)
    {
        if (!$this->isAuthorized()) {
            if (!$this->logged()) {
                \sowerphp\core\Model_Datasource_Session::message(sprintf(
                    $this->settings['messages']['error']['nologin'],
                    $this->request->request
                ));
                $controller->redirect(
                    $this->settings['redirect']['form'].'/'.
                    base64_encode($this->request->request)
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(sprintf(
                    $this->settings['messages']['error']['auth'],
                    $this->request->request
                ));
                $controller->redirect($this->settings['redirect']['error']);
            }
        }
    }

    /**
     * Agregar acciones que se permitirán ejecutar sin estár autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2013-06-30
     */
    public function allow ($action = null)
    {
        $this->allowedActions = array_merge(
            $this->allowedActions, func_get_args()
        );
    }

    /**
     * Agregar acciones que se permitirán ejecutar a cualquier usuario que
     * esté autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-22
     */
    public function allowWithLogin ($action = null)
    {
        $this->allowedActionsWithLogin = array_merge(
            $this->allowedActionsWithLogin, func_get_args()
        );
    }

    /**
     * Método para determinar si un usuario está o no autorizado a un área
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function isAuthorized ()
    {
        // Si la acción se encuentra dentro de las permitidas dejar pasar
        if (in_array($this->request->params['action'], $this->allowedActions))
            return true;
        // si el usuario no existe en la sesión se retorna falso
        if (!$this->logged())
            return false;
        // si la acción se encuentra dentro de las que solo requieren un
        // usuario logueado se acepta
        if (in_array(
                $this->request->params['action'],
                $this->allowedActionsWithLogin
        )) {
            return true;
        }
        // Chequear permisos
        return $this->check($this->request);
    }

    /**
     * Indica si existe una sesión de un usuario creada
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function logged ()
    {
        // si es un arreglo $this->session se verifica el hash de la sesión
        if (
            is_array($this->session)
            && isset($this->session['id'])
            && isset($this->session['usuario'])
            && isset($this->session['hash'])
        ) {
            $userModel = $this->settings['model']['user']['class'];
            $$userModel = new $userModel($this->session['id']);
            if ($$userModel->{$this->settings['model']['user']['columns']['lastlogin_hash']} != $this->session['hash']) {
                \sowerphp\core\Model_Datasource_Session::destroy();
                \sowerphp\core\Model_Datasource_Session::message(
                    sprintf($this->settings['messages']['error']['newlogin'], $this->session['usuario'])
                );
                return false;
            }
            return true;
        }
        // si se llegó acá entonces no se está logueado
        return false;
    }

    /**
     * Método que revisa si hay o no permisos para determinado recurso
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function check ($recurso = null, $usuario = null)
    {
        // si no se indico el usuario se recupera de la sesión
        if (!$usuario) $usuario = $this->session[$this->settings['model']['user']['columns']['id']];
        // si la clase Auth no existe no hay permiso porque no se puede verificar
        if(!class_exists('\sowerphp\app\Sistema\Usuarios\Model_Auth')) return false;
        // por que se consultará
        if(!$recurso) $recurso = str_replace(__BASE, '', $_SERVER['REQUEST_URI']);
        // verificar permiso
        return (new \sowerphp\app\Sistema\Usuarios\Model_Auth())->check($usuario, $recurso);
    }

    /**
     * Método que realiza el login del usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function login ($controller)
    {
        // si ya está logueado se redirecciona
        if ($this->logged()) {
            // mensaje para mostrar
            \sowerphp\core\Model_Datasource_Session::message(sprintf(
                $this->settings['messages']['ok']['logged'],
                $this->session['usuario']
            ));
            // redireccionar
            $controller->redirect(
                $this->settings['redirect']['login']
            );
        }
        // si se envió el formulario se procesa
        if(isset($_POST['submit'])) {
            // campos usuario y contraseña
            $idField = $this->settings['model']['user']['columns']['id'];
            $userField = $this->settings['model']['user']['columns']['user'];
            $passField = $this->settings['model']['user']['columns']['pass'];
            // si el usuario o contraseña es vacio mensaje de error
            if (empty($_POST[$userField]) || empty($_POST[$passField])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['empty']
                );
                return;
            }
            // crear objeto del usuario con el nombre de usuario entregado
            $userModel = $this->settings['model']['user']['class'];
            $$userModel = new $userModel($_POST[$userField]);
            // si las contraseñas no son iguales error (si el usuario no existe tambiém habrá error)
            if ($$userModel->$passField != $this->hash($_POST[$passField])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['invalid']
                );
                return;
            }
            if (
                $$userModel->{$this->settings['model']['user']['columns']['active']} == 'f' ||
                $$userModel->{$this->settings['model']['user']['columns']['active']} == '0'
            ) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['inactive']
                );
                return;
            }
            // si existe, crear sesión
            else {
                // hash de la sesión
                $timestamp = date('Y-m-d H:i:s');
                $ip = $this->ip (true);
                $hash = md5 ($ip.$timestamp.$this->hash($_POST[$passField]));
                // registrar ingreso en la base de datos
                // se asume que si está seteada una de las columnas lastlogin_* lo estarán todas
                if (isset($this->settings['model']['user']['columns']['lastlogin_timestamp'][0])) {
                    if (isset($$userModel->{$this->settings['model']['user']['columns']['lastlogin_timestamp']}[0])) {
                        $lastlogin = '<br />'.sprintf(
                            $this->settings['messages']['ok']['lastlogin'],
                            $$userModel->{$this->settings['model']['user']['columns']['lastlogin_timestamp']},
                            $$userModel->{$this->settings['model']['user']['columns']['lastlogin_from']}
                        );
                    } else {
                        $lastlogin = '';
                    }
                    $$userModel->edit (array(
                        $this->settings['model']['user']['columns']['lastlogin_timestamp'] => $timestamp,
                        $this->settings['model']['user']['columns']['lastlogin_from'] => $ip,
                        $this->settings['model']['user']['columns']['lastlogin_hash'] => $hash
                    ));
                } else {
                    $lastlogin = '';
                }
                // crear info de la sesión
                $this->session =  array(
                    'id' => $$userModel->$idField,
                    'usuario' => $$userModel->$userField,
                    'hash' => $hash,
                );
                \sowerphp\core\Model_Datasource_Session::write(
                    $this->settings['session']['key'], $this->session
                );
                // mensaje para mostrar
                \sowerphp\core\Model_Datasource_Session::message(sprintf(
                    $this->settings['messages']['ok']['login'],
                    $$userModel->$userField
                ).$lastlogin);
                // redireccionar
                if (isset($_POST['redirect'][0])) $controller->redirect($_POST['redirect']);
                    else $controller->redirect($this->settings['redirect']['login']);
            }
        }
    }

    /**
     * Método que termina la sesión del usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-23
     */
    public function logout ($controller)
    {
        \sowerphp\core\Model_Datasource_Session::destroy();
        \sowerphp\core\Model_Datasource_Session::message(sprintf(
            $this->settings['messages']['ok']['logout'],
            $this->session['usuario']
        ));
        $this->session = null;
        $controller->redirect($this->settings['redirect']['logout']);
    }

    /**
     * Método que calcula el hash de la contraseña utilizando el método
     * especificado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-23
     */
    public function hash ($string)
    {
        return hash($this->settings['model']['user']['hash'], $string);
    }

    /**
     * Establecer ip del visitante
     * @return Ip del visitante
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function ip ($get_from_proxy = false)
    {
        if ($get_from_proxy && getenv('HTTP_X_FORWARDED_FOR')) {
            $ips = explode(', ', getenv('HTTP_X_FORWARDED_FOR'));
            return $ips[count($ips)-1];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Establecer host del visitante
     * @return Host del visitante
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function host ($get_from_proxy = false)
    {
        return gethostbyaddr($this->ip($get_from_proxy));
    }

}
