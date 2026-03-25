<?php

namespace Drupal\aeo_multilingual\Service;

/**
 * Calculates overall AEO scores from individual check results.
 */
class ScoreCalculator {

  /**
   * Calculate overall score from individual check results.
   *
   * @param array $checks
   *   Array of check results, each with a 'score' key.
   *
   * @return int
   *   Overall score (0-100).
   */
  public function calculateOverallScore(array $checks): int {
    if (empty($checks)) {
      return 0;
    }
    $total = array_sum(array_column($checks, 'score'));
    return (int) round($total / count($checks));
  }

  /**
   * Get status label for a score.
   *
   * @param int $score
   *   Score (0-100).
   *
   * @return string
   *   'pass', 'warning', or 'fail'.
   */
  public function getStatus(int $score): string {
    if ($score >= 80) {
      return 'pass';
    }
    elseif ($score >= 50) {
      return 'warning';
    }
    return 'fail';
  }

  /**
   * Get CSS class for a score.
   *
   * @param int $score
   *   Score (0-100).
   *
   * @return string
   *   CSS modifier class.
   */
  public function getScoreClass(int $score): string {
    if ($score >= 80) {
      return 'good';
    }
    elseif ($score >= 50) {
      return 'warning';
    }
    return 'poor';
  }

}
