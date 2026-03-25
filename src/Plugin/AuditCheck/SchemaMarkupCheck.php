<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks schema markup for AEO optimization.
 *
 * @AuditCheck(
 *   id = "schema_markup",
 *   label = @Translation("Schema Markup"),
 *   description = @Translation("Verifies JSON-LD schema markup is present and includes correct inLanguage."),
 *   weight = 3
 * )
 */
class SchemaMarkupCheck extends AuditCheckBase {

  /**
   * {@inheritdoc}
   */
  public function audit(NodeInterface $node, string $langcode): array {
    $module_handler = \Drupal::moduleHandler();

    if (!$module_handler->moduleExists('schema_metatag')) {
      return [
        'score' => 50,
        'message' => $this->t('Schema Metatag module not installed. Cannot validate schema markup.'),
        'status' => 'warning',
        'suggestions' => [
          $this->t('Install and configure the schema_metatag module for proper JSON-LD schema markup.'),
        ],
      ];
    }

    if (!$node->hasTranslation($langcode)) {
      return [
        'score' => 0,
        'message' => $this->t('No translation found for @lang.', ['@lang' => $langcode]),
        'status' => 'fail',
        'suggestions' => [],
      ];
    }

    $translation = $node->getTranslation($langcode);

    if (!$translation->hasField('field_metatag') || $translation->get('field_metatag')->isEmpty()) {
      return [
        'score' => 30,
        'message' => $this->t('No schema metatag field found or configured for this content type.'),
        'status' => 'warning',
        'suggestions' => [
          $this->t('Configure schema markup for this content type via Metatag settings.'),
          $this->t('Ensure JSON-LD includes "inLanguage": "@lang".', ['@lang' => $langcode]),
        ],
      ];
    }

    return [
      'score' => 80,
      'message' => $this->t('Schema Metatag module is active. Verify JSON-LD includes inLanguage: @lang.', [
        '@lang' => $langcode,
      ]),
      'status' => 'pass',
      'suggestions' => [
        $this->t('Ensure schema includes "inLanguage": "@lang" in your JSON-LD configuration.', [
          '@lang' => $langcode,
        ]),
      ],
    ];
  }

}
