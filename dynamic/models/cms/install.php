<?php
$upgrade[1][] = "CREATE TABLE `resume` (
					`id` INT(3) UNSIGNED AUTO_INCREMENT,
					`time_span` VARCHAR(32),
					`position_company` VARCHAR(255),
					`description` TEXT,
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table resume";
$upgrade[1][] = "CREATE TABLE `work` (
					`id` INT(3) UNSIGNED AUTO_INCREMENT,
					`title` VARCHAR(32),
					`url` VARCHAR(255),
					`description` TEXT,
					`types` VARCHAR(20),
					`cover` VARCHAR(255),
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table work";
$upgrade[1][] = "CREATE TABLE `blog` (
					`id` INT(3) UNSIGNED AUTO_INCREMENT,
					`title` VARCHAR(32),
					`body` TEXT,
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table blog";
$upgrade[1][] = "CREATE TABLE `projects` (
					`id` INT(3) UNSIGNED AUTO_INCREMENT,
					`title` VARCHAR(32),
					`body` TEXT,
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table projects";
$upgrade[1][] = "CREATE TABLE `contacts` (
					`id` INT(3) UNSIGNED AUTO_INCREMENT,
					`email` VARCHAR(32),
					`message` TEXT,
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table contacts";

// fix the truncated work title
$upgrade[2][] = "ALTER TABLE `work` CHANGE `title` `title` varchar(255) NULL DEFAULT NULL;";
$downgrade[2][] = "ALTER TABLE `work` CHANGE `title` `title` varchar(32) NULL DEFAULT NULL;";