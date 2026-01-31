<?php

namespace App\Modules\Assessment\Application\Services;

class AssessmentCalculationService
{
    /**
     * Calculate Total Score from array of scores
     */
    public function calculateTotal(array $scores): float
    {
        // Simple sum of all values
        $sum = 0;
        foreach ($scores as $score) {
            $sum += (float) $score;
        }
        return $sum;
    }

    /**
     * Calculate Average Score from array of scores
     */
    public function calculateAverage(array $scores): float
    {
        $count = count($scores);
        if ($count === 0) {
            return 0.0;
        }

        $total = $this->calculateTotal($scores);
        return round($total / $count, 2);
    }

    /**
     * Determine Predicate based on Type and Average Score
     *
     * @param string $type 'english' | 'computer'
     * @param float $score
     * @return string
     */
    public function determinePredicate(string $type, float $score): string
    {
        if ($type === 'computer') {
            return $this->getComputerPredicate($score);
        }

        // Default to English logic
        return $this->getEnglishPredicate($score);
    }

    /**
     * Determine English Level (A1-C2) based on score
     * This is a simplified logic, usually based on specific ranges
     */
    public function determineEnglishLevel(float $score): string
    {
        // Example CEFR mapping
        if ($score >= 95) return 'C2'; // Proficient
        if ($score >= 85) return 'C1'; // Advanced
        if ($score >= 75) return 'B2'; // Upper Intermediate
        if ($score >= 65) return 'B1'; // Intermediate
        if ($score >= 50) return 'A2'; // Elementary
        return 'A1'; // Beginner
    }

    private function getEnglishPredicate(float $score): string
    {
        // Scheme: EXCELLENT / VERY GOOD / GOOD / FAIR / FAIL
        if ($score >= 90) return 'EXCELLENT';
        if ($score >= 80) return 'VERY GOOD';
        if ($score >= 70) return 'GOOD';
        if ($score >= 60) return 'FAIR';
        return 'FAIL';
    }

    private function getComputerPredicate(float $score): string
    {
        // Scheme: 90-100 A, 80-89 B, 70-79 C, 60-69 D, <60 FAIL
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'FAIL'; // Less than 60
    }
}
