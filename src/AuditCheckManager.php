<?php

namespace Drupal\aeo_multilingual;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages AuditCheck plugins.
 */
class AuditCheckManager extends DefaultPluginManager {

  /**
   * Constructs an AuditCheckManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/AuditCheck',
      $namespaces,
      $module_handler,
      'Drupal\aeo_multilingual\Plugin\AuditCheck\AuditCheckInterface',
      'Drupal\aeo_multilingual\Annotation\AuditCheck'
    );
    $this->alterInfo('aeo_multilingual_audit_check_info');
    $this->setCacheBackend($cache_backend, 'aeo_multilingual_audit_check_plugins');
  }

}
