#!/bin/bash

# Run composer install
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs

# Run post-root-package-install script
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer run-script post-root-package-install

# Run post-create-project-cmd script
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer run-script post-create-project-cmd

# Run artisan vendor:publish command
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    php artisan vendor:publish --tag=laravel-assets --ansi --force

# Up the sail containers
./vendor/bin/sail up -d

# Install npm dependencies using Sail
./vendor/bin/sail npm install

# Build the assets using Vite with Sail
./vendor/bin/sail npm run build