WHM/CPanel Healthcheck Script
=============================

Accesses account list from cpanel, and does a HTTP get to each domain hosted int the cpanel and checks that it returns
a non-empty page with a successful HTTP status, over an SSL connection.

Reports errors into graylog to allow for alerting and creating of dashboards

#### Config Files

`/secrets/secrets.json`
```json
{
  "cpanelapi": {
    "user": "root",
    "host": "whm.hostname.net",
    "token": "<<API Token with account list access>>"
  },
  "graylog": {
    "host": "graylog",
    "port": 12201
  },
  "hostedip": [
    "100.0.0.1",
    "100.0.0.2"
  ]
}
```

Also include `ca.pem` with the ssl certificate for graylog server and `key.pem` with the combined client private key and
client cer[Dockerfile](Dockerfile)tificate in the `/secrets` directory.

Currently in the crontab file the healthcheck is run every 15 minutes

To host service you can use included docker-compose.yml file.

`docker compose up -d`

