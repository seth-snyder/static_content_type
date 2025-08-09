<?php

namespace Drupal\static_content_type\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Component\Serialization\Yaml;
use Drupal\node\Entity\Node;

/**
 * Controller for static content type.
 */
class StaticContentTypeController extends ControllerBase {

  /**
   * Render a static content node based on config: 'php', 'twig', or 'iframe'.
   *
   * @param int $nid
   *   The Node ID from the route.
   *
   * @return array|\Symfony\Component\HttpFoundation\Response
   *   Description for the @return value is missing.
   */
  public function view($nid) {

    // Get the rendering method from cleaned config.
    $config = $this->config('static_content_type.settings');
    $render_method = $config->get('render_method') ?: 'php';

    if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
      \Drupal::logger('static_content_type')->notice("Controller: nid=@nid, render_method=@method", [
        '@nid' => $nid,
        '@method' => $render_method,
      ]);
    }

    // Check for per-directory override.
    $override_options = $this->getDirectoryOptions($nid, 'static-content-nodes');
    if (!empty($override_options['render_method'])) {
      $render_method = $override_options['render_method'];
      // Override found.
      if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
        \Drupal::logger('static_content_type')->notice("Override found: @method", ['@method' => $render_method]);
      }
    }

    switch ($render_method) {
      case 'twig':
        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("Rendering via Twig template");
        }

        // Render using Twig template.
        return [
          '#theme' => 'static_content_type_node',
          '#title' => $this->getNodeTitle($nid),
          '#body' => 'Static content rendered via Twig',
          '#nid' => $nid,
          '#location' => 'static-content-nodes',
        ];

      case 'iframe':
        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("Rendering via iFrame");
        }

        // Render in iframe - find the best file using precedence.
        $file_info = $this->findBestContentFile($nid, 'static-content-nodes');

        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("File info: @info", [
            '@info' => $file_info ? print_r($file_info, TRUE) : 'NOT FOUND',
          ]);
        }

        if ($file_info) {
          $base_url = \Drupal::request()->getSchemeAndHttpHost();
          $iframe_url = $base_url . '/sites/default/files/' . $file_info['relative_path'];

          if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
            \Drupal::logger('static_content_type')->notice("iFrame URL: @url", ['@url' => $iframe_url]);
          }

          return [
            '#theme' => 'static_content_iframe',
            '#src' => $iframe_url,
            '#id' => $nid,
            '#location' => 'static-content-nodes',
          ];
        }
        else {
          \Drupal::messenger()->addError($this->t('Static content file not found for node @nid', ['@nid' => $nid]));
          return ['#markup' => '<p>Content not found</p>'];
        }

      case 'php':
      default:
        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("Rendering via PHP");
        }
        // Use the enhanced loader with directory precedence.
        return $this->loadStaticContentWithPrecedence($nid, 'static-content-nodes');
    }
  }

  /**
   * Replace the loadStaticContentWithPrecedence method in your controller.
   */
  private function loadStaticContentWithPrecedence($id, $location) {
    $file_info = $this->findBestContentFile($id, $location);

    if (!$file_info) {
      if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
        \Drupal::logger('static_content_type')->error("No content file found for @id in @location", [
          '@id' => $id,
          '@location' => $location,
        ]);
      }
      return [
        '#markup' => '<div class="messages messages--error">Static content not found</div>',
      ];
    }

    // Get processing type from directory or config override.
    $processing_type = $this->getProcessingType($file_info['subdirectory'], $id, $location);

    if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
      \Drupal::logger('static_content_type')->notice("Loading @id with processing @type from @file (@subdir)", [
        '@id' => $id,
        '@type' => $processing_type,
        '@file' => $file_info['relative_path'],
        '@subdir' => $file_info['subdirectory'],
      ]);
    }

    // Use the enhanced loader with subdirectory info.
    return static_content_type_loader_enhanced_v2(
      $id,
      $processing_type,
      $location,
      $file_info['full_path'],
      // Pass the subdirectory for path processing.
      $file_info['subdirectory']
    );
  }

  /**
   * Find the best content file using directory precedence.
   */
  private function findBestContentFile($id, $location) {
    $file_system = \Drupal::service('file_system');
    $public_path = $file_system->realpath('public://');
    $base_path = $public_path . '/' . $location . '/' . $id;

    // Directory precedence order.
    $precedence = ['dist', 'build', 'raw', 'proxied', 'hardened', 'src', ''];

    if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
      \Drupal::logger('static_content_type')->notice("Searching for content in @path", ['@path' => $base_path]);
    }

    foreach ($precedence as $subdir) {
      $check_path = $subdir ? $base_path . '/' . $subdir : $base_path;
      $index_file = $check_path . '/index.html';

      if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
        \Drupal::logger('static_content_type')->notice("Checking: @file", ['@file' => $index_file]);
      }

      if (file_exists($index_file)) {
        $result = [
          'full_path' => $index_file,
          'subdirectory' => $subdir ?: 'root',
          'relative_path' => $location . '/' . $id . ($subdir ? '/' . $subdir : '') . '/index.html',
        ];

        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("Found content in @subdir", ['@subdir' => $subdir ?: 'root']);
        }
        return $result;
      }
    }

    if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
      \Drupal::logger('static_content_type')->error("No index.html found in any precedence directory for @id", ['@id' => $id]);
    }

    return NULL;
  }

  /**
   * Get processing type based on subdirectory and config.
   */
  private function getProcessingType($subdirectory, $id, $location) {
    // Check for per-directory override first.
    $override_options = $this->getDirectoryOptions($id, $location);
    if (!empty($override_options['processing_type'])) {
      if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
        \Drupal::logger('static_content_type')->notice("Using override processing: @type", [
          '@type' => $override_options['processing_type'],
        ]);
      }
      return $override_options['processing_type'];
    }

    // Use subdirectory-based processing.
    switch ($subdirectory) {
      case 'raw':
        return 'raw';

      case 'proxied':
      case 'src':
        return 'proxied';

      case 'dist':
      case 'build':
        // Bundled content - use config default.
        $default = $this->config('static_content_type.settings')->get('default_processing') ?: 'hardened';
        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("Bundled content using default: @type", ['@type' => $default]);
        }
        return $default;

      case 'hardened':
      case 'root':
      default:
        return 'hardened';
    }
  }

  /**
   * Load directory-specific options from stc.options.yml.
   */
  private function getDirectoryOptions($id, $location) {
    $file_system = \Drupal::service('file_system');
    $public_path = $file_system->realpath('public://');
    $options_file = $public_path . '/' . $location . '/' . $id . '/stc.options.yml';

    if (file_exists($options_file)) {
      try {
        $yaml_content = file_get_contents($options_file);
        $options = Yaml::decode($yaml_content);

        if (defined('STATIC_CONTENT_TYPE_DEBUG')) {
          \Drupal::logger('static_content_type')->notice("Found options file for @id: @options", [
            '@id' => $id,
            '@options' => print_r($options, TRUE),
          ]);
        }

        return $options;
      }
      catch (\Exception $e) {
        \Drupal::logger('static_content_type')->error('Error parsing stc.options.yml for @id: @error', [
          '@id' => $id,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    return [];
  }

  /**
   * Get node title for display.
   */
  private function getNodeTitle($nid) {
    $node = Node::load($nid);
    return $node ? $node->getTitle() : 'Static Content ' . $nid;
  }

}
