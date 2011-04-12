<?php
include 'dynamic/controller.php';

$app = the::app();
$app->theme = 'backend';
$app->default = '404';

$app->base_file = "backend";

$app->template("/$", "index");
$app->template("library", "library");
$app->template("crud", "crud");

$app->observe("before_run", "backend", "checklogin");

$app->connection('local.host', 'localhost', 'spaghetti', 'root', '');

$app->run();