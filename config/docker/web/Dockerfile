FROM php:7.4-apache
RUN apt-get update && apt-get upgrade -y  --allow-unauthenticated
RUN apt-get install software-properties-common -y
RUN apt-get update
RUN pecl install xdebug
RUN apt-get install git libonig-dev libxml2-dev sqlite3 libsqlite3-dev curl libzip-dev libcurl4-openssl-dev zip unzip jq -y
RUN docker-php-ext-install pdo pdo_mysql mysqli pdo_sqlite mbstring dom zip curl intl # bcmath calendar ctype exif fileinfo json reflection session simplexml spl standard xml xmlreader xmlwriter
RUN docker-php-ext-enable xdebug
RUN apt-get update
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
EXPOSE 80
