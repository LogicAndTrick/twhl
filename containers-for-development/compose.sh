#!/usr/bin/env bash
# Runs `podman compose` or `docker compose` with the right compose files and
# the UID/GID variables used by docker-compose.yaml for the php-apache container.
# Usage example: `./compose.sh logs php-apache`

set -eu

cd "$(dirname "$0")"

# The php-apache container runs as your user so it can write.
# bash sets `UID` but doesn't export it, and `GID` isn't set at all.
GID="$(id -g)"
export GID
export UID

if podman compose version >/dev/null 2>&1; then
	exec podman compose -f docker-compose.yaml -f compose.podman.yaml "$@"
elif command -v docker >/dev/null 2>&1; then
	exec docker compose -f docker-compose.yaml "$@"
else
	echo "You need to install Podman (with a Compose backend) or Docker."
	exit 1
fi
