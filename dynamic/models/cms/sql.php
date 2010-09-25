<?php
$querries["get_posts_with_usernames"] = "select p.title, u.username from users u join posts p where u.id = p.user_id";
$querries["get_some_posts"] = "select * from posts limit %s offset %s";
$querries["save_a_post"] = "insert into posts (title, body) values ('%s','%s')";
 