# Dockerfile
FROM php:latest

RUN mkdir -p /usr/app
WORKDIR /usr/app

ADD composer.json /usr/app/composer.json
ADD .env.dev /usr/app/.env

RUN apt update && apt upgrade -y && apt install -y python-pip git zip sudo wget

RUN curl --silent --show-error https://getcomposer.org/installer | php

#RUN wget http://zlib.net/zlib-1.2.11.tar.gz && \
#    tar xvf zlib-1.2.11.tar.gz && \
#    cd zlib-1.2.11 && \
#    ./configure && \
#    echo -e "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h && \
#    make && \
#    make install

RUN pip install pyflakes

RUN mkdir -p /usr/app/vendor && chmod -R 777 vendor
RUN chown -R www-data:www-data /usr/app

USER 1000

RUN /usr/app/composer.phar install

RUN php artisan key:generate
RUN php artisan migrate
RUN yarn && yarn production

CMD ["php", "artisan", "serve"]
