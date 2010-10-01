<?php
$upgrade[1][] = "CREATE TABLE `users` (
					`id` INT(3) UNSIGNED AUTO_INCREMENT,
					`username` VARCHAR(32),
					`password` VARCHAR(32),
					PRIMARY KEY (`id`))
					ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;";
$downgrade[1][] = "drop table users";
$upgrade[2][] = "insert into users (`username`, `password`) values ('admin','".md5('qwerty@')."')";
$downgrade[2][] = "delete from users where `id` = 1";