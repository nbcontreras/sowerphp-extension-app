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
 * Controlador base para Bot de la aplicación web
 * Para usar con Telegram se debe configurar el webhook en la URL:
 *   https://api.telegram.org/bot<token>/setWebhook?url=https://example.com/api/bot/telegram
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2015-07-08
 */
abstract class Controller_Bot extends \Controller_App
{

    protected $Bot; ///< Objeto para el Bot
    public $log_facility = LOG_DAEMON; ///< Origen para sistema de Logs
    protected $messages = [
        'hello' => '¡Hola %s! ¿Qué necesitas?',
        'doNotUnderstand' => "No entiendo lo que me dices \xF0\x9F\x98\x9E Si necesitas ayuda dime /help",
        'helpMiss' => "No sé como explicar lo que puedo hacer por ti \xF0\x9F\x98\x85",
        'canceled' => "He cancelado lo último que estábamos haciendo \xF0\x9F\x91\x8D",
        'nothingToCancel' => "Aun no me has pedido algo, no sé que quieres cancelar \xF0\x9F\x98\x96",
        'doNotKnow' => "No sé que me estás pidiendo, no sé nada sobre /%s \xF0\x9F\x98\x95",
        'argsMiss' => "Por favor háblame claro \xF0\x9F\x98\x91 Dime lo que necesitas así:\n/%s %s",
        'whoami' => "%s \nUsuario: %s\nID: %s",
        'settings' => [
            'select' => 'Dime qué opción quieres configurar',
            'miss' => "No tengo opciones que se puedan configurar \xF0\x9F\x98\x9E",
        ],
        'support' => [
            'msg' => 'Dime el mensaje que quieres que envíe a mis creadores',
            'subject' => '@%s necesita ayuda con %s #%d',
            'ok' => "He enviado tu mensaje a mis creadores \xF0\x9F\x91\x8D",
            'bad' => "Ups, no pude enviar el mensaje \xF0\x9F\x98\xA2",
        ],
    ]; ///< Mensajes del Bot (http://apps.timwhitlock.info/emoji/tables/unicode)
    private $auto_previous_command = true; ///< ¿Colocar automáticamente el último comando usado?
    protected $keyboards = [
        'numbers' => [['1','2','3'], ['4','5','6'], ['7','8','9'], ['0']],
    ]; ///< Layouts de teclados

    /**
     * Acción principal de la API, se encargará de llamar los comandos del Bot
     * @param token Token del Bot de Telegra, permite validar que es Telegram quien escribe al Bot
     * @return Entrega el retorno entregado por el método del bot ejecutado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-03
     */
    public function _api_telegram_POST($token)
    {
        if ($token!=\sowerphp\core\Configure::read('telegram.default.token'))
            $this->Api->send('Token del Bot de Telegram incorrecto', 401);
        $this->Bot = new \sowerphp\app\Utility_Bot_Telegram();
        return $this->run($this->Bot->getCommand());
    }

