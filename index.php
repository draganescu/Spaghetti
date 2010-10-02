<?php
include 'dynamic/controller.php';

$app = the::app();
$app->theme = 'welcome';
$app->default = 'index';

$app->connection('localhost', 'localhost', 'spaghetti', 'root', '');

$app->run();