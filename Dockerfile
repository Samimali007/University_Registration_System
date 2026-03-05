FROM php:8.2-apache

# copy project files
COPY . /var/www/html/

# enable apache rewrite (optional but useful)
RUN a2enmod rewrite

EXPOSE 80