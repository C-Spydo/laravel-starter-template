FROM php:8.2-fpm-buster

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    zip \
    unzip \
    cron \
    supervisor \
    libzip-dev \
    libexif-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libcurl4-openssl-dev \
    libevent-dev \
    libicu-dev \
    libidn2-dev \
    libidn11-dev \
    libssl-dev \
    zlib1g-dev \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip exif \
    && pecl install raphf \
    && docker-php-ext-enable raphf \
    && pecl install pecl_http \
    && docker-php-ext-enable http \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Confirm Node.js and npm are installed
RUN node -v && npm -v

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www/html

# Copy composer files first
COPY composer.json ./

# Install PHP dependencies without running post-install scripts
RUN composer install --no-interaction --no-progress --no-scripts

# Copy all Laravel files to the container
COPY . /var/www/html

# Fix git ownership issue
RUN git config --global --add safe.directory /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run composer scripts and regenerate autoloader after all files are copied
RUN composer install --no-interaction --no-progress --optimize-autoloader

# Install frontend dependencies and build assets with Vite
RUN npm install && npm run build

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
