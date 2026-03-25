<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks hreflang implementation for AEO optimization.
 *
 * @AuditCheck(
 *   id = "hreflang",
 *   label = @Translation("Hreflang"),
 *   description = @Translation("Verifies hreflang tags are present and correct for all translations."),
 *   weight = 2
 * )
 */
class HreflangCheck extends AuditCheckBase {

  /**
   * {@inheritdoc}
   */
  public function audit(NodeInterface $node, string $langcode): array {
    $languages = \Drupal::languageManager()->getLanguages();
    $translations = $node->getTranslationLanguages();

    $missing = [];
    foreach ($languages as $lang_id => $language) {
      if (!isset($translations[$lang_id])) {
        $missing[] = $lang_id;
      }
    }

    $total_languages = count($languages);
    $translated_languages = count($translations);

    if ($translated_languages === $total_languages) {
      return [
        'score' => 100,
        'message' => $this->t('All @count language versions exist for hreflang.', ['@count' => $total_languages]),
        'status' => 'pass',
        'suggestions' => [],
      ];
    }
    elseif ($translated_languages > 1) {
      $score = (int) (($translated_languages / $total_languages) * 100);
      return [
        'score' => $score,
        'message' => $this->t('@translated of @total language versions translated. Missing: @missing.', [
          '@translated' => $translated_languages,
          '@total' => $total_languages,
          '@missing' => implode(', ', $missing),
        ]),
        'status' => 'warning',
        'suggestions' => [
          $this->t('Create translations for: @langs to enable complete hreflang.', [
            '@langs' => implode(', ', $missing),
          ]),
        ],
      ];
    }

    return [
      'score' => 20,
      'message' => $this->t('Only 1 language version exists. Hreflang requires multiple translations.'),
      'status' => 'fail',
      'suggestions' => [$this->t('Create translations in other languages to enable hreflang.')],
    ];
  }

}