    /**
     * Método que ejecuta un comando solicitado al Bot
     * @param command String completo con el comando y sus argumentos
     * @return Entrega el retorno entregado por el método del bot ejecutado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    private function run($command)
    {
        if (!$command)
            return false;
        $argv = $this->string2argv($command);
        $next_command = $this->getNextCommand();
        if ($next_command) {
            if (!isset($argv[0]) or $argv[0][0]!='/')
                array_unshift($argv, '/'.$next_command);
            else {
                if ($argv[0]=='/cancel') {
                    $this->setNextCommand();
                    return $this->Bot->send(__($this->messages['canceled']));
                }
                else if ($argv[0]=='/start') {
                    $this->setNextCommand();
                }
                else {
                    $argv[0] = '/'.$next_command;
                }
            }
        }
        if ($argv[0][0]!='/') {
            return $this->Bot->send(__($this->messages['doNotUnderstand']));
        }
        if (in_array($argv[0], ['/', '/ayuda'])) {
            $argv[0] = '/help';
        }
        $command = substr(array_shift($argv), 1);
        $method = '_bot_'.$command;
        if (!method_exists($this, $method)) {
            $this->Bot->send(__($this->messages['doNotKnow'], $command));
            return;
        }
        $reflectionMethod = new \ReflectionMethod($this, $method);
        if (count($argv)<$reflectionMethod->getNumberOfRequiredParameters()) {
            $args = [];
            foreach($reflectionMethod->getParameters() as &$p) {
                $args[] = $p->isOptional() ? '['.$p->name.']' : $p->name;
            }
            $this->Bot->send(__($this->messages['argsMiss'], $command, implode(' ', $args)));
            return;
        }
        $this->beforeRun($command);
        $result = call_user_func_array([$this, $method], $argv);
        $this->afterRun($command);
        return $result;
    }

    /**
     * Método que parsea un string extrayendo el comango y sus argumentos
     * @param string String que se desea parsear
     * @return Arreglo con formamto argv (en 0 nombre del comando y desde 1 los argumentos)
     * @author http://stackoverflow.com/a/18217486
     * @version 2013-08-14
     */
    private function string2argv($string)
    {
        preg_match_all('#(?<!\\\\)("|\')(?<escaped>(?:[^\\\\]|\\\\.)*?)\1|(?<unescaped>\S+)#s', $string, $matches, PREG_SET_ORDER);
        $results = array();
        foreach ($matches as $array) {
            if (!empty($array['escaped'])) {
                $results[] = $array['escaped'];
            } else {
                $results[] = $array['unescaped'];
            }
        }
        return $results;
    }

    /**
     * Método que ejecuta antes de correr el comando
     * @param command Nombre del comando que se ejecutará
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-28
     */
    protected function beforeRun($command)
    {
    }

    /**
     * Método que ejecuta después de correr el comando
     * @param command Nombre del comando que se ejecutó
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-28
     */
    protected function afterRun($command)
    {
        if ($this->auto_previous_command)
            $this->setPreviousCommand($command);
    }

    /**
     * Método que asigna el próximo comando que se debe ejecutar, lo forzará
     * @param command Nombre del comando que se deberá ejecutar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    protected function setNextCommand($command = null)
    {
        if ($command)
            $this->Cache->set('bot_next_command_'.$this->Bot->getFrom()->id, $command);
        else
            $this->Cache->delete('bot_next_command_'.$this->Bot->getFrom()->id);
    }

    /**
     * Método que obtiene el próximo comando que se debe ejecutar
     * @return Nombre del comando que se deberá ejecutar próximamente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    protected function getNextCommand()
    {
        return $this->Cache->get('bot_next_command_'.$this->Bot->getFrom()->id);
    }

    /**
     * Método que recuerda el comando que se ejecutó para ser usado en una
     * próxima llamada como referencia de donde se "venía"
     * @param command Nombre del comando que se desea recordar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    protected function setPreviousCommand($command = null)
    {
        if (!$command)
            $command = $this->getPreviousCommand();
        $this->auto_previous_command = false;
        $this->Cache->set('bot_previous_command_'.$this->Bot->getFrom()->id, $command);
    }

    /**
     * Método que obtiene el comando que se ejecuto antes que el actual, o bien
     * desde donde venía el comando actual
     * @return Nombre del comando que se ejecutó antes del comando que se está ejecutando ahora
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    protected function getPreviousCommand()
    {
        return $this->Cache->get('bot_previous_command_'.$this->Bot->getFrom()->id);
    }

    /**
     * Método que obtiene un layout de teclado para ser envíado al usuario
     * @param keyboard Teclado que se quiere recuperar
     * @return Layout (arreglo) con el teclado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-03
     */
    protected function getKeyboard($keyboard, $cols = 2)
    {
        if (is_string($keyboard)) {
            if (method_exists($this, 'getKeyboard'.ucfirst($keyboard)))
                return $this->{'getKeyboard'.ucfirst($keyboard)}();
            else if (isset($this->keyboards[$keyboard]))
                return $this->keyboards[$keyboard];
        }
        else if (is_array($keyboard)) {
            $kb = [];
            $i = 0;
            foreach ($keyboard as $option) {
                if (is_array($option))
                    $option = implode(' - ', $option);
                if ($i%$cols==0)
                    $kb[] = [];
                $kb[(int)($i/$cols)][] = $option;
                $i++;
            }
            return $kb;
        }
        return false;
    }

