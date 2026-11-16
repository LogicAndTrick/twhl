#!/usr/bin/env bash
# Stops and removes all containers

set -eu
exec "$(dirname "$0")/compose.sh" down
