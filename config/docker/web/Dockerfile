FROM php:8.3-apache
RUN a2enmod ssl && a2enmod rewrite
RUN apt-get update && apt-get upgrade -y  --allow-unauthenticated
RUN apt-get install software-properties-common -y
RUN apt-get update
RUN pecl install xdebug redis pcntl
RUN apt-get install git libonig-dev libxml2-dev sqlite3 libsqlite3-dev curl libzip-dev libcurl4-openssl-dev zip unzip jq libldap2-dev  -y
RUN docker-php-ext-configure pcntl --enable-pcntl
RUN docker-php-ext-install pdo pdo_mysql mysqli pdo_sqlite mbstring dom zip curl intl ldap pcntl calendar # bcmath ctype exif fileinfo json reflection session simplexml spl standard xml xmlreader xmlwriter
RUN docker-php-ext-enable xdebug ldap redis pcntl calendar
RUN apt-get update
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
EXPOSE 80
EXPOSE 443
