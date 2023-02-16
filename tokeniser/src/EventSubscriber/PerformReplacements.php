<?php

namespace Drupal\tokeniser\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Token\TokensReplacementEvent;
use Drupal\core_event_dispatcher\TokenHookEvents;
use Drupal\tokeniser\Token\TokenResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PerformReplacements implements EventSubscriberInterface {

  public function __construct(private readonly TokenResolver $tokenResolver) {
  }

  public function applyTokeniserReplacementValues(TokensReplacementEvent $event): void {
    foreach ($event->getTokens() as $token_name) {
      $token_value = $this->tokenResolver->getTokenValue(
        $event->getType(),
        $token_name,
        $event->getRawData(),
        $event->getOptions()
      );

      if (empty($token_value)) {
        continue;
      }

      $event->setReplacementValue(
        $event->getType(),
        $token_name,
        $token_value
      );
    }
  }

  public static function getSubscribedEvents(): array {
    $events[TokenHookEvents::TOKEN_REPLACEMENT][] = ['applyTokeniserReplacementValues'];

    return $events;
  }

}