<?php
include 'dynamic/controller.php';
$blog = the::app();
$blog->theme = 'interchange';
$blog->default = 'index';

$blog->observe('before_run','cms','cache');
$blog->cache_life = 0;
$blog->observe('before_output','cms','cache');

$blog->server('localhost','development');
$blog->server('local.host','production');

$blog->connection('local.host', 'localhost', 'testdb', 'root', '');
$blog->connection('localhost', 'localhost', 'testdb', 'root', '');

// cms admin pages, this could get lenghty for a complex system? oh noes :) just create a new admin.php file.
$blog->template('cms/admin$', 'admin', 'cmsadmintheme');
$blog->template('cms/admin/new', 'new', 'cmsadmintheme');


$blog->template('cms$', 'index');
$blog->template('cms/posts/\d', 'post');


$blog->run();
?>