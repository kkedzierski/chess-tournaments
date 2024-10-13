#!/usr/bin/env bash
source ./docker/.env-scripts.dev
docker exec -it "$CONTAINER_NAME" ./vendor/bin/phpunit "$@" --display-deprecations