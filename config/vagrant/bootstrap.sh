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

mysql -uroot -p$DBPASSWD -e "CREATE DATABASE $DBNAME"
mysql -uroot -p$DBPASSWD -e "grant all privileges on $DBNAME.* to '$DBUSER'@'localhost' identified by '$DBPASSWD'"

sudo apt-get -y install apache2 php7.1 php7.1-mysql php7.1-mbstring php7.1-dom php7.1-sqlite php7.1-zip php7.1-curl

sudo apt-get -y install curl composer zip unzip

sudo phpenmod pdo_mysql
sudo service apache2 restart