FROM php:8.4-apache

# Use the PHP development config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pdo_mysql @composer

# Set the document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/twhl/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Apache listens on TCP port 80, so expose that
EXPOSE 80/tcp

# If we wanted to store the PHP files etc. in a volume:
# VOLUME /var/www/html/twhl

# Environment variables
ENV DB_DATABASE=twhl
ENV DB_HOST=twhl-mysql-db
ENV DB_PASSWORD=cs_canbunk2
ENV DB_USER=root

# Script for waiting for the MySQL server to start
COPY ./wait-for-mysql-to-start.php /usr/local/bin/wait-for-mysql-to-start
RUN chmod +x /usr/local/bin/wait-for-mysql-to-start

# Script for creating a .env file and running Composer
COPY ./twhl-install.sh /usr/local/bin/twhl-install
RUN chmod +x /usr/local/bin/twhl-install

# This is where the good stuff goes, so make that the working directory
WORKDIR /var/www/html/twhl/

# Startup script
COPY ./entrypoint.sh ../entrypoint.sh
RUN chmod +x ../entrypoint.sh
ENTRYPOINT "../entrypoint.sh"
