<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?=$_header_title?></title>
        <link rel="shortcut icon" href="<?=$_base?>/img/favicon.png" />
        <link rel="stylesheet" href="<?=$_base?>/layouts/Bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="<?=$_base?>/layouts/Bootstrap/css/bootstrap-theme.min.css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
            <script src="<?=$_base?>/js/html5shiv.js"></script>
            <script src="<?=$_base?>/js/respond.js"></script>
        <![endif]-->
        <script src="<?=$_base?>/js/jquery.js"></script>
        <script src="<?=$_base?>/layouts/Bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            var _url = "<?=$_url?>",
                _base = "<?=$_base?>",
                _request = "<?=$_request?>"
            ;
        </script>
        <link rel="stylesheet" href="<?=$_base?>/css/font-awesome.min.css" />
<?=$_header_extra?>
    </head>
    <body>
        <div class="container">
<?php echo $_content; ?>
        </div>
    </body>
</html>
