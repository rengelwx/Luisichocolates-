FROM php:8.2-cli

COPY . /var/www/html/
WORKDIR /var/www/html

RUN mkdir -p /var/www/html/data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-80} router.php"]
