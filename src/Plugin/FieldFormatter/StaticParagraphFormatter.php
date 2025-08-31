<?php

namespace Drupal\static_content_type\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'static_paragraph' formatter.
 *
 * @FieldFormatter(
 *   id = "static_paragraph",
 *   label = @Translation("Static Paragraph"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class StaticParagraphFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'view_mode' => 'default',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('View mode'),
      '#options' => \Drupal::service('entity_display.repository')->getViewModeOptions('paragraph'),
      '#default_value' => $this->getSetting('view_mode'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('View mode: @view_mode', ['@view_mode' => $this->getSetting('view_mode')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($item->entity && $item->entity->bundle() === 'static_paragraph') {
        $paragraph_id = $item->entity->id();

        $elements[$delta] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['static-paragraph-wrapper'],
            'data-paragraph-id' => $paragraph_id,
          ],
          // Create paragraphs that render HTML.
          // From static-content-paragraphs/[ID]/index.html.
          'content' => static_content_type_loader_with_precedence($paragraph_id, 'static-content-paragraphs'),
        ];
      }
      else {
        // Fallback to default paragraph rendering.
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('paragraph');
        $elements[$delta] = $view_builder->view($item->entity, $this->getSetting('view_mode'), $langcode);
      }
    }

    return $elements;
  }

}
