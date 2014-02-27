USE SocialNetwork;

INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated)
VALUES
	('John', 'Adam', 'Smith', 'jsmith@gmail.com', 'johnsmith', SHA1('shortpassword'), 
		SHA1('jsmith@gmail.com1391362436'), 1),
	('Anna', NULL, 'Smith', 'asmith@gmail.com', 'annasmith', SHA1('password'),
		SHA1('asmith@gmail.com1391362036'), 1),
	('Test', NULL, 'User', 'fake@gmail.com', 'test', SHA1('test'),
		SHA1('fake@gmail.com1391362036'), 1);

INSERT INTO profile(userId, gender, dob, about, locations, languages)
VALUES
	(1, 'Male', 593136000, 'A young man looking for love', 'England, Germany', 'English, German'),
	(3, 'Female', 593106000, 'A young woman studying in Cambridge', 'England', 'English');

INSERT INTO friendships(user1, user2, status, startTimestamp)
VALUES
    (1, 2, 1, 1391362436),
    (1, 3, 1, 1391362436),
    (2, 3, 1, 1391362436);

INSERT INTO circles(owner, name)
VALUES
    (3, 'Work'),
    (3, 'Family');

INSERT INTO circle_memberships(user, circle)
VALUES
    (1, 1),
    (2, 1),
    (2, 2);

INSERT INTO messages(`from`, `to_user`, `to_circle`, `type`, `content`, `timestamp`)
VALUES
    (1, 2, NULL, 'P', 'Message', 1391362436),
    (1, 3, NULL, 'P', 'Message', 1391362436),
    (2, 3, NULL, 'P', 'Message', 1391362436),
    (3, 1, NULL, 'P', 'Message', 1391362436),
    (3, 2, NULL, 'P', 'Message', 1391362436),
    (3, NULL, 1, 'C', 'Message to Circle', 1391362436),
    (2, NULL, 1, 'C', 'Message to Circle', 1391362436);
