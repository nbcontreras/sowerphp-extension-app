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
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-05-04
 */
class Model_Auth extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'auth'; ///< Tabla del modelo

    public static $fkNamespace = array(
        'Model_Grupo' => 'sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    // Atributos de la clase (columnas en la base de datos)
    public $id; ///< Identificador (serial): integer(32) NOT NULL DEFAULT 'nextval('auth_id_seq'::regclass)' AUTO PK
    public $grupo; ///< Grupo al que se le condede el permiso: integer(32) NOT NULL DEFAULT '' FK:grupo.id
    public $recurso; ///< Recurso al que el grupo tiene acceso: character varying(300) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'id' => array(
            'name'      => 'Id',
            'comment'   => 'Identificador (serial)',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => "nextval('auth_id_seq'::regclass)",
            'auto'      => true,
            'pk'        => true,
            'fk'        => null
        ),
        'grupo' => array(
            'name'      => 'Grupo',
            'comment'   => 'Grupo al que se le concede el permiso',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'grupo', 'column' => 'id')
        ),
        'recurso' => array(
            'name'      => 'Recurso',
            'comment'   => 'Recurso al que el grupo tiene acceso',
            'type'      => 'character varying',
            'length'    => 300,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = 'Permisos de grupos para acceder a recursos';

    /**
     * Método que revisa los permisos de un usuario sobre un recurso
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-08-08
     */
    public function check ($usuario, $recurso = null)
    {
        $recurso = is_string($recurso) ? $recurso : $recurso->request;
        // determinar que usar para strpos
        if (in_array($this->db->config['type'], ['MariaDB', 'MySQL']))
            $strpos = 'INSTR';
        else
            $strpos = 'strpos';
        // chequeo "rápido"
        $ok = (boolean) $this->db->getValue('
            SELECT COUNT(*)
            FROM auth
            WHERE
                grupo IN (
                    SELECT grupo
                    FROM usuario_grupo
                    WHERE usuario = :usuario
                )
                AND (
                    -- verificar el recurso de forma exacta
                    recurso = :recurso
                    -- verificar el recurso con / al final
                    OR recurso||\'/\' = :recurso
                    -- verificar si existe algo del tipo recurso*
                    OR
                    CASE WHEN '.$strpos.'(recurso,\'*\')!=0 THEN
                        CASE WHEN '.$strpos.'(:recurso, substring(recurso from 1 for length(recurso)-1))=1 THEN
                            true
                        ELSE
                            false
                        END
                    ELSE
                        false
                    END
                )
        ', [
            ':usuario' => $usuario,
            ':recurso' => $recurso,
        ]);
        if ($ok) {
            return true;
        }
        // chequeo desglosando permisos (más lento)
        // esto evita tener que asignar cada recurso por el que se debe "pasar"
        // para llegar al recurso final que se quiere acceder
        $permisos = $this->db->getCol('
            SELECT a.recurso
            FROM auth AS a, usuario_grupo AS ug
            WHERE
                ug.usuario = :usuario
                AND a.grupo = ug.grupo
        ', [':usuario'=>$usuario]);
        foreach ($permisos as &$permiso) {
            $partes = explode('/', $permiso);
            array_shift($partes);
            $aux = '';
            foreach ($partes as &$parte) {
                if ($parte=='*') {
                    if (strpos($recurso, $aux)!==false) {
                        return true;
                    }
                } else {
                    $aux .= '/'.$parte;
                    if ($recurso === $aux)
                        return true;
                }
            }
        }
        // si no se encontró permiso => false
        return false;
    }

}
