<?php

namespace Drupal\facets_views_area\EventSubscriber;

use Drupal\views_event_dispatcher\Event\Views\ViewsDataEvent;
use Drupal\views_event_dispatcher\ViewsHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DefineFacetsViewsArea implements EventSubscriberInterface {

  public function provideViewsData(ViewsDataEvent $event): void {
    $data['views']['facets'] = [
      'title' => t('Facets'),
      'help' => t('Adds the available facets in an area.'),
      'area' => ['id' => 'facets'],
    ];

    $event->addData($data);
  }

  public static function getSubscribedEvents(): array {
    $events[ViewsHookEvents::VIEWS_DATA][] = ['provideViewsData'];

    return $events;
  }

}
