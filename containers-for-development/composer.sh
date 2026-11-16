#!/usr/bin/env bash
# Runs Composer in the Composer container.
# Usage example: `./composer.sh install`

set -eu
exec "$(dirname "$0")/compose.sh" run --rm composer composer "$@"
