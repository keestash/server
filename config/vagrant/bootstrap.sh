#!/usr/bin/env bash

# Variables
DBNAME=keestash
DBUSER=keestash
DBPASSWD=keestash

apt-get -y install software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update


apt-get update

debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASSWD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASSWD"

apt-get -y install mysql-server
sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf

mysql -uroot -p$DBPASSWD -e "CREATE DATABASE $DBNAME"
mysql -uroot -p$DBPASSWD -e "CREATE USER '$DBUSER'@'%' IDENTIFIED BY '$DBPASSWD';"
mysql -uroot -p$DBPASSWD -e "GRANT ALL PRIVILEGES ON $DBNAME.* TO '$DBUSER'@'%' WITH GRANT OPTION;"
mysql -uroot -p$DBPASSWD -e "flush privileges"

sudo apt-get -y install apache2 php7.4 php7.4-mysql php7.4-mbstring php7.4-dom php7.4-sqlite php7.4-zip php7.4-curl php7.4-intl php7.4-redis redis-server

sudo apt-get -y install curl composer zip unzip

sudo phpenmod pdo_mysql
sudo service apache2 restart
sudo service mysql restart
