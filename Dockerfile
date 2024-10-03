FROM php:8.3-cli-alpine as sio_test
RUN apk add --no-cache \
    git \
    zip \
    bash \
    postgresql-dev \
    autoconf \
    automake \
    make \
    gcc \
    libc-dev \
    linux-headers \
    curl-dev \
    libmemcached-dev \
    gd \
    libxml2-dev
RUN docker-php-ext-install pdo_pgsql
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Setup php app user
ARG USER_ID=1000
RUN adduser -u 1000 -D -H app
USER app

ENV PHP_IDE_CONFIG 'serverName=docker'

COPY --chown=app . /app
WORKDIR /app

EXPOSE 8337

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]
