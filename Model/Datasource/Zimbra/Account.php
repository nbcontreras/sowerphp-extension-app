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
 * Modelo para trabajar con una persona del LDAP de zimbra
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-12-29
 */
class Model_Datasource_Zimbra_Account extends Model_Datasource_Ldap_Person
{

    public $zimbraId;
    public $zimbraAccountStatus;
    public $zimbraIsAdminAccount;
    public $zimbraMailStatus;
    public $zimbraMailHost;
    public $zimbraMailDeliveryAddress;
    public $zimbraMailAlias;
    public $zimbraMailForwardingAddress;
    public $zimbraCreateTimestamp;
    public $zimbraLastLogonTimestamp;
    public $zimbraPasswordModifiedTime;
    public $zimbraPrefOutOfOfficeReplyEnabled;
    public $zimbraPrefOutOfOfficeReply;
    public $zimbraPrefOutOfOfficeStatusAlertOnLogin;
    public $zimbraAuthTokenValidityValue;

    protected $Zimbra; ///< Objeto que representa la conexión al servidor Zimbra
    protected $Ldap; ///< Objeto que representa la conexión al servidor LDAP


    public function __construct($uid, $Zimbra)
    {
        $this->Zimbra = $Zimbra;
        parent::__construct($uid, $this->Zimbra->Ldap);
    }

    /**
     * Método que indica si la cuenta está o no activa
     * @return =true si la cuenta está activa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-28
     */
    public function isActive()
    {
        return $this->zimbraAccountStatus == 'active';
    }

    /**
     * Método que indica si la cuenta es de tipo administrador
     * @return =true si la cuenta es de tipo administrador
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-28
     */
    public function isAdmin()
    {
        return $this->zimbraIsAdminAccount == 'TRUE';
    }

    /**
     * Método que cambia la contraseña del usuario
     * @param pass Contraseña en texto plano que se desea asignar
     * @return =true si la contraseña pudo ser cambiada
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-27
     */
    public function savePassword($pass)
    {
        $entry = [
            'userPassword' => [$this->hashPassword($pass)],
            'zimbraPasswordModifiedTime' => [gmdate('YmdHis').'Z'],
        ];
        $status = $this->Ldap->modify($this->dn, $entry);
        if ($status) {
            $this->userPassword = $entry['userPassword'][0];
            $this->zimbraPasswordModifiedTime = $entry['zimbraPasswordModifiedTime'][0];
            return true;
        } else return false;
    }

    /**
     * Método que obtiene la URL para entrar al correo con preautenticación
     * @param redirect Ruta a la que se desea ir luego de autenticar
     * @return URL para correo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-29
     */
    public function getUserUrl($redirect = '/mail')
    {
        $url = 'https://'.$this->zimbraMailHost.'/service/preauth';
        $timestamp = time() * 1000;
        $preAuthToken = hash_hmac('sha1', $this->zimbraMailDeliveryAddress.'|name|0|'.$timestamp, $this->Zimbra->getPreAuthKey());
        return $url.'?account='.$this->zimbraMailDeliveryAddress.'&amp;by=name&amp;timestamp='.$timestamp.'&amp;expires=0&amp;preauth='.$preAuthToken.'&amp;redirectURL='.$redirect;
    }

    /**
     * Método que obtiene la URL para entrar a la consola de administración de
     * Zimbra con preautenticación
     * @return URL para consola de administración
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-28
     */
    public function getAdminUrl()
    {
        if (!$this->isAdmin()) return false;
        $url = 'https://'.$this->zimbraMailHost.':7071/service/preauth';
        $timestamp = time() * 1000;
        $preAuthToken = hash_hmac('sha1', $this->zimbraMailDeliveryAddress.'|1|name|0|'.$timestamp, $this->Zimbra->getPreAuthKey());
        return $url.'?account='.$this->zimbraMailDeliveryAddress.'&amp;admin=1&amp;by=name&amp;timestamp='.$timestamp.'&amp;expires=0&amp;preauth='.$preAuthToken;
    }

    public function getUserAuthToken()
    {
        $url = 'https://'.$this->zimbraMailHost.'/service/preauth';
        $timestamp = time() * 1000;
        $preAuthToken = hash_hmac('sha1', $this->zimbraMailDeliveryAddress.'|name|0|'.$timestamp, $this->Zimbra->getPreAuthKey());
        $Rest = new \sowerphp\core\Network_Http_Rest();
        $response = $Rest->get($url, [
            'account' => $this->zimbraMailDeliveryAddress,
            'by' => 'name',
            'timestamp' => $timestamp,
            'expires' => 0,
            'preauth' => $preAuthToken,
        ], [], false, false);
        if ($response['status']['code']!=302) return false;
        return substr($response['header']['Set-Cookie'], 14, 213);
    }

    public function getUnreadMessages($folder = 'inbox', $length = false, $offset = 0)
    {
        $Rest = new \sowerphp\core\Network_Http_Rest();
        $response = $Rest->get('https://'.$this->zimbraMailHost.'/service/home/'.$this->uid.'/'.$folder, [
            'auth' => 'qp',
            'zauthtoken' => $this->getUserAuthToken(),
            'fmt' => 'json',
            'query' => 'is:unread',
        ], [], false, false);
        if ($response['status']['code']!=200) return false;
        if (!isset($response['body']['m'])) return [];
        if (!$length) return $response['body']['m'];
        return array_slice($response['body']['m'], $offset, $length);
    }

}
