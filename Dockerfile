FROM php:8.5-fpm-alpine

# tzdata 讓容器的 TZ 環境變數真正生效（Alpine 預設不含時區資料庫）
RUN apk add --no-cache tzdata && \
    curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql opcache

WORKDIR /var/www/html

RUN { \
    echo 'date.timezone = "Asia/Taipei"'; \
    echo 'memory_limit = 256M'; \
    echo 'post_max_size = 8M'; \
    echo 'upload_max_filesize = 2M'; \
    echo 'expose_php = Off'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 128'; \
    echo 'opcache.interned_strings_buffer = 8'; \
    echo 'opcache.max_accelerated_files = 4000'; \
    echo 'opcache.revalidate_freq = 2'; \
    echo 'display_errors = Off'; \
    echo 'display_startup_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'error_log = /proc/self/fd/2'; \
} > /usr/local/etc/php/conf.d/custom.ini
