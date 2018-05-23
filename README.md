SowerPHP: extensión app
=======================

Extensión con funcionalidades básicas para una aplicación web. Implementa
código general que ayudará a crear el proyecto.

La extensión requiere que previamente se haya cargado la extensión general, ya
que es utilizada por esta. Por lo cual verificar que el archivo
*webroot/index.php* al menos contenga en la definición de extensiones:

	$_EXTENSIONS = ['sowerphp/app', 'sowerphp/general'];

Puedes ver la documentación de la extensión en el
[Wiki de SowerPHP](http://wiki.sowerphp.org/doku.php/extensions/app)
