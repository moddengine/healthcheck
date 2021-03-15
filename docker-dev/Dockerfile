
RUN set -xe; \
  apt-get update && \
  apt-get upgrade -y && \
  rm -rf /var/lib/apt/lists/* && \
  curl -fsSLO "$SUPERCRONIC_URL" && \
  echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - && \
  chmod +x "$SUPERCRONIC" && \
  mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" && \
  ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic && \
  mkdir /healthcheck/ && \
  chmod 775 /healthcheck

WORKDIR /healthcheck/

COPY * /healthcheck/
CMD supercronic -overlapping -debug /healthcheck/crontab
