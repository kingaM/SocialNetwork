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

CREATE TABLE IF NOT EXISTS `wall_posts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `to` int(11) NOT NULL,
    `from` int(11) NOT NULL,
    `content` VARCHAR(10000) NOT NULL DEFAULT 0,
    `timestamp` int(11) NOT NULL,
    `type` VARCHAR(11) NOT NULL,
    `lastTouched` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`to`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`from`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `from` int(11) NOT NULL,
    `wall_post` int(11) NOT NULL,
    `content` VARCHAR(10000) NOT NULL DEFAULT 0,
    `timestamp` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`from`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`wall_post`) REFERENCES `wall_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `blogs` (
    `blogId` INT NOT NULL AUTO_INCREMENT,
    `about` VARCHAR(10000) NOT NULL,
    `user` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL, 
    `url` VARCHAR(100) NOT NULL, 
    UNIQUE(`user`, `url`),
    PRIMARY KEY (`blogId`),
    FOREIGN KEY (`user`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `posts` (
    `postId` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL,
    `timestamp` VARCHAR(100) NOT NULL,
    `blogId` INT NOT NULL, 
    PRIMARY KEY (`postId`),
    FOREIGN KEY (`blogId`) REFERENCES `blogs` (`blogId`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Seperate table in case a blog post is longer than the value of text, unlikely.
CREATE TABLE IF NOT EXISTS `posts_details` (
    `postDetailId` INT NOT NULL AUTO_INCREMENT,
    `postId` INT NOT NULL, 
    `content` TEXT NOT NULL, 
    PRIMARY KEY (`postDetailId`),
    FOREIGN KEY (`postId`) REFERENCES `posts` (`postId`) ON DELETE CASCADE ON UPDATE CASCADE
);
