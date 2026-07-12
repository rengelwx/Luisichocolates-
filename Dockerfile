FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_sqlite zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load

COPY . /var/www/html/
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]