<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace sowerphp\app;

/**
 * Clase para generar los mantenedores
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-04-26
 */
class View_Helper_Maintainer extends \sowerphp\general\View_Helper_Table
{

    private $options; ///< Opciones del mantenedor
    private $form; ///< Formulario (objeto de FormHelper) que se está usando en el mantenedor

    /**
     * Constructor de la clase
     * @param options Arreglo con las opciones para el mantenedor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2014-04-26
     */
    public function __construct ($options = array(), $filter = true)
    {
        if ($filter) {
            $this->options = array_merge(array(
                'link'=>'', 'linkEnd'=>'', 'listarFilterUrl'=>'', 'thead'=>2, 'remove'=>array(2),
            ), $options);
        } else {
            $this->options = array_merge(array(
                'link'=>'', 'linkEnd'=>'', 'listarFilterUrl'=>'', 'thead'=>1, 'remove'=>array(),
            ), $options);
        }
        $this->form = new \sowerphp\general\View_Helper_Form('normal');
        $this->setExport(true);
        $this->setExportRemove(array('rows'=>$this->options['remove'], 'cols'=>array(-1)));
    }

    /**
     * Método que generará el mantenedor y listará los registros disponibles
     * @param data Registros que se deben renderizar
     * @param pages Cantidad total de páginas que tienen los registros
     * @param page Página que se está revisando o 0 para no usar el paginador
     * @param create =true se agrega icono para crear registro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-17
     */
    public function listar ($data, $pages = 1, $page = 1, $create = true)
    {
        $buffer = $this->form->begin(array('onsubmit'=>'buscar(this)'))."\n";
        if ($create) {
            $buffer .= '<div style="float:left"><a href="'.$this->options['link'].'/crear'.$this->options['listarFilterUrl'].'" title="Crear nuevo registro"><span class="fa fa-plus btn btn-default" aria-hidden="true"></span></a></div>'."\n";
        }
        if ($page)
            $buffer .= $this->paginator ($pages, $page)."\n";
        $buffer .= parent::generate ($data, $this->options['thead']);
        $buffer .= $this->form->end(false)."\n";
        if ($pages) {
            $buffer .= '<div style="text-align:right;margin-bottom:1em;font-size:0.8em">'."\n";
            if ($page)
                $buffer .= '<a href="'.$this->options['link'].'/listar/0'.$this->options['linkEnd'].'">Mostrar todos los registros (sin paginar)</a>'."\n";
            else
                $buffer .= '<a href="'.$this->options['link'].'/listar/1'.$this->options['linkEnd'].'">Paginar registros</a>'."\n";
            $buffer .= '</div>'."\n";
        }
        return $buffer;
    }

    /**
     * Método que genera el paginador para el mantenedor
     * @param pages Cantidad total de páginas que tienen los registros
     * @param page Página que se está revisando o 0 para no usar el paginador
     * @param groupOfPages De a cuantas páginas se mostrará en el paginador
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-01-17
     */
    private function paginator ($pages, $page, $groupOfPages = 10)
    {
        if (!$pages)
            return;
        // cálculoss necesarios para crear enlaces
        $group = ceil($page/$groupOfPages);
        $from = ($group-1)*$groupOfPages + 1;
        $to = min($from+$groupOfPages-1, $pages);
        // crear enlaces para paginador
        $buffer = '<div style="float:left;margin-left:20%;width:50%;text-align:center"><nav><ul class="pagination">'."\n";
        if ($page==1)
            $buffer .= '<li class="disabled"><span class="fa fa-fast-backward" aria-hidden="true"></span></li>';
        else
            $buffer .= '<li><a href="'.$this->options['link'].'/listar/1'.$this->options['linkEnd'].'" title="Ir a la primera página"><span class="fa fa-fast-backward" aria-hidden="true"></span></a></li>';
        if ($group==1)
            $buffer .= '<li class="disabled"><span class="fa fa-backward" aria-hidden="true"></span></li>';
        else
            $buffer .= '<li><a href="'.$this->options['link'].'/listar/'.($from-1).$this->options['linkEnd'].'" title="Ir al grupo de páginas anterior (página '.($from-1).')"><span class="fa fa-backward" aria-hidden="true"></span></a></li>';
        for ($i=$from; $i<=$to; $i++) {
            if ($page==$i)
                $buffer .= '<li class="active"><a href="#" onclick="return false">'.$i.'</a></li>';
            else
                $buffer .= '<li><a href="'.$this->options['link'].'/listar/'.$i.$this->options['linkEnd'].'" title="Ir a la página '.$i.'">'.$i.'</a></li>';
        }
        if ($group==ceil($pages/$groupOfPages))
            $buffer .= '<li class="disabled"><span class="fa fa-forward" aria-hidden="true"></span></li>';
        else
            $buffer .= '<li><a href="'.$this->options['link'].'/listar/'.($to+1).$this->options['linkEnd'].'" title="Ir al grupo de páginas siguiente (página '.($to+1).')"><span class="fa fa-forward" aria-hidden="true"></span></a></li>';
        if ($page==$pages)
            $buffer .= '<li class="disabled"><span class="fa fa-fast-forward" aria-hidden="true"></span></li>';
        else
            $buffer .= '<li><a href="'.$this->options['link'].'/listar/'.$pages.$this->options['linkEnd'].'" title="Ir a la última página"><span class="fa fa-fast-forward" aria-hidden="true"></span></a></li>';
        $buffer .= '</ul></nav></div>'."\n";
        // retornar enlaces
        return $buffer;
    }

}
