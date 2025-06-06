#!/bin/sh

set -e

# Replace ServerAlias with actual domain
if [ -n "$SITE_DOMAIN" ]; then

    # Replace the ServerAlias line
    sed -i "s/ServerAlias x/ServerAlias $SITE_DOMAIN/" /etc/apache2/sites-enabled/starwars.conf

    echo "Updated ServerAlias to '$SITE_DOMAIN' in /etc/apache2/sites-enabled/starwars.conf"
else
    echo "Environment variable SITE_DOMAIN is not set or empty."
    echo "Running in debug mode (site will be accessible at localhost only)."
fi

# su www-data
apache2ctl -D FOREGROUND