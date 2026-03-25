<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Interface for AEO audit check plugins.
 */
interface AuditCheckInterface {

  /**
   * Run the audit check on a node translation.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to audit.
   * @param string $langcode
   *   The language code to audit.
   *
   * @return array
   *   Array with keys:
   *   - score: int (0-100)
   *   - message: string
   *   - status: string ('pass', 'warning', 'fail')
   *   - suggestions: array (optional)
   */
  public function audit(NodeInterface $node, string $langcode): array;

  /**
   * Get the weight/priority of this check.
   *
   * @return int
   *   Weight (lower runs first).
   */
  public function getWeight(): int;

}
