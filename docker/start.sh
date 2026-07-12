#!/bin/bash
set -e

PORT=${PORT:-80}

# Configurar Apache para escuchar en el puerto de Railway
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen [0-9]*/Listen ${PORT}/" /etc/apache2/apache2.conf 2>/dev/null || true
echo "Listen ${PORT}" > /etc/apache2/ports.conf

# Crear directorio de datos si no existe
mkdir -p /var/www/html/data /var/www/html/uploads
chown -R www-data:www-data /var/www/html/data /var/www/html/uploads

# Inicializar BD si no existe
if [ ! -f /var/www/html/data/database.sqlite ]; then
    php /var/www/html/setup_db.php || true
fi

# Iniciar Apache
exec apache2-foreground