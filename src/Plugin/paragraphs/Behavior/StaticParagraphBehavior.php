<?php

namespace Drupal\static_content_type\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Provides a behavior for static paragraph rendering.
 *
 * @ParagraphsBehavior(
 *   id = "static_paragraph_behavior",
 *   label = @Translation("Static Paragraph Behavior"),
 *   description = @Translation("Renders static content from the public files directory."),
 *   weight = 0,
 * )
 */
class StaticParagraphBehavior extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    $id = $paragraph->id();
    
    if ($id) {
      $static_content = static_content_type_loader($id, 'proxied', 'static-content-paragraphs');
      $build['static_content'] = $static_content;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['static_paragraph_info'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p><strong>Static Paragraph ID:</strong> @id</p><p>Place your HTML content in: <code>static-content-paragraphs/@id/index.html</code></p>', 
        ['@id' => $paragraph->id() ?: $this->t('(will be assigned after saving)')]),
    ];
    
    return $form;
  }

}
