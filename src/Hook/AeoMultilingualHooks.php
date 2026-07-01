<?php

declare(strict_types=1);

namespace Drupal\aeo_multilingual\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Hook implementations for the AEO Multilingual module.
 */
class AeoMultilingualHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help(string $route_name, RouteMatchInterface $route_match): ?string {
    switch ($route_name) {
      case 'help.page.aeo_multilingual':
        $output = '<h3>' . $this->t('About') . '</h3>';
        $output .= '<p>' . $this->t('The AEO Multilingual module audits and optimizes each language version of your content independently for Answer Engine Optimization (AEO) and AI citations.') . '</p>';
        $output .= '<h3>' . $this->t('Uses') . '</h3>';
        $output .= '<dl>';
        $output .= '<dt>' . $this->t('Dashboard') . '</dt>';
        $output .= '<dd>' . $this->t('View AEO scores for all content at <a href=":url">AEO Multilingual Dashboard</a>.', [':url' => Url::fromRoute('aeo_multilingual.dashboard')->toString()]) . '</dd>';
        $output .= '<dt>' . $this->t('Per-node audit') . '</dt>';
        $output .= '<dd>' . $this->t('Edit any node and click the "AEO Multilingual" tab.') . '</dd>';
        $output .= '</dl>';
        return $output;
    }
    return NULL;
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path): array {
    return [
      'aeo_multilingual_dashboard' => [
        'variables' => [
          'languages' => [],
          'nodes' => [],
          'summary' => [],
        ],
        'template' => 'aeo-multilingual-dashboard',
      ],
      'aeo_multilingual_node_tab' => [
        'variables' => [
          'node' => NULL,
          'results' => [],
          'languages' => [],
        ],
        'template' => 'aeo-multilingual-node-tab',
      ],
    ];
  }

}
