#!/usr/bin/env bash

sudo apt-get update
sudo apt-get upgrade

sudo apt-get install apache2
sudo apt-get install mysql-server

sudo apt-get install -y python-software-properties
sudo add-apt-repository -y ppa:ondrej/php

sudo apt-get update
sudo apt-get upgrade

sudo apt-get install php7.1
sudo apt-get install php7.1-mysql
sudo phpenmod pdo_mysql
sudo apt-get install php-xdebug

sudo service apache2 restart