sudo apt-get install tasksel
sudo tasksel install lamp-server
sudo apt-get install phpmyadmin
echo "Include /etc/phpmyadmin/apache.conf" | sudo tee -a /etc/apache2/apache2.conf
sudo /etc/init.d/apache2 restart