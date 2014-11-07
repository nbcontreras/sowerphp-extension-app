<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title><?=$_header_title?></title>
        <link rel="shortcut icon" href="<?=$_base?>/img/favicon.png" />
        <link type="text/css" href="<?=$_base?>/layouts/popup/css/screen.css" media="screen" rel="stylesheet" />
        <link type="text/css" href="<?=$_base?>/layouts/popup/css/print.css" media="print" rel="stylesheet" />
        <script type="text/javascript" src="<?=$_base?>/js/jquery.js"></script>
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
<?php echo $_content; ?>
        </div>
    </body>
</html>
