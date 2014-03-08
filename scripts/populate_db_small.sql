USE SocialNetwork;

INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated)
VALUES
	('John', 'Adam', 'Smith', 'jsmith@gmail.com', 'johnsmith', SHA1('shortpassword'), 
		SHA1('jsmith@gmail.com1391362436'), 1),
	('Anna', NULL, 'Smith', 'asmith@gmail.com', 'annasmith', SHA1('password'),
		SHA1('asmith@gmail.com1391362036'), 1),
	('Test', NULL, 'User', 'fake@gmail.com', 'test', SHA1('test'),
		SHA1('fake@gmail.com1391362036'), 1);

INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated, admin)
VALUES
    ('Site', NULL, 'Admin', 'admin@example.com', 'admin', SHA1('admin'),
        SHA1('admin@example.com1391362036'), 1, 1);

INSERT INTO profile(userId, gender, dob, about, locations, languages, profilePicture)
VALUES
	(1, 'Male', 593136000, 'A young man looking for love', 'England, Germany', 'English, German',
        null),
    (2, null, null, null, null, null, null),
	(3, 'Female', 593106000, 'A young woman studying in Cambridge', 'England', 'English',
        null);

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

INSERT INTO blogs(`user`, `name`, `url`, `about`)
VALUES 
    (3, 'Cooking', 'cooking', 'A simple cooking blog.'),
    (3, 'Personal', 'personal', 'My personal life :)');

INSERT INTO posts(`blogId`, `title`, `timestamp`)
VALUES
    (1, 'Bok Choy', 1393003907),
    (1, 'Chicken stir-fry', 1361454065),
    (1, 'Dessert', 1361536865),
    (1, 'Chocolate trifle', 1362055265),
    (1, 'Beef Burger', 1363869665),
    (1, 'Chicken Burger', 1366548065),
    (1, 'Salsa', 1366551665),
    (1, 'Home-made pasta', 1366634465);

INSERT INTO posts_details(`postId`, `content`)
VALUES 
    (1, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (2, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (3, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (4, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (5, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (6, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (7, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>"),
    (8, "<p><a href=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock.png\" rel=\"prettyPhoto[42]\"><img class=\"alignleft\" title=\"1311022937_lock\" src=\"http://themes.simplethemes.com/orion/wp-content/uploads/2011/07/1311022937_lock-150x150.png\" alt=\"\" width=\"150\" height=\"150\" /></a>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
        <span id=\"more-42\"></span><br />
        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#8217;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>");





