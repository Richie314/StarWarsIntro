FROM php:8.4-apache

ARG INSTALL_DIR=/starwars
RUN apt update && \
    apt install -y --no-install-recommends --upgrade \
        apache2-utils && \
    apt clean && \
    rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install -j$(nproc) \
    mysqli

COPY apache.conf /etc/apache2/sites-enabled/starwars.conf
RUN a2enmod rewrite
RUN sed -i "s|INSTALL_DIR|${INSTALL_DIR}|g" /etc/apache2/sites-enabled/starwars.conf
# RUN chmod 777 /etc/apache2/sites-enabled/starwars.conf

RUN mkdir -p ${INSTALL_DIR}
WORKDIR ${INSTALL_DIR}

EXPOSE 80
COPY --chown=www-data:www-data . .
RUN chmod +x entrypoint.sh
# USER www-data
ENTRYPOINT [ "./entrypoint.sh" ]