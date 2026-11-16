#!/usr/bin/env bash
# Runs NPM in the Node container
# Usage example: `./npm.sh run prod`

set -eu
exec "$(dirname "$0")/compose.sh" run --rm node npm "$@"
