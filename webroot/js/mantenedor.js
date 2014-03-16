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

/**
 * Envía un formulario para filtrar por diferentes parámetros
 * @param formulario Formulario genérico que se utilizará para enviar elementos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2011-03-29
 */
function buscar (formulario) {
	var total = formulario.elements.length;
	var search = new Array();
	for (i=0; i<total; ++i) {
		campo = formulario.elements[i].name;
		valor = formulario.elements[i].value;
		if(!Form.empty(valor)) search.push(campo+':'+valor);
	}
	search = search.join(",");
	if(Form.empty(search))
		document.location.href = window.location.pathname;
	else
		document.location.href =
			window.location.pathname + '?search=' + search;
	// en teoria nunca se llega aquí, pero está para que no reclame porque
	// buscar() no retorna nada
	return false;
}

/**
 * Función que consulta si efectivamente se desea eliminar el registro
 * @param registro Nombre del registro
 * @param tupla Identificador (generalmente PK) del registro
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-03-16
 */
function eliminar (registro, tupla) {
	return confirm('¿Eliminar registro '+registro+'('+tupla+')?')
}
