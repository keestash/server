FROM php:8.4-apache-bookworm AS base

# Enable Apache modules
RUN a2enmod ssl && a2enmod rewrite && a2enmod headers

# Update system packages and upgrade
RUN apt-get update && apt-get upgrade -y --allow-unauthenticated

# Install various dependencies
RUN apt-get install -y \
    git \
    libonig-dev \
    libxml2-dev \
    sqlite3 \
    libsqlite3-dev \
    curl \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    jq \
    libjpeg62-turbo-dev \
    libpng-dev \
    libldap2-dev \
    software-properties-common

# Install IMAP dependencies
RUN apt-get update && apt-get install -y \
    libc-client2007e-dev \
    libkrb5-dev

# Install PECL extensions (IMAP is now a PECL extension in PHP 8.4)
RUN pecl install redis imap

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli pdo_sqlite mbstring dom zip curl intl ldap calendar gd

# Enable extensions
RUN docker-php-ext-enable ldap redis imap

# Install Composer from official image (no curl | php supply chain risk)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

EXPOSE 443

# ── Production image ───────────────────────────────────────────────────────────
FROM base AS production

# ── Development image (adds Xdebug) ───────────────────────────────────────────
FROM base AS development

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

EXPOSE 80
