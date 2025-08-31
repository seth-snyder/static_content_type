<?php

namespace Drupal\static_content_type\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Static Block' block.
 *
 * @Block(
 *  id = "static_block_plugin",
 *  admin_label = @Translation("Static Block"),
 *  category = @Translation("Static Content"),
 * )
 */
class StaticBlockPlugin extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new StaticBlockPlugin instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'static_block_id' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    // Get all static block content entities.
    $static_blocks = $this->entityTypeManager
      ->getStorage('block_content')
      ->loadByProperties(['type' => 'static_block']);

    $options = [];
    foreach ($static_blocks as $block) {
      $options[$block->id()] = $block->label() . ' (ID: ' . $block->id() . ')';
    }

    $form['static_block_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Static Block'),
      '#description' => $this->t('Select the static block content to display.'),
      '#options' => $options,
      '#default_value' => $config['static_block_id'],
      '#empty_option' => $this->t('- Select a static block -'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['static_block_id'] = $values['static_block_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $block_id = $config['static_block_id'];

    if (empty($block_id)) {
      return [
        '#markup' => $this->t('No static block selected.'),
      ];
    }

    return static_content_type_loader_with_precedence($block_id, 'static-content-blocks');
  }

}
