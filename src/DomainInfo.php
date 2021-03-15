<?php


namespace Modd\Engine\HealthCheck;


class DomainInfo
{
  public function __construct(
    public AccountInfo $account,
    public string $domain,
    public string $type,
    public string $ip,
  )
  {
  }
}
