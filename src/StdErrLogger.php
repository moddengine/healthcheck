<?php

namespace Modd\Engine\HealthCheck;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class StdErrLogger extends AbstractLogger
{
  public const LEVEL_TO_EMOJI = [
    LogLevel::EMERGENCY => '💥',
    LogLevel::ALERT => '🚨',
    LogLevel::CRITICAL => '🔥',
    LogLevel::ERROR => '❌',
    LogLevel::WARNING => '⚠️',
    LogLevel::NOTICE => '👀',
    LogLevel::INFO => '🛈️',
    LogLevel::DEBUG => '🐛',
  ];

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   *
   * @return void
   */
  public function log($level, $message, array $context = [])
  {
    $out = self::LEVEL_TO_EMOJI[$level] . $message;
    if ($context) {
      $context = array_filter(
        $context,
        fn($v, $key) => strpos($key, 'source_') !== 0,
        ARRAY_FILTER_USE_BOTH,
      );
      $data = json_encode($context, JSON_UNESCAPED_SLASHES);
      if (strlen($message) + strlen($data) < 100) {
        $out .= ' ' . $data;
      } else {
        $out .= "\n" .json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
      }
    }
    $out .= PHP_EOL;
    file_put_contents('php://stderr', $out);
  }
}
