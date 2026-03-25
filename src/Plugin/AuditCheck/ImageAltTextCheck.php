<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\node\NodeInterface;

/**
 * Checks image alt text for AEO optimization.
 *
 * @AuditCheck(
 *   id = "image_alt_text",
 *   label = @Translation("Image Alt Text"),
 *   description = @Translation("Verifies all images have descriptive alt text in each language."),
 *   weight = 6
 * )
 */
class ImageAltTextCheck extends AuditCheckBase {

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
    $total_images = 0;
    $missing_alt = 0;

    // Check image fields.
    foreach ($translation->getFields() as $field) {
      if ($field->getFieldDefinition()->getType() === 'image') {
        foreach ($field as $item) {
          $total_images++;
          if (empty($item->alt)) {
            $missing_alt++;
          }
        }
      }
    }

    // Check images in body.
    if ($translation->hasField('body') && !$translation->get('body')->isEmpty()) {
      $body = $translation->get('body')->value;
      preg_match_all('/<img[^>]+>/i', $body, $img_matches);
      foreach ($img_matches[0] as $img_tag) {
        $total_images++;
        if (!preg_match('/alt\s*=\s*["\'][^"\']+["\']/i', $img_tag)) {
          $missing_alt++;
        }
      }
    }

    if ($total_images === 0) {
      return [
        'score' => 80,
        'message' => $this->t('No images found in this content.'),
        'status' => 'pass',
        'suggestions' => [$this->t('Consider adding relevant images with descriptive alt text.')],
      ];
    }

    if ($missing_alt === 0) {
      return [
        'score' => 100,
        'message' => $this->t('All @count images have alt text.', ['@count' => $total_images]),
        'status' => 'pass',
        'suggestions' => [],
      ];
    }

    $good_images = $total_images - $missing_alt;
    $score = (int) (($good_images / $total_images) * 100);
    $status = $score >= 80 ? 'pass' : ($score >= 50 ? 'warning' : 'fail');

    return [
      'score' => $score,
      'message' => $this->t('@good of @total images have alt text. @missing missing.', [
        '@good' => $good_images,
        '@total' => $total_images,
        '@missing' => $missing_alt,
      ]),
      'status' => $status,
      'suggestions' => [
        $this->t('Add descriptive alt text to @count images in @lang.', [
          '@count' => $missing_alt,
          '@lang' => $langcode,
        ]),
      ],
    ];
  }

}
