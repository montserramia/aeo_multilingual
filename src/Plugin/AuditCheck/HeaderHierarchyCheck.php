<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks header hierarchy for AEO optimization.
 *
 * @AuditCheck(
 *   id = "header_hierarchy",
 *   label = @Translation("Header Hierarchy"),
 *   description = @Translation("Verifies H1-H3 headers are hierarchical and descriptive."),
 *   weight = 5
 * )
 */
class HeaderHierarchyCheck extends AuditCheckBase {

  /**
   * {@inheritdoc}
   */
  public function audit(NodeInterface $node, string $langcode): array {
    if (!$node->hasTranslation($langcode)) {
      return [
        'score' => 0,
        'message' => $this->t('No translation found for @lang.', ['@lang' => $langcode]),
        'status' => 'fail',
        'suggestions' => [],
      ];
    }

    $translation = $node->getTranslation($langcode);

    if (!$translation->hasField('body') || $translation->get('body')->isEmpty()) {
      return [
        'score' => 0,
        'message' => $this->t('No body content to analyze headers.'),
        'status' => 'fail',
        'suggestions' => [$this->t('Add body content with proper header structure.')],
      ];
    }

    $body = $translation->get('body')->value;
    preg_match_all('/<h([1-6])[^>]*>/i', $body, $matches);
    $headers = array_map('intval', $matches[1]);

    if (empty($headers)) {
      return [
        'score' => 20,
        'message' => $this->t('No headers (H1-H6) found in content.'),
        'status' => 'fail',
        'suggestions' => [
          $this->t('Add H2 and H3 headers to structure your content for better AEO.'),
          $this->t('Use descriptive headers that answer potential user questions.'),
        ],
      ];
    }

    $h1_count = count(array_filter($headers, fn($h) => $h === 1));
    $h2_count = count(array_filter($headers, fn($h) => $h === 2));
    $score = 60;
    $issues = [];

    if ($h1_count > 1) {
      $score -= 20;
      $issues[] = $this->t('Multiple H1 tags found (@count). Use only one H1 per page.', ['@count' => $h1_count]);
    }

    if ($h2_count === 0) {
      $score -= 20;
      $issues[] = $this->t('No H2 headers found. Add H2 headers to structure your content.');
    }
    else {
      $score += 20;
    }

    if ($h2_count >= 3) {
      $score += 20;
    }

    $score = max(0, min(100, $score));
    $status = $score >= 70 ? 'pass' : ($score >= 40 ? 'warning' : 'fail');
    $message = empty($issues)
      ? $this->t('Header structure looks good (@count headers found).', ['@count' => count($headers)])
      : $this->t('@count header issue(s) found.', ['@count' => count($issues)]);

    return [
      'score' => $score,
      'message' => $message,
      'status' => $status,
      'suggestions' => array_map('strval', $issues),
    ];
  }

}
