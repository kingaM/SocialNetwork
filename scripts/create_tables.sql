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
	`hash` VARCHAR(50) NOT NULL, 
	`activated` BOOLEAN NOT NULL DEFAULT FALSE,
	UNIQUE (`login`),
	UNIQUE (`email`),
	UNIQUE (`hash`),
	PRIMARY KEY(`ID`)
);

# TODO: Enable multiple locations and languages as a seperate table. Create a table with all 
# languages and locations for reference and to check the validity of the data. 
CREATE TABLE IF NOT EXISTS `profile` (
	`userId` INT NOT NULL,
	`gender` VARCHAR(10),
	`dob` INT,
	`about` VARCHAR(10000),
	`locations` VARCHAR(1000),
	`languages` VARCHAR(1000),
	PRIMARY KEY (`userId`),
	FOREIGN KEY (`userId`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
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

CREATE TABLE IF NOT EXISTS `circles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `owner` int(11) NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`owner`, `name`),
    FOREIGN KEY (`owner`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `from` INT NOT NULL,
    `to_user` INT,
    `to_circle` INT,
    `type` char(1) NOT NULL,
    `content` VARCHAR(10000) NOT NULL DEFAULT 0,
    `timestamp` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`from`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`to_user`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`to_circle`) REFERENCES `circles` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `circle_memberships` (
    `user` int(11) NOT NULL,
    `circle` int(11) NOT NULL,
    PRIMARY KEY (`user`,`circle`),
    FOREIGN KEY (`user`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`circle`) REFERENCES `circles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
);
