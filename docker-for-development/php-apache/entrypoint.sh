#!/bin/bash

if [ ! -d ./vendor ] || [ ! -f ./.env ]
then
  echo "Running 'twhl-install'."
  twhl-install
else
  echo "Remember to run 'composer install' or 'twhl-install' to install dependencies after pulling or changing branch."
fi


wait-for-mysql-to-start
php artisan migrate:status | grep -q "Migration table not found"
if [ $? == 0 ]
then
  echo "Did not find a migration table in the database."
  echo "Running 'php artisan migrate --seed'."
  echo "If it fails, you may have to rerun it manually."
  php artisan migrate --seed
fi

echo "Running 'docker-php-entrypoint'"
docker-php-entrypoint "apache2-foreground"
