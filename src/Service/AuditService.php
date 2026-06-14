<?php

namespace Drupal\aeo_multilingual\Service;

use Drupal\aeo_multilingual\AuditCheckManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Service to run AEO audits on multilingual content.
 */
class AuditService {

  /**
   * Constructs an AuditService object.
   */
  public function __construct(
    protected LanguageManagerInterface $languageManager,
    protected AuditCheckManager $auditCheckManager,
    protected ScoreCalculator $scoreCalculator,
    protected LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  /**
   * Run audit on a node for all active languages.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to audit.
   *
   * @return array
   *   Keyed by langcode, each with 'checks', 'score', and 'status'.
   */
  public function auditNode(NodeInterface $node): array {
    $results = [];
    $languages = $this->languageManager->getLanguages();

    foreach ($languages as $langcode => $language) {
      if ($node->hasTranslation($langcode)) {
        $results[$langcode] = $this->auditNodeLanguage($node, $langcode);
      }
    }

    return $results;
  }

  /**
   * Run audit on a node for a specific language.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to audit.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   Array with 'checks', 'score', and 'status' keys.
   */
  public function auditNodeLanguage(NodeInterface $node, string $langcode): array {
    $definitions = $this->auditCheckManager->getDefinitions();

    uasort($definitions, fn($a, $b) => ($a['weight'] ?? 0) <=> ($b['weight'] ?? 0));

    $checks = [];
    foreach ($definitions as $plugin_id => $definition) {
      try {
        /** @var \Drupal\aeo_multilingual\Plugin\AuditCheck\AuditCheckInterface $plugin */
        $plugin = $this->auditCheckManager->createInstance($plugin_id);
        $result = $plugin->audit($node, $langcode);
        $result['id'] = $plugin_id;
        $result['label'] = (string) $definition['label'];
        $checks[$plugin_id] = $result;
      }
      catch (\Exception $e) {
        $this->loggerFactory->get('aeo_multilingual')->error('Error running audit check @id: @message', [
          '@id' => $plugin_id,
          '@message' => $e->getMessage(),
        ]);
      }
    }

    $overall_score = $this->scoreCalculator->calculateOverallScore($checks);

    return [
      'checks' => $checks,
      'score' => $overall_score,
      'status' => $this->scoreCalculator->getStatus($overall_score),
    ];
  }

}
