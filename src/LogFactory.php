<?php

namespace Modd\Engine\HealthCheck;

use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Psr\Log\LoggerInterface;

class LogFactory
{
  public function default(): LoggerInterface
  {
    $host = $_SERVER['GRAYLOG_HOST'] ?? '127.0.0.1';
    return new Logger(new Publisher(new UdpTransport($host)), null, [
      'healthcheck' => 1,
    ]);
  }
}
