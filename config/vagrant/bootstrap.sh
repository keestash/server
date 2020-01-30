#!/usr/bin/env bash

# Variables
DBNAME=keestash
DBUSER=keestash
DBPASSWD=keestash

apt-get -y install software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update

apt-get -y install nano curl

apt-get update

debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASSWD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASSWD"

apt-get -y install mysql-server

mysql -uroot -p$DBPASSWD -e "CREATE DATABASE $DBNAME"
mysql -uroot -p$DBPASSWD -e "grant all privileges on $DBNAME.* to '$DBUSER'@'localhost' identified by '$DBPASSWD'"

sudo apt-get install php7.1
sudo apt-get install php7.1-mysql
sudo apt-get install php7.1-mbstring
sudo apt-get install php7.1-dom
sudo apt-get install php7.1-sqlite
sudo apt-get install php7.1-zip
sudo apt-get install php7.1-curl
sudo phpenmod pdo_mysql
sudo apt-get install composer
sudo apt-get install zip
sudo apt-get install unzip

sudo service apache2 restart