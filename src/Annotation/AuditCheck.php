<?php

namespace Drupal\aeo_multilingual\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an AuditCheck annotation object.
 *
 * @Annotation
 */
class AuditCheck extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The weight of this check (lower = runs first).
   *
   * @var int
   */
  public $weight = 0;

}
