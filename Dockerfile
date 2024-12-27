FROM php:8.3-apache
WORKDIR /var/www/html/
COPY . .
EXPOSE 80
EXPOSE 443
RUN a2enmod rewrite
RUN docker-php-ext-install mysqli
