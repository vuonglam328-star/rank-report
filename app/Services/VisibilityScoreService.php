<?php

namespace App\Services;

/**
 * VisibilityScoreService
 *
 * Calculates internal visibility score for a keyword position.
 * Based on the weighted scoring table in config/rankreport.php
 */
class VisibilityScoreService
{
    private array $points;

    public function __construct()
    {
        $this->points = config('rankreport.visibility_points', []);
    }

    /**
     * Calculate visibility points for a given position.
     *
     * @param int|null $position  Keyword ranking position (1 = best)
     * @return int                Visibility score (0–100)
     */
    public function calculate(?int $position): int
    {
        if ($position === null || $position < 1) {
            return 0;
        }

        // Exact match first (positions 1, 2, 3)
        if (isset($this->points[$position])) {
            return (int) $this->points[$position];
        }

        // Range matches
        if ($position <= 5)   return 70;
        if ($position <= 10)  return 50;
        if ($position <= 20)  return 20;
        if ($position <= 50)  return 5;
        if ($position <= 100) return 1;

        return 0;
    }

    /**
     * Get the display label for a position group.
     */
    public function getPositionGroup(?int $position): string
    {
        if ($position === null || $position < 1) return 'outside';
        if ($position <= 3)   return 'top_3';
        if ($position <= 10)  return 'top_10';
        if ($position <= 20)  return 'top_20';
        if ($position <= 50)  return 'top_50';
        if ($position <= 100) return 'top_100';
        return 'outside';
    }
}
