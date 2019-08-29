# Dockerfile
FROM php:latest

WORKDIR /app

COPY . /app
ADD .env.dev /app/.env

RUN apt update && apt upgrade -y && apt install -y python-pip git zip sudo wget nodejs gnupg

RUN docker-php-ext-install pdo pdo_mysql mysqli

ENV COMPOSER_HOME /composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
ENV PATH ./vendor/bin:/composer/vendor/bin:$PATH
RUN curl -o- -L https://yarnpkg.com/install.sh | bash -s --
ENV PATH /root/.yarn/bin:/root/.config/yarn/global/node_modules/.bin:$PATH

RUN wget http://zlib.net/zlib-1.2.11.tar.gz && \
    tar xvf zlib-1.2.11.tar.gz && \
    cd zlib-1.2.11 && \
    ./configure && \
    echo "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h && \
    make && \
    make install

RUN pip install pyflakes

RUN composer install

RUN yarn && yarn production

EXPOSE 8000

CMD ["php", "artisan", "serve"]
