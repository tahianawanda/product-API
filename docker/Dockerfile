#* ------------- BUILD ARGUMENTS ------------------------ #
# See https://docs.docker.com/build/building/variables/#arg-usage-example
ARG PHP_VERSION

#* ------------------------------------------------------ #

FROM php:${PHP_VERSION}-fpm

# Set build arguments
ARG user
ARG uid
ARG API___PORT
ARG COMPOSER_VERSION

# Install system dependencies
RUN apt update && apt install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    && apt clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
# the --from parameter doesn’t support interpolation (that means you cant use, for example, $COMPOSER_VERSION)
# If we want the composer version to be a variable, this needs to be done in an alternate way, like installing compose manually
# See https://forums.docker.com/t/how-can-i-expand-a-variable-within-a-copy-command-in-the-dockerfile/113538/8
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a non-root user for the application
RUN useradd -G www-data,root -u "$uid" -d /home/"$user" "$user" \
    && mkdir -p /home/"$user"/.composer \
    && chown -R "$user":"$user" /home/"$user"

# Set working directory
WORKDIR /var/www

# Copy the application code to the container
COPY --chown=$user:$user . .

# Install PHP dependencies using Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Switch to the non-root user
USER $user

# Expose the port the app runs on
EXPOSE $API___PORT

# Run Laravel Artisan commands
# Laravel caches configuration files for better performance.
# If the configuration was cached before the key from key:generate was generated,
# Laravel may still think the key is missing.
# Thats why we clear the cached configuration files after generating the key.

# After that, we then run php-fpm
# Please, remember that CMD does not run on Docker build, but it runs when the Docker container starts up

CMD php artisan key:generate \
    && php artisan jwt:secret -f \
    # Esto del migrate:fresh -seed está comentado por la siguiente razon
    # Esto interactua contra la base, seedeandola con los datos necesarios, y en prod la base no la podes estar destruyendo y recreando todo el tiempo
    # En realidad, habría que ejecutarlo a mano en el server
    # O en otra Dockerfile
    # && php artisan migrate:fresh --seed \
    && php artisan storage:link \
    && php artisan config:clear \
    && php artisan config:cache \
    && php-fpm
