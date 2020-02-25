# Dockerfile
FROM php:7.4

WORKDIR /app

COPY . /app
COPY .env.dev /app/.env

RUN apt update && apt upgrade -y && apt install -y python-pip git zip sudo wget nodejs gnupg \
    zlib1g-dev libzip-dev libicu-dev libpng-dev libonig-dev libgmp-dev

RUN docker-php-ext-install pdo pdo_mysql mysqli pcntl zip intl gd mbstring gmp exif

ENV COMPOSER_HOME /composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
ENV PATH ./vendor/bin:/composer/vendor/bin:$PATH
RUN curl -o- -L https://yarnpkg.com/install.sh | bash -s --
ENV PATH /root/.yarn/bin:/root/.config/yarn/global/node_modules/.bin:$PATH

RUN curl -O http://zlib.net/zlib-1.2.11.tar.gz && \
    tar xvf zlib-1.2.11.tar.gz && \
    cd zlib-1.2.11 && \
    ./configure && \
    echo "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h && \
    make && \
    make install

RUN pip install pyflakes==2.1.1

RUN composer install
RUN chmod -R 777 bootstrap/cache storage

RUN yarn && yarn production

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host", "0.0.0.0"]
