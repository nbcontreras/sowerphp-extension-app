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
 * Clase para el controlador asociado a la tabla usuario de la base de
 * datos
 * Comentario de la tabla: Usuarios de la aplicación
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla usuario
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-09-15
 */
class Controller_Usuarios extends \sowerphp\app\Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar'=>['id', 'nombre', 'usuario', 'activo', 'ultimo_ingreso_fecha_hora']
    ]; ///< Columnas que se deben mostrar en las vistas
    protected $deleteRecord = false; ///< Indica si se permite o no borrar registros

    /**
     * Permitir ciertas acciones y luego ejecutar verificar permisos con
     * parent::beforeFilter()
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    public function beforeFilter ()
    {
        $this->Auth->allow('ingresar', 'salir', 'contrasenia_recuperar');
        $this->Auth->allowWithLogin('perfil');
        parent::beforeFilter();
    }

    /**
     * Acción para que un usuario ingrese al sistema (inicie sesión)
     * @param redirect Ruta (en base64) de hacia donde hay que redireccionar una vez se autentica el usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-28
     */
    public function ingresar ($redirect = null)
    {
        // si ya está logueado se redirecciona
        if ($this->Auth->logged()) {
            \sowerphp\core\Model_Datasource_Session::message(sprintf(
                'Usuario <em>%s</em> tiene su sesión abierta',
                $this->Auth->Usuario->usuario
            ));
            $this->redirect(
                $this->Auth->settings['redirect']['login']
            );
        }
        // procesar inicio de sesión
        if ($redirect)
            $redirect = base64_decode ($redirect);
        $this->set('redirect', $redirect);
        if (isset($_POST['submit'])) {
            // si el usuario o contraseña es vacio mensaje de error
            if (empty($_POST['usuario']) || empty($_POST['contrasenia'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe especificar usuario y clave'
                );
            }
            // realizar proceso de validación de datos
            else {
                $public_key = \sowerphp\core\Configure::read('recaptcha.public_key');
                if ($public_key) {
                    \sowerphp\core\App::import('Vendor/google/recaptcha/recaptchalib');
                }
                $this->Auth->login($_POST['usuario'], $_POST['contrasenia']);
                if ($this->Auth->User->contrasenia_intentos) {
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
        $this->Auth->logout();
    }

    /**
     * Acción para recuperar la contraseña
     * @param usuario Usuario al que se desea recuperar su contraseña
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    public function contrasenia_recuperar ($usuario = null)
    {
        $this->autoRender = false;
        // pedir correo
        if ($usuario == null) {
            if (!isset($_POST['submit'])) {
                $this->render ('Usuarios/contrasenia_recuperar_step1');
            } else {
                $Usuario = new Model_Usuario ($_POST['id']);
                if (!$Usuario->exists()) {
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Usuario o email inválido'
                    );
                    $this->render ('Usuarios/contrasenia_recuperar_step1');
                } else {
                    $this->contrasenia_recuperar_email (
                        $Usuario->email,
                        $Usuario->nombre,
                        $Usuario->usuario,
                        $this->Auth->hash($Usuario->contrasenia)
                    );
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Se ha enviado un email con las instrucciones para recuperar su contraseña'
                    );
                    $this->redirect('/usuarios/ingresar');
                }
            }
        }
        // cambiar contraseña
        else {
            $Usuario = new Model_Usuario ($usuario);
            if (!$Usuario->exists()) {
                \sowerphp\core\Model_Datasource_Session::message (
                    'Usuario inválido'
                );
                $this->redirect ('/usuarios/contrasenia/recuperar');
            }
            if (!isset($_POST['submit'])) {
                $this->set('usuario', $usuario);
                $this->render ('Usuarios/contrasenia_recuperar_step2');
            } else {
                if ($this->Auth->hash($Usuario->contrasenia)!=$_POST['codigo']) {
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Código ingresado no es válido para el usuario'
                    );
                    $this->set('usuario', $usuario);
                    $this->render ('Usuarios/contrasenia_recuperar_step2');
                }
                else if (empty ($_POST['contrasenia1']) || empty ($_POST['contrasenia2']) || $_POST['contrasenia1']!=$_POST['contrasenia2']) {
                    \sowerphp\core\Model_Datasource_Session::message (
                        'Contraseña nueva inválida (en blanco o no coinciden)'
                    );
                    $this->set('usuario', $usuario);
                    $this->render ('Usuarios/contrasenia_recuperar_step2');
                }
                else {
                    $Usuario->saveContrasenia(
                        $_POST['contrasenia1'],
                        $this->Auth->settings['hash']
                    );
                    $Usuario->setContraseniaIntentos($this->Auth->settings['maxLoginAttempts']);
                    \sowerphp\core\Model_Datasource_Session::message (
                        'La contraseña para el usuario '.$usuario.' ha sido cambiada con éxito'
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
     * @version 2014-10-01
     */
    public function crear ()
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
            $Usuario = new Model_Usuario();
            $Usuario->set($_POST);
            $ok = true;
            if ($Usuario->checkIfUsuarioAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso'
                );
                $ok = false;
            }
            if ($ok and $Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $ok = false;
            }
            if ($ok and $Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
                );
                $ok = false;
            }
            if ($ok) {
                if (empty($Usuario->contrasenia)) {
                    $Usuario->contrasenia = \sowerphp\core\Utility_String::random(8);
                }
                $contrasenia = $Usuario->contrasenia;
                $Usuario->contrasenia = $this->Auth->hash($Usuario->contrasenia);
                if (empty($Usuario->hash)) {
                    do {
                        $Usuario->hash = \sowerphp\core\Utility_String::random(32);
                    } while ($Usuario->checkIfHashAlreadyExists ());
                }
                if($Usuario->save()) {
                    $Usuario->saveGrupos($_POST['grupos']);
                    // enviar correo
                    $emailConfig = \sowerphp\core\Configure::read('email.default');
                    if (!empty($emailConfig['type']) && !empty($emailConfig['type']) && !empty($emailConfig['pass'])) {
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
                        $msg = 'Registro creado (se envió email a '.$Usuario->email.' con los datos de acceso)';
                    } else {
                        $msg = 'Registro creado (no se envió correo)';
                    }
                } else {
                    $msg = 'Registro no creado (hubo algún error)';
                }
                \sowerphp\core\Model_Datasource_Session::message($msg);
                $this->redirect('/sistema/usuarios/usuarios/listar'.$filterListar);
            }
        }
        // setear variables
        Model_Usuario::$columnsInfo['contrasenia']['null'] = true;
        Model_Usuario::$columnsInfo['hash']['null'] = true;
        $this->set(array(
            'accion' => 'Crear',
            'columns' => Model_Usuario::$columnsInfo,
            'grupos_asignados' => (isset($_POST['grupos'])?$_POST['grupos']:[]),
            'listarUrl'=>'/sistema/usuarios/usuarios/listar'.$filterListar
        ));
        $this->setGruposAsignables();
        $this->autoRender = false;
        $this->render ('Usuarios/crear_editar');
    }

    /**
     * Acción para editar un nuevo usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-24
     */
    public function editar ($id)
    {
        if (!empty($_GET['listar'])) {
            $filterListarUrl = '?listar='.$_GET['listar'];
            $filterListar = base64_decode($_GET['listar']);
        } else {
            $filterListarUrl = '';
            $filterListar = '';
        }
        $Usuario = new Model_Usuario($id);
        // si el registro que se quiere editar no existe error
        if(!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro ('.implode(', ', func_get_args()).') no existe, no se puede editar'
            );
            $this->redirect('/sistema/usuarios/usuarios/listar'.$filterListar);
        }
        // si no se ha enviado el formulario se mostrará
        if(!isset($_POST['submit'])) {
            Model_Usuario::$columnsInfo['contrasenia']['null'] = true;
            $grupos_asignados = $Usuario->groups();
            $this->setGruposAsignables();
            $this->set(array(
                'accion' => 'Editar',
                'Obj' => $Usuario,
                'columns' => Model_Usuario::$columnsInfo,
                'grupos_asignados' => array_keys($grupos_asignados),
                'listarUrl'=>'/sistema/usuarios/usuarios/listar'.$filterListar
            ));
            $this->autoRender = false;
            $this->render ('Usuarios/crear_editar');
        }
        // si se envió el formulario se procesa
        else {
            $Usuario->set($_POST);
            if ($Usuario->checkIfUsuarioAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            if ($Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            if ($Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id.$filterListarUrl);
            }
            $Usuario->save();
            if(!empty($_POST['contrasenia'])) {
                $Usuario->saveContrasenia(
                    $_POST['contrasenia'],
                    $this->Auth->settings['hash']
                );
            }
            $Usuario->saveGrupos($_POST['grupos']);
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') editado'
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
     * @version 2014-10-25
     */
    public function perfil ()
    {
        // procesar datos personales
        if (isset($_POST['datosUsuario'])) {
            // actualizar datos generales
            $this->Auth->User->set($_POST);
            if ($this->Auth->User->checkIfUsuarioAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso'
                );
                $this->redirect('/usuarios/perfil');
            }
            if ($this->Auth->User->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $this->redirect('/usuarios/perfil');
            }
            if ($this->Auth->User->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
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
            \sowerphp\core\Model_Datasource_Session::message('Perfil actualizado');
            $this->redirect('/usuarios/perfil');
        }
        // procesar cambio de contraseña
        else if (isset($_POST['cambiarContrasenia'])) {
            if(!empty($_POST['contrasenia1']) && $_POST['contrasenia1']==$_POST['contrasenia2']) {
                $this->Auth->User->saveContrasenia(
                    $_POST['contrasenia1'],
                    $this->Auth->settings['hash']
                );
                $this->Auth->saveCache();
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Contraseñas no coinciden'
                );
                $this->redirect('/usuarios/perfil');
            }
            // mensaje de ok y redireccionar
            \sowerphp\core\Model_Datasource_Session::message(
                'Contraseña actualizada'
            );
            $this->redirect('/usuarios/perfil');
        }
        // procesar creación del token
        else if (isset($_POST['crearToken']) and $this->Auth->settings['auth2'] !== null) {
            if ($this->Auth->User->createToken($_POST['codigo'])) {
                $this->Auth->saveCache();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Token creado, ahora tiene el control usando '.$this->Auth->settings['auth2']['name']
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible crear el token'
                );
            }
            $this->redirect('/usuarios/perfil');
        }
        // procesar destrucción del token
        else if (isset($_POST['destruirToken']) and $this->Auth->settings['auth2'] !== null) {
            if ($this->Auth->User->destroyToken()) {
                $this->Auth->saveCache();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Token destruído'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible destruir el token'
                );
            }
            $this->redirect('/usuarios/perfil');
        }
        // mostrar formulario para edición
        else {
            $this->set([
                'qrcode' => base64_encode($this->Auth->User->hash),
                'auth2' => $this->Auth->settings['auth2'],
            ]);
        }
    }

}
