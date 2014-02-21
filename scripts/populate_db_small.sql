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

INSERT INTO blogs(`user`, `name`, `url`, `about`)
VALUES 
    (3, 'Cooking', 'cooking' 'A simple cooking blog.'),
    (3, 'Personal', 'personal', 'My personal life :)');

INSERT INTO posts(`blogId`, `title`, `timestamp`)
VALUES
    (1, 'Bok Choy', 1361450465),
    (1, 'Chicken stir-fry', 1361454065),
    (1, 'Dessert', 1361536865),
    (1, 'Chcocolate trifle', 1362055265),
    (1, 'Beef Burger', 1363869665),
    (1, 'Chicken Burger', 1366548065),
    (1, 'Salsa', 1366551665),
    (1, 'Home-made pasta', 1366634465);

INSERT INTO posts_details(`postId`, `content`)
VALUES 
    (1, "Recently I learned that bok choy is the number one vegetable in China. It seems to be the number one vegetable in my <a href=\"http://www.localharvest.org/csa/\">CSA</a> box lately. It's a very healthy vegetable with a ton of vitamin A, vitamin C and vitamin K plus and is even a good source of calcium and iron, but I have to admit, after serving it steamed or sautéed again and again, I was looking for a new way to prepare it.<br />
        <br />
        As luck would have it, at a Chinese New Year\'s dinner I stumbled upon a terrific dish at <a href=\"http://www.fangrestaurant.com/\">Fang restaurant</a>. It was served raw, as a salad with a soy and sesame vinaigrette alongside some chunks of short rib. Bok choy is very mild flavored but it has great texture. The leaves are tender and somewhat herbal without being bitter, and the stems are very juicy and crisp. I had never considered using bok choy in salad but after trying that dish, I couldn\'t stop thinking about it.<br />
        <br />
        Looking around online I found plenty of Asian inspired recipes for bok choy salad, and a few takes on coleslaw and even a chopped salad. My idea was to make a more Italian style salad using extra virgin olive oil, lemon juice and Parmigiano Reggiano. The result is a salad at once familiar and yet fresh. It\'s a great choice for a potluck or dinner party, because it is very sturdy and won't easily wilt. You could mix in other greens, add cherry tomatoes or even fresh fava beans when in season.<br />
        <br />
        <b>Bok Choy Salad</b><br />
        1 serving (multiply for as many servings as you like)<br />
        <br />
        Ingredients<br />
        <br />
        1 cup sliced bok choy<br />
        1/2 teaspoon fresh lemon juice<br />
        1 teaspoon extra virgin olive oil<br />
        Salt<br />
        Parmigiano reggiano, preferably young less than 18 months<br />
        Croutons<br />
        Freshly ground pepper<br />
        <br />
        Instructions<br />
        <br />
        Toss the bok choy in a bowl with the lemon juice and olive oil and a tiny pinch of salt. Shave long strips of Parmigiano using a vegetable peeler and add those and about five or so croutons to the bok choy. Season with pepper before serving.<br />
        <br />
        Enjoy!<br />
        <br />
        <br />
        More inspiring bok choy salad recipes:<br />
        <br />
        <a href=\"http://honestcooking.com/baby-bok-choy-and-avocado-salad/\">Bok Choy and Avocado Salad</a><br />
        <br />
        <a href=\"http://www.williams-sonoma.com/recipe/thai-steak-and-bok-choy-salad.html\">Thai Steak and Bok Choy Salad</a><br />
        <br />
        <a href=\"http://www.urbansacredgarden.com/2013/06/11/edamame-corn-and-bok-choy-salad-me/\">Bok Choy Salad with Corn &amp; Edamame</a><br />
        <br />
        <a href=\"http://www.cleaneatingmag.com/recipes/20-minutes-or-less/turkey-bacon-bok-choy-salad-with-shaved-parmesan/\">Turkey Bacon &amp; Bok Choy Salad with Shaved Parmesan</a> (chopped salad style)<br />
        <br />
        <a href=\"http://thecozyapron.com/cozy-cameo-pass-the-bok-choy-greens-and-my-skinny-jeans-please/\">Bok Choy with Sesame Soy Vinaigrette</a><br />
        <br />
        <a href=\"http://www.epicurious.com/recipes/food/views/Asian-Chicken-Salad-with-Snap-Peas-and-Bok-Choy-242110\">Bok Choy Salad</a>&nbsp;(with ramen noodles and almonds)<br />"),
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





