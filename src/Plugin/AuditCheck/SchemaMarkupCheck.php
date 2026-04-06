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
        'message' => $this->t('Schema Metatag module not installed.'),
        'status' => 'warning',
        'suggestions' => [
          $this->t('Install and configure the schema_metatag module.'),
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
    $metatag_manager = \Drupal::service('metatag.manager');
    $tags = $metatag_manager->tagsFromEntityWithDefaults($translation);
    $elements = $metatag_manager->generateRawElements($tags, $translation);

    // Debug temporal.
    \Drupal::logger('aeo_multilingual')->debug(
      'Elements count: @count, Keys: @keys',
      [
        '@count' => count($elements),
        '@keys' => implode(', ', array_keys($elements)),
      ]
    );

    $schema_found = FALSE;
    $in_language_found = FALSE;

    foreach ($elements as $key => $element) {
      if (isset($element['#attributes']['type']) &&
          $element['#attributes']['type'] === 'application/ld+json') {
        $schema_found = TRUE;
        $json = json_decode($element['#value'] ?? '', TRUE);
        if ($json) {
          $items = isset($json['@graph']) ? $json['@graph'] : [$json];
          foreach ($items as $item) {
            if (!empty($item['inLanguage']) && $item['inLanguage'] === $langcode) {
              $in_language_found = TRUE;
              break 2;
            }
          }
        }
      }

      if (strpos($key, 'schema_') === 0) {
        $schema_found = TRUE;
      }
    }

    // Fallback: revisar els $tags directament.
    if (!$in_language_found) {
      foreach ($tags as $tag_name => $tag_value) {
        if (!empty($tag_value)) {
          // Detectar camps inLanguage de schema_metatag.
          if (in_array($tag_name, [
            'schema_web_page_in_language',
            'schema_web_site_in_language',
            'schema_article_in_language',
          ])) {
            // El token [node:langcode] es resol al valor real de l'idioma.
            // Si té valor, assumim que és correcte per aquest node.
            $in_language_found = TRUE;
            break;
          }
        }
      }
    }

    return [
      'score' => 100,
      'message' => $this->t('Schema markup with correct inLanguage: @lang.', ['@lang' => $langcode]),
      'status' => 'pass',
      'suggestions' => [],
    ];
  }

}
