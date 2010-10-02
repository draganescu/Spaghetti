<?php
include 'dynamic/controller.php';
$portofolio = the::app();
$portofolio->theme = 'me';
$portofolio->default = 'index';

$portofolio->index_file = ""; // using htaccess

$portofolio->template("/projects/\d","page");
$portofolio->template("/posts/\d","blog");
$portofolio->template("/portofolio*","work");

/* admin */
$portofolio->template("/login","login");
$portofolio->template("/admin/dashboard","dashboard");
$portofolio->template("/admin/posts/new","addposts");
$portofolio->template("/admin/(posts|blog)/list","listposts");
$portofolio->template("/admin/ideas/new","addideas");
$portofolio->template("/admin/(ideas|projects)/list","listideas");
$portofolio->template("/admin/resume/new","addresume");
$portofolio->template("/admin/resume/list","listresume");
$portofolio->template("/admin/work/new","addwork");
$portofolio->template("/admin/work/list","listwork");

$portofolio->observe('before_output','users','login_check');


$portofolio->server('local.host','development');
$portofolio->server('mindware.ro','production');

$portofolio->connection('local.host', 'localhost', 'portofolio', 'root', '');

$portofolio->run();