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

CREATE TABLE IF NOT EXISTS `friendships` (
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `startTimestamp` int(11) NOT NULL,
  PRIMARY KEY (`user1`,`user2`),
  FOREIGN KEY (`user1`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`user2`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
);
