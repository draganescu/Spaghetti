<?php
//exit; //we have a bug that kills aw2 //sublime text
include 'controller/my.php';
$website = my::app();
$website->theme = 'basic';
$website->default = '404';
$website->debug_events = true;


$website->run();
