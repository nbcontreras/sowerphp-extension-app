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
 * Controlador para el envío masivo de correos electrónicos a usuarios de la
 * aplicación
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2015-04-03
 */
class Controller_Email extends \Controller_App
{

    /**
     * Acción que permite enviar correos masivos a los usuarios de ciertos
     * grupos de la aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-04-03
     */
    public function grupos()
    {
        $Grupos = new Model_Grupos();
        $page_title = \sowerphp\core\Configure::read('page.header.title');
        $this->set([
            'grupos' => $Grupos->getList(),
            'page_title' => $page_title,
        ]);
        if (isset($_POST['submit'])) {
            if (!isset($_POST['grupos']) or empty($_POST['asunto']) or empty($_POST['mensaje'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe completar todos los campos del formulario', 'error'
                );
            } else {
                $emails = $Grupos->emails($_POST['grupos']);
                if(($key = array_search($this->Auth->User->email, $emails)) !== false) {
                    unset($emails[$key]);
                }
                $n_emails = count($emails);
                if (!$n_emails) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No hay destinatarios para el correo electrónico con los grupos seleccionados', 'error'
                    );
                } else {
                    // preparar mensaje a enviar
                    $layout = $this->layout;
                    $this->layout = null;
                    $this->set (array(
                        'mensaje' => $_POST['mensaje'],
                        'n_emails' => $n_emails,
                        'grupos' => $Grupos->getGlosas($_POST['grupos']),
                        'de_nombre' => $this->Auth->User->nombre,
                        'de_email' => $this->Auth->User->email,
                    ));
                    $msg = $this->render('Email/grupos_email')->body();
                    $this->layout = $layout;
                    // enviar email
                    $email = new \sowerphp\core\Network_Email();
                    $email->from($this->Auth->User->email, $this->Auth->User->nombre);
                    $email->replyTo($this->Auth->User->email, $this->Auth->User->nombre);
                    $email->to($this->Auth->User->email);
                    if ($_POST['enviar_como']=='cc') {
                        $email->cc($emails);
                    } else {
                        $email->bcc($emails);
                    }
                    $email->subject('['.$page_title.'] '.$_POST['asunto']);
                    $status = $email->send($msg);
                    if ($status===true) {
                        \sowerphp\core\Model_Datasource_Session::message(
                            'Mensaje envíado a '.$n_emails.' usuarios', 'ok'
                        );
                    } else {
                        \sowerphp\core\Model_Datasource_Session::message(
                            'Ha ocurrido un error al intentar enviar su mensaje, por favor intente nuevamente.<br /><em>'.$status['message'].'</em>', 'error'
                        );
                    }
                    $this->redirect($this->request->request);
                }
            }
        }
    }

}
