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
namespace sowerphp\app\Sistema\Usuarios;

/**
 * Clase para mapear la tabla auth de la base de datos
 * Comentario de la tabla: Permisos de grupos para acceder a recursos
 * Esta clase permite trabajar sobre un registro de la tabla auth
 * @author SowerPHP Code Generator
 * @version 2014-04-05 17:32:18
 */
class Model_Auth extends Model_Base_Auth
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'auth'; ///< Tabla del modelo

    public static $fkNamespace = array(
        'Model_Grupo' => 'sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que revisa los permisos de un usuario sobre un recurso
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-02-29
     */
    public function check ($usuario, $recurso)
    {
        // limpiar parámetros
        $usuario = $this->db->sanitize($usuario);
        if (is_string($recurso))
            $recurso = $this->db->sanitize($recurso);
        else
            $recurso = $this->db->sanitize($recurso->request);
        // obtener permiso si existe
        return (boolean) $this->db->getValue("
            SELECT COUNT(*)
            FROM auth
            WHERE
                grupo IN (
                    SELECT grupo
                    FROM usuario_grupo
                    WHERE usuario = '".$usuario."'
                )
                AND (
                    -- verificar el recurso de forma exacta
                    recurso = '".$recurso."'
                    -- verificar el recurso con / al final
                    OR recurso||'/' = '".$recurso."'
                    -- verificar si existe algo del tipo recurso*
                    OR
                    CASE WHEN strpos(recurso,'*')!=0 THEN
                        CASE WHEN strpos('".$recurso."', substring(recurso from 1 for length(recurso)-1))=1 THEN
                            true
                        ELSE
                            false
                        END
                    ELSE
                        false
                    END
                )
        ");
    }

}
