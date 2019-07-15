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
 * @version 2017-08-16
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
                'not-found' => 'Recurso %s no permite método %s en la API %s',
                'methods-miss' => 'El recurso %s no tiene métodos asociados en la API %s',
                'args-miss' => 'Argumentos insuficientes para el recurso %s(%s) a través de %s en la API %s',
                'auth-miss' => 'Cabecera Authorization no fue recibida',
                'auth-bad' => 'Cabecera Authorization es incorrecta',
                'not-auth' => 'No está autorizado a acceder al recurso %s a través del método %s en la API %s',
            ]
        ],
        'localhost' => ['::1', '127.0.0.1'],
        'data' => [
            'keep-raw' => false, // por defecto los datos de entrada por POST se asumen JSON y se parsean como tal
        ],
    ];
    protected $User = null; ///< Usuario que se ha autenticado en la API

    /**
     * Método para inicializar la función de la API que se está ejecutando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2017-08-16
     */
    private function init()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->headers = $this->controller->request->header();
        if ($this->settings['data']['keep-raw']) {
            $this->data = file_get_contents('php://input');
        } else {
            $this->data = json_decode(file_get_contents('php://input'), true);
        }
        $this->controller->response->type('application/json');
    }

    /**
     * Método principal para ejecutar las funciones de la API. Esta buscará y
     * lanzará las funciones, obteniendo su resultado y devolvíendolos a quien
     * solicitó la ejecución. Este método es el que controla las funciones del
     * controlador que se está ejecutando.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-08-06
     */
    public function run($resource, $args = null)
    {
        // inicializar api
        $this->init();
        // si se solicitan opciones se buscan para el recurso
        if ($this->method=='OPTIONS') {
            $resources = $this->resources();
            $methods = ['OPTIONS'];
            foreach ($resources as $r) {
                $method = substr($r, strrpos($r, '_')+1);
                if ($r == $resource.'_'.$method) {
                    $methods[] = $method;
                }
            }
            if (isset($methods[1])) {
                $this->controller->response->header('Allow', implode(',', $methods));
                $this->send($methods);
            } else {
                $this->send(
                    sprintf(
                        $this->settings['messages']['error']['methods-miss'],
                        $resource,
                        get_class($this->controller)
                    ),
                    404
                );
            }
        }
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
        // si se requiere autenticación se valida con el usuario que se haya pasado
        $recurso = $this->getResource();
        if (\sowerphp\core\Configure::read('api.auth') and !$this->controller->Auth->allowedWithoutLogin($method)) {
            $User = $this->getAuthUser();
            if (is_string($User)) {
                $this->send($User, 401);
            }
            if (!in_array($this->controller->Auth->ip(), $this->settings['localhost']) and !$User->auth($recurso)) {
                $this->send(
                    sprintf(
                        $this->settings['messages']['error']['not-auth'],
                        $resource,
                        $this->method,
                        get_class($this->controller)
                    ), 402
                );
            }
        }
        // hacer log de la llamada a la API
        if ($this->settings['log']) {
            $this->controller->Log->write($recurso, LOG_INFO, $this->settings['log']);
        }
        // ejecutar función de la API
        try {
            if ($n_args)
                $data = call_user_func_array([$this->controller, $method], array_slice(func_get_args(), 1));
            else
                $data = $this->controller->$method();
        } catch (\Exception $e) {
            $this->send($e->getMessage(), 500);
        } catch (\Error $e) {
            $this->send($e->getMessage(), 500);
        }
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
     * @version 2016-07-03
     */
    public function resources()
    {
        $resources = [];
        foreach(get_class_methods($this->controller) as $action) {
            if (substr($action, 0, 5)=='_api_' && $action!=__FUNCTION__) {
                $resources[] = substr($action, 5);
            }
        }
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
     * @version 2019-02-22
     */
    public function getAuthUser()
    {
        // si ya se determinó el usuario se entrega
        if ($this->User!==null) {
            return $this->User;
        }
        // si hay un usuario con sesión iniciada se usa ese
        if ($this->controller->Auth->User) {
            $this->User = $this->controller->Auth->User;
            return $this->User;
        }
        // buscar datos del usuario (se busca en cabecera Authorization o bien en api_hash o api_key por GET, esto último no se recomienda usar)
        $auth = isset($this->headers['Authorization']) ? trim($this->headers['Authorization']) : (isset($this->headers['authorization']) ? trim($this->headers['authorization']) : false);
        if ($auth===false) {
            if (!empty($_GET['api_hash'])) {
                $auth = base64_encode($_GET['api_hash'].':X');
            }
            else if (!empty($_GET['api_key'])) {
                $auth = $_GET['api_key'];
            }
        }
        if ($auth===false) {
            $this->User = $this->settings['messages']['error']['auth-miss'];
            return $this->User;
        }
        if (!strpos($auth, ' ')) {
            $user_pass = $auth;
        } else {
            list($auth_type, $user_pass) = explode(' ', $auth);
            if ($auth_type=='Bearer') {
                $user_pass = base64_encode($user_pass.':X');
            }
        }
        $aux = explode(':', (string)base64_decode($user_pass));
        if (!isset($aux[1])) {
            $this->User = $this->settings['messages']['error']['auth-bad'];
            return $this->User;
        }
        list($user, $pass) = $aux;
        // crear objeto del usuario
        try {
            $User = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($user);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->User = $e->getMessage();
            return $this->User;
        }
        // si el usuario no existe -> error
        if (!$User->exists()) {
            $this->User = sprintf($this->controller->Auth->settings['messages']['error']['invalid'], $User->usuario);
            return $this->User;
        }
        // si el usuario está inactivo -> error
        if (!$User->isActive()) {
            $this->User = sprintf($this->controller->Auth->settings['messages']['error']['inactive'], $User->usuario);
            return $this->User;
        }
        // solo hacer las validaciones de contraseña y auth2 si se está
        // autenticando con usuario y contraseña, si se autentica con el hash
        // ignorar estas validaciones
        if ($user != $User->hash) {
            // si el usuario tiene bloqueada su cuenta por intentos máximos -> error
            if (!$User->contrasenia_intentos) {
                $this->User = sprintf($this->controller->Auth->settings['messages']['error']['login_attempts_exceeded'], $User->usuario);
                return $this->User;
            }
            // si la contraseña no es correcta -> error
            if (!$User->checkPassword($pass)) {
                $User->setContraseniaIntentos($User->contrasenia_intentos-1);
                if ($User->contrasenia_intentos) {
                    $this->User = sprintf($this->controller->Auth->settings['messages']['error']['invalid'], $User->usuario);
                } else {
                    $this->User = sprintf($this->controller->Auth->settings['messages']['error']['login_attempts_exceeded'], $User->usuario);
                }
                return $this->User;
            }
            // verificar token en sistema secundario de autorización
            try {
                $User->checkAuth2(!empty($_GET['auth2_token']) ? $_GET['auth2_token'] : null);
            } catch (\Exception $e) {
                $this->User = sprintf($this->controller->Auth->settings['messages']['error']['auth2'], $User->usuario, $e->getMessage());
                return $this->User;
            }
            // actualizar intentos de contraseña
            $User->setContraseniaIntentos($this->controller->Auth->settings['maxLoginAttempts']);
        }
        $this->User = $User;
        return $this->User;
    }

    /**
     * Método que entrega los valores de los parámetros solicitados si es que
     * están presentes en la query de la URL hecha a la API
     * @param params Arreglo con los parámetros, si se manda param => value, value será el valor por defecto (sino será null)
     * @return Arreglo con los parámetros y sus valores
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-07-02
     */
    public function getQuery(array $params)
    {
        $vars = [];
        foreach ($params as $param => $default) {
            if (is_int($param)) {
                $param = $default;
                $default = null;
            }
            $vars[$param] = isset($_GET[$param]) ? $_GET[$param] : $default;
        }
        return $vars;
    }

    /**
     * Método que permite mantener los datos crudos y no convertirlos a JSON
     * @param keep =true (por defecto) para mantener datos en crudo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2017-08-16
     */
    public function setKeepRawData($keep = true)
    {
        $this->settings['data']['keep-raw'] = $keep;
    }

}
