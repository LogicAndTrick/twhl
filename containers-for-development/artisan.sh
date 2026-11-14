#!/usr/bin/env bash
# Runs Artisan in the php-apache container.
# Needs the container to already be running.
# Usage example: `./artisan.sh migrate`

set -eu

exec "$(dirname "$0")/compose.sh" exec php-apache php artisan "$@"
