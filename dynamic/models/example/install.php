<?php
$upgrade[1][] = "CREATE table users (
					`id` INT(10) UNSIGNED ,
					`username` VARCHAR(255),
					`password` VARCHAR(32),
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table users";