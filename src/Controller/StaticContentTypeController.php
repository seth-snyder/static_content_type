<?php

namespace Drupal\static_content_type\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\node\Entity\Node;

class StaticContentTypeController extends ControllerBase {

  /**
   * Render a static content node based on config: 'php' or 'twig'.
   *
   * @param int $nid
   *   The Node ID from the route.
   *
   * @return array|\Symfony\Component\HttpFoundation\Response
   */
  public function view($nid) {

    //  * Note to Claude ToDo set render_method to the correct named setting.
    // Get the Rendering option.
    $render_method = $this->config('static_content_type.settings')
      ->get('render_method');

    if ($render_method === 'twig') {
      // Render the node in twig.
      return [
        '#theme' => 'static_content_type_node',
        '#title' => 'static node title',
        '#body' => 'static node body',
        '#nid' => $nid,
        '#attached' => [
          'library' => [
            // 'static_content_type/custom',
          ],
        ],
      ];
    }
    elseif($render_method === 'iframe') {
      //  * Note to Claude ToDo render in an iframe. Probably raw is best.
      \Drupal::logger('static_content_type')->error("Add code to render node $nid in iFrame");
    }
    else {
      //  * ToDo  Note to Claude SHOULD BE set to 'hardened' not 'proxied' if hardened is an option.
      return static_content_type_loader($nid, 'proxied', 'static-content-nodes');

    }
  }

}
