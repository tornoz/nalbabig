FROM composer:2 AS composer

FROM php:8.2-fpm-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apk update
RUN apk add --no-cache ca-certificates icu-libs git libtool zlib-dev
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev
RUN apk add --no-cache freetype libpng libjpeg libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev
RUN apk add --no-cache libzip-dev
RUN apk add --no-cache xvfb ttf-dejavu ttf-droid ttf-freefont ttf-liberation postgresql-dev

#  curl mbstring xml

RUN docker-php-ext-install zip
RUN docker-php-ext-install intl exif
RUN apk add --no-cache  gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd
RUN pecl install -o -f apcu
RUN docker-php-ext-enable apcu
RUN docker-php-ext-enable opcache
RUN docker-php-ext-enable exif
RUN docker-php-ext-install pdo_pgsql

RUN echo "short_open_tag = off" >> /usr/local/etc/php/php.ini
RUN echo "memory_limit = -1" >> /usr/local/etc/php/php.ini
RUN echo "max_execution_time = 120" >> /usr/local/etc/php/php.ini
RUN echo "upload_max_filesize = 40M" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size = 40M" >> /usr/local/etc/php/php.ini
RUN echo "date.timezone = Europe/Paris" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo "opcache.max_accelerated_files = 20000" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo "realpath_cache_size=4096K" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo "realpath_cache_ttl=600" >> /usr/local/etc/php/conf.d/symfony.ini
RUN echo "memory_limit = -1" >> /usr/local/etc/php/conf.d/symfony.ini
RUN apk del .build-deps
RUN apk add gosu --update --no-cache --repository http://dl-3.alpinelinux.org/alpine/edge/testing/ --allow-untrusted
RUN addgroup bar
RUN adduser -D -h /home -s /bin/sh -G bar foo

ADD entrypoint.sh /entrypoint

ENTRYPOINT ["/entrypoint"]




