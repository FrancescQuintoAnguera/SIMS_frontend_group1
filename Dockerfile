FROM php:8.2-fpm-alpine

# Instalar nginx
RUN apk add --no-cache nginx

# Copiar configuración de nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copiar código fuente
COPY src/ /var/www/html/

# Ajustar permisos para que nginx pueda leer los archivos
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 755 /var/www/html

# Crear directorio para logs
RUN mkdir -p /var/log/nginx /run/nginx

# Exponer puerto
EXPOSE 80

# Script de inicio para ejecutar nginx y php-fpm
CMD php-fpm -D && nginx -g 'daemon off;'
