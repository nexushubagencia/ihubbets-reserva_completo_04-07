FROM php:8.3-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix MPM - disable event, use prefork only
RUN a2dismod mpm_event 2>/dev/null || true
RUN a2enmod mpm_prefork 2>/dev/null || true
RUN a2enmod rewrite

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN echo "upload_max_filesize = 64M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini

EXPOSE 80

CMD ["apache2-foreground"]
