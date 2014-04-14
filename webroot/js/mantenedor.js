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

/*jslint browser: true, devel: true, nomen: true */

/**
 * Envía un formulario para filtrar por diferentes parámetros
 * @param formulario Formulario genérico que se utilizará para enviar elementos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-04-13
 */
function buscar(formulario) {
    'use strict';
    var i, total = formulario.elements.length, search = [], campo, valor;
    for (i = 0; i < total; i += 1) {
        campo = formulario.elements[i].name;
        valor = formulario.elements[i].value;
        if (!__.empty(valor)) {
            search.push(campo + ':' + valor);
        }
    }
    search = search.join(",");
    if (__.empty(search)) {
        document.location.href = window.location.pathname;
    } else {
        document.location.href = window.location.pathname + '?search=' + search;
    }
    // en teoria nunca se llega aquí, pero está para que no reclame porque
    // buscar() no retorna nada
    return false;
}

/**
 * Función que consulta si efectivamente se desea eliminar el registro
 * @param registro Nombre del registro
 * @param tupla Identificador (generalmente PK) del registro
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-04-13
 */
function eliminar(registro, tupla) {
    'use strict';
    return confirm('¿Eliminar registro ' + registro + '(' + tupla + ')?');
}
