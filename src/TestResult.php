<?php

namespace Modd\Engine\HealthCheck;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class TestResult
{
  const ERR_UNKNOWN = 599;
  const ERR_DNS = 598;
  const ERR_SSL = 597;
  const ERR_CONNECT = 596;

  const MODD_IP = [
    '223.27.15.3',
    '223.27.15.4',
    '223.27.15.5',
    '223.27.15.6',
    '223.27.15.7',
    '223.27.15.8',
    '223.27.15.9',
    '223.27.15.10',
    '223.27.15.11',
    '223.27.15.12',
    '223.27.15.13',
    '223.27.15.14',
    '223.27.15.117',
    '223.27.15.118',
    '223.27.15.119',
    '223.27.15.120',
    '223.27.15.121',
    '223.27.15.122',
    '223.27.15.123',
  ];

  public function __construct(
    public DomainInfo $domain,
    public int $status,
    public float $time = 0,
    public int $bytes = 0,
    public string $remoteIp = '',
    public string $error = '',
  )
  {
  }

  static function fromResponse(
    DomainInfo $d,
    ResponseInterface $res,
    TransferStats $stats
  ): self
  {
    $ip = $stats->getHandlerStats()['primary_ip'] ?? '';
    return new self(
      $d,
      $res->getStatusCode(),
      $stats->getTransferTime(),
      $stats->getHandlerStats()['size_download'],
      remoteIp:$ip
    );
  }

  static function fromRequestException(DomainInfo $d, RequestException $e)
  {
    return new self($d, self::ERR_UNKNOWN, error:$e->getMessage());
  }

  public static function fromConnectException(DomainInfo $d, ConnectException $e)
  {
    return new self($d, self::ERR_CONNECT, error:$e->getMessage());
  }

  public static function fromCurlError(DomainInfo $d, TransferStats $stats)
  {
    $ip = $stats->getHandlerStats()['primary_ip'] ?? '';
//    $stats->getHandlerStats();
    return match ($stats->getHandlerErrorData()) {
      CURLE_COULDNT_RESOLVE_HOST =>
        new self($d, self::ERR_DNS, remoteIp: $ip, error: 'Could not resolve host'),
      CURLE_SSL_CACERT =>
        new self($d, self::ERR_DNS, remoteIp: $ip, error: 'Problem with SSL Certificate'),
      default =>
        new self($d, self::ERR_UNKNOWN, remoteIp: $ip, error: 'Unknown Error')
    };
  }

  public function log(LoggerInterface $log)
  {
    $data = [
      'health_status' => $this->status,
      'health_domain' => $this->domain->domain,

    ];
    if($this->time) $data['health_time'] = $this->time * 1000;
    if($this->bytes) $data['health_size'] = $this->bytes;
    if($this->error) $data['health_error'] = $this->error;
    if($this->remoteIp) {
      $data['health_host'] = $this->remoteIp;
      $data['health_modd_host'] = in_array($this->remoteIp, self::MODD_IP, true) ? 1 : 0;
    }

    $log->notice(
      match($this->status) {
        200,203 => "Website {$this->domain->domain} is UP",
        400,401,402,403,404 => "Website {$this->domain->domain} is UP but returing Not Found ({$this->status})",
        500,501,502,503 => "Website {$this->domain->domain} is DOWN with Server Error",
        self::ERR_DNS => "Website {$this->domain->domain} is DOWN with DNS Error",
        self::ERR_SSL => "Website {$this->domain->domain} is DOWN with SSL Error",
        self::ERR_UNKNOWN => "Website {$this->domain->domain} is DOWN",
        default => "Website {$this->domain->domain} is DOWN"
      },
     $data
    );
  }

  public function isRedirect():bool {
    return ($this->status > 300 && $this->status < 400);
  }
}
