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
 * @version 2014-04-23
 */
class Controller_Usuarios extends \sowerphp\app\Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar'=>['id', 'nombre', 'usuario', 'activo', 'ultimo_ingreso_fecha_hora']
    ]; ///< Columnas que se deben mostrar en las vistas

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
     * @version 2014-04-23
     */
    public function ingresar ($redirect = null)
    {
        if ($redirect) $redirect = base64_decode ($redirect);
        $this->set('redirect', $redirect);
        $this->Auth->login($this);
    }

    /**
     * Acción para que un usuario cierra la sesión
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-23
     */
    public function salir ()
    {
        $this->Auth->logout($this);
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
                        $this->Auth->settings['model']['user']['hash']
                    );
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
     * @version 2014-04-24
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
            if ($Usuario->checkIfUsuarioAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/crear'.$filterListarUrl);
            }
            if ($Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/crear'.$filterListarUrl);
            }
            if ($Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/crear'.$filterListarUrl);
            }
            if (empty($Usuario->contrasenia)) {
                $Usuario->contrasenia = string_random (8);
            }
            if (empty($Usuario->hash)) {
                do {
                    $Usuario->hash = string_random (32);
                } while ($Usuario->checkIfHashAlreadyExists ());
            }
            $layout = $this->layout;
            $this->layout = null;
            $this->set(array(
                'nombre'=>$Usuario->nombre,
                'usuario'=>$Usuario->usuario,
                'contrasenia'=>$Usuario->contrasenia,
            ));
            $msg = $this->render('Usuarios/crear_email')->body();
            $this->layout = $layout;
            $Usuario->contrasenia = $this->Auth->hash($Usuario->contrasenia);
            if($Usuario->save()) {
                // enviar correo
                $emailConfig = \sowerphp\core\Configure::read('email.default');
                if (!empty($emailConfig['type']) && !empty($emailConfig['type']) && !empty($emailConfig['pass'])) {
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
        // setear variables
        Model_Usuario::$columnsInfo['contrasenia']['null'] = true;
        Model_Usuario::$columnsInfo['hash']['null'] = true;
        $this->set(array(
            'accion' => 'Crear',
            'columns' => Model_Usuario::$columnsInfo,
        ));
        $this->autoRender = false;
        $this->render ('Maintainer/crear_editar', 'sowerphp/app');
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
            $this->set(array(
                'accion' => 'Editar',
                'Obj' => $Usuario,
                'columns' => Model_Usuario::$columnsInfo,
            ));
            $this->autoRender = false;
            $this->render ('Maintainer/crear_editar', 'sowerphp/app');
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
                    $this->Auth->settings['model']['user']['hash']
                );
            }
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') editado'
            );
            $this->redirect('/sistema/usuarios/usuarios/listar'.$filterListar);
        }
    }

    /**
     * Acción para mostrar y editar el perfil del usuario que esta autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-24
     */
    public function perfil ()
    {
        // obtener usuario
        $Usuario = new Model_Usuario(\sowerphp\core\Model_Datasource_Session::read('auth.id'));
        // procesar datos personales
        if (isset($_POST['datosUsuario'])) {
            // actualizar datos generales
            $Usuario->set($_POST);
            if ($Usuario->checkIfUsuarioAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso'
                );
                $this->redirect('/usuarios/perfil');
            }
            if ($Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $this->redirect('/usuarios/perfil');
            }
            if ($Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
                );
                $this->redirect('/usuarios/perfil');
            }
            if (empty($Usuario->hash)) {
                do {
                    $Usuario->hash = string_random (32);
                } while ($Usuario->checkIfHashAlreadyExists ());
            }
            $Usuario->save();
            // mensaje de ok y redireccionar
            \sowerphp\core\Model_Datasource_Session::message('Perfil actualizado');
            $this->redirect('/usuarios/perfil');
        }
        // procesar cambio de contraseña
        else if (isset($_POST['cambiarContrasenia'])) {
            if(!empty($_POST['contrasenia1']) && $_POST['contrasenia1']==$_POST['contrasenia2']) {
                $Usuario->saveContrasenia(
                    $_POST['contrasenia1'],
                    $this->Auth->settings['model']['user']['hash']
                );
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
        // mostrar formulario para edición
        else {
            $this->set(array(
                'Usuario' => $Usuario,
            ));
        }
    }

}
