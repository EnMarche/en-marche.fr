FROM ubuntu:xenial-20210429

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8" \
    TERM="xterm" \
    DEBIAN_FRONTEND="noninteractive" \
    COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app

RUN apt-get update -q && \
    apt-get install -qy software-properties-common language-pack-en-base && \
    export LC_ALL=en_US.UTF-8 && \
    export LANG=en_US.UTF-8 && \
    add-apt-repository -y ppa:ondrej/php && \
    apt-get update -q && \
    apt-get install --no-install-recommends -qy \
        ca-certificates \
        cron \
        curl \
        nano \
        nginx \
        git \
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
        php7.3-opcache \
        php7.3-pdo \
        php7.3-phar \
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
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    sed -i -e "s/<policy domain=\"coder\" rights=\"none\" pattern=\"PDF\" \/>/<policy domain=\"coder\" rights=\"read|write\" pattern=\"PDF\" \/>/g" /etc/ImageMagick-6/policy.xml && \
    cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

COPY . /app

COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

RUN chmod 0444 gcloud-service-key.json && \
    mkdir /run/php && \
    mkdir var && \
    APP_ENV=prod composer install --optimize-autoloader --no-interaction --no-ansi --no-dev && \
    APP_ENV=prod bin/console cache:clear --no-warmup && \
    APP_ENV=prod bin/console cache:warmup && \
    chown -R www-data:www-data var && \
    cp docker/prod/php.ini /etc/php/7.3/cli/conf.d/50-setting.ini && \
    mv docker/prod/php.ini /etc/php/7.3/fpm/conf.d/50-setting.ini && \
    rm -rf /etc/php/7.3/fpm/pool.d/www.conf && \
    mv docker/prod/pool.conf /etc/php/7.3/fpm/pool.d/www.conf && \
    rm -rf /etc/nginx/nginx.conf && \
    mv docker/prod/nginx.conf /etc/nginx/nginx.conf && \
    mv docker/prod/supervisord.conf /etc/supervisor/conf.d/ && \
    rm -rf docker composer.lock

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
