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
 * @version 2014-10-25
 */
class Controller_Component_Auth extends \sowerphp\core\Controller_Component
{

    public $settings = [ ///< Opciones por defecto
        'maxLoginAttempts' => 3,
        'hash' => 'sha256',
        'model' => '\sowerphp\app\Sistema\Usuarios\Model_Usuario',
        'session' => [
            'key' => 'session.auth',
        ],
        'redirect' => [
            'login' => '/',
            'logout' => '/',
            'error' => '/',
            'form' => '/usuarios/ingresar',
        ],
        'messages' => [
            'ok' => [
                'login' => 'Usuario <em>%s</em> ha iniciado su sesión',
                'lastlogin' => 'Último ingreso fue el <em>%s</em> desde <em>%s</em>',
                'logout' => 'Usuario <em>%s</em> ha cerrado su sesión',
            ],
            'error' => [
                'nologin' => 'Debe iniciar sesión para tratar de acceder a <em>%s</em>',
                'auth' => 'No dispone de permisos para acceder a <em>%s</em>',
                'invalid' => 'Usuario o clave inválida',
                'inactive' => 'Cuenta de usuario no activa',
                'newlogin' => 'Sesión cerrada. Usuario <em>%s</em> tiene una más nueva en otro lugar',
                'login_attempts_exceeded' => 'Número de intentos de sesión excedidos. Cuenta bloqueada, debe recuperar su contraseña.',
                'token' => 'Token se encuentra bloqueado',
                'recaptcha_required' => 'Se detectaron intentos previos fallidos. Se requiere Captcha',
                'recaptcha_invalid' => 'Captcha incorrecto',
            ],
        ],
    ];
    private $allowedActions = array(); ///< Acciones sin login
    private $allowedActionsWithLogin = array(); ///< Acciones con login
    private $session = null; ///< Información de la sesión del usuario
    public $User = false; ///< Usuario que se ha identificado en la sesión
    private $Cache; ///< Objeto para el caché

    /**
     * Método que inicializa el componente y carga la sesión activa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-23
     */
    public function __construct(\sowerphp\core\Controller_Component_Collection $Components, $settings = [])
    {
        // ejecutar el constructor padre
        parent::__construct($Components, $settings);
        // cargar opciones para autorización secundaria
        $this->settings['auth2'] = \sowerphp\core\Configure::read('auth2');
        // Recuperar sesión
        $this->session = \sowerphp\core\Model_Datasource_Session::read(
            $this->settings['session']['key']
        );
        if ($this->session) {
            $this->Cache = new \sowerphp\core\Cache();
            $this->User = $this->Cache->get($this->settings['session']['key'].$this->session['id']);
            if (!$this->User) {
                $this->User = new $this->settings['model']($this->session['id']);
                $this->User->groups();
                $this->User->auths();
                $this->Cache->set($this->settings['session']['key'].$this->session['id'], $this->User);
            }
        }
    }

    /**
     * Método que actualiza el usuario autenticado en la caché
     *@ author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-23
     */
    public function saveCache()
    {
        $this->Cache->set($this->settings['session']['key'].$this->session['id'], $this->User);
    }

    /**
     * Método que verifica si el usuario tiene permisos o bien da error
     * Wrapper para el método que hace la validación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function beforeFilter()
    {
        if (!$this->isAuthorized()) {
            if (!$this->logged()) {
                \sowerphp\core\Model_Datasource_Session::message(sprintf(
                    $this->settings['messages']['error']['nologin'],
                    $this->controller->request->request
                ), 'error');
                $this->controller->redirect(
                    $this->settings['redirect']['form'].'/'.
                    base64_encode($this->controller->request->request)
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(sprintf(
                    $this->settings['messages']['error']['auth'],
                    $this->controller->request->request
                ), 'error');
                $this->controller->redirect($this->settings['redirect']['error']);
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
        if (in_array($this->controller->request->params['action'], $this->allowedActions))
            return true;
        // si el usuario no existe en la sesión se retorna falso
        if (!$this->logged())
            return false;
        // si la acción se encuentra dentro de las que solo requieren un
        // usuario logueado se acepta
        if (in_array(
                $this->controller->request->params['action'],
                $this->allowedActionsWithLogin
        )) {
            return true;
        }
        // Chequear permisos
        return $this->check($this->controller->request);
    }

    /**
     * Indica si existe una sesión de un usuario creada
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-11-01
     */
    public function logged ()
    {
        // si se creó el objeto usuario se verifica el hash
        if ($this->session and $this->User) {
            if (!$this->User->checkLastLoginHash($this->session['hash'])) {
                (new \sowerphp\core\Cache())->delete($this->settings['session']['key'].$this->session['id']);
                \sowerphp\core\Model_Datasource_Session::destroy();
                \sowerphp\core\Model_Datasource_Session::message(
                    sprintf(
                        $this->settings['messages']['error']['newlogin'],
                        $this->User->usuario
                    ), 'error'
                );
                return false;
            }
            return true;
        }
        // si se llegó acá entonces no se está logueado
        return false;
    }

