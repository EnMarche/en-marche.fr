FROM ubuntu:xenial-20210416

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8" \
    TERM="xterm" \
    DEBIAN_FRONTEND="noninteractive" \
    NODE_VERSION=10.20.1 \
    GOSU_VERSION=1.11 \
    PHP_SECURITY_CHECHER_VERSION=1.0.0 \
    COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app

RUN apt-get update -q && \
    apt-get install -qy software-properties-common language-pack-en-base build-essential && \
    export LC_ALL=en_US.UTF-8 && \
    export LANG=en_US.UTF-8 && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update -q && \
    apt-get install --no-install-recommends -qy \
        ca-certificates \
        cron \
        curl \
        nano \
        vim \
        nginx \
        git \
        graphviz \
        mysql-client \
        php7.3 \
        php7.3-bcmath \
        php7.3-common \
        php7.3-curl \
        php7.3-dom \
        php7.3-fpm \
        php7.3-gd \
        php7.3-iconv \
        php7.3-intl \
        php7.3-json \
        php7.3-mbstring \
        php7.3-mysql \
        php7.3-pdo \
        php7.3-phar \
        php7.3-sqlite \
        php7.3-xdebug \
        php7.3-xml \
        php7.3-zip \
        php7.3-amqp \
        php7.3-apcu \
        php7.3-uuid \
        php7.3-imagick \
        ghostscript \
        supervisor \
        tzdata \
        wget \
        wkhtmltopdf && \
    # Disable XDEBUG by default
    phpdismod xdebug && \
    # Clean
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    # Config ImageMagick lib
    sed -i -e "s/<policy domain=\"coder\" rights=\"none\" pattern=\"PDF\" \/>/<policy domain=\"coder\" rights=\"read|write\" pattern=\"PDF\" \/>/g" /etc/ImageMagick-6/policy.xml && \
    # Config TimeZone
    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone && \
    # Install Node & npm
    curl -L -o /tmp/nodejs.tar.gz https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.gz && \
    tar xfvz /tmp/nodejs.tar.gz -C /usr/local --strip-components=1 && \
    rm -f /tmp/nodejs.tar.gz && \
    npm install yarn -g && \
    # Install Symfony security checker
    curl -L https://github.com/fabpot/local-php-security-checker/releases/download/v${PHP_SECURITY_CHECHER_VERSION}/local-php-security-checker_${PHP_SECURITY_CHECHER_VERSION}_linux_$(dpkg --print-architecture) --output /usr/local/bin/local-php-security-checker && \
    chmod +x /usr/local/bin/local-php-security-checker && \
    mkdir /run/php

RUN curl -L https://github.com/tianon/gosu/releases/download/${GOSU_VERSION}/gosu-$(dpkg --print-architecture) --output /usr/local/bin/gosu && \
    chmod +x /usr/local/bin/gosu && \
    addgroup enmarche && \
    adduser --home=/home/enmarche --shell=/bin/bash --ingroup=enmarche --disabled-password --quiet enmarche

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

COPY php.ini /etc/php/7.3/cli/conf.d/50-setting.ini
COPY php.ini /etc/php/7.3/fpm/conf.d/50-setting.ini
COPY pool.conf /etc/php/7.3/fpm/pool.d/www.conf
COPY nginx.conf /etc/nginx/nginx.conf
COPY symfony.conf /etc/nginx/symfony.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY entrypoint.sh /usr/local/bin/

ENTRYPOINT ["entrypoint.sh"]
