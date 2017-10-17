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

// namespace del controlador
namespace sowerphp\app\Sistema\Usuarios;

/**
 * Clase para el controlador asociado a la tabla usuario de la base de
 * datos
 * Comentario de la tabla: Usuarios de la aplicación
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla usuario
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2016-02-22
 */
class Controller_Usuarios extends \sowerphp\app\Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar'=>['id', 'nombre', 'usuario', 'email', 'activo', 'ultimo_ingreso_fecha_hora']
    ]; ///< Columnas que se deben mostrar en las vistas
    protected $deleteRecord = false; ///< Indica si se permite o no borrar registros
    protected $changeUsername = true; ///< Indica si se permite que se cambie el nombre de usuario

    /**
     * Permitir ciertas acciones y luego ejecutar verificar permisos con
     * parent::beforeFilter()
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-21
     */
    public function beforeFilter ()
    {
        $this->Auth->allow('ingresar', 'salir', 'contrasenia_recuperar', 'registrar', 'preauth');
        $this->Auth->allowWithLogin('perfil', 'telegram_parear');
        parent::beforeFilter();
    }

    /**
     * Acción para que un usuario ingrese al sistema (inicie sesión)
     * @param redirect Ruta (en base64) de hacia donde hay que redireccionar una vez se autentica el usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-16
     */
    public function ingresar ($redirect = null)
    {
        // si ya está logueado se redirecciona
        if ($this->Auth->logged()) {
            \sowerphp\core\Model_Datasource_Session::message(sprintf(
                'Usuario <em>%s</em> tiene su sesión abierta',
                $this->Auth->User->usuario
            ), 'info');
            $this->redirect(
                $this->Auth->settings['redirect']['login']
            );
        }
        // asignar variables para la vista
        $this->set([
            'redirect' => $redirect ? base64_decode ($redirect) : null,
            'self_register' => (boolean)\sowerphp\core\Configure::read('app.self_register'),
            'language' => \sowerphp\core\Configure::read('language'),
        ]);
        // procesar inicio de sesión
        if (isset($_POST['submit'])) {
            // si el usuario o contraseña es vacio mensaje de error
            if (empty($_POST['usuario']) || empty($_POST['contrasenia'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe especificar usuario y clave', 'warning'
                );
            }
            // realizar proceso de validación de datos
            else {
                $public_key = \sowerphp\core\Configure::read('recaptcha.public_key');
                $this->Auth->login($_POST['usuario'], $_POST['contrasenia']);
                if ($this->Auth->User->contrasenia_intentos and $this->Auth->User->contrasenia_intentos<$this->Auth->settings['maxLoginAttempts']) {
                    $this->set('public_key', $public_key);
                }
            }
        }
    }

    /**
     * Acción para que un usuario cierra la sesión
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    public function salir ()
    {
        if ($this->Auth->logged()) {
            $this->Auth->logout();
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe sesión de usuario abierta',
                'warning'
            );
            $this->redirect('/');
        }
    }

     /**
     * Acción que fuerza el cierre de sesión de un usuario eliminando su hash
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2017-08-11
     */
    public function salir_forzar($id)
    {
        $Usuario = new $this->Auth->settings['model']($id);
        if(!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Usuario no existe, no se puede forzar el cierre de la sesión',
                'error'
            );
            $this->redirect('/sistema/usuarios/usuarios/listar');
        }
        $Usuario->ultimo_ingreso_hash = null;
        try {
            $Usuario->save();
            (new \sowerphp\core\Cache())->delete($this->Auth->settings['session']['key'].$id);
            \sowerphp\core\Model_Datasource_Session::message(
                'Sesión del usuario '.$Usuario->usuario.' cerrada',
                'ok'
            );
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible forzar el cierre de la sesión: '.$e->getMessage(),
                'error'
            );
        }
        $this->redirect('/sistema/usuarios/usuarios/editar/'.$id);
    }

    /**
     * Acción para recuperar la contraseña
     * @param usuario Usuario al que se desea recuperar su contraseña
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-11-24
     */
    public function contrasenia_recuperar ($usuario = null, $codigo = null)
    {
        $this->autoRender = false;
        // pedir correo
        if ($usuario == null) {
            if (!isset($_POST['submit'])) {
                $this->render ('Usuarios/contrasenia_recuperar_step1');
            } else {
                $Usuario = new $this->Auth->settings['model']($_POST['id']);
                if (!$Usuario->exists()) {
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Usuario o email inválido', 'error'
                    );
                    $this->render ('Usuarios/contrasenia_recuperar_step1');
                } else {
                    $this->contrasenia_recuperar_email (
                        $Usuario->email,
                        $Usuario->nombre,
                        $Usuario->usuario,
                        md5(hash('sha256', $Usuario->contrasenia))
                    );
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Se ha enviado un email con las instrucciones para recuperar su contraseña',
                        'ok'
                    );
                    $this->redirect('/usuarios/ingresar');
                }
            }
        }
        // cambiar contraseña
        else {
            $Usuario = new $this->Auth->settings['model']($usuario);
            if (!$Usuario->exists()) {
                \sowerphp\core\Model_Datasource_Session::message (
                    'Usuario inválido', 'error'
                );
                $this->redirect ('/usuarios/contrasenia/recuperar');
            }
            if (!isset($_POST['submit'])) {
                $this->set([
                    'usuario' => $usuario,
                    'codigo' => $codigo,
                ]);
                $this->render ('Usuarios/contrasenia_recuperar_step2');
            } else {
                if ($_POST['codigo']!=md5(hash('sha256', $Usuario->contrasenia))) {
                    \sowerphp\core\Model_Datasource_Session::message (
                        'El enlace para recuperar su contraseña no es válido, solicite uno nuevo por favor', 'error'
                    );
                    $this->redirect('/usuarios/contrasenia/recuperar');
                }
                else if (empty ($_POST['contrasenia1']) || empty ($_POST['contrasenia2']) || $_POST['contrasenia1']!=$_POST['contrasenia2']) {
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Contraseña nueva inválida (en blanco o no coinciden)', 'warning'
                    );
                    $this->set('usuario', $usuario);
                    $this->render ('Usuarios/contrasenia_recuperar_step2');
                }
                else {
                    $Usuario->savePassword($_POST['contrasenia1']);
                    $Usuario->savePasswordRetry($this->Auth->settings['maxLoginAttempts']);
                    \sowerphp\core\Model_Datasource_Session::message (
                        'La contraseña para el usuario '.$usuario.' ha sido cambiada con éxito',
                        'ok'
                    );
                    $this->redirect('/usuarios/ingresar');
                }
            }
        }
    }

    /**
     * Método que envía el correo con los datos para poder recuperar la contraseña
     * @param correo Donde enviar el email
     * @param nombre Nombre "real" del usuario
     * @param usuario Nombre de usuario
     * @param hash Hash para identificar que el usuario es quien dice ser y cambiar su contraseña
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    private function contrasenia_recuperar_email ($correo, $nombre, $usuario, $hash)
    {
        $this->layout = null;
        $this->set (array(
            'nombre'=>$nombre,
            'usuario'=>$usuario,
            'hash'=>$hash,
            'ip'=>$this->Auth->ip(),
        ));
        $msg = $this->render('Usuarios/contrasenia_recuperar_email')->body();
        $email = new \sowerphp\core\Network_Email();
        $email->to($correo);
        $email->subject('Recuperación de contraseña');
        $email->send($msg);
    }

    /**
     * Acción para crear un nuevo usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-09-07
     */
    public function crear()
    {
        if (!empty($_GET['listar'])) {
            $filterListarUrl = '?listar='.$_GET['listar'];
            $filterListar = base64_decode($_GET['listar']);
        } else {
            $filterListarUrl = '';
            $filterListar = '';
        }
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            $Usuario = new $this->Auth->settings['model']();
            $Usuario->set($_POST);
            $Usuario->email = strtolower($Usuario->email);
            $ok = true;
            if ($Usuario->checkIfUserAlreadyExists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso',
                    'warning'
                );
                $ok = false;
            }
            if ($ok and $Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso', 'warning'
                );
                $ok = false;
            }
            if ($ok and $Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso', 'warning'
                );
                $ok = false;
            }
            if ($ok) {
                if (empty($Usuario->contrasenia)) {
                    $Usuario->contrasenia = \sowerphp\core\Utility_String::random(8);
                }
                $contrasenia = $Usuario->contrasenia;
                $Usuario->contrasenia = $Usuario->hashPassword($Usuario->contrasenia);
                if (empty($Usuario->hash)) {
                    do {
                        $Usuario->hash = \sowerphp\core\Utility_String::random(32);
                    } while ($Usuario->checkIfHashAlreadyExists ());
                }
                if ($Usuario->save()) {
                    $Usuario->saveGroups($_POST['grupos']);
                    if (empty($_POST['contrasenia'])) {
                        if ($Usuario->getEmailAccount())
                            $contrasenia = 'actual contraseña de correo '.$Usuario->getEmailAccount()->getEmail();
                        else if ($Usuario->getLdapPerson())
                            $contrasenia = 'actual contraseña de cuenta '.$Usuario->getLdapPerson()->uid.' en LDAP';
                    } else {
                        $Usuario->savePassword($contrasenia);
                    }
                    // enviar correo
                    $emailConfig = \sowerphp\core\Configure::read('email.default');
                    if (!empty($emailConfig['type']) && !empty($emailConfig['user']) && !empty($emailConfig['pass'])) {
                        $layout = $this->layout;
                        $this->layout = null;
                        $this->set(array(
                            'nombre'=>$Usuario->nombre,
                            'usuario'=>$Usuario->usuario,
                            'contrasenia'=>$contrasenia,
                        ));
                        $msg = $this->render('Usuarios/crear_email')->body();
                        $this->layout = $layout;
                        $email = new \sowerphp\core\Network_Email();
                        $email->to($Usuario->email);
                        $email->subject('Cuenta de usuario creada');
                        $email->send($msg);
                        \sowerphp\core\Model_Datasource_Session::message(
                            'Registro creado (se envió email a '.$Usuario->email.' con los datos de acceso)',
                            'ok'
                        );
                    } else {
                        \sowerphp\core\Model_Datasource_Session::message(
                            'Registro creado (no se envió correo)', 'warning'
                        );
                    }
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Registro no creado (hubo algún error)', 'error'
                    );
                }
                $this->redirect('/sistema/usuarios/usuarios/listar'.$filterListar);
            }
        }
        // setear variables
        $this->Auth->settings['model']::$columnsInfo['contrasenia']['null'] = true;
        $this->Auth->settings['model']::$columnsInfo['hash']['null'] = true;
        $this->set(array(
            'accion' => 'Crear',
            'columns' => $this->Auth->settings['model']::$columnsInfo,
            'grupos_asignados' => (isset($_POST['grupos'])?$_POST['grupos']:[]),
            'listarUrl'=>'/sistema/usuarios/usuarios/listar'.$filterListar,
            'ldap' => \sowerphp\core\Configure::read('ldap.default'),
        ));
        $this->setGruposAsignables();
        $this->autoRender = false;
        $this->render ('Usuarios/crear_editar');
    }

    /**
     * Acción para editar un nuevo usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-09-07
     */
    public function editar($id)
    {
        if (!empty($_GET['listar'])) {
            $filterListarUrl = '?listar='.$_GET['listar'];
            $filterListar = base64_decode($_GET['listar']);
        } else {
            $filterListarUrl = '';
            $filterListar = '';
        }
        $Usuario = new $this->Auth->settings['model']($id);
        // si el registro que se quiere editar no existe error
        if(!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro ('.implode(', ', func_get_args()).') no existe, no se puede editar',
                'error'
            );
            $this->redirect('/sistema/usuarios/usuarios/listar'.$filterListar);
        }
        // si no se ha enviado el formulario se mostrará
        if(!isset($_POST['submit'])) {
            $this->Auth->settings['model']::$columnsInfo['contrasenia']['null'] = true;
            $grupos_asignados = $Usuario->groups();
            $this->setGruposAsignables();
            $this->set(array(
                'accion' => 'Editar',
                'Obj' => $Usuario,
                'columns' => $this->Auth->settings['model']::$columnsInfo,
                'grupos_asignados' => array_keys($grupos_asignados),
                'listarUrl'=>'/sistema/usuarios/usuarios/listar'.$filterListar,
                'ldap' => \sowerphp\core\Configure::read('ldap.default'),
            ));
            $this->autoRender = false;
            $this->render ('Usuarios/crear_editar');
        }
        // si se envió el formulario se procesa
        else {
            if (isset($_POST['usuario']) and !$this->changeUsername and $Usuario->usuario!=$_POST['usuario']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario no puede ser cambiado',
                    'warning'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            $activo = $Usuario->activo;
            $Usuario->set($_POST);
            $Usuario->email = strtolower($Usuario->email);
            if ($Usuario->checkIfUserAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso',
                    'warning'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            if ($Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso', 'warning'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            if ($Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso', 'warning'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            $Usuario->save();
            // enviar correo solo si el usuario estaba inactivo y ahora está activo
            if (!$activo and $Usuario->activo) {
                $emailConfig = \sowerphp\core\Configure::read('email.default');
                    if (!empty($emailConfig['type']) && !empty($emailConfig['user']) && !empty($emailConfig['pass'])) {
                    $layout = $this->layout;
                    $this->layout = null;
                    $this->set([
                        'nombre'=>$Usuario->nombre,
                        'usuario'=>$Usuario->usuario,
                    ]);
                    $msg = $this->render('Usuarios/activo_email')->body();
                    $this->layout = $layout;
                    $email = new \sowerphp\core\Network_Email();
                    $email->to($Usuario->email);
                    $email->subject('Cuenta de usuario habilitada');
                    $email->send($msg);
                }
            }
            if (!empty($_POST['contrasenia'])) {
                $Usuario->savePassword($_POST['contrasenia']);
                $Usuario->savePasswordRetry($this->Auth->settings['maxLoginAttempts']);
            }
            $Usuario->saveGroups($_POST['grupos']);
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') editado',
                'ok'
            );
            $this->redirect('/sistema/usuarios/usuarios/listar'.$filterListar);
        }
    }

    /**
     * Método que asigna los grupos que el usuario logueado puede asignar al
     * crear o editar un usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-26
     */
    private function setGruposAsignables ()
    {
        $grupos = (new Model_Grupos())->getList();
        // si el usuario no pertenece al grupo sysadmin quitar los grupos
        // sysadmin y appadmin del listado para evitar que los asignen
        if (!$this->Auth->User->inGroup()) {
            $aux = $grupos;
            $grupos = [];
            foreach ($aux as $key => &$grupo) {
                if (!in_array($grupo['glosa'], ['sysadmin', 'appadmin'])) {
                    $grupos[] = $grupo;
                }
            }
            unset ($aux);
        }
        $this->set('grupos', $grupos);
    }

    /**
     * Acción para mostrar y editar el perfil del usuario que esta autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2017-10-16
     */
    public function perfil()
    {
        // si hay cualquier campo que empiece por 'config_' se quita ya que son
        // configuraciones reservadas para los administradores de la APP y no pueden
        // ser asignadas por los usuarios (esto evita que envién "a la mala" una
        // configuración). Si se desea que el usuario pueda configurar alguna
        // configuración personalizada en el perfil del usuario, se deberá enviar a una
        // acción diferente en un Controlador de usuarios personalizado (que herede este)
        foreach ($_POST as $var => $val) {
            if (strpos($var, 'config_')===0) {
                unset($_POST[$var]);
            }
        }
        // procesar datos personales
        if (isset($_POST['datosUsuario'])) {
            // actualizar datos generales
            if (isset($_POST['usuario']) and !$this->changeUsername and $this->Auth->User->usuario!=$_POST['usuario']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario no puede ser cambiado',
                    'warning'
                );
                $this->redirect('/usuarios/perfil');
            }
            $this->Auth->User->nombre = $_POST['nombre'];
            if ($this->changeUsername and !empty($_POST['usuario']))
                $this->Auth->User->usuario = $_POST['usuario'];
            $this->Auth->User->email = strtolower($_POST['email']);
            if (isset($_POST['hash']))
                $this->Auth->User->hash = $_POST['hash'];
            if ($this->Auth->User->checkIfUserAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso',
                    'warning'
                );
                $this->redirect('/usuarios/perfil');
            }
            if ($this->Auth->User->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso', 'warning'
                );
                $this->redirect('/usuarios/perfil');
            }
            if ($this->Auth->User->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso', 'warning'
                );
                $this->redirect('/usuarios/perfil');
            }
            if (empty($this->Auth->User->hash)) {
                do {
                    $this->Auth->User->hash = \sowerphp\core\Utility_String::random(32);
                } while ($this->Auth->User->checkIfHashAlreadyExists ());
            }
            $this->Auth->User->save();
            $this->Auth->saveCache();
            // mensaje de ok y redireccionar
            \sowerphp\core\Model_Datasource_Session::message(
                'Perfil actualizado', 'ok'
            );
            $this->redirect('/usuarios/perfil');
        }
        // procesar cambio de contraseña
        else if (isset($_POST['cambiarContrasenia'])) {
            // verificar que las contraseñas no sean vacías
            if (empty($_POST['contrasenia']) or empty(trim($_POST['contrasenia1'])) or empty($_POST['contrasenia2'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe especificar su contraseña actual y escribir dos veces su nueva contraseña', 'error'
                );
                $this->redirect('/usuarios/perfil');
            }
            // verificar que la contraseña actual sea correcta
            if (!$this->Auth->User->checkPassword($_POST['contrasenia'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Contraseña actual es incorrecta', 'error'
                );
                $this->redirect('/usuarios/perfil');
            }
            // verificar que la contraseña nueva se haya escrito 2 veces de forma correcta
            if ($_POST['contrasenia1']!=$_POST['contrasenia2']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Contraseñas no coinciden', 'error'
                );
                $this->redirect('/usuarios/perfil');
            }
            // actualizar contraseña
            if ($this->Auth->User->savePassword($_POST['contrasenia1'], $_POST['contrasenia'])) {
                $this->Auth->saveCache();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Contraseña actualizada', 'ok'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible cambiar la contraseña', 'error'
                );
            }
            $this->redirect('/usuarios/perfil');
        }
        // procesar creación del token
        else if (isset($_POST['crearToken']) and $this->Auth->settings['auth2'] !== null) {
            if ($this->Auth->User->createToken($_POST['codigo'])) {
                $this->Auth->saveCache();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Token creado, ahora tiene el control usando '.$this->Auth->settings['auth2']['name'],
                    'ok'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible crear el token', 'error'
                );
            }
            $this->redirect('/usuarios/perfil');
        }
        // procesar destrucción del token
        else if (isset($_POST['destruirToken']) and $this->Auth->settings['auth2'] !== null) {
            if ($this->Auth->User->destroyToken()) {
                $this->Auth->saveCache();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Token destruído', 'ok'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible destruir el token', 'error'
                );
            }
            $this->redirect('/usuarios/perfil');
        }
        // mostrar formulario para edición
        else {
            $this->set([
                'changeUsername' => $this->changeUsername,
                'qrcode' => base64_encode($this->request->url.';'.$this->Auth->User->hash),
                'auth2' => $this->Auth->settings['auth2'],
            ]);
        }
    }

    /**
     * Acción que permite registrar un nuevo usuario en la aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-06-14
     */
    public function registrar()
    {
        // si ya está logueado se redirecciona
        if ($this->Auth->logged()) {
            \sowerphp\core\Model_Datasource_Session::message(sprintf(
                'Usuario <em>%s</em> tiene su sesión abierta',
                $this->Auth->User->usuario
            ));
            $this->redirect(
                $this->Auth->settings['redirect']['login']
            );
        }
        // si no se permite el registro se redirecciona
        $config = \sowerphp\core\Configure::read('app.self_register');
        if (!$config) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro de usuarios deshabilitado', 'error'
            );
            $this->redirect(
                $this->Auth->settings['redirect']['login']
            );
        }
        // colocar variable para captcha (si está configurado)
        $public_key = \sowerphp\core\Configure::read('recaptcha.public_key');
        if ($public_key) {
            $this->set([
                'public_key' => $public_key,
                'language' => \sowerphp\core\Configure::read('language'),
            ]);
        }
        // colocar variable para terminos si está configurado
        if (!empty($config['terms'])) {
            $this->set('terms', $config['terms']);
        }
        // si se envió formulario se procesa
        if (isset($_POST['submit'])) {
            // verificar que campos no sean vacios
            if (empty($_POST['nombre']) or empty($_POST['usuario']) or empty($_POST['email'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe completar todos los campos del formulario', 'warning'
                );
                return;
            }
            // si existen términos y no se aceptaron se redirecciona
            if (!empty($config['terms']) and empty($_POST['terms_ok'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe aceptar los términos y condiciones', 'warning'
                );
                return;
            }
            // validar que el usuario y/o correo no exista previamente
            $Usuario = new $this->Auth->settings['model']();
            $Usuario->nombre = $_POST['nombre'];
            $Usuario->usuario = $_POST['usuario'];
            $Usuario->email = strtolower($_POST['email']);
            if ($Usuario->checkIfUserAlreadyExists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso',
                    'warning'
                );
                return;
            }
            if ($Usuario->checkIfEmailAlreadyExists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso', 'warning'
                );
                return;
            }
            // si existe la configuración para recaptcha se debe validar
            $private_key = \sowerphp\core\Configure::read('recaptcha.private_key');
            if ($private_key) {
                if (empty($_POST['g-recaptcha-response'])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Se requiere Captcha para poder registrar un nuevo usuario',
                        'warning'
                    );
                    return;
                }
                $recaptcha = new \ReCaptcha\ReCaptcha($private_key);
                $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                if (!$resp->isSuccess()) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Captcha incorrecto', 'error'
                    );
                    return;
                }
            }
            // asignar contraseña al usuario
            $contrasenia = \sowerphp\core\Utility_String::random(8);
            $Usuario->contrasenia = $Usuario->hashPassword($contrasenia);
            // asignar hash al usuario
            do {
                $Usuario->hash = \sowerphp\core\Utility_String::random(32);
            } while ($Usuario->checkIfHashAlreadyExists ());
            if ($Usuario->save()) {
                // asignar grupos por defecto al usuario
                if (is_array($config) and !empty($config['groups']))
                    $Usuario->saveGroups($config['groups']);
                // enviar correo
                $emailConfig = \sowerphp\core\Configure::read('email.default');
                if (!empty($emailConfig['type']) && !empty($emailConfig['user']) && !empty($emailConfig['pass'])) {
                    $layout = $this->layout;
                    $this->layout = null;
                    $this->set([
                        'nombre'=>$Usuario->nombre,
                        'usuario'=>$Usuario->usuario,
                        'contrasenia'=>$contrasenia,
                    ]);
                    $msg = $this->render('Usuarios/crear_email')->body();
                    $this->layout = $layout;
                    $email = new \sowerphp\core\Network_Email();
                    $email->to($Usuario->email);
                    $email->subject('Cuenta de usuario creada');
                    $email->send($msg);
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Registro creado, se envió contraseña a '.$Usuario->email,
                        'ok'
                    );
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Registro creado, su contraseña es <em>'.$contrasenia.'</em>', 'warning'
                    );
                }
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Registro de usuario falló por algún motivo', 'error'
                );
            }
            $this->redirect('/usuarios/ingresar');
        }
    }

    /**
     * Acción que permite ingresar a la aplicación con un usuario ya autenticado
     * a través de un token provisto
     * @param token Token de pre autenticación para validar la sesión
     * @param usuario Usuario con el que se desea ingresar
     * @param url URL a la cual redireccionar el usuario una vez ha iniciado sesión
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-28
     */
    public function preauth($token = null, $usuario = null, $url = null)
    {
        // si se pasaron datos por POST tienen preferencia
        if (!empty($_POST['token'])) {
            $token = $_POST['token'];
            $usuario = !empty($_POST['usuario']) ? $_POST['usuario'] : null;
            $url = !empty($_POST['url']) ? $_POST['url'] : null;
        }
        // buscar clave de preauth, si no existe se indica que la
        // preautenticación no está disponible
        $enabled = \sowerphp\core\Configure::read('preauth.enabled');
        if (!$enabled) {
            \sowerphp\core\Model_Datasource_Session::message(
                'La preautenticación no está disponible', 'error'
            );
            $this->redirect('/usuarios/ingresar');
        }
        if ($usuario) {
            $key = \sowerphp\core\Configure::read('preauth.key');
            if (!$key) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No hay clave global para preautenticación', 'error'
                );
                $this->redirect('/usuarios/ingresar');
            }
        }
        // definir url
        $url = $url ? base64_decode($url) : $this->Auth->settings['redirect']['login'];
        // si ya está logueado se redirecciona de forma silenciosa
        if ($this->Auth->logged()) {
            $this->redirect($url);
        }
        // procesar inicio de sesión con preauth, si no se puede autenticar se
        // genera un error
        if (!$this->Auth->preauth($token, $usuario)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'La preautenticación del usuario falló', 'error'
            );
            $this->redirect('/usuarios/ingresar');
        }
        // todo ok -> redirigir
        $this->redirect($url);
    }

    /**
     * Acción que verifica el token ingresado y hace el pareo con telegram
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2017-10-16
     */
    public function telegram_parear()
    {
        if (!empty($_POST['telegram_token'])) {
            $token = $_POST['telegram_token'];
            $telegram_user = $this->Cache->get('telegram.pairing.'.$token);
            // si no se encontró el usuario el token no es válido o expiró
            if (!$telegram_user) {
                \sowerphp\core\Model_Datasource_Session::message('Token no válido o expiró, por favor, solicite uno nuevo al Bot con <strong><em>/token</em></strong>', 'error');
            }
            // se encontró el usuario, entonces guardar los datos del usuario de Telegram en el usuario de la aplicación web
            else {
                $this->Auth->User->config_telegram_id = $telegram_user['id'];
                $this->Auth->User->config_telegram_username = $telegram_user['username'];
                try {
                    $this->Auth->User->save();
                    $this->Auth->saveCache();
                    $this->Cache->delete('telegram.pairing.'.$token);
                    \sowerphp\core\Model_Datasource_Session::message('Usuario @'.$telegram_user['username'].' pareado con éxito', 'ok');
                } catch (\Exception $e) {
                    \sowerphp\core\Model_Datasource_Session::message('Ocurrió un error al parear con Telegram: '.$e->getMessage(), 'error');
                }
            }
        }
        $this->redirect('/usuarios/perfil#apps');
    }

    /**
     * Función de la API que permite obtener el perfil del usuario autenticado
     * @param usuario Usuario al que se desea obtener su perfil
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-02
     */
    public function _api_perfil_GET($usuario)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        if (strtolower($usuario)!=strtolower($User->usuario)) {
            $this->Api->send('Solo es posible consultar por el perfil del usuario autenticado', 403);
        }
        return [
            'id' => $User->id,
            'nombre' => $User->nombre,
            'usuario' => $User->usuario,
            'email' => $User->email,
            'hash' => $User->hash,
        ];
    }

}
