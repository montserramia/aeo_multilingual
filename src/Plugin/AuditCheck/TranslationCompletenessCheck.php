<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks translation completeness for AEO optimization.
 *
 * @AuditCheck(
 *   id = "translation_completeness",
 *   label = @Translation("Translation Completeness"),
 *   description = @Translation("Verifies all key translatable fields are translated."),
 *   weight = 7
 * )
 */
class TranslationCompletenessCheck extends AuditCheckBase {

  const IMPORTANT_FIELDS = ['title', 'body', 'field_summary', 'field_description'];

  /**
   * {@inheritdoc}
   */
  public function audit(NodeInterface $node, string $langcode): array {
    if (!$node->hasTranslation($langcode)) {
      return [
        'score' => 0,
        'message' => $this->t('No translation exists for @lang.', ['@lang' => $langcode]),
        'status' => 'fail',
        'suggestions' => [$this->t('Create a translation for this language.')],
      ];
    }

    $source_langcode = $node->language()->getId();

    if ($langcode === $source_langcode) {
      return [
        'score' => 100,
        'message' => $this->t('This is the source language (@lang).', ['@lang' => $langcode]),
        'status' => 'pass',
        'suggestions' => [],
      ];
    }

    $translation = $node->getTranslation($langcode);
    $untranslated = [];
    $total_checked = 0;

    foreach (self::IMPORTANT_FIELDS as $field_name) {
      if (!$node->hasField($field_name)) {
        continue;
      }
      if (!$node->getFieldDefinition($field_name)->isTranslatable()) {
        continue;
      }

      $total_checked++;
      $source_value = '';

      if ($node->hasTranslation($source_langcode)) {
        $source_value = $node->getTranslation($source_langcode)->get($field_name)->getString();
      }

      $translated_value = $translation->get($field_name)->getString();

      if (empty($translated_value) || $translated_value === $source_value) {
        $untranslated[] = $field_name;
      }
    }

    if ($total_checked === 0) {
      return [
        'score' => 70,
        'message' => $this->t('No key translatable fields found to check.'),
        'status' => 'pass',
        'suggestions' => [],
      ];
    }

    if (empty($untranslated)) {
      return [
        'score' => 100,
        'message' => $this->t('All @count key fields are translated.', ['@count' => $total_checked]),
        'status' => 'pass',
        'suggestions' => [],
      ];
    }

    $translated_count = $total_checked - count($untranslated);
    $score = (int) (($translated_count / $total_checked) * 100);
    $status = $score >= 80 ? 'pass' : ($score >= 50 ? 'warning' : 'fail');

    return [
      'score' => $score,
      'message' => $this->t('@translated of @total key fields translated. Untranslated: @fields.', [
        '@translated' => $translated_count,
        '@total' => $total_checked,
        '@fields' => implode(', ', $untranslated),
      ]),
      'status' => $status,
      'suggestions' => [
        $this->t('Translate the following fields in @lang: @fields.', [
          '@lang' => $langcode,
          '@fields' => implode(', ', $untranslated),
        ]),
      ],
    ];
  }

}
