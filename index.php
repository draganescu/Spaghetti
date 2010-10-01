<?php
include 'dynamic/controller.php';
$portofolio = the::app();
$portofolio->theme = 'me';
$portofolio->default = 'index';

$portofolio->index_file = ""; // using htaccess

$portofolio->template("/projects/*","migrations");
$portofolio->template("$/posts/*","blog");

/* admin */
$portofolio->template("/login","login");
$portofolio->template("/admin/dashboard","dashboard");
$portofolio->template("/admin/posts/new","addposts");
$portofolio->template("/admin/posts/list","listposts");
$portofolio->template("/admin/ideas/new","addideas");
$portofolio->template("/admin/ideas/list","listideas");
$portofolio->template("/admin/resume/new","addresumt");
$portofolio->template("/admin/resume/list","listresume");
$portofolio->template("/admin/work/new","addwork");
$portofolio->template("/admin/work/list","listwork");

$portofolio->observe('before_output','cms','login_check');


$portofolio->server('local.host','development');
$portofolio->server('mindware.ro','production');

$portofolio->connection('local.host', 'localhost', 'portofolio', 'root', '');

$portofolio->run();