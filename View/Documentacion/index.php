<div class="page-header"><h1>Documentación</h1></div>

<?php if ($doxygen) : ?>
<a href="<?=$_base?>/doc/html/" target="_blank" class="pull-right" title="Ver documentación en Doxygen">
    <img src="<?=$_base?>/img/documentacion/doxygen.png" alt="doxygen" />
</a>
<?php endif; ?>

<?php

enlaces($archivos);

function enlaces($archivos, $ruta = '')
{
    $omitir = [];
    $mostrado = [];
    echo '<ul>',"\n";
    foreach ($archivos as $dir => $docs) {
        if (is_string($dir)) {
            $aux = $ruta.'/'.urlencode($dir);
            if (in_array($dir, $archivos)) {
                $omitir[] = $dir;
                $dir = '<a href="'._BASE.'/documentacion'.$ruta.'/'.urlencode($dir).'">'.$dir.'</a>';
            }
            echo '<li>',$dir,"\n";
            enlaces($docs, $aux);
            echo '</li>',"\n";
        } else if (!in_array($docs, $omitir) and !in_array($docs, $mostrado)) {
            echo '<li><a href="',_BASE,'/documentacion',$ruta,'/',urlencode($docs),'">',$docs,'</a></li>',"\n";
            $mostrado[] = $docs;
        }
    }
    echo '</ul>',"\n";
}
