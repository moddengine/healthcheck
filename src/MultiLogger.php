<?php

namespace Modd\Engine\HealthCheck;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class MultiLogger implements LoggerInterface
{

  use LoggerTrait;

  /**
   * @param LoggerInterface[] $loggers
   */
  public function __construct(public array $loggers)
  {
    assert(count($this->loggers) > 0);
  }


  public function log($level, $message, array $context = array())
  {
    $ex = null;
    foreach ($this->loggers as $l) {
      try {
        $l->log($level, $message, $context);
      } catch (\Exception $e) {
        $ex = $e;
      }
    }
    if ($ex !== null)
      throw $e;
  }
}
