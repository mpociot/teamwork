<?php

namespace Mpociot\Teamwork\Traits;

use Illuminate\Container\Container;

trait DetectNamespace
{
  public function getAppNamespace()
  {
    return Container::getInstance()->getNamespace();
  }
}
