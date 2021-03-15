<?php


namespace Modd\Engine\HealthCheck;


class AccountInfo
{

  public function __construct(public string $domain,
                              public string $user,
                              public bool $suspended)
  {
  }

}
