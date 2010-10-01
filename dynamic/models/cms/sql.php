<?php 
$querries["select_recent_post_titles"] = "select id, title from posts order by date desc limit %s";
$querries["select_recent_resume"] = "select * from resume order by date desc limit 5";