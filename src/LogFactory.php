<?php

namespace Modd\Engine\HealthCheck;

use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\HttpTransport;
use Gelf\Transport\SslOptions;
use Gelf\Transport\UdpTransport;
use Psr\Log\LoggerInterface;

class LogFactory
{
  public function default(\stdClass $config): LoggerInterface
  {
    $host = $_SERVER['GRAYLOG_HOST'] ?? '127.0.0.1';
    $keyFile = __DIR__ . '/../secrets/key.pem';
    $caPem = __DIR__ . '/../secrets/ca.pem';
    $ssl = new GraylogSSLOptions($caPem, $keyFile);
    return new Logger(
      new Publisher(
        new HttpTransport(
          $config->host ?? 'localhost',
          $config->port ?? 12201,
          '/gelf',
          $ssl,
        ),
      ),
      null,
      [
        'healthcheck' => 1,
      ],
    );
  }
}
