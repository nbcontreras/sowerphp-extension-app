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
 * Clase para comunicación con Bot de Telegram
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2015-07-01
 */
class Utility_Bot_Telegram
{

    private $config; ///< Configuración del Bot
    private $data; ///< Datos envíados al Bot a través de POST

    /**
     * Constructor del Bot: asigna configuración y recupera datos envíados al bot
     * @param config Configuración: arreglo, token o nombre de la configuración en SowerPHP
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    public function __construct($config = 'default')
    {
        // asignar configuración del bot
        if (is_string($config)) {
            if (strpos($config, ':')) {
                $config = ['token' => $config];
            } else {
                if (class_exists('\sowerphp\core\Configure')) {
                    $config = \sowerphp\core\Configure::read('telegram.'.$config);
                    if (!$config)
                        $config = [];
                } else {
                    $config = [];
                }
            }
        }
        $this->config = array_merge([
            'bot' => get_class($this),
        ], $config);
        // si no hay token error
        if (empty($this->config['token'])) {
            trigger_error('Debes indicar al menos el token del Bot, ', E_USER_ERROR);
        }
        // asignar datos que se enviaron al bot
        $this->data = json_decode(file_get_contents('php://input'));
    }

    /**
     * Método que obtiene el objeto con el mensaje envíado al Bot
     * @return Objeto StdClass con el mensaje enviado al Bot
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-28
     */
    public function getMessage()
    {
        return $this->data ? $this->data->message : null;
    }

    /**
     * Método que obtiene el objeto de quien envió el mensaje al bot
     * @return Objeto StdClass con el remitente del mensaje al Bot
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    public function getFrom()
    {
        return $this->data ? $this->data->message->from : null;
    }

    /**
     * Método que obtiene el comando que está solicitando el usuario, ya sea un
     * texto o un comando especial por algo que haya enviado (por ejemplo una foto)
     * @return Comando que se deberá ejecutar o =false en caso de no existir un mensaje recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    public function getCommand()
    {
        if (!$this->getMessage())
            return false;
        if (isset($this->getMessage()->text))
            return $this->getMessage()->text;
        if (isset($this->getMessage()->location))
            return '/location '.$this->getMessage()->location->latitude.' '.$this->getMessage()->location->longitude;
        if (isset($this->getMessage()->photo))
            return '/photo '.$this->getMessage()->photo[0]->file_id;
        if (isset($this->getMessage()->document))
            return '/document '.$this->getMessage()->document->file_id;
        if (isset($this->getMessage()->audio))
            return '/audio '.$this->getMessage()->audio->file_id;
        if (isset($this->getMessage()->video))
            return '/video '.$this->getMessage()->video->file_id;
        if (isset($this->getMessage()->contact))
            return '/contact '.$this->getMessage()->contact->phone_number;
    }

    /**
     * Método que envía una respuesta a un usuario
     * @param message Mensaje que se desea enviar al usuario
     * @param chat_id ID del chat con el usuario, sino se indica se asumirá es respuesta a mensaje previo enviado por usuario
     * @return Retorno de \sowerphp\core\Network_Http_Socket::post() con estado solicitud POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-29
     */
    public function send($message, $chat_id = null)
    {
        return $this->sendMessage(array_merge([
            'chat_id' => $chat_id ? $chat_id : $this->data->message->chat->id,
            'reply_markup' => json_encode(['hide_keyboard'=>true]),
        ], !is_array($message) ? ['text'=>$message] : $message));
    }

    /**
     * Método que envía una respuesta a un usuario con un teclado personalizado
     * @param message Mensaje que se desea enviar al usuario
     * @param keyboard Layout, en arreglo, del teclado que se enviará
     * @param options Opciones para el teclado
     * @param chat_id ID del chat con el usuario, sino se indica se asumirá es respuesta a mensaje previo enviado por usuario
     * @return Retorno de \sowerphp\core\Network_Http_Socket::post() con estado solicitud POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-29
     */
    public function sendKeyboard($message, $keyboard, $options = [], $chat_id = null)
    {
        $default = [
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'selective' => false,
        ];
        if (isset($keyboard['keyboard'])) {
            $reply_markup = array_merge($default, $keyboard, $options);
        } else {
            $reply_markup = array_merge($default, ['keyboard' => $keyboard], $options);
        }
        return $this->send(['text'=>$message, 'reply_markup'=>json_encode($reply_markup)], $chat_id);
    }

    /**
     * Método que envía un estado de acción de chat al usuario
     * @param action Acción que se informará al usuario
     * @param chat_id ID del chat con el usuario, sino se indica se asumirá es respuesta a mensaje previo enviado por usuario
     * @return Retorno de \sowerphp\core\Network_Http_Socket::post() con estado solicitud POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-29
     */
    public function sendChatAction($action = 'typing', $chat_id = null)
    {
        return $this->__call('sendChatAction', [[
            'chat_id' => $chat_id ? $chat_id : $this->data->message->chat->id,
            'action' => $action,
        ]]);
    }

    /**
     * Método que permite ejecutar un comando en la API de Bots de Telegram
     * @param method Método que se quiere ejecutar en la API de Bots de Telegram
     * @param args Argumentos que se enviarán al servidor de Telegram
     * @return Retorno de \sowerphp\core\Network_Http_Socket::post() con estado solicitud POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    public function __call($method, $args = [])
    {
        return \sowerphp\core\Network_Http_Socket::post(
            'https://api.telegram.org/bot'.$this->config['token'].'/'.$method,
            isset($args[0]) ? $args[0] : []
        );
    }

    /**
     * Método mágico que entrega el nombre del Bot cuando el objeto es usado
     * como string
     * @return Nombre del Bot
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-28
     */
    public function __toString()
    {
        return $this->config['bot'];
    }

}
