<?php 
// homepage
$querries["select_recent_post_titles"] = "select id, title from blog order by id desc limit %s";
$querries["select_recent_resume"] = "select * from resume order by id desc limit 5";
$querries["select_recent_work"] = "select * from work order by id desc limit %s";
$querries["select_ideas"] = "select * from projects order by id asc";
$querries["select_work_by_type"] = "select * from work where types like '%%%s%%'";

$querries["insert_a_resume"] = "insert into resume (`time_span`, `position_company`, `description`) values ('%s', '%s', '%s')";
$querries["insert_a_contact"] = "insert into contacts (`email`, `message`) values ('%s', '%s')";

// generic querries
$querries["load_by_id"] = "select * from `%s` where `id` = '%s'";
$querries["remove_by_id"] = "delete from `%s` where `id` = '%s'";
$querries["get_resume"] = "select * from resume order by id desc";
$querries["get_ideas"] = "select * from projects order by id desc";
$querries["get_posts"] = "select * from blog order by id desc";
$querries["get_work"] = "select * from work order by id desc";
$querries["get_contacts"] = "select * from contacts order by id desc";