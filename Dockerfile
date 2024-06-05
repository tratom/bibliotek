FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo pdo_mysql

RUN a2enmod rewrite
 
COPY . /var/www/html/
WORKDIR /var/www/html/