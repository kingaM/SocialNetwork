import random
from random import choice

boys = ["Jacob",
"John",
"Michael",
"Alexander",
"Aiden"]

girls = ["Sophia",
"Abigail",
"Anna",
"Madison",
"Elizabeth"]

firstNames = boys + girls

surnames = ["Smith",
"Thomas",
"Jackson",
"White",
"Harris"]

countries = ["Germany",
"Russia",
"United Kingdom",
"France",
"Italy",
"Spain",
"Poland",
"Netherlands",
"Belgium",
"Sweden",
"Austria",
"Switzerland",
"Ukraine",
"Greece",
"Czech",
"Norway",
"Romania",
"Portugal",
"Denmark",
"Hungary",
"Finland",
"Ireland"]

languages = ["Mandarin",
"English",
"Spanish",
"Hindi",
"Russian",
"Arabic",
"Portuguese",
"Bengali",
"French"]

about = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur eget laoreet ante. Curabitur posuere mauris et commodo consectetur." +\
" Nullam justo est, sodales nec viverra non, faucibus non enim. Donec fringilla eleifend fringilla. Curabitur vestibulum, ipsum et ultrices pulvinar," +\
" nulla mi tempor sapien, et eleifend odio nisl a elit. Morbi accumsan dignissim malesuada. Duis malesuada accumsan nunc, egestas tincidunt orci" +\
" bibendum id. Proin nisi lacus, venenatis at nisl a, convallis euismod lectus. Pellentesque pellentesque lacinia justo id rutrum. Nam lobortis metus" +\
" sagittis elit tincidunt, at venenatis nisl viverra. In sit amet sem dapibus, eleifend justo a, scelerisque felis."

friends = {}

print "USE SocialNetwork;\n"
print """INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated)
    VALUES"""

for x in firstNames:
    for y in surnames:
        login = x + y
        login = login.lower()
        email = login + "@example.com"
        print "    ('" + x + "', NULL, '" + y + "', '" + email + "', '" + login + "', SHA1('pass'), SHA1('" + email + "'), 1),"

print "    ('Test', NULL, 'User', 'fake@gmail.com', 'test', SHA1('test'), SHA1('fake@gmail.com1391362036'), 1);\n";

print """INSERT INTO users(first_name, middle_name, last_name, email, login, password, hash, activated, 
    admin)
VALUES
    ('Site', NULL, 'Admin', 'admin@example.com', 'admin', SHA1('admin'),
        SHA1('admin@example.com1391362036'), 1, 1);"""

print """INSERT INTO profile(userId, gender, dob, about, locations, languages)
    VALUES"""

i = 1
for x in firstNames:
    for y in surnames:
        if x in boys:
            gender = "Male"
        else:
            gender = "Female"
        print "    (" + str(i) + ", '" + gender + "', 593136000, '" + about + "', '" + choice(countries) + "', '" + choice(languages) + "'),"
        i = i + 1

print "    (" + str(i) + ", 'Female', 593106000, 'A young woman studying in Cambridge', 'England', 'English'),"
i += 1
print "    (" + str(i) + ", 'Male', 593106000, 'I made this site', 'England', 'English');\n"
i -= 1

print """INSERT INTO friendships(user1, user2, status, startTimestamp)
    VALUES"""

for j in range(1, i-1):
    for k in range(1, i+1):
        if(j >= k):
            continue
        if(random.randrange(100) > 80):
            if(random.randrange(100) > 80):
                status = 0
            else:
                status = 1
            print "    (" + str(j) + ", " + str(k) + ", " + str(status) + ", 1391362436),"

print "    (" + str(i-1) + ", " + str(i) + ", 1, 1391362436);\n"

# print """INSERT INTO circles(owner, name)
#     VALUES"""

# for j in range(1,i):
#     pass


# # TODO - Finish these off properly
# print """INSERT INTO circles(owner, name)
# VALUES
#     (3, 'Work'),
#     (3, 'Family');

# INSERT INTO circle_memberships(user, circle)
# VALUES
#     (1, 1),
#     (2, 1),
#     (2, 2);

# INSERT INTO messages(`from`, `to_user`, `to_circle`, `type`, `content`, `timestamp`)
# VALUES
#     (1, 2, NULL, 'P', 'Message', 1391362436),
#     (1, 3, NULL, 'P', 'Message', 1391362436),
#     (2, 3, NULL, 'P', 'Message', 1391362436),
#     (3, 1, NULL, 'P', 'Message', 1391362436),
#     (3, 2, NULL, 'P', 'Message', 1391362436),
#     (3, NULL, 1, 'C', 'Message to Circle', 1391362436),
#     (2, NULL, 1, 'C', 'Message to Circle', 1391362436);"""
