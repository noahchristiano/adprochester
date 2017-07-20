<!doctype html>
<html>
<head>
    <title>Alpha Delta Phi</title>
    <meta charset="utf-8">
    <link rel="icon" href="<?php echo Config::get('URL'); ?>favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo Config::get('URL'); ?>css/style.css" />
</head>
<body>
    <div class="navigation">
        <ul class="tab left">
            <li <?php if (View::checkForActiveController($filename, "index")) { echo ' class="active" '; } ?> >
                <a href="<?php echo Config::get('URL'); ?>index/index">Alpha Delta Phi: Rochester Chapter</a>
            </li>
        </ul>

        <ul class="tab right">
        <?php if (Session::userIsLoggedIn()) { ?>
            <li>
                <a href="<?php echo Config::get('URL'); ?>login/logout">Logout</a>
            </li>
            <li <?php if (View::checkForActiveController($filename, "user")) { echo ' class="active" '; } ?> >
                <a href="<?php echo Config::get('URL'); ?>user/index">My Account</a>
            </li>
        <?php } else { ?>
            <li <?php if (View::checkForActiveControllerAndAction($filename, "login/index")) { echo ' class="active" '; } ?> >
                <a href="<?php echo Config::get('URL'); ?>login/index">Login</a>
            </li>
        <?php } ?>
        </ul>
    </div>