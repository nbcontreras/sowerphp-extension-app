-- SCHEMA PARA MÓDULO: Sistema.Usuarios
-- Base de datos: MariaDB
-- WARNING: no se están usando llaves foráneas: ¿ERROR 1005 (errno: 150)?

START TRANSACTION;

-- tabla para usuarios
DROP TABLE IF EXISTS usuario CASCADE;
CREATE TABLE usuario (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador (serial)',
    nombre CHARACTER VARYING (50) NOT NULL COMMENT 'Nombre real del usuario',
    usuario CHARACTER VARYING (30) NOT NULL COMMENT 'Nombre de usuario',
    email CHARACTER VARYING (50) NOT NULL COMMENT 'Correo electrónico del usuario',
    contrasenia CHAR(64) NOT NULL COMMENT 'Contraseña del usuario',
    hash CHAR(32) NOT NULL COMMENT 'Hash único del usuario (32 caracteres)',
    activo BOOLEAN NOT NULL DEFAULT true COMMENT 'Indica si el usuario está o no activo en la aplicación',
    ultimo_ingreso_fecha_hora TIMESTAMP COMMENT 'Fecha y hora del último ingreso del usuario',
    ultimo_ingreso_desde CHARACTER VARYING (45) COMMENT 'Dirección IP del último ingreso del usuario',
    ultimo_ingreso_hash CHAR(32) COMMENT 'Hash del último ingreso del usuario'
) ENGINE = InnoDB COMMENT = 'Usuarios de la aplicación';
ALTER TABLE usuario AUTO_INCREMENT=1000;
CREATE UNIQUE INDEX usuario_usuario_idx ON usuario (usuario);
CREATE UNIQUE INDEX usuario_email_idx ON usuario (email);
CREATE UNIQUE INDEX usuario_hash_idx ON usuario (hash);

-- tabla para grupos
DROP TABLE IF EXISTS grupo CASCADE;
CREATE TABLE grupo (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador (serial)',
    grupo CHARACTER VARYING (30) NOT NULL COMMENT 'Nombre del grupo',
    activo BOOLEAN NOT NULL DEFAULT true COMMENT 'Indica si el grupo se encuentra activo'
) ENGINE = InnoDB COMMENT = 'Grupos de la aplicación';
ALTER TABLE grupo AUTO_INCREMENT=1000;
CREATE UNIQUE INDEX grupo_grupo_idx ON grupo (grupo);

-- tabla que relaciona usuarios con sus grupos
DROP TABLE IF EXISTS usuario_grupo CASCADE;
CREATE TABLE usuario_grupo (
    usuario INTEGER NOT NULL COMMENT 'Usuario de la aplicación',
    grupo INTEGER NOT NULL COMMENT 'Grupo al que pertenece el usuario',
    primario BOOLEAN NOT NULL DEFAULT false COMMENT 'Indica si el grupo es el grupo primario del usuario',
    PRIMARY KEY (usuario, grupo)
) ENGINE = InnoDB COMMENT = 'Relación entre usuarios y los grupos a los que pertenecen';
-- ALTER TABLE usuario_grupo ADD CONSTRAINT FOREIGN KEY (usuario) REFERENCES usuario (id) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE usuario_grupo ADD CONSTRAINT FOREIGN KEY (grupo) REFERENCES grupo (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- tabla que contiene los permisos de los grupos sobre recursos
DROP TABLE IF EXISTS auth CASCADE;
CREATE TABLE auth (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador (serial)',
    grupo INTEGER NOT NULL COMMENT 'Grupo al que se le concede el permiso',
    recurso CHARACTER VARYING (300) COMMENT 'Recurso al que el grupo tiene acceso',
    INDEX auth_grupo_idx (grupo)
) ENGINE = InnoDB COMMENT = 'Permisos de grupos para acceder a recursos';
-- ALTER TABLE auth ADD CONSTRAINT FOREIGN KEY (grupo) REFERENCES grupo (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- DATOS PARA EL MÓDULO:  Sistema.Usuarios

INSERT INTO grupo (grupo) VALUES
    ('sysadmin'), -- Grupo para quienes desarrollan la aplicación
    ('appadmin')  -- Grupo para aquellos que administran la aplicación y al no ser desarrolladores no necesitan "ver todo"
;
INSERT INTO auth (grupo, recurso) VALUES
    ((SELECT id FROM grupo WHERE grupo = 'sysadmin'), '*') -- grupo sysadmin tiene acceso a todos los recursos de la aplicación
;

INSERT INTO usuario (nombre, usuario, email, contrasenia, hash) VALUES
    -- usuario por defecto Administrador con clave admin, el hash único DEBE ser cambiado es un riesgo dejar el mismo!!!
    ('Administrador', 'admin', 'admin@example.com', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 't7dr5B1ujphds043WMMEFWwFLeyWYqMU')
;
INSERT INTO usuario_grupo (usuario, grupo, primario) VALUES
    ((SELECT id FROM usuario WHERE usuario = 'admin'), (SELECT id FROM grupo WHERE grupo = 'sysadmin'), true)
;

COMMIT;
