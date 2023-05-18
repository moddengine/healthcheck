<?php

namespace Modd\Engine\HealthCheck;

use Gelf\Transport\SslOptions;

class GraylogSSLOptions extends SslOptions
{
  public $keyFile = null;
  public function __construct(string $caFile = null, string $keyFile = null)
  {
    $this->caFile = $caFile;
    $this->keyFile = $keyFile;
  }

  public function toStreamContext($serverName = null)
  {
    $sslContext = parent::toStreamContext($serverName)['ssl'];
    if ($this->keyFile !== null) {
      $sslContext['local_cert'] = $this->keyFile;
    }
    return ['ssl' => $sslContext];
  }
}
