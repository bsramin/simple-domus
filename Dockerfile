FROM node:21 as node

COPY --link . /var/www/html/
WORKDIR /var/www/html/

RUN mkdir node_modules && npm ci
RUN npm run build

FROM composer:2.6.5 as composer

COPY --link --from=node /var/www/html /var/www/html
WORKDIR /var/www/html/

RUN APP_ENV=prod composer install --no-dev --optimize-autoloader

FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV APP_ENV prod


RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --link --from=composer /var/www/html /var/www/html

RUN a2enmod rewrite

RUN apt-get update && \
    apt-get install -yq tzdata && \
    ln -fs /usr/share/zoneinfo/Europe/Rome /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata \
    apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/
