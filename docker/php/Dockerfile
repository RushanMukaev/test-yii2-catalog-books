# PHP 8.3 FPM для Yii2 проекта
FROM php:8.3-fpm-alpine

LABEL maintainer="Yii2 Books Catalog"
LABEL version="1.0"

# Аргументы сборки
ARG XDEBUG_VERSION=3.3.2

# Установка системных зависимостей
RUN apk add --no-cache \
    # Базовые утилиты
    bash \
    git \
    curl \
    wget \
    zip \
    unzip \
    # Библиотеки для расширений PHP
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    # MySQL клиент
    mysql-client \
    # Для работы с изображениями
    imagemagick \
    imagemagick-dev

# Настройка и установка PHP расширений
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        mbstring \
        opcache \
        bcmath \
        soap \
        exif

# Установка Imagick через PECL
RUN apk add --no-cache --virtual .imagick-deps $PHPIZE_DEPS \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .imagick-deps

# Установка XDebug для разработки (можно отключить в production)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Установка Composer (последняя версия 2.x)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Копирование конфигурационных файлов PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Создание пользователя для безопасности (не запускаем от root)
RUN addgroup -g 1000 yii2 \
    && adduser -D -u 1000 -G yii2 yii2 \
    && mkdir -p /home/yii2/.composer \
    && chown -R yii2:yii2 /home/yii2

# Переключаемся на пользователя yii2
USER yii2

# Рабочая директория
WORKDIR /app

# Открываем порт PHP-FPM
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"]
