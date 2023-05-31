<?php

namespace Modd\Engine\HealthCheck;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class HealthCheck
{
  private Client $http;

  private Request $baseReq;
  private LoggerInterface $log;

  static array $hostedIps = [];

  /** @var \stdClass */
  private $config;

  public function __construct(Client $http, LogFactory $logFactory)
  {
    $this->config = json_decode(
      file_get_contents(__DIR__ . '/../secrets/secrets.json'),
    );
    $this->http = $http;
    $this->log = new MultiLogger([
        $logFactory->default($this->config->graylog ?? new \stdClass()),
//        new StdErrLogger()
      ]);
    self::$hostedIps = $this->config->hostedip;
    $this->baseReq = new Request(
      'GET',
      new Uri("https://{$this->config->cpanelapi->host}:2087/"),
      [
        'Authorization' => "whm {$this->config->cpanelapi->user}:{$this->config->cpanelapi->token}",
      ],
    );
  }

  function makeRequest(
    string $method,
    string $path,
    string $query
  ): RequestInterface
  {
    return $this->baseReq->withMethod($method)->withUri(
      $this->baseReq
        ->getUri()
        ->withPath($path)
        ->withQuery($query),
    );
  }

  function getWhm(string $path, string $query): \stdClass
  {
    return json_decode(
      $this->http
        ->send($this->makeRequest('GET', $path, $query))
        ->getBody()
        ->getContents(),
    );
  }

  /**
   * @var AccountInfo[]
   */
  public array $accounts = [];

  /** @var DomainInfo[] */
  public array $domains = [];

  public function run(): void
  {
    $accountList = $this->getWhm(
      'json-api/listaccts',
      'api.version=1&want=user,domain,suspended',
    )->data->acct;
    foreach ($accountList as $acct) {
      $this->accounts[$acct->user] = new AccountInfo(
        $acct->domain,
        $acct->user,
        (bool)$acct->suspended,
      );
    }

    $domainList = $this->getWhm('json-api/get_domain_info', 'api.version=1')
      ->data->domains;
    foreach ($domainList as $domInfo) {
      if (!$this->accounts[$domInfo->user]) {
        var_dump(array_keys($this->accounts), $domInfo);
        die();
      }
      if (!$this->accounts[$domInfo->user]->suspended) {
        $this->domains[$domInfo->domain] = new DomainInfo(
          $this->accounts[$domInfo->user],
          $domInfo->domain,
          $domInfo->domain_type,
          $domInfo->ipv4,
        );
      }
    }
    usort(
      $this->domains,
      fn(DomainInfo $a, DomainInfo $b) => strcmp($a->domain, $b->domain),
    );
    foreach ($this->domains as $d) {
      $this->testDomain($d);
    }
  }

  private function testDomain(DomainInfo $d): void
  {
    try {
      $res = $this->http->get('https://' . $d->domain . '/', [
        RequestOptions::ALLOW_REDIRECTS => true,
        RequestOptions::TIMEOUT => 10,
        RequestOptions::ON_STATS => function (TransferStats $stats) use ($d) {
          $res = null;
          if ($stats->hasResponse()) {
            $res = TestResult::fromResponse($d, $stats->getResponse(), $stats);
          } else {
            $res = TestResult::fromCurlError($d, $stats);
          }
          if ($res && !$res->isRedirect()) {
            $res->log($this->log);
          }
        },
      ]);
    } catch (RequestException $e) {
      //      TestResult::fromRequestException($d, $e)->log($this->log);
    } catch (ConnectException $e) {
      //      TestResult::fromConnectException($d, $e)->log($this->log);
    }
  }
}
