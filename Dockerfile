FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo pdo_mysql

RUN a2enmod rewrite

RUN apt update && apt install -y sendmail

# RUN apt-get update && \
#   apt-get install -y ssmtp && \
#   apt-get clean && \
#   echo "FromLineOverride=YES" >> /etc/ssmtp/ssmtp.conf && \
#   echo 'sendmail_path = "/usr/sbin/ssmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

COPY . /var/www/html/
WORKDIR /var/www/html/