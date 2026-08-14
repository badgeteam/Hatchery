# SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
# SPDX-License-Identifier: MIT

# Dockerfile
FROM php:8.4-cli

WORKDIR /app

COPY . /app
COPY .env.dev /app/.env

RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    python3-pip pyflakes3 git zip unzip sudo wget curl gnupg ca-certificates \
    zlib1g-dev libzip-dev libicu-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    libonig-dev libgmp-dev iverilog yosys arachne-pnr fpga-icestorm \
    && ln -sf /usr/bin/pyflakes3 /usr/local/bin/pyflakes \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mysqli pcntl zip intl gd mbstring gmp exif

ENV COMPOSER_HOME=/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV PATH=./vendor/bin:/composer/vendor/bin:$PATH

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# The MAX_WBITS tweak makes minigzip produce streams the badges can inflate.
# Retry and verify: a bad response from the mirror used to be unpacked blindly,
# which failed later with "not in gzip format" instead of saying what went wrong.
ENV ZLIB_VERSION=1.2.11
ENV ZLIB_SHA256=c3e5e9fdd5004dcb542feda5ee4f0ff0744628baf8ed2dd5d66f8ca1197cb1a1
RUN curl -fsSL --retry 5 --retry-delay 3 --retry-all-errors \
        -o zlib.tar.gz "https://zlib.net/fossils/zlib-${ZLIB_VERSION}.tar.gz" && \
    echo "${ZLIB_SHA256}  zlib.tar.gz" | sha256sum -c - && \
    tar xf zlib.tar.gz && \
    cd "zlib-${ZLIB_VERSION}" && \
    ./configure && \
    echo "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h && \
    make && \
    cp minigzip /usr/local/bin/ && \
    cd .. && rm -rf "zlib-${ZLIB_VERSION}" zlib.tar.gz

RUN composer install
RUN chmod -R 777 bootstrap/cache storage

RUN npm ci && npm run build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host", "0.0.0.0"]
