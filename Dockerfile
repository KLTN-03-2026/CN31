# Dùng "Hệ điều hành" PHP 8.2
FROM php:8.2-cli

# Cài đặt các lõi cần thiết cho Database MySQL
RUN apt-get update && apt-get install -y \
    libzip-dev unzip curl \
    && docker-php-ext-install pdo pdo_mysql zip

# Cài đặt Node.js để Build giao diện Vue
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Tiến hành cài thư viện và Đóng gói giao diện
RUN composer install --optimize-autoloader --no-dev
RUN npm install
RUN npm run build

# Khởi động Web Server
CMD php artisan serve --host=0.0.0.0 --port=$PORT
