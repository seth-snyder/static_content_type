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

    $form['description'] = [
      '#markup' => '<p>' . $this->t('Configure global settings for static content rendering. These can be overridden per-directory using stc.options.yml files.') . '</p>',
    ];

    $form['render_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Render Method'),
      '#description' => $this->t('How static content should be displayed by default.'),
      '#options' => [
        'php' => $this->t('PHP - Drupal Render Arrays (Standard)'),
        'twig' => $this->t('Twig - Raw HTML Injection (Bypass Drupal rendering)'),
        'iframe' => $this->t('iFrame - Isolated Rendering (Complete documents)'),
      ],
      '#default_value' => $config->get('render_method') ?: 'php',
      '#required' => TRUE,
    ];

    $form['default_processing'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Processing Type'),
      '#description' => $this->t("How content should be processed when directory structure doesn't specify. Directory precedence: dist → build → raw → proxied → hardened → src → root."),
      '#options' => [
        'hardened' => $this->t('Hardened - Security sanitization + path proxying (Safest)'),
        'proxied' => $this->t('Proxied - Path fixing only (Faster)'),
        'raw' => $this->t('Raw - No processing (Fastest, least secure)'),
      ],
      '#default_value' => $config->get('default_processing') ?: 'hardened',
      '#required' => TRUE,
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced Settings'),
      '#open' => FALSE,
    ];

    $form['advanced']['use_drupal_react'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Use Drupal's Built-in React Library"),
      // Include Drupal core React library to avoid duplicates when multiple
      // React components are used on a page. Recommended for bundled content.
      '#description' => $this->t("Include Drupal core React library to avoid duplicates when multiple React components are used on a page. Recommended for bundled content."),
      '#default_value' => $config->get('use_drupal_react') ?: FALSE,
    ];

    // Help section.
    $form['help'] = [
      '#type' => 'details',
      '#title' => $this->t('Directory Structure Help'),
      '#open' => FALSE,
    ];

    $form['help']['info'] = [
      '#markup' => $this->getHelpText(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('static_content_type.settings')
      ->set('render_method', $form_state->getValue('render_method'))
      ->set('default_processing', $form_state->getValue('default_processing'))
      ->set('use_drupal_react', $form_state->getValue('use_drupal_react'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get help text for directory structure.
   */
  private function getHelpText() {
    return '
      <h3>Directory Structure</h3>
      <p>Static content is organized in public files under these directories:</p>
      <ul>
        <li><strong>static-content-nodes/[ID]/</strong> - Node content</li>
        <li><strong>static-content-blocks/[ID]/</strong> - Block content</li>
        <li><strong>static-content-paragraphs/[ID]/</strong> - Paragraph content</li>
        <li><strong>static-content-sdc/[ID]/</strong> - SDC content</li>
        <li><strong>static-content-twig/[ID]/</strong> - Twig template content</li>
        <li><strong>static-content-pages/[ID]/</strong> - Complete page content</li>
      </ul>

      <h3>Subdirectory Precedence</h3>
      <p>The system looks for index.html in this order:</p>
      <ol>
        <li><strong>[ID]/dist/index.html</strong> - Production bundles</li>
        <li><strong>[ID]/build/index.html</strong> - Build output</li>
        <li><strong>[ID]/raw/index.html</strong> - No processing</li>
        <li><strong>[ID]/proxied/index.html</strong> - Path fixing only</li>
        <li><strong>[ID]/hardened/index.html</strong> - Security + proxying</li>
        <li><strong>[ID]/src/index.html</strong> - Source files (proxied)</li>
        <li><strong>[ID]/index.html</strong> - Root level (hardened)</li>
      </ol>

      <h3>Per-Directory Overrides</h3>
      <p>Create <strong>[ID]/stc.options.yml</strong> to override settings:</p>
      <pre>
render_method: \'iframe\'     # Override global render method
processing_type: \'raw\'      # Override directory-based processing
custom_settings:
  use_drupal_react: true
  iframe_height: \'800px\'
      </pre>

      <h3>Development Workflow</h3>
      <ol>
        <li>Start with raw HTML in <strong>src/</strong> directory</li>
        <li>Use build tools to output to <strong>dist/</strong> or <strong>build/</strong></li>
        <li>Override settings with <strong>stc.options.yml</strong> as needed</li>
        <li>Use different subdirectories for different processing levels</li>
      </ol>
    ';
  }

}
