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

// Tema de la página (diseño)
sowerphp\core\Configure::write('page.layout', 'App');

// Textos de la página
sowerphp\core\Configure::write('page.footer', array(
    'left' => '',
    'right' => 'Página web generada utilizando el framework <a href="http://sowerphp.org">SowerPHP</a>'
));

// Menú principal del sitio web
sowerphp\core\Configure::write('nav.website', array(
    '/inicio'=>'Inicio',
    '/contacto'=>'Contacto'
));

// Menú principal de la aplicación
sowerphp\core\Configure::write('nav.app', array(
    '/sistema'=>'Sistema'
));

// Módulos que usará esta aplicación
sowerphp\core\Module::uses(array(
    'Exportar',
    'Sistema',
    'Sistema.Usuarios' => array('autoLoad' => true),
));

// Registros por página
sowerphp\core\Configure::write('app.registers_per_page', 20);
