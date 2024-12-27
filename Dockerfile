FROM php:8-apache
WORKDIR /var/www/html
COPY . .
EXPOSE 80
EXPOSE 443