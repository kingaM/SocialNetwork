USE SocialNetwork;

INSERT INTO users(first_name, middle_name, last_name, email, login, password)
VALUES
	('John', 'Adam', 'Smith', 'jsmith@gmail.com', 'johnsmith', SHA1('mysecretpassword')),
	('Anna', NULL, 'Smith', 'asmith@gmail.com', 'annasmith', SHA1('shortpassword'));