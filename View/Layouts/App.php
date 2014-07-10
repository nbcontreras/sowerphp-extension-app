<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- Design by http://www.oswd.org/design/preview/id/3459 modified by http://delaf.cl -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="generator" content="SowerPHP"/>
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
                    <li><a href="<?=$_base?>/usuarios/ingresar" title="Iniciar sesión en la Intranet">Iniciar sesión</a></li>
                </ul>
<?php
} else {
    foreach ($_nav_app as $link=>&$info) {
        if ($_Auth->check($link)) {
            if(!is_array($info)) $info = array('name'=>$info);
                echo "\t\t\t\t\t",'<li><a href="',$_base,$link,'">',$info['name'],'</a></li>',"\n";
            }
    }
?>
                </ul>
                <div id="navapp-icons">
<?php
if (\sowerphp\core\Module::loaded('Sistema.Enlaces')) {
?>
                    <a href="<?=$_base?>/enlaces" title="Listado de enlaces">
                        <img src="<?=$_base?>/sistema/enlaces/img/icons/16x16/enlaces.png" alt="" />
                    </a>
<?php } ?>
                    <a href="<?=$_base?>/usuarios/perfil" title="Perfil del usuario <?php echo \sowerphp\core\Model_Datasource_Session::read('auth.usuario'); ?>">
                        <img src="<?=$_base?>/img/icons/16x16/navapp/profile.png" alt="" />
                    </a>
                    <a href="<?=$_base?>/usuarios/salir" title="Cerrar sesión del usuario <?php echo \sowerphp\core\Model_Datasource_Session::read('auth.usuario'); ?>">
                        <img src="<?=$_base?>/img/icons/16x16/navapp/logout.png" alt="" />
                    </a>
                </div>
<?php }?>
                <div id="sidebar-bottom">&nbsp;</div>
            </div>
            <div id="content">
<?php
$message = \sowerphp\core\Model_Datasource_Session::message();
if($message) echo '<div class="session_message">',$message,'</div>';
?>
                <a href="javascript:print()" title="Imprimir página" class="fright" id="printIcon">
                    <img src="<?=$_base?>/img/icons/16x16/actions/print.png" alt="Imprimir página" />
                </a>
<?php echo $_content; ?>
            </div>
            <div id="footer">
                <div>
                    <div class="fleft" id="footer_left">
                        <?php echo $_footer['left']; ?>
                    </div>
                    <div class="fright" id="footer_right">
                        <?php echo $_footer['right']; ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
