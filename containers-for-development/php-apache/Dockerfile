FROM php:8.4-apache

# Use the PHP development config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pdo_mysql

# Set the document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/twhl/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Listen on an unprivileged port so Apache can run as a regular user
RUN sed -ri 's/^Listen 80$/Listen 1082/' /etc/apache2/ports.conf && grep -q 'Listen 1082' /etc/apache2/ports.conf
RUN sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:1082>/' /etc/apache2/sites-available/000-default.conf && grep -q '<VirtualHost \*:1082>' /etc/apache2/sites-available/000-default.conf
EXPOSE 1082/tcp

# If we wanted to store the PHP files etc. in a volume:
# VOLUME /var/www/html/twhl

# This is where the good stuff goes, so make that the working directory
WORKDIR /var/www/html/twhl/
