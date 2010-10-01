<?php 
$querries["select_recent_post_titles"] = "select id, title from posts order by id desc limit %s";
$querries["select_recent_resume"] = "select * from resume order by id desc limit 5";
$querries["select_recent_work"] = "select * from work order by id desc limit 3";
$querries["select_recent_ideas"] = "select * from projects order by id desc";