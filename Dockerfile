FROM php:8.3-apache

# Instala dependências do sistema
RUN apt-get update -y && apt-get upgrade -y && \
    apt-get install -y \
    libpng-dev \
    zlib1g-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    libmagickwand-dev \
    --no-install-recommends && \
    rm -rf /var/lib/apt/lists/*

# Instala extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip && \
    pecl install imagick && docker-php-ext-enable imagick

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configura Apache
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# PHP settings para alto volume
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    sed -i 's/max_execution_time = 30/max_execution_time = 300/' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/max_input_time = 60/max_input_time = 300/' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/memory_limit = 128M/memory_limit = 1024M/' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/post_max_size = 8M/post_max_size = 1024M/' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 1024M/' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/max_input_vars = 1000/max_input_vars = 100000/' "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Copia os arquivos da aplicação
COPY . /var/www/html

# Permissões para produção (descomentar)
# RUN chown -R www-data:www-data /var/www/html && \
#     chmod -R 755 /var/www/html/storage && \
#     chmod -R 755 /var/www/html/bootstrap/cache && \
#     composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && \
#     php artisan config:cache && \
#     php artisan route:cache && \
#     php artisan view:cache

EXPOSE 80
