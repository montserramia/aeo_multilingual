<?php

namespace Drupal\aeo_multilingual\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure AEO Multilingual settings.
 */
class SettingsForm extends ConfigFormBase {

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
    $config = $this->config('aeo_multilingual.settings');

    $form['enabled_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types to audit'),
      '#description' => $this->t('Select which content types should be audited for AEO. Leave empty to audit all.'),
      '#options' => $this->getContentTypes(),
      '#default_value' => $config->get('enabled_content_types') ?: [],
    ];

    $form['score_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum AEO score threshold'),
      '#description' => $this->t('Nodes scoring below this threshold will be flagged. Default: 70.'),
      '#min' => 0,
      '#max' => 100,
      '#default_value' => $config->get('score_threshold') ?? 70,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('aeo_multilingual.settings')
      ->set('enabled_content_types', array_filter($form_state->getValue('enabled_content_types')))
      ->set('score_threshold', (int) $form_state->getValue('score_threshold'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get available content types as options array.
   */
  protected function getContentTypes(): array {
    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();

    $options = [];
    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }
    return $options;
  }

}
