Extensión: app
==============

[![Dependency Status](https://www.versioneye.com/user/projects/56b439d90a0ff50035ba7e4b/badge.svg)](https://www.versioneye.com/user/projects/56b439d90a0ff50035ba7e4b)
[![License](https://poser.pugx.org/sowerphp/app/license)](https://packagist.org/packages/sowerphp/app)

Extensión con funcionalidades básicas para una aplicación web. Implementa
código general que pueda servir para generar una aplicación web.

La extensión requiere que previamente se haya cargado la extensión general, ya
que es utilizada por esta. Por lo cual verificar que el archivo
*webroot/index.php* al menos contenga en la definición de extensiones:

	$_EXTENSIONS = ['sowerphp/app', 'sowerphp/general'];

La extensión usará los módulos de la extensión general:

- Exportar
- Sistema
- Sistema.Usuarios (este será autocargado)

Componentes
-----------

### Auth

Componente para realizar autenticación y autorización en la aplicación:

[Leer más sobre componente Auth](http://sowerphp.org/doc/componentes/Auth)

### Api

Componente que se encarga de ejecutar las acciones de la API de la aplicación.

[Leer más sobre componente Api](http://sowerphp.org/doc/componentes/Api)

### Log

Componente que permite registrar eventos que suceden en la aplicación.

[Leer más sobre componente Log](http://sowerphp.org/doc/componentes/Log)

### Notify

Componente que permite enviar notificaciones a usuarios.

[Leer más sobre componente Notify](http://sowerphp.org/doc/componentes/Notify)

Módulos
-------

### Dev

Provee funcionalidades para ayudar al desarrollo de la aplicación, por ejemplo
una ejecución de cualquier consulta en la base de datos vía web o poder
descargar el diccionario de datos de la base de datos.

Este módulo es cargado automáticamente cuando el sistema está en [ambiente de
desarrollo](http://sowerphp.org/doc/general/ambiente_desarrollo).

### Sistema.Enlaces

Módulo para mantenedor y visualizador de enlaces disponibles en http://example.com/enlaces

### Sistema.General

Provee mantenedores para AFDs y cambio de monedas.

### Sistema.General.DivisionGeopolitica

Provee mantenedores para Regiones, Provincias y Comunas de Chile.

### Sistema.Logs

Módulo para mantenedor y visualizador de logs registrados en la base de datos.

### Sistema.Notificaciones

Módulo para mantenedor y visualizador de notificaciones almacenadas en la base
de datos disponibles en http://example.com/sistema/notificaciones/notificaciones

### Sistema.Usuarios

Provee las funcionalidades básicas para administrar: usuarios, grupos y permisos
sobre recursos. Se debe cargar el modelo de datos disponible en el módulo en:

	Model/Sql/Base_de_Datos/usuarios.sql
