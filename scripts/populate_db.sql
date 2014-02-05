USE SocialNetwork;

INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated)
VALUES
	('John', 'Adam', 'Smith', 'jsmith@gmail.com', 'johnsmith', SHA1('mysecretpassword'), 
		SHA1('jsmith@gmail.com1391362436'), 1),
	('Anna', NULL, 'Smith', 'asmith@gmail.com', 'annasmith', SHA1('shortpassword'),
		SHA1('asmith@gmail.com1391362036'), 1);

INSERT INTO friendships(user1, user2, startTimestamp)
VALUES
    (1, 2, 1391362436);
