<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks content length for AEO optimization.
 *
 * @AuditCheck(
 *   id = "content_length",
 *   label = @Translation("Content Length"),
 *   description = @Translation("Verifies content meets minimum word count for AEO."),
 *   weight = 1
 * )
 */
class ContentLengthCheck extends AuditCheckBase {

  /**
   * {@inheritdoc}
   */
  public function audit(NodeInterface $node, string $langcode): array {
    if (!$node->hasTranslation($langcode)) {
      return [
        'score' => 0,
        'message' => $this->t('No translation found for @lang.', ['@lang' => $langcode]),
        'status' => 'fail',
        'suggestions' => [$this->t('Create a translation for this language.')],
      ];
    }

    $translation = $node->getTranslation($langcode);

    if (!$translation->hasField('body') || $translation->get('body')->isEmpty()) {
      return [
        'score' => 0,
        'message' => $this->t('No body content found.'),
        'status' => 'fail',
        'suggestions' => [$this->t('Add body content to this translation.')],
      ];
    }

    $body = $translation->get('body')->value;
    $word_count = str_word_count(strip_tags($body));

    if ($word_count >= 2000) {
      return [
        'score' => 100,
        'message' => $this->t('Optimal: @count words.', ['@count' => $word_count]),
        'status' => 'pass',
        'suggestions' => [],
      ];
    }
    elseif ($word_count >= 1000) {
      return [
        'score' => 70,
        'message' => $this->t('Good: @count words (aim for 2000+ for best AEO results).', ['@count' => $word_count]),
        'status' => 'pass',
        'suggestions' => [$this->t('Consider expanding content to 2000+ words.')],
      ];
    }
    elseif ($word_count >= 500) {
      return [
        'score' => 40,
        'message' => $this->t('Short: @count words (minimum recommended: 1000).', ['@count' => $word_count]),
        'status' => 'warning',
        'suggestions' => [$this->t('Expand content to at least 1000 words for better AEO performance.')],
      ];
    }

    return [
      'score' => 10,
      'message' => $this->t('Too short: @count words (minimum: 500).', ['@count' => $word_count]),
      'status' => 'fail',
      'suggestions' => [$this->t('Content is too short for effective AEO. Aim for at least 1000 words.')],
    ];
  }

}
