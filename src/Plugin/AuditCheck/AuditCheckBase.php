<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for AEO audit check plugins.
 */
abstract class AuditCheckBase extends PluginBase implements AuditCheckInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Constructs an audit check plugin.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected ModuleHandlerInterface $moduleHandler,
    protected LanguageManagerInterface $languageManager,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected ContainerInterface $container,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('logger.factory'),
      $container,
    );
  }

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
