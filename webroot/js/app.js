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
 * Implementación sincrónica de $.getJSON, esto para poder recuperar
 * el objeto json fuera de la funcion que se ejecuta en success
 * @param url Url desde donde se obtendrá el JSON
 * @param data Datos que se deben enviar por la URL (como GET)
 * @return json Datos retornados por la página
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2011-05-02
 */
function getJSON (url, data) {
	var json;
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'json',
		success: function (result) {json = result;},
		data: data,
		async: false
	});
	return json;
} 

/**
 * Función para autocompletar un input de un formulario
 * @param id Identificador del input
 * @param url URL donde están los posibles valores para el input
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-03-16
 */
function autocomplete (id, url) {
	var field = id+'Field';
	$(function(){ $('#'+field).keyup(function(){
		if($('#'+field).val()!='' && $('#'+field).val().length > 2) {
			$.getJSON(url, { filter: $('#'+field).val() }, function(data) {
				var items = [];
				$.each(data, function(key, val) {
					items.push(val.glosa);
				});
				$('#'+field).autocomplete({
					source: items
					, autoFocus: true
					, minLength: 3
				});
			});
		}
	});});
}

/**
 * Función que formatea un número
 * @param n Número a formatear
 * @author http://joaquinnunez.cl/blog/2010/09/20/formatear-numeros-con-punto-como-separador-de-miles-con-javascript/
 */
function num (n) {
	var number = new String(n);
	var result='';
	var isNegative = false;
	if(number.indexOf('-')>-1) {
		number = number.substr(1);
		isNegative=true;
	}
	while( number.length > 3 ) {
		result = '.' + number.substr (number.length - 3) + result;
		number = number.substring(0, number.length - 3);
	}
	result = number + result;
	if(isNegative) result = '-'+result;
	return result;
}
