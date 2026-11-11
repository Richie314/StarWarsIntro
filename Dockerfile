FROM php:8.4-fpm
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && \
    apt-get install -y --no-install-recommends nginx supervisor gettext-base unzip && \
    rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install -j$(nproc) mysqli && \
    pecl install xdebug && docker-php-ext-enable xdebug

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-site.conf
COPY docker/nginx.conf.template /etc/nginx/templates/site.conf.template
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/starwars-entrypoint
RUN mkdir -p /run/php && chmod +x /usr/local/bin/starwars-entrypoint

WORKDIR /starwars
COPY --chown=www-data:www-data composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
COPY --chown=www-data:www-data . .

ENV SITE_PATH_PREFIX="" ASSET_CACHE_TTL="off"
EXPOSE 80
HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=3 \
    CMD php -r '$url = "http://127.0.0.1" . getenv("SITE_PATH_PREFIX") . "/"; exit(@file_get_contents($url) === false ? 1 : 0);'
ENTRYPOINT ["starwars-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
