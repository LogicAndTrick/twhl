#!/usr/bin/env bash
# Shows the logs of the php-apache container.
# Usage example: `./apache-logs.sh --follow`

set -eu
exec "$(dirname "$0")/compose.sh" logs php-apache "$@"
