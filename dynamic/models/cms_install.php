<?php
$upgrade[1][] = "CREATE table posts (
					`id` INT(10) UNSIGNED ,
					`title` VARCHAR(255),
					`body` TEXT,
					`user_id` INT(10),
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table posts";

$upgrade[1][] = "CREATE table users (
					`id` INT(10) UNSIGNED ,
					`username` VARCHAR(255),
					`password` VARCHAR(32),
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table users";

$upgrade[1][] = "ALTER TABLE `users` CHANGE `id` `id` int(10) UNSIGNED NOT NULL  auto_increment";
$upgrade[1][] = "ALTER TABLE `posts` CHANGE `id` `id` int(10) UNSIGNED NOT NULL  auto_increment";

$upgrade[1][] = "CREATE table categories (
					`id` INT(4) UNSIGNED ,
					`title` VARCHAR(255),
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table categories";