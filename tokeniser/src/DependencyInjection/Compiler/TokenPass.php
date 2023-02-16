<?php

namespace Drupal\tokeniser\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TokenPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container_builder): void {
    $token_definitions = array_filter(
      $container_builder->findTaggedServiceIds('tokeniser.token'),
      fn(array $tag): bool => isset($tag['token'])
    );

    $tokens = [];
    foreach ($token_definitions as $id => $tags) {
      foreach ($tags as $tag) {
        $tag_type = $tag['type'];
        $tag_token = $tag['token'];
        $tag_title = $tag['title'];

        $tokens[$tag_type][$tag_token] = [
          'id' => $id,
          'title' => $tag_title,
        ];
      }
    }

    $definition = $container_builder->getDefinition('tokeniser.token_resolver');
    $definition->addArgument($tokens);
  }

}