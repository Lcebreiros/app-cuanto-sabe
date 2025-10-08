# Stage 1: Composer (instala vendor)
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --optimize

# Stage 2: Node (para assets)
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci --silent
COPY . .
RUN npm run build

# Stage 3: PHP-FPM + Nginx (Producción)
FROM php:8.3-fpm-bookworm

# Instalar dependencias del sistema
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev mariadb-client nginx supervisor \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Instalar composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar vendor y assets compilados
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Copiar código de la aplicación
COPY . .

# Configuración de Nginx
RUN echo 'server {\n\
    listen 8080;\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
    charset utf-8;\n\
\n\
    # Aumentar tamaño de buffer\n\
    client_max_body_size 20M;\n\
\n\
    # Logs\n\
    access_log /var/log/nginx/access.log;\n\
    error_log /var/log/nginx/error.log;\n\
\n\
    # Agregar headers de seguridad\n\
    add_header X-Frame-Options "SAMEORIGIN" always;\n\
    add_header X-Content-Type-Options "nosniff" always;\n\
    add_header X-XSS-Protection "1; mode=block" always;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        fastcgi_param PATH_INFO $fastcgi_path_info;\n\
        include fastcgi_params;\n\
        fastcgi_hide_header X-Powered-By;\n\
        fastcgi_read_timeout 300;\n\
    }\n\
\n\
    location ~ /\.(?!well-known).* {\n\
        deny all;\n\
    }\n\
\n\
    # Cache de assets estáticos\n\
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {\n\
        expires 1y;\n\
        add_header Cache-Control "public, immutable";\n\
    }\n\
}' > /etc/nginx/sites-available/default

# Configuración de Supervisor
RUN echo '[supervisord]\n\
nodaemon=true\n\
user=root\n\
logfile=/dev/stdout\n\
logfile_maxbytes=0\n\
\n\
[program:php-fpm]\n\
command=/usr/local/sbin/php-fpm\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=/usr/sbin/nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0' > /etc/supervisor/conf.d/supervisord.conf

# Configuración de PHP para producción
RUN echo 'memory_limit = 256M\n\
upload_max_filesize = 20M\n\
post_max_size = 20M\n\
max_execution_time = 300\n\
opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=10000\n\
opcache.revalidate_freq=2\n\
opcache.fast_shutdown=1' > /usr/local/etc/php/conf.d/custom.ini

# --- crear usuario 'vscode' para devcontainer ---
ARG DEV_USER=vscode
ARG DEV_UID=1000
ARG DEV_GID=1000

# Asegurarnos de tener bash y sudo (apt ya fue ejecutado arriba; si no, se puede repetir)
RUN apt-get update && apt-get install -y --no-install-recommends bash sudo \
  && groupadd --gid ${DEV_GID} ${DEV_USER} || true \
  && useradd -m -u ${DEV_UID} -g ${DEV_GID} -s /bin/bash ${DEV_USER} || true \
  && mkdir -p /home/${DEV_USER}/.ssh \
  && echo "${DEV_USER} ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/${DEV_USER} \
  && chmod 0440 /etc/sudoers.d/${DEV_USER} \
  && chown -R ${DEV_USER}:${DEV_USER} /home/${DEV_USER}
# --- fin usuario 'vscode' ---

# Crear directorios y permisos
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R 775 storage bootstrap/cache

# Script de inicialización
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Esperando base de datos..."\n\
until php artisan migrate --force 2>/dev/null; do\n\
  echo "Base de datos no disponible, esperando..."\n\
  sleep 5\n\
done\n\
\n\
echo "Ejecutando migraciones..."\n\
php artisan migrate --force\n\
\n\
echo "Optimizando aplicación..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "Iniciando supervisord..."\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' > /usr/local/bin/start.sh \
  && chmod +x /usr/local/bin/start.sh

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
  CMD curl -f http://localhost:8080/ || exit 1

CMD ["/usr/local/bin/start.sh"]
