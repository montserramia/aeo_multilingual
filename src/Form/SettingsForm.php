<?php

namespace Drupal\aeo_multilingual\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure AEO Multilingual settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var static $instance */
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aeo_multilingual_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['aeo_multilingual.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['enabled_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types to audit'),
      '#description' => $this->t('Select which content types should be audited for AEO. Leave empty to audit all.'),
      '#options' => $this->getContentTypes(),
      '#config_target' => 'aeo_multilingual.settings:enabled_content_types',
    ];

    $form['score_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum AEO score threshold'),
      '#description' => $this->t('Nodes scoring below this threshold will be flagged. Default: 70.'),
      '#min' => 0,
      '#max' => 100,
      '#config_target' => 'aeo_multilingual.settings:score_threshold',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Get available content types as options array.
   */
  protected function getContentTypes(): array {
    $types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    $options = [];
    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }
    return $options;
  }

}
