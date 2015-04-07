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
 * Clase para mapear la tabla grupo de la base de datos
 * Comentario de la tabla: Grupos de la aplicación
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla grupo
 * @author SowerPHP Code Generator
 * @version 2014-04-05 17:32:18
 */
class Model_Grupos extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'grupo'; ///< Tabla del modelo

    /**
     * Método que entrega los IDs de un listado de nombres de grupos
     * @param grupos Arreglo con los grupos que se quiere saber sus IDs
     * @return Arreglo asociativo con grupo => id
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-11-19
     */
    public function getIDs($grupos)
    {
        $ids = [];
        foreach ($grupos as &$grupo) {
            $id = $this->db->getValue(
                'SELECT id FROM grupo WHERE grupo = :grupo',
                [':grupo'=>$grupo]
            );
            if ($id)
                $ids[$grupo] = $id;
        }
        return $ids;
    }

    /**
     * Método que entrega las glosas de lo grupos a partir de sus IDs
     * @param grupos Arreglo con los grupos que se buscan sus glosas
     * @return Arreglo con las glosas de los grupos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-04-03
     */
    public function getGlosas($grupos)
    {
        if (!is_array($grupos))
            $grupos = [$grupos];
        $where = $vars = [];
        $i = 1;
        foreach ($grupos as &$g) {
            $where[] = ':grupo'.$i;
            $vars[':grupo'.$i] = $g;
            $i++;
        }
        return $this->db->getCol('
            SELECT DISTINCT grupo
            FROM grupo
            WHERE id IN ('.implode(', ', $where).')
            ORDER BY grupo
        ', $vars);
    }

    /**
     * Método que entrega los correos electrónicos de los usuarios que
     * pertenecen a los grupos indicados
     * @param grupos Arreglo con los grupos que se buscan los email de sus usuarios
     * @return Arreglo con los correos de los usuarios que pertenecen a esos grupos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2015-04-07
     */
    public function emails($grupos)
    {
        if (!is_array($grupos))
            $grupos = [$grupos];
        $where = $vars = [];
        $i = 1;
        foreach ($grupos as &$g) {
            $where[] = ':grupo'.$i;
            $vars[':grupo'.$i] = $g;
            $i++;
        }
        return $this->db->getCol('
            SELECT DISTINCT u.email
            FROM usuario AS u JOIN usuario_grupo AS ug ON u.id = ug.usuario
            WHERE ug.grupo IN ('.implode(', ', $where).') AND u.activo
            ORDER BY u.email
        ', $vars);
    }

}
