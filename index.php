<?php
include 'dynamic/controller.php';
$blog = the::app();
$blog->theme = 'interchange';
$blog->default = 'index';

$blog->template('/\d', 'post');

// cms admin pages, this could get lenghty for a complex system?
// oh noes :) just create a new admin.php file.

$blog->template('/admin(/?)$', 'admin', 'cmsadmintheme');
$blog->template('/admin/new', 'new', 'cmsadmintheme');

// more

$blog->cache_life = 0;
$blog->observe('before_run','cms','cache');
$blog->observe('before_output','cms','cache');

$blog->server('localhost','development');
$blog->server('local.host','production');

$blog->connection('local.host', 'localhost', 'testdb', 'root', '');
$blog->connection('localhost', 'localhost', 'testdb', 'root', '');

// advanced

$blog->replace('<title>Web App Theme</title>', '<title>Blog admin</title>', 'cms/admin.*');
$blog->replace('<h1><a href="index.html">Web App Theme</a></h1>', '<h1><a href="index.html?/cms">Lovely blog</a></h1>', 'cms/admin.*');



$blog->run();
?>