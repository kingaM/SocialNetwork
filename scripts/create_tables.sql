CREATE DATABASE IF NOT EXISTS SocialNetwork;

USE SocialNetwork;

CREATE TABLE IF NOT EXISTS `users` (
	`ID` INT NOT NULL AUTO_INCREMENT,
	`first_name` VARCHAR(50) NOT NULL, 
	`middle_name` VARCHAR(50),
	`last_name` VARCHAR(50) NOT NULL, 
	`email` VARCHAR(100) NOT NULL, 
	`login` VARCHAR(50) NOT NULL, 
	`password` VARCHAR(50) NOT NULL,
	UNIQUE (`login`),
	PRIMARY KEY(`ID`)
);
