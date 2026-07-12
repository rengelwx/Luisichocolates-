FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev libsqlite3-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
WORKDIR /var/www/html
RUN mkdir -p data uploads && chown -R www-data:www-data .

EXPOSE 80

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-80} router.php"]