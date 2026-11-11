#!/bin/sh
set -eu

# Only substitute deployment settings, preserving Nginx's own $uri/$args variables.
envsubst '${SITE_PATH_PREFIX} ${ASSET_CACHE_TTL}' \
    < /etc/nginx/templates/site.conf.template \
    > /etc/nginx/sites-available/default
nginx -t
php-fpm -t
exec "$@"