    /**
     * Comando del Bot para iniciar saludando al usuario
     * @param token Token de autenticación para el usuario que escribe al Bot
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    protected function _bot_start($token = null)
    {
        $this->Bot->sendChatAction();
        $this->Bot->send(__($this->messages['hello'], $this->Bot->getFrom()->first_name));
    }

    /**
     * Comando del Bot para mostrar un mensaje por defecto de no existencia de
     * ayuda el bot
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-29
     */
    protected function _bot_help()
    {
        $this->Bot->sendChatAction();
        if (!isset($this->help))
            $this->Bot->send(__($this->messages['helpMiss']));
        else {
            $help = '';
            foreach ($this->help as $cmd => $desc)
                $help .= '/'.$cmd.' - '.$desc."\n";
            $this->Bot->send($help);
        }
    }

    /**
     * Comando del Bot para mostrar un mensaje por defecto de no existencia de
     * opciones del bot
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-08
     */
    protected function _bot_settings()
    {
        $this->Bot->sendChatAction();
        if (isset($this->settings) and is_array($this->settings) and !empty($this->settings)) {
            $this->Bot->sendKeyboard(
                __($this->messages['settings']['select']),
                $this->getKeyboard($this->settings, 3)
            );
        } else {
            $this->Bot->send(__($this->messages['settings']['miss']));
        }
    }

    /**
     * Comando del Bot que muestra mensaje en caso de que se haya solicitado
     * cancelar una acción y no se esté esperando ninguna
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-28
     */
    protected function _bot_cancel()
    {
        $this->Bot->sendChatAction();
        $this->Bot->send(__($this->messages['nothingToCancel']));
    }

    /**
     * Comando del Bot que dice quien es el usuario que habla con él
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-01
     */
    protected function _bot_whoami()
    {
        $this->Bot->sendChatAction();
        $from = $this->Bot->getFrom();
        $this->Bot->send(__(
            $this->messages['whoami'],
            $from->first_name.' '.$from->last_name,
            $from->username,
            $from->id
        ));
    }

    /**
     * Comando del Bot que envía la fecha y hora del servidor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-06-28
     */
    protected function _bot_date()
    {
        $this->Bot->sendChatAction();
        $this->Bot->send(date('Y-m-d H:i:s'));
    }

    /**
     * Comando del Bot que envía un mensaje al contacto de la aplicación
     * @param msg Mensaje que se desea enviar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-07-02
     */
    protected function _bot_support($msg = null)
    {
        if (!$msg) {
            $this->setNextCommand('support');
            $this->Bot->Send(__($this->messages['support']['msg']));
        } else {
            $this->setNextCommand();
            $this->Bot->sendChatAction();
            $msg = implode(' ', func_get_args());
            $email = new \sowerphp\core\Network_Email();
            $email->to(\sowerphp\core\Configure::read('email.default.to'));
            $email->subject(__($this->messages['support']['subject'], $this->Bot->getFrom()->username, $this->Bot, date('YmdHis')));
            $msg .= "\n\n".'-- '."\n".$this->Bot->getFrom()->first_name.' '.$this->Bot->getFrom()->last_name."\n".'https://telegram.me/'.$this->Bot->getFrom()->username."\n".$this->Bot.' - Telegram Bot';
            $status = $email->send($msg);
            if ($status===true) {
                $this->Bot->Send(__($this->messages['support']['ok']));
            } else {
                $this->Bot->Send(__($this->messages['support']['bad']));
            }
        }
    }

}
