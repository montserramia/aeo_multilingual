<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks meta description for AEO optimization.
 *
 * @AuditCheck(
 *   id = "meta_description",
 *   label = @Translation("Meta Description"),
 *   description = @Translation("Verifies meta description is present and correctly sized for each language."),
 *   weight = 4
 * )
 */
class MetaDescriptionCheck extends AuditCheckBase {

  const MIN_LENGTH = 120;
  const MAX_LENGTH = 160;

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

    if (!\Drupal::moduleHandler()->moduleExists('metatag')) {
      return [
        'score' => 40,
        'message' => $this->t('Metatag module not installed. Cannot validate meta descriptions.'),
        'status' => 'warning',
        'suggestions' => [
          $this->t('Install the metatag module to manage meta descriptions per language.'),
        ],
      ];
    }

    $translation = $node->getTranslation($langcode);

    if ($translation->hasField('field_metatag') && !$translation->get('field_metatag')->isEmpty()) {
      $metatag_value = $translation->get('field_metatag')->value;
      $metatags = unserialize($metatag_value, ['allowed_classes' => FALSE]);

      if (!empty($metatags['description'])) {
        $description = strip_tags($metatags['description']);
        $length = mb_strlen($description);

        if ($length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH) {
          return [
            'score' => 100,
            'message' => $this->t('Meta description is optimal (@length chars).', ['@length' => $length]),
            'status' => 'pass',
            'suggestions' => [],
          ];
        }
        elseif ($length > self::MAX_LENGTH) {
          return [
            'score' => 70,
            'message' => $this->t('Meta description too long (@length chars, max @max).', [
              '@length' => $length,
              '@max' => self::MAX_LENGTH,
            ]),
            'status' => 'warning',
            'suggestions' => [
              $this->t('Shorten meta description to @max characters or less.', ['@max' => self::MAX_LENGTH]),
            ],
          ];
        }
        else {
          return [
            'score' => 40,
            'message' => $this->t('Meta description too short (@length chars, min @min).', [
              '@length' => $length,
              '@min' => self::MIN_LENGTH,
            ]),
            'status' => 'warning',
            'suggestions' => [
              $this->t('Expand meta description to at least @min characters.', ['@min' => self::MIN_LENGTH]),
            ],
          ];
        }
      }
    }

    return [
      'score' => 0,
      'message' => $this->t('No meta description found for @lang.', ['@lang' => $langcode]),
      'status' => 'fail',
      'suggestions' => [
        $this->t('Add a meta description of @min-@max characters for this language version.', [
          '@min' => self::MIN_LENGTH,
          '@max' => self::MAX_LENGTH,
        ]),
      ],
    ];
  }

}
