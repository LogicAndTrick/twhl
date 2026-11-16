FROM php:8.4

# Use the PHP development config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql

WORKDIR /var/twhl/

# Migration script
COPY ./run-migrations.sh ../run-migrations.sh
RUN chmod +x ../run-migrations.sh
ENTRYPOINT ["../run-migrations.sh"]
