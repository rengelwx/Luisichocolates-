FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_sqlite zip \
    && a2enmod rewrite \
    && a2dismod mpm_prefork || true \
    && ls -la /etc/apache2/mods-enabled/mpm_* || echo "NO MPM FILES" \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]