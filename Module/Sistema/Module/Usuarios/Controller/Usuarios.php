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
 * @author SowerPHP Code Generator
 * @version 2014-04-05 17:32:18
 */
class Controller_Usuarios extends Controller_Base_Usuarios
{

    protected $module_url = '/sistema/usuarios/';

    public function beforeFilter ()
    {
        $this->Auth->allow('ingresar', 'salir', 'contrasenia_recuperar');
        $this->Auth->allowWithLogin('perfil');
        parent::beforeFilter();
    }

    public function ingresar ($redirect = null)
    {
        if ($redirect) $redirect = base64_decode ($redirect);
        $this->set('redirect', $redirect);
        $this->Auth->login($this);
    }

    public function salir () {
        $this->Auth->logout($this);
    }

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

    public function crear ()
    {
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            $Usuario = new Model_Usuario();
            $Usuario->set($_POST);
            if ($Usuario->checkIfUsuarioAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Nombre de usuario '.$_POST['usuario'].' ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/crear');
            }
            if ($Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/crear');
            }
            if ($Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/crear');
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
            $Usuario->save();
//            if(method_exists($this, 'u')) $this->u();
            $email = new \sowerphp\core\Network_Email();
            $email->to($Usuario->email);
            $email->subject('Cuenta de usuario creada');
            $email->send($msg);
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario creado (se envió email a '.$Usuario->email.' con los datos de acceso'
            );
            $this->redirect($this->module_url.'usuarios/listar');
        }
        // setear variables
        Model_Usuario::$columnsInfo['contrasenia']['null'] = true;
        Model_Usuario::$columnsInfo['hash']['null'] = true;
        $this->set(array(
            'accion' => 'Crear',
            'columnsInfo' => Model_Usuario::$columnsInfo,
        ));
        $this->autoRender = false;
        $this->render ('Usuarios/crear_editar');
    }

    public function editar ($id)
    {
        $Usuario = new Model_Usuario($id);
        // si el registro que se quiere editar no existe error
        if(!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') no existe, no se puede editar'
            );
            $this->redirect($this->module_url.'usuarios/listar');
        }
        // si no se ha enviado el formulario se mostrará
        if(!isset($_POST['submit'])) {
            Model_Usuario::$columnsInfo['contrasenia']['null'] = true;
            $this->set(array(
                'accion' => 'Editar',
                'Usuario' => $Usuario,
                'columnsInfo' => Model_Usuario::$columnsInfo,
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
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id);
            }
            if ($Usuario->checkIfHashAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hash seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id);
            }
            if ($Usuario->checkIfEmailAlreadyExists ()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Email seleccionado ya está en uso'
                );
                $this->redirect('/sistema/usuarios/usuarios/editar/'.$id);
            }
            $Usuario->save();
//            if(method_exists($this, 'u')) $this->u();
            if(!empty($_POST['contrasenia'])) {
                $Usuario->saveContrasenia(
                    $_POST['contrasenia'],
                    $this->Auth->settings['model']['user']['hash']
                );
            }
            \sowerphp\core\Model_Datasource_Session::message(
                'Registro Usuario('.implode(', ', func_get_args()).') editado'
            );
            $this->redirect($this->module_url.'usuarios/listar');
        }
    }

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
