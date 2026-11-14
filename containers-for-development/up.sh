#!/usr/bin/env bash
# Builds the images and starts all containers

set -eu
exec "$(dirname "$0")/compose.sh" up --build "$@"
