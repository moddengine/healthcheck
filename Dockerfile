FROM php:8.0-fpm-buster as php-dev
RUN  set -xe; \
  apt-get update && \
  apt-get upgrade -y && \
  apt-get install -y \
          libicu-dev \
          libonig-dev \
          libzip-dev \
          zlib1g-dev && \
  docker-php-ext-install intl \
                         mbstring \
                         opcache \
                         zip

FROM php-dev as php-composer
COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer
COPY composer.* /tmp/
RUN cd /tmp && composer install --no-dev -o


FROM php-dev
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=048b95b48b708983effb2e5c935a1ef8483d9e3e

RUN set -xe; \
  curl -fsSLO "$SUPERCRONIC_URL" && \
  echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - && \
  chmod +x "$SUPERCRONIC" && \
  mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" && \
  ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic && \
  mkdir /healthcheck/ && \
  chmod 775 /healthcheck

WORKDIR /healthcheck/

COPY --from=php-composer /tmp/vendor /healthcheck/vendor

COPY crontab /healthcheck/
COPY run.php /healthcheck/
COPY src/ /healthcheck/src
CMD supercronic -overlapping -debug /healthcheck/crontab
