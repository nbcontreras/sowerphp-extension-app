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
 * Clase que sirve para extender la clase Controller, este archivo
 * deberá ser sobreescrito en cada una de las aplicaciones
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-03-29
 */
class Controller_App extends \sowerphp\core\Controller
{

    public $components = array('Auth');

    /**
     * Constructor de la clase
     * @param request Objeto con la solicitud realizada
     * @param response Objeto para la respuesta que se enviará al cliente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-03-29
     */
    public function __construct (\sowerphp\core\Network_Request $request, \sowerphp\core\Network_Response $response)
    {
        parent::__construct ($request, $response);
        $this->set('_Auth', $this->Auth);
    }

}