    /**
     * Método que revisa si hay o no permisos para determinado recurso y cierto
     * usuario (por defecto la web que se trata de acceder y el usuario
     * autenticado).
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function check($recurso = false, $usuario = false)
    {
        if (!$recurso)
            $recurso = str_replace(__BASE, '', $_SERVER['REQUEST_URI']);
        if ($usuario)
            return (new $this->settings['model']($usuario))->auth($recurso);
        else
            return $this->User->auth($recurso);
    }

    /**
     * Método que realiza el login del usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-11-19
     */
    public function login ($usuario, $contrasenia)
    {
        // crear objeto del usuario con el nombre de usuario entregado
        $this->User = new $this->settings['model']($usuario);
        // si el usuario no existe -> error
        if (!$this->User->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                $this->settings['messages']['error']['invalid'], 'error'
            );
            return;
        }
        // si el usuario no está activo -> error
        if (!$this->User->isActive()) {
            \sowerphp\core\Model_Datasource_Session::message(
                $this->settings['messages']['error']['inactive'], 'error'
            );
            return;
        }
        // si la cuenta ya no tienen intentos de login -> error
        if (!$this->User->contrasenia_intentos) {
            \sowerphp\core\Model_Datasource_Session::message(
                $this->settings['messages']['error']['login_attempts_exceeded'],
                'error'
            );
            return;
        }
        // si ya hubo un intento de login fallido entonces se pedirá captcha
        $private_key = \sowerphp\core\Configure::read('recaptcha.private_key');
        if ($this->User->contrasenia_intentos<$this->settings['maxLoginAttempts'] and $private_key!==null) {
            if (empty($_POST['recaptcha_challenge_field']) or empty($_POST['recaptcha_response_field'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['recaptcha_required'],
                    'warning'
                );
                return;
            }
            $resp = recaptcha_check_answer(
                $private_key,
                $_SERVER['REMOTE_ADDR'],
                $_POST['recaptcha_challenge_field'],
                $_POST['recaptcha_response_field']
            );
            if (!$resp->is_valid) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['recaptcha_invalid'],
                    'error'
                );
                return;
            }
        }
        // si la contraseña no es correcta -> error
        if (!$this->User->checkPassword($this->hash($contrasenia))) {
            $this->User->setContraseniaIntentos($this->User->contrasenia_intentos-1);
            if ($this->User->contrasenia_intentos) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['invalid'], 'error'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    $this->settings['messages']['error']['login_attempts_exceeded'],
                    'error'
                );
            }
            return;
        }
        // verificar token en sistema secundario de autorización
        if ($this->settings['auth2'] !== null and !$this->User->checkToken()) {
            \sowerphp\core\Model_Datasource_Session::message(
                $this->settings['messages']['error']['token'], 'error'
            );
            return;
        }
        // si se pasaron toda las validaciones anteriores -> crear sesión
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->ip (true);
        $hash = md5 ($ip.$timestamp.$this->hash($contrasenia));
        // registrar ingreso en la base de datos
        $lastLogin = $this->User->lastLogin();
        if (isset($lastLogin['fecha_hora'][0])) {
            $lastlogin = '<br />'.sprintf(
                $this->settings['messages']['ok']['lastlogin'],
                $lastLogin['fecha_hora'],
                $lastLogin['desde']
            );
        } else {
            $lastlogin = '';
        }
        $this->User->updateLastLogin($timestamp, $ip, $hash);
        $this->User->setContraseniaIntentos($this->settings['maxLoginAttempts']);
        // crear info de la sesión
        $this->session =  array(
            'id' => $this->User->id,
            'hash' => $hash,
        );
        \sowerphp\core\Model_Datasource_Session::write(
            $this->settings['session']['key'], $this->session
        );
        // mensaje para mostrar
        \sowerphp\core\Model_Datasource_Session::message(sprintf(
            $this->settings['messages']['ok']['login'],
            $this->User->usuario
        ).$lastlogin);
        // redireccionar
        if (isset($_POST['redirect'][0]))
            $this->controller->redirect($_POST['redirect']);
        else
            $this->controller->redirect($this->settings['redirect']['login']);
    }

    /**
     * Método que termina la sesión del usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-16
     */
    public function logout()
    {
        (new \sowerphp\core\Cache())->delete($this->settings['session']['key'].$this->session['id']);
        \sowerphp\core\Model_Datasource_Session::destroy();
        \sowerphp\core\Model_Datasource_Session::start();
        \sowerphp\core\Model_Datasource_Session::message(sprintf(
            $this->settings['messages']['ok']['logout'],
            $this->User->usuario
        ));
        $this->controller->redirect($this->settings['redirect']['logout']);
    }

    /**
     * Método que calcula el hash de la contraseña utilizando el método
     * especificado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function hash ($string)
    {
        return hash($this->settings['hash'], $string);
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
