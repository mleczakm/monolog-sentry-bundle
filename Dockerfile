FROM php:cli-alpine

WORKDIR /app

CMD tail -f /dev/null

COPY --from=composer:1.10.5 /usr/bin/composer /usr/bin/composer

ENV BROWSCAP_DIR /usr/local/etc/php/browsecap

ADD http://browscap.org/stream?q=Lite_PHP_BrowsCapINI $BROWSCAP_DIR/browscap.ini
RUN echo "browscap='$BROWSCAP_DIR/browscap.ini'" >> /usr/local/etc/php/conf.d/browsecap.ini && \
    chmod 755 -R $BROWSCAP_DIR
