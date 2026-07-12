#!/bin/bash
set -e

mkdir -p /var/www/html/data /var/www/html/uploads
chown -R www-data:www-data /var/www/html/data /var/www/html/uploads /var/www/html

exec apache2-foreground