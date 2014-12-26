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
 * Clase para mapear la tabla usuario de la base de datos
 * Comentario de la tabla: Usuarios de la aplicación
 * Esta clase permite trabajar sobre un registro de la tabla usuario
 * @author SowerPHP Code Generator
 * @version 2014-10-25
 */
class Model_Usuario extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'usuario'; ///< Tabla del modelo

    public static $fkNamespace = array(); ///< Namespaces que utiliza esta clase

    // Atributos de la clase (columnas en la base de datos)
    public $id; ///< Identificador (serial): integer(32) NOT NULL DEFAULT 'nextval('usuario_id_seq'::regclass)' AUTO PK
    public $nombre; ///< Nombre real del usuario: character varying(30) NOT NULL DEFAULT ''
    public $usuario; ///< Nombre de usuario: character varying(20) NOT NULL DEFAULT ''
    public $email; ///< Correo electrónico del usuario: character varying(20) NOT NULL DEFAULT ''
    public $contrasenia; ///< Contraseña del usuario: character(64) NOT NULL DEFAULT ''
    public $contrasenia_intentos; ///< Intentos de inicio de sesión antes de bloquear cuenta: SMALLINT(6) NOT NULL DEFAULT '3'
    public $hash; ///< Hash único del usuario (32 caracteres): character(32) NOT NULL DEFAULT ''
    public $token; ///< Token para servicio secundario de autorización: character(64) NULL DEFAULT ''
    public $activo; ///< Indica si el usuario está o no activo en la aplicación: boolean() NOT NULL DEFAULT 'true'
    public $ultimo_ingreso_fecha_hora; ///< Fecha y hora del último ingreso del usuario: timestamp without time zone() NULL DEFAULT ''
    public $ultimo_ingreso_desde; ///< Dirección IP del último ingreso del usuario: character varying(45) NULL DEFAULT ''
    public $ultimo_ingreso_hash; ///< Hash del último ingreso del usuario: character(32) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'id' => array(
            'name'      => 'ID',
            'comment'   => 'Identificador (serial)',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => "nextval('usuario_id_seq'::regclass)",
            'auto'      => true,
            'pk'        => true,
            'fk'        => null
        ),
        'nombre' => array(
            'name'      => 'Nombre',
            'comment'   => 'Nombre real del usuario',
            'type'      => 'character varying',
            'length'    => 30,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => 'Nombre de usuario',
            'type'      => 'character varying',
            'length'    => 20,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'email' => array(
            'name'      => 'Email',
            'comment'   => 'Correo electrónico del usuario',
            'type'      => 'character varying',
            'length'    => 20,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
            'check'     => ['email'],
        ),
        'contrasenia' => array(
            'name'      => 'Contraseña',
            'comment'   => 'Contraseña del usuario',
            'type'      => 'character',
            'length'    => 64,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'contrasenia_intentos' => array(
            'name'      => 'Contraseña Intentos',
            'comment'   => 'Intentos de inicio de sesión antes de bloquear cuenta',
            'type'      => 'smallint',
            'length'    => 6,
            'null'      => false,
            'default'   => "3",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'hash' => array(
            'name'      => 'Hash',
            'comment'   => 'Hash único del usuario (32 caracteres)',
            'type'      => 'character',
            'length'    => 32,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'token' => array(
            'name'      => 'Token',
            'comment'   => 'Token para servicio secundario de autorización',
            'type'      => 'character',
            'length'    => 64,
            'null'      => true,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'activo' => array(
            'name'      => 'Activo',
            'comment'   => 'Indica si el usuario está o no activo en la aplicación',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => "true",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'ultimo_ingreso_fecha_hora' => array(
            'name'      => 'Último ingreso',
            'comment'   => 'Fecha y hora del último ingreso del usuario',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => "",
            'auto'      => true,
            'pk'        => false,
            'fk'        => null
        ),
        'ultimo_ingreso_desde' => array(
            'name'      => 'Última IP',
            'comment'   => 'Dirección IP del último ingreso del usuario',
            'type'      => 'character varying',
            'length'    => 45,
            'null'      => true,
            'default'   => "",
            'auto'      => true,
            'pk'        => false,
            'fk'        => null
        ),
        'ultimo_ingreso_hash' => array(
            'name'      => 'Último hash',
            'comment'   => 'Hash del último ingreso del usuario',
            'type'      => 'character',
            'length'    => 32,
            'null'      => true,
            'default'   => "",
            'auto'      => true,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = 'Usuarios de la aplicación';

    // atributos para caché
    protected $groups = null; ///< Grupos a los que pertenece el usuario
    protected $auths = null; ///< Permisos que tiene el usuario

    /**
     * Constructor de la clase usuario
     * Permite crear el objeto usuario ya sea recibiendo el id del usuario, el
     * email, el nombre de usuario o el hash de usuario.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-23
     */
    public function __construct ($id = null)
    {
        if (!is_array($id) && !is_numeric($id)) {
            $this->db = \sowerphp\core\Model_Datasource_Database::get ($this->_database);
            // se crea usuario a través de su correo electrónico
            if (strpos($id, '@')) {
                $id = $this->db->getValue('
                    SELECT id
                    FROM usuario
                    WHERE email = :email
                ', [':email'=>$id]);
            }
            // se crea usuario a través de su nombre de usuario
            else if (!isset($id[31])) {
                $id = $this->db->getValue('
                    SELECT id
                    FROM usuario
                    WHERE usuario = :usuario
                ', [':usuario'=>$id]);
            }
            //
            else {
                $id = $this->db->getValue('
                    SELECT id
                    FROM usuario
                    WHERE hash = :hash
                ', [':hash'=>$id]);
            }
        }
        parent::__construct ($id);
    }

    /**
     * Método que hace un UPDATE del usuario en la BD
     * Actualiza todos los campos, excepto: contrasenia, contrasenia_internos y
     * token, lo anterior ya que hay métodos especiales para actualizar dichas
     * columnas.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function update ($columns = null)
    {
        if ($columns) {
            return parent::update($columns);
        } else {
            return parent::update([
                'nombre' => $this->nombre,
                'usuario' => $this->usuario,
                'email' => $this->email,
                'hash' => $this->hash,
                'activo' => $this->activo,
                'ultimo_ingreso_fecha_hora' => $this->ultimo_ingreso_fecha_hora,
                'ultimo_ingreso_desde' => $this->ultimo_ingreso_desde,
                'ultimo_ingreso_hash' => $this->ultimo_ingreso_hash,
            ]);
        }
    }

    /**
     * Método que revisa si el nombre de usuario ya existe en la base de datos
     * @return =true si el nombre de usuario ya existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-27
     */
    public function checkIfUserAlreadyExists ()
    {
        if (empty($this->id)) {
            return (boolean)$this->db->getValue('
                SELECT COUNT(*) FROM usuario WHERE usuario = :usuario
            ', [':usuario'=>$this->usuario]);
        } else {
            return (boolean)$this->db->getValue('
                SELECT COUNT(*)
                FROM usuario
                WHERE id != :id AND usuario = :usuario
            ', [':id'=>$this->id, ':usuario'=>$this->usuario]);
        }
    }

    /**
     * Método que revisa si el email ya existe en la base de datos
     * @return =true si el correo ya existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-19
     */
    public function checkIfEmailAlreadyExists ()
    {
        if (empty($this->id)) {
            return (boolean)$this->db->getValue('
                SELECT COUNT(*) FROM usuario WHERE email = :email
            ', [':email'=>$this->email]);
        } else {
            return (boolean)$this->db->getValue('
                SELECT COUNT(*)
                FROM usuario
                WHERE id != :id AND email = :email
            ', [':id'=>$this->id, ':email'=>$this->email]);
        }
    }

    /**
     * Método que revisa si el hash del usuario ya existe en la base de datos
     * @return =true si el hash ya existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-19
     */
    public function checkIfHashAlreadyExists ()
    {
        if (empty($this->id)) {
            return (boolean)$this->db->getValue('
                SELECT COUNT(*) FROM usuario WHERE hash = :hash
            ', [':hash'=>$this->hash]);
        } else {
            return (boolean)$this->db->getValue('
                SELECT COUNT(*)
                FROM usuario
                WHERE id != :id AND hash = :hash
            ', [':id'=>$this->id, ':hash'=>$this->hash]);
        }
    }

    /**
     * Método que guarda la contraseña de un usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-27
     */
    public function savePassword($password)
    {
        $this->contrasenia = $this->hashPassword($password);
        $this->db->query('
            UPDATE usuario SET contrasenia = :contrasenia WHERE id = :id
        ', [':contrasenia'=>$this->contrasenia, ':id'=>$this->id]);
    }

    /**
     * Método que calcula el hash para la contraseña según el algoritmo más
     * fuerte disponible en PHP y usando un salt automático.
     * @param password Contraseña que se desea encriptar
     * @return Contraseña encriptada (su hash)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-26
     */
    public function hashPassword($password)
    {
        return password_hash($password, \PASSWORD_DEFAULT, ['cost' => 9]);
    }

    /**
     * Método que revisa si la contraseña entregada es igual a la contraseña del
     * usuario almacenada en la base de datos
     * @param password Contrasela que se desea verificar
     * @return =true si la contraseña coincide con la de la base de datos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-26
     */
    public function checkPassword($password)
    {
        if (isset($this->contrasenia[63])) {
            $status = $this->contrasenia == hash('sha256', $password);
            if ($status) $this->savePassword($password);
            return $status;
        }
        return password_verify($password, $this->contrasenia);
    }

    /**
     * Método que revisa si el hash indicado es igual al hash que tiene el
     * usuario para su último ingreso (o sea si la sesión es aun válida)
     * @return =true si el hash aun es válido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function checkLastLoginHash($hash)
    {
        return $this->ultimo_ingreso_hash == $hash;
    }

    /**
     * Método que indica si el usuario está o no activo
     * @return =true si el usuario está activo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function isActive()
    {
        return (boolean) $this->activo;
    }

    /**
     * Método que entrega un arreglo con los datos del último acceso del usuario
     * @return Arreglo con índices: fecha_hora, desde, hash
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function lastLogin()
    {
        return [
            'fecha_hora' => $this->ultimo_ingreso_fecha_hora,
            'desde' => $this->ultimo_ingreso_desde,
            'hash' => $this->ultimo_ingreso_hash,
        ];
    }

    /**
     * Método que actualiza el último ingreso del usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-26
     */
    public function updateLastLogin($ip)
    {
        $timestamp = date('Y-m-d H:i:s');
        $hash = md5($ip.$timestamp.$this->contrasenia);
        $this->update ([
            'ultimo_ingreso_fecha_hora' => $timestamp,
            'ultimo_ingreso_desde' => $ip,
            'ultimo_ingreso_hash' => $hash
        ]);
        return $hash;
    }

    /**
     * Método que entrega el listado de grupos a los que pertenece el usuario
     * @return Arreglo asociativo con el GID como clave y el nombre del grupo como valor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function groups()
    {
        if ($this->groups===null) {
            $this->groups = $this->db->getAssociativeArray('
                SELECT g.id, g.grupo
                FROM grupo AS g, usuario_grupo AS ug
                WHERE ug.usuario = :usuario AND g.id = ug.grupo
                ORDER BY g.grupo
            ', [':usuario'=>$this->id]);
        }
        return $this->groups;
    }

    /**
     * Método que permite determinar si un usuario pertenece a cierto grupo.
     * Además se revisará si pertenece al grupo sysadmin, en cuyo caso también
     * entregará true
     * @param grupos Arreglo con los grupos que se desean revisar
     * @return =true si pertenece a alguno de los grupos que se solicitaron
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function inGroup ($grupos = [])
    {
        $this->groups();
        $grupos[] = 'sysadmin';
        foreach ($grupos as $g) {
            if (in_array($g, $this->groups()))
                return true;
        }
        return false;
    }

    /**
     * Método que asigna los grupos al usuario, eliminando otros que no están
     * en el listado
     * @param grupos Arreglo con los GIDs de los grupos que se deben asignar/mantener
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-27
     */
    public function saveGroups($grupos)
    {
        if (!$grupos) return false;
        if (!is_numeric($grupos[0])) {
            $grupos = (new Model_Grupos())->getIDs($grupos);
        }
        $grupos = array_map('intval', $grupos);
        $this->db->beginTransaction();
        if ($grupos) {
            $this->db->query ('
                DELETE FROM usuario_grupo
                WHERE
                    usuario = :usuario
                    AND grupo NOT IN ('.implode(', ', $grupos).')
            ', [':usuario'=>$this->id]);
            foreach ($grupos as &$grupo) {
                (new Model_UsuarioGrupo ($this->id, $grupo))->save();
            }
        } else {
            $this->db->query ('
                DELETE FROM usuario_grupo
                WHERE usuario = :usuario
            ', [':usuario'=>$this->id]);
        }
        $this->db->commit();
    }

    /**
     * Método que entrega el listado de recursos sobre los que el usuario tiene
     * permisos para acceder.
     * @return Arreglo asociativo con el GID como clave y el nombre del grupo como valor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function auths()
    {
        if ($this->auths===null) {
            $this->auths = $this->db->getCol('
                SELECT a.recurso
                FROM auth AS a, usuario_grupo AS ug
                WHERE ug.usuario = :usuario AND a.grupo = ug.grupo
            ', [':usuario'=>$this->id]);
        }
        return $this->auths;
    }

    /**
     * Método que verifica si el usuario tiene permiso para acceder a cierto
     * recurso.
     * @return =true si tiene permiso
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-14
     */
    public function auth($recurso)
    {
        $recurso = is_string($recurso) ? $recurso : $recurso->request;
        $permisos = $this->auths();
        // buscar permiso de forma exacta
        if (in_array($recurso, $permisos))
            return true;
        // buscar si el usuario tiene permiso para acceder a todo
        if (in_array('*', $permisos))
            return true;
        // revisar por cada permiso
        foreach ($permisos as &$permiso) {
            // buscar si el permiso es del tipo recurso*
            if ($permiso[strlen($permiso)-1]=='*' and strpos($recurso, substr($permiso, 0, -1))===0) {
                return true;
            }
            // buscar por partes
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

    /**
     * Método que asigna los intentos de contraseña
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-12-27
     */
    public function savePasswordRetry($intentos)
    {
        $this->contrasenia_intentos = $intentos;
        $this->db->query(
            'UPDATE usuario SET contrasenia_intentos = :intentos WHERE id = :id'
        , [':id' => $this->id, ':intentos' => $intentos]);
    }

    /**
     * Método que crea el token para el usuario
     * @param codigo Código que se usará para crear el token
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function createToken($codigo)
    {
        $config = \sowerphp\core\Configure::read('auth2');
        if ($config===null) return false;
        $class = '\sowerphp\app\Model_Auth2_'.$config['name'];
        $Auth2 = new $class($config);
        $token = $Auth2->createToken($codigo);
        if ($token) {
            $this->token = $token;
            $this->db->query(
                'UPDATE usuario SET token = :token WHERE id = :id'
            , [':id' => $this->id, ':token' => $token]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Método que destruye el token en la autorización secundaria
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function destroyToken()
    {
        $config = \sowerphp\core\Configure::read('auth2');
        if ($config===null) return true;
        $class = '\sowerphp\app\Model_Auth2_'.$config['name'];
        $Auth2 = new $class($config);
        if ($Auth2->destroyToken($this->token)) {
            $this->token = null;
            $this->db->query(
                'UPDATE usuario SET token = NULL WHERE id = :id'
            , [':id' => $this->id]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Método que valida el estado del token con la autorización secundaria
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-10-25
     */
    public function checkToken()
    {
        $config = \sowerphp\core\Configure::read('auth2');
        if ($config===null or !isset($this->token[0])) return true;
        $class = '\sowerphp\app\Model_Auth2_'.$config['name'];
        $Auth2 = new $class($config);
        return $Auth2->checkToken($this->token);
    }

}
