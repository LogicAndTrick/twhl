#!/bin/bash

echo "Running 'composer install'"
composer install

if [ ! -f ./.env ]
then
  echo "You do not have an '.env'. file yet."
  echo "Copying '.env.example' to '.env'."
  cp ./.env.example ./.env
  echo "Replacing database connection details in .env."
  sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/g" ./.env
  sed -i "s/DB_HOST=.*/DB_HOST=${DB_HOST}/g" ./.env
  sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/g" ./.env
  sed -i "s/DB_USER=.*/DB_USER=${DB_USER}/g" ./.env
  echo "Running 'php artisan key:generate'."
  php artisan key:generate
else
  echo "You have an .env file. Good."
fi
