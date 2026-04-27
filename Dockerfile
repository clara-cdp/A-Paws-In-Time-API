FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libsqlite3-dev \
    sqlite3 \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    nginx \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite mbstring zip intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p /data database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && touch /data/database.sqlite \
    && chown -R www-data:www-data /var/www/html /data \
    && chmod -R 775 storage bootstrap/cache database /data

COPY docker/nginx/default.conf /etc/nginx/sites-available/default

EXPOSE 10000

CMD sh -c "php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan migrate --force && php artisan db:seed --force && php artisan passport:ensure-personal-client --name='A Paws In Time Personal Access Client' --provider=users && php-fpm -D && nginx -g 'daemon off;'"
