#!/usr/bin/env bash

environment=dev
rebuilt=false

source ./docker/.env-run-app.dev

while getopts e:r: flag
do
    case "${flag}" in
        e) environment=${OPTARG};;
        r) rebuilt=${OPTARG};;
    esac
done

if [ "$rebuilt" == "true" ]; then
  echo "Rebuilding configuration image..."
  docker build --no-cache . -f ./docker/etc/php/main.Dockerfile
fi

ENV="./docker/.env.dist"
if [ -f ./docker/.env ]; then
    ENV="./docker/.env"
fi

echo "Building and starting containers..."
docker-compose down
docker-compose --env-file $ENV up -d --build
echo "Containers are up and running."

echo "Installing composer dependencies..."
docker exec -it "${CONTAINER_NAME}" composer install
echo "Composer dependencies installed."

echo "Creating database schema..."
docker exec -it "${CONTAINER_NAME}" bin/console doctrine:database:create --if-not-exists
docker exec -it "${CONTAINER_NAME}" bin/console doctrine:schema:update --force
docker exec -it "${CONTAINER_NAME}" bin/console doctrine:schema:validate

echo "Creating test database schema..."
docker exec -it "${CONTAINER_NAME}" bin/console doctrine:schema:update -etest --force
