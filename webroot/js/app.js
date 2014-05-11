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
