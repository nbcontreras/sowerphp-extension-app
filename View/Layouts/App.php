<!DOCTYPE html>
<html lang="es">
<!-- Design by http://www.oswd.org/design/preview/id/3459 modified by http://delaf.cl -->
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title><?=$_header_title?></title>
        <link rel="shortcut icon" href="<?=$_base?>/img/favicon.png" />
        <link type="text/css" href="<?=$_base?>/layouts/<?=$_layout?>/css/screen.css" media="screen" rel="stylesheet" />
        <link type="text/css" href="<?=$_base?>/layouts/<?=$_layout?>/css/print.css" media="print" rel="stylesheet" />
        <script type="text/javascript" src="<?=$_base?>/js/jquery.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/__.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/form.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/app.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/jquery/browser.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/jquery-ui/jquery-ui.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/jquery-ui/datepicker.js"></script>
        <link rel="stylesheet" type="text/css" href="<?=$_base?>/js/jquery-ui/css/smoothness/jquery-ui.css" media="screen"/>
        <script type="text/javascript">
            var _url = "<?=$_url?>",
                _base = "<?=$_base?>",
                _request = "<?=$_request?>"
            ;
        </script>
<?=$_header_extra?>
    </head>
    <body>
        <div id="wrapper">
            <div id="header">
                <div class="img">
                    <a href="<?=$_base?>/inicio">
                        <img src="<?=$_base?>/img/logo.png" alt="" />
                    </a>
                </div>
                <div class="txt">
                    <a href="<?=$_base?>/inicio">
                        <?php echo $_body_title; ?>
                    </a>
                </div>
            </div>
            <div id="navsite">
                <ul>
<?php
foreach ($_nav_website as $link=>&$name) {
    if ($link[0]=='/') $link = $_base.$link;
    echo '                    <li><a href="',$link,'">',$name,'</a></li>',"\n";
}
?>
                </ul>
            </div>
            <div id="sidebar">
                <div id="sidebar-title">
                    <div class="txt">Aplicación</div>
                </div>
                <ul id="navapp">
<?php if (!$_Auth->logged()) { ?>
                    <li><a href="<?=$_base?>/usuarios/ingresar" title="Iniciar sesión en la aplicación">Iniciar sesión</a></li>
                </ul>
<?php
} else {
    foreach ($_nav_app as $link=>&$info) {
        if ($_Auth->check($link)) {
            if(!is_array($info)) $info = ['name'=>$info];
            echo "\t\t\t\t\t",'<li><a href="',$_base,$link,'">',$info['name'],'</a></li>',"\n";
        }
    }
?>
                </ul>
                <div id="navapp-icons">
<?php if (\sowerphp\core\Module::loaded('Sistema.Enlaces')) : ?>
                    <a href="<?=$_base?>/enlaces" title="Listado de enlaces">
                        <img src="<?=$_base?>/sistema/enlaces/img/icons/16x16/enlaces.png" alt="" />
                    </a>
<?php endif; ?>
<?php if (\sowerphp\core\Module::loaded('Rrhh')) : ?>
                    <a href="<?=$_base?>/rrhh/empleados/cumpleanios" title="Próximos cumpleaños">
                        <img src="<?=$_base?>/rrhh/img/icons/16x16/cumpleanio.png" alt="" />
                    </a>
<?php endif; ?>
                    <a href="<?=$_base?>/documentacion" title="Documentación de la aplicación">
                        <img src="<?=$_base?>/img/icons/16x16/actions/doc.png" alt="" />
                    </a>
                    <a href="<?=$_base?>/usuarios/perfil" title="Perfil del usuario <?=$_Auth->User->usuario?>">
                        <img src="<?=$_base?>/img/icons/16x16/navapp/profile.png" alt="" />
                    </a>
                    <a href="<?=$_base?>/usuarios/salir" title="Cerrar sesión del usuario <?=$_Auth->User->usuario?>">
                        <img src="<?=$_base?>/img/icons/16x16/navapp/logout.png" alt="" />
                    </a>
                </div>
<?php }?>
                <div id="sidebar-bottom">&nbsp;</div>
            </div>
            <div id="content">
<?php
$message = \sowerphp\core\Model_Datasource_Session::message();
if($message) echo '<div class="session_message bg-',$message['type'],'">',$message['text'],'</div>';
?>
                <a href="javascript:print()" title="Imprimir página" class="fright" id="printIcon">
                    <img src="<?=$_base?>/img/icons/16x16/actions/print.png" alt="Imprimir página" />
                </a>
<?php echo $_content; ?>
            </div>
            <div id="footer">
                <div>
                    <div class="fleft" id="footer_left">
                        <?php echo $_footer['left'],"\n"; ?>
                    </div>
                    <div class="fright" id="footer_right">
<?php
                        echo $_footer['right'],"\n";
                        if ($_Auth->logged()) {
                            echo ' [stats] time: ',round(microtime(true)-TIME_START, 2),' [s] - ';
                            echo 'memory: ',round(memory_get_usage()/1024/1024,2),' [MiB] - ';
                            echo 'querys: ',\sowerphp\core\Model_Datasource_Database_Manager::$querysCount,' - ';
                            echo 'cache: ',\sowerphp\core\Cache::$setCount,'/',\sowerphp\core\Cache::$getCount,"\n";
                        }
?>
                    </div>
                </div>
            </div>
<?php
$footer_fix = \sowerphp\core\Configure::read('page.footer.fix');
if ($footer_fix and $_Auth->logged()) {
?>
            <div id="footer_fix_container">
                <div id="footer_fix_message">
                    <?=$footer_fix."\n"?>
                </div>
            </div>
<?php } ?>
            <script type="text/javascript">header_fix_adjust_wrapper()</script>
        </div>
    </body>
</html>
