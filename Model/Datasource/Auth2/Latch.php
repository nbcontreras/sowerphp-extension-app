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

// namespace del modelo
namespace sowerphp\app;

// Incluir biblioteca
\sowerphp\core\App::import('Vendor/elevenpaths/latch-sdk-php/src/Latch');
\sowerphp\core\App::import('Vendor/elevenpaths/latch-sdk-php/src/Response');
\sowerphp\core\App::import('Vendor/elevenpaths/latch-sdk-php/src/Error');

/**
 * Wrapper para la autorización secundaria usando Latch
 * Requiere (en Debian GNU/Linux) paquete: php5-curl
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-12-29
 */
class Model_Datasource_Auth2_Latch
{

    private $Latch; ///< API de Latch
    private $config; ///< Configuración de Latch

    /**
     * Constructor de la clase
     * @param config Configuración de la autorización secundaria
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->Latch = new \ElevenPaths\Latch\Latch(
            $this->config['app_id'],
            $this->config['app_key']
        );
    }

    /**
     * Método que crea un token a partir del código entregado
     * @param codigo Código que se usará para crear el token
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function createToken($codigo)
    {
        $Response = $this->Latch->pair($codigo);
        if ($Response->error) return false;
        return $Response->data->accountId;
    }

    /**
     * Método que destruye el token en la autorización secundaria
     * @param token Token que se desea destruir
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function destroyToken($token)
    {
        $Response = $this->Latch->unpair($token);
        return !$Response->error ? true : false;
    }

    /**
     * Método que valida el estado del token con la autorización secundaria
     * @param token Token que se desea validar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-27
     */
    public function checkToken($token)
    {
        $Response = $this->Latch->status($token);
        if ($Response->error || $Response->data===null)
            return $this->config['default'];
        $status = $Response->data->operations->{$this->config['app_id']}->status;
        return $status == 'on' ? true : false;
    }

}
