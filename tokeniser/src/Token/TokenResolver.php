<?php

namespace Drupal\tokeniser\Token;

use Drupal\Component\Render\MarkupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class TokenResolver {

  public function __construct(
    private readonly ContainerInterface $container,
    private readonly array $tokens = []
  ) {
  }

  public function getTokenValue(
    string $type,
    string $name,
    array $data = [],
    array $options = []
  ): MarkupInterface|string|NULL {
    return $this->getToken($type, $name)
      ?->getValue($data, $options);
  }

  public function getAvailableTokens(): array {
    return $this->tokens;
  }

  private function getToken(string $type, string $name): ?TokenInterface {
    $token_service_id = $this->tokens[$type][$name]['id'] ?? NULL;

    return !is_null($token_service_id)
      && $this->container->has($token_service_id)
        ? $this->container->get($token_service_id)
        : NULL;
  }

}