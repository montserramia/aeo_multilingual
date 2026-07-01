<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\metatag\MetatagManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Constructs a MetaDescriptionCheck plugin.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModuleHandlerInterface $module_handler,
    LanguageManagerInterface $language_manager,
    LoggerChannelFactoryInterface $logger_factory,
    ContainerInterface $container,
    protected ?MetatagManagerInterface $metatagManager = NULL,
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $module_handler,
      $language_manager,
      $logger_factory,
      $container,
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('logger.factory'),
      $container,
      $container->has('metatag.manager')
        ? $container->get('metatag.manager')
        : NULL,
    );
  }

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

    if (!$this->moduleHandler->moduleExists('metatag')) {
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

    $tags = $this->metatagManager->tagsFromEntityWithDefaults($translation);
    $description = '';

    if (!empty($tags['description'])) {
      $description = strip_tags(trim($tags['description']));
    }

    if ($description === '') {
      $elements = $this->metatagManager->generateRawElements($tags, $translation);
      foreach ($elements as $element) {
        if (isset($element['#attributes']['name']) &&
            $element['#attributes']['name'] === 'description' &&
            !empty($element['#attributes']['content'])) {
          $description = strip_tags(trim($element['#attributes']['content']));
          break;
        }
      }
    }

    if ($description !== '') {
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
