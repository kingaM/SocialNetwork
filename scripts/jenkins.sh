rm -rf ./SocialNetwork-master
rm -f ./master.zip
wget https://github.com/kingaM/SocialNetwork/archive/master.zip
unzip master.zip
sudo rm -rf /var/www/*
sudo cp -r ./SocialNetwork-master/www/* /var/www
sudo cp -r ./SocialNetwork-master/www/.htaccess /var/www/.htaccess

cd ./SocialNetwork-master/scripts
mysql -u root --password=root -e 'DROP DATABASE IF EXISTS SocialNetwork;'
mysql -u root --password=root < create_tables.sql
mysql -u root --password=root < video_db.sql
#python ./generateLargeTables.py | mysql -u root --password=root

sudo chmod -R 777 /var/www/uploads
