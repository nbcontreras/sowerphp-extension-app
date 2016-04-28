<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace sowerphp\app;

/**
 * Componente para proveer una API para funciones de los controladores
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2016-03-20
 */
class Controller_Component_Api extends \sowerphp\core\Controller_Component
{

    public $method; ///< Método HTTP que se utilizó para acceder a la API
    public $headers; ///< Cabeceras HTTP de la solicitud que se hizo a la API
    public $data; ///< Datos que se han pasado a la función de la API
    public $settings = [
        'log' => false,
        'messages' => [
            'error' => [
                'not-found' => 'Recurso %s a través de %s no existe en la API %s',
                'args-miss' => 'Argumentos insuficientes para el recurso %s(%s) a través de %s en la API %s',
                'auth-miss' => 'Cabecera _Authorization_ no fue recibida',
            ]
        ],
    ];
    protected $User = null; ///< Usuario que se ha autenticado en la API

    /**
     * Método para inicializar la función de la API que se está ejecutando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-02
     */
    private function init()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->headers = $this->controller->request->header();
        $this->data = json_decode(file_get_contents('php://input'), true);
        $this->controller->response->type('application/json');
    }

    /**
     * Método principal para ejecutar las funciones de la API. Esta buscará y
     * lanzará las funciones, obteniendo su resultado y devolvíendolos a quien
     * solicitó la ejecución. Este método es el que controla las funciones del
     * controlador que se está ejecutando.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-01-31
     */
    public function run($resource, $args = null)
    {
        // inicializar api
        $this->init();
        // verificar que la función de la API del controlador exista
        $method = '_api_'.$resource.'_'.$this->method;
        if (!method_exists($this->controller, $method)) {
            $this->send(
                sprintf(
                    $this->settings['messages']['error']['not-found'],
                    $resource,
                    $this->method,
                    get_class($this->controller)
                ), 404
            );
        }
        // verificar que a lo menos se hayan pasado los argumentos requeridos
        $n_args = func_num_args() - 1;
        $reflectionMethod = new \ReflectionMethod($this->controller, $method);
        if ($n_args<$reflectionMethod->getNumberOfRequiredParameters()) {
            $args = [];
            foreach($reflectionMethod->getParameters() as &$p) {
                $args[] = $p->isOptional() ? '['.$p->name.']' : $p->name;
            }
            $this->send(
                sprintf(
                    $this->settings['messages']['error']['args-miss'],
                    $resource,
                    implode(', ', $args),
                    $this->method,
                    get_class($this->controller)
                ), 400
            );
        }
        unset($reflectionMethod);
        // hacer log de la llamada a la API
        if ($this->settings['log']) {
            $this->controller->Log->write($this->getResource(), LOG_INFO, $this->settings['log']);
        }
        // ejecutar función de la API
        if ($n_args)
            $data = call_user_func_array([$this->controller, $method], array_slice(func_get_args(), 1));
        else
            $data = $this->controller->$method();
        // si se llegó hasta acá es porque no se envió respuesta desde la
        // función en la API
        $this->send($data, 200);
    }

    /**
     * Método que entrega el recurso que se está accediendo a través de la API
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-03-20
     */
    public function getResource()
    {
        $find = '/'.$this->controller->request->params['controller'].'/'.$this->controller->request->params['pass'][0];
        $pos = strrpos($this->controller->request->request, $find)+strlen($find);
        return substr($this->controller->request->request, 0, $pos);
    }

    /**
     * Método que lista los recursos disponibles de la API en el controlador
     * que se está ejecutando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-02
     */
    public function resources()
    {
        $resources = [];
        foreach(get_class_methods($this->controller) as $action)
            if (substr($action, 0, 12)=='_api_' && $action!=__FUNCTION__)
                $resources[] = substr($action, 12);
        return $resources;
    }

    /**
     * Método para enviar respuestas hacia el cliente de la API
     * @param data Datos que se enviarán
     * @param status Estado HTTP de resultado de la ejecución de la funcionalidad
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-12-30
     */
    public function send($data, $status = 200, $options = 0)
    {
        $this->controller->response->status($status);
        $this->controller->response->send(json_encode($data, $options)."\n");
    }

    /**
     * Método que valida las credenciales pasadas a la función de la API del
     * controlador y devuelve el usuario que se autenticó
     * @return Objeto con usuario autenticado o string con el error si hubo uno
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-04-28
     */
    public function getAuthUser()
    {
        if ($this->User!==null) {
            return $this->User;
        }
        $auth = isset($this->headers['Authorization']) ? $this->headers['Authorization'] : false;
        if ($auth===false) {
            $this->User = $this->settings['messages']['error']['auth-miss'];
            return $this->User;
        }
        list($basic, $user_pass) = explode(' ', $auth);
        list($user, $pass) = explode(':', base64_decode($user_pass));
        // crear objeto del usuario
        try {
            $User = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($user);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->User = $e->getMessage();
            return $this->User;
        }
        // si el usuario no existe -> error
        if (!$User->exists()) {
            $this->User = $this->controller->Auth->settings['messages']['error']['invalid'];
            return $this->User;
        }
        // si el usuario está inactivo -> error
        if (!$User->isActive()) {
            $this->User = $this->controller->Auth->settings['messages']['error']['inactive'];
            return $this->User;
        }
        // solo hacer las validaciones de contraseña y auth2 si se está
        // autenticando con usuario y contraseña, si se autentica con el hash
        // ignorar estas validaciones
        if ($user != $User->hash) {
            // si el usuario tiene bloqueada su cuenta por intentos máximos -> error
            if (!$User->contrasenia_intentos) {
                $this->User = $this->controller->Auth->settings['messages']['error']['login_attempts_exceeded'];
                return $this->User;
            }
            // si la contraseña no es correcta -> error
            if (!$User->checkPassword($this->controller->Auth->hash($pass))) {
                $User->setContraseniaIntentos($User->contrasenia_intentos-1);
                if ($User->contrasenia_intentos) {
                    $this->User = $this->controller->Auth->settings['messages']['error']['invalid'];
                } else {
                    $this->User = $this->controller->Auth->settings['messages']['error']['login_attempts_exceeded'];
                }
                return $this->User;
            }
            // verificar token en sistema secundario de autorización
            if ($this->controller->Auth->settings['auth2'] !== null and !$User->checkToken()) {
                $this->User = $this->controller->Auth->settings['messages']['error']['token'];
                return $this->User;
            }
            // actualizar intentos de contraseña
            $User->setContraseniaIntentos($this->controller->Auth->settings['maxLoginAttempts']);
        }
        $this->User = $User;
        return $this->User;
    }

}
