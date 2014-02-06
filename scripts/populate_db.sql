USE SocialNetwork;

INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated)
VALUES
	('John', 'Adam', 'Smith', 'jsmith@gmail.com', 'johnsmith', SHA1('shortpassword'), 
		SHA1('jsmith@gmail.com1391362436'), 1),
	('Anna', NULL, 'Smith', 'asmith@gmail.com', 'annasmith', SHA1('password'),
		SHA1('asmith@gmail.com1391362036'), 1),
	('Test', NULL, 'User', 'fake@gmail.com', 'test', SHA1('test'),
		SHA1('fake@gmail.com1391362036'), 1);

INSERT INTO friendships(user1, user2, status, startTimestamp)
VALUES
    (1, 2, 1, 1391362436),
    (1, 3, 1, 1391362436),
    (2, 3, 1, 1391362436);
