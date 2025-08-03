<?php

namespace Drupal\static_content_type\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for static content loading.
 */
class StaticContentTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('static_content_type_loader', [$this, 'staticContentLoader'], [
        'is_safe' => ['html'],
      ]),
      new TwigFunction('static_content_raw', [$this, 'staticContentRaw'], [
        'is_safe' => ['html'],
      ]),
    ];
  }

  /**
   * Twig function to load static content (returns render array).
   *
   * @param string|int $id
   *   The ID of the entity or custom identifier.
   * @param string $option
   *   The rendering option: 'proxied', 'raw', 'hardened', or 'iframe'.
   * @param string $location
   *   The directory location in public files.
   *
   * @return array
   *   A render array with the static content.
   */
  public function staticContentLoader($id, $option = 'hardened', $location = 'static-content-nodes') {
    return static_content_type_loader($id, $option, $location);
  }

  /**
   * Twig function to load raw static content (returns HTML string).
   *
   * @param string|int $id
   *   The ID of the entity or custom identifier.
   * @param string $location
   *   The directory location in public files.
   *
   * @return \Twig\Markup
   *   Raw HTML markup.
   */
  public function staticContentRaw($id, $location = 'static-content-nodes') {
    return static_content_type_loader_raw($id, $location);
  }

}
