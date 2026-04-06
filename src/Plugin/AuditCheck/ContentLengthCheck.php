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
      $text_parts = [];
    
      // Títol.
      $text_parts[] = $translation->label();
    
      // Tots els camps de text del node (body, summary, text fields).
      $field_types = ['text', 'text_long', 'text_with_summary', 'string', 'string_long'];
      foreach ($translation->getFields() as $field_name => $field) {
        // Saltar camps interns de Drupal.
        if (in_array($field_name, ['title', 'uuid', 'langcode', 'status', 'uid', 'type', 'revision_log'])) {
          continue;
        }
        $field_type = $field->getFieldDefinition()->getType();
        if (!in_array($field_type, $field_types)) {
          continue;
        }
        if ($field->isEmpty()) {
          continue;
        }
        foreach ($field->getValue() as $value) {
          if (!empty($value['value'])) {
            $text_parts[] = strip_tags($value['value']);
          }
          if (!empty($value['summary'])) {
            $text_parts[] = strip_tags($value['summary']);
          }
        }
      }
    
      $full_text = implode(' ', array_filter($text_parts));
      $word_count = str_word_count($full_text);
    
      if ($word_count === 0) {
        return [
          'score' => 0,
          'message' => $this->t('No text content found.'),
          'status' => 'fail',
          'suggestions' => [$this->t('Add body content to this translation.')],
        ];
      }
    
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
