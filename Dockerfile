FROM php:8.2-apache

# Disable conflicting MPM modules and enable prefork
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork

# Enable rewrite module (optional)
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

EXPOSE 80