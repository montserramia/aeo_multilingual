<?php

namespace Drupal\aeo_multilingual\Controller;

use Drupal\aeo_multilingual\Service\AuditService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for AEO Multilingual dashboard and node audit pages.
 */
class AeoMultilingualDashboard extends ControllerBase {

  /**
   * The audit service.
   *
   * @var \Drupal\aeo_multilingual\Service\AuditService
   */
  protected $auditService;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aeo_multilingual.audit'),
      $container->get('language_manager')
    );
  }

  /**
   * Constructs a dashboard controller.
   */
  public function __construct(AuditService $audit_service, LanguageManagerInterface $language_manager) {
    $this->auditService = $audit_service;
    $this->languageManager = $language_manager;
  }

  /**
   * Dashboard page showing AEO scores for all multilingual content.
   */
  public function dashboard(): array {
    $config = $this->config('aeo_multilingual.settings');
    $enabled_types = $config->get('enabled_content_types') ?: [];
    $languages = $this->languageManager->getLanguages();

    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->range(0, 50);

    if (!empty($enabled_types)) {
      $query->condition('type', $enabled_types, 'IN');
    }

    $nids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    $rows = [];
    foreach ($nodes as $node) {
      $audit_results = $this->auditService->auditNode($node);
      $row = [
        'nid' => $node->id(),
        'title' => $node->getTitle(),
        'type' => $node->bundle(),
        'languages' => [],
      ];

      foreach ($languages as $langcode => $language) {
        if (isset($audit_results[$langcode])) {
          $row['languages'][$langcode] = [
            'score' => $audit_results[$langcode]['score'],
            'status' => $audit_results[$langcode]['status'],
          ];
        }
        else {
          $row['languages'][$langcode] = [
            'score' => NULL,
            'status' => 'missing',
          ];
        }
      }

      $rows[] = $row;
    }

    return [
      '#theme' => 'aeo_multilingual_dashboard',
      '#languages' => $languages,
      '#nodes' => $rows,
      '#summary' => $this->buildSummary($rows, $languages),
      '#attached' => [
        'library' => ['aeo_multilingual/dashboard'],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Node audit page showing detailed AEO scores per language.
   */
  public function nodeAudit(NodeInterface $node): array {
    $languages = $this->languageManager->getLanguages();
    $results = $this->auditService->auditNode($node);

    return [
      '#theme' => 'aeo_multilingual_node_tab',
      '#node' => $node,
      '#results' => $results,
      '#languages' => $languages,
      '#attached' => [
        'library' => ['aeo_multilingual/dashboard'],
      ],
      '#cache' => [
        'tags' => $node->getCacheTags(),
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Build summary statistics per language.
   */
  protected function buildSummary(array $rows, array $languages): array {
    $summary = [];
    foreach ($languages as $langcode => $language) {
      $scores = [];
      foreach ($rows as $row) {
        $score = $row['languages'][$langcode]['score'] ?? NULL;
        if ($score !== NULL) {
          $scores[] = $score;
        }
      }
      $summary[$langcode] = [
        'language' => $language->getName(),
        'average' => !empty($scores) ? (int) round(array_sum($scores) / count($scores)) : 0,
        'count' => count($scores),
      ];
    }
    return $summary;
  }

}
