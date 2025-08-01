<?php

namespace Drupal\static_content_type\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Static Content Type settings for this site.
 */
class StaticContentTypeConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'static_content_type_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['static_content_type.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('static_content_type.settings');

    $options = [
      'proxied' => $this->t('Proxied (Default) - Modify relative paths automatically'),
      'raw' => $this->t('Raw - Use content as-is'),
      'iframe' => $this->t('iFrame - Render content in an iframe'),
    ];

    $form['description'] = [
      '#markup' => '<p>' . $this->t('Configure how static content is rendered for each entity type.') . '</p>',
    ];

    $form['static_content_nodes_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Content Nodes Rendering'),
      '#description' => $this->t('How to render content from static-content-nodes/ directory.'),
      '#options' => $options,
      '#default_value' => $config->get('static_content_nodes_option') ?: 'proxied',
    ];

    $form['static_content_blocks_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Content Blocks Rendering'),
      '#description' => $this->t('How to render content from static-content-blocks/ directory.'),
      '#options' => $options,
      '#default_value' => $config->get('static_content_blocks_option') ?: 'proxied',
    ];

    $form['static_content_paragraphs_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Content Paragraphs Rendering'),
      '#description' => $this->t('How to render content from static-content-paragraphs/ directory.'),
      '#options' => $options,
      '#default_value' => $config->get('static_content_paragraphs_option') ?: 'proxied',
    ];

    $form['static_content_sdc_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Content SDC Rendering'),
      '#description' => $this->t('How to render content from static-content-sdc/ directory.'),
      '#options' => $options,
      '#default_value' => $config->get('static_content_sdc_option') ?: 'proxied',
    ];

    $form['static_content_twig_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Content Twig Rendering'),
      '#description' => $this->t('How to render content from static-content-twig/ directory.'),
      '#options' => $options,
      '#default_value' => $config->get('static_content_twig_option') ?: 'proxied',
    ];

    $form['static_content_pages_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Content Pages Rendering'),
      '#description' => $this->t('How to render content from static-content-pages/ directory.'),
      '#options' => $options,
      '#default_value' => $config->get('static_content_pages_option') ?: 'proxied',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('static_content_type.settings')
      ->set('static_content_nodes_option', $form_state->getValue('static_content_nodes_option'))
      ->set('static_content_blocks_option', $form_state->getValue('static_content_blocks_option'))
      ->set('static_content_paragraphs_option', $form_state->getValue('static_content_paragraphs_option'))
      ->set('static_content_sdc_option', $form_state->getValue('static_content_sdc_option'))
      ->set('static_content_twig_option', $form_state->getValue('static_content_twig_option'))
      ->set('static_content_pages_option', $form_state->getValue('static_content_pages_option'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
