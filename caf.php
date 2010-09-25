<?php
include 'dynamic/controller.php';
$caf = the::app();
$caf->index_file = 'caf.php';
$caf->theme = 'caf';
$caf->default = 'index';

$caf->server('local.host','development');

$caf->connection('local.host', 'localhost', 'caf', 'root', '');

// advanced

$caf->replace('<title>Web App Theme</title>', '<title>caf admin</title>', 'cms/admin.*');
$caf->replace('<h1><a href="index.html">Web App Theme</a></h1>', '<h1><a href="index.html?/cms">Lovely caf</a></h1>', 'cms/admin.*');



$caf->run();