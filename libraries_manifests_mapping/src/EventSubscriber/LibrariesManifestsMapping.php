<?php

namespace Drupal\libraries_manifests_mapping\EventSubscriber;

use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\core_event_dispatcher\Event\Theme\LibraryInfoAlterEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LibrariesManifestsMapping implements EventSubscriberInterface {

  private array $manifests = [];

  public function __construct(
    private readonly ThemeExtensionList $themeExtensionList
  ) {
  }

  public function replaceLibraryCssSourceByManifestMapping(
    LibraryInfoAlterEvent $event
  ): void {
    $extension = $event->getExtension();

    $libraries =& $event->getLibraries();
    foreach ($libraries as $library_name => $library) {
      if (!empty($library['css'])) {
        foreach ($library['css'] as $type => $assets) {
          $libraries[$library_name]['css'][$type] = $this->transformAssets(
            $assets,
            $extension
          );
        }
      }
    }
  }

  public function replaceLibraryJsSourceByManifestMapping(
    LibraryInfoAlterEvent $event
  ): void {
    $extension = $event->getExtension();

    $libraries =& $event->getLibraries();
    foreach ($libraries as $library_name => $library) {
      if (!empty($library['js'])) {
        $libraries[$library_name]['js'] = $this->transformAssets(
          $library['js'],
          $extension
        );
      }
    }
  }

  private function transformAssets(array $assets, string $extension): array {
    foreach ($assets as $asset_source => $options) {
      if (!isset($options['manifest'])) {
        continue;
      }

      $manifest = $this->manifests["$extension:{$options['manifest']}"]
        ??= $this->getManifestContents($extension, $options['manifest']);

      $asset_destination = $manifest[$asset_source] ?? '';
      $asset_destination = trim($asset_destination, '/');
      if (empty($asset_destination)) {
        continue;
      }

      unset($options['manifest']);
      unset($assets[$asset_source]);
      $assets[$asset_destination] = $options;
    }

    return $assets;
  }

  private function getManifestContents(
    string $extension,
    string $manifest
  ): array {
    try {
      $path = realpath($this->themeExtensionList->getPath($extension));
      $manifest_contents = file_get_contents("$path/$manifest");

      return json_decode($manifest_contents, TRUE);
    } catch (\Exception) {
      return [];
    }
  }

  public static function getSubscribedEvents(): array {
    $events[ThemeHookEvents::LIBRARY_INFO_ALTER] = [
      ['replaceLibraryCssSourceByManifestMapping'],
      ['replaceLibraryJsSourceByManifestMapping'],
    ];

    return $events;
  }

}
