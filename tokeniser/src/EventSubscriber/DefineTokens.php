<?php

namespace Drupal\tokeniser\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Token\TokensInfoEvent;
use Drupal\core_event_dispatcher\TokenHookEvents;
use Drupal\core_event_dispatcher\ValueObject\Token;
use Drupal\tokeniser\Token\TokenResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DefineTokens implements EventSubscriberInterface {

  public function __construct(private readonly TokenResolver $tokenResolver) {
  }

  public function defineTokeniserDiscoveredTokens(TokensInfoEvent $event): void {
    foreach ($this->tokenResolver->getAvailableTokens() as $type => $tokens) {
      foreach ($tokens as $token => $data) {
        $token = trim($token, '[]');
        [, $name] = explode(':', $token, 2);
        $event->addToken(Token::create($type, $name, $data['title']));
      }
    }
  }

  public static function getSubscribedEvents(): array {
    $events[TokenHookEvents::TOKEN_INFO][] = ['defineTokeniserDiscoveredTokens'];

    return $events;
  }

}