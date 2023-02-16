<?php

namespace Drupal\tokeniser;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\tokeniser\DependencyInjection\Compiler\TokenPass;

final class TokeniserServiceProvider extends ServiceProviderBase {

  public function register(ContainerBuilder $container): void {
    $container->addCompilerPass(new TokenPass());
  }

}