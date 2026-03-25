<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for AEO audit check plugins.
 */
abstract class AuditCheckBase extends PluginBase implements AuditCheckInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return $this->pluginDefinition['weight'] ?? 0;
  }

  /**
   * Get label from plugin definition.
   */
  public function getLabel(): string {
    return (string) ($this->pluginDefinition['label'] ?? '');
  }

  /**
   * Get description from plugin definition.
   */
  public function getDescription(): string {
    return (string) ($this->pluginDefinition['description'] ?? '');
  }

}
