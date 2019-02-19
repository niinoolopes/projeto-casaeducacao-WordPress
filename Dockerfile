FROM php:5.6-apache
RUN docker-php-ext-install mysqli

FROM mysql:5.6
ADD dump.sql /docker-entrypoint-initdb.d