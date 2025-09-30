FROM php:8.2-apache

# Instalar dependencias necesarias para PostgreSQL y MongoDB
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PostgreSQL
RUN docker-php-ext-install pgsql pdo_pgsql

# Instalar extensión MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Habilitar mod_rewrite (útil para frameworks)
RUN a2enmod rewrite
