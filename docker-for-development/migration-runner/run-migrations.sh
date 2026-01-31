#!/bin/bash
set -e

if php artisan migrate:status | grep -q "Migration table not found"
then
  echo "Did not find a migration table in the database."
  echo "Running 'php artisan migrate --seed'."
  echo "If it fails, you may have to rerun it manually."
  php artisan migrate --seed
fi
php artisan migrate
