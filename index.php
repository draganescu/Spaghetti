<?php
include 'dynamic/controller.php';
$portofolio = the::app();
$portofolio->theme = 'me';
$portofolio->default = 'index';

$portofolio->index_file = ""; // using htaccess
$portofolio->debug_events = true; // using htaccess

/* pages and blog */
$portofolio->template("/projects/\d","page");
$portofolio->template("/posts/\d","blog");
$portofolio->template("/portofolio*","work");
$portofolio->template("/archives*","archives");

/* admin */
$portofolio->template("/login","admin/login");
$portofolio->template("/admin/dashboard","admin/dashboard");
$portofolio->template("/admin/posts/new","admin/addposts");
$portofolio->template("/admin/(posts|blog)/list","admin/listposts");
$portofolio->template("/admin/ideas/new","admin/addideas");
$portofolio->template("/admin/(ideas|projects)/list","admin/listideas");
$portofolio->template("/admin/resume/new","admin/addresume");
$portofolio->template("/admin/resume/list","admin/listresume");
$portofolio->template("/admin/work/new","admin/addwork");
$portofolio->template("/admin/work/list","admin/listwork");

$portofolio->observe('before_output','users','login_check');


$portofolio->server('local.host','development');
$portofolio->server('mindware.ro','production');

$portofolio->connection('local.host', 'localhost', 'portofolio', 'root', '');

$portofolio->run();