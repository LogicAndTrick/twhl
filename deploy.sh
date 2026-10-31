#!/usr/bin/env bash

# if any command fails, the whole script stops and fails
# https://gist.github.com/mohanpedala/1e2ff5661761d3abd0385e8223e16425
set -euxo pipefail

# pull latest, run this as root since www-data only needs to read these files
git pull --ff-only

# run all these as the web user so it doesn't get hit with 

# composer install
sudo -u www-data /composer.phar install

# run database migrations, using force to stop the prompt to continue in production
sudo -u www-data php artisan migrate --force --no-interaction

# clear + reset the laravel caches
sudo -u www-data php artisan optimize

# make sure the web user owns the cached config and view files
sudo chown -R www-data:www-data storage bootstrap/cache