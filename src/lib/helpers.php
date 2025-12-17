<?php
// helpers.php - small utility helpers

/**
 * Normalize words and compute a simple heuristic stress score from text.
 * Returns integer 0-10.
 */
function heuristic_score_from_text(string $text): int {
    $text = mb_strtolower($text);
    $negatives = ['overwhelm','panic','anx','stuck','hate','impossible','burnout',"can't",'cant','stress','stressed','nervous','sad','depressed','angry','worried','worry'];
    $positives = ['ok','fine','good','calm','relaxed','better','okay','sleep','sleeping'];

    $score = 4; // baseline
    foreach ($negatives as $w) {
        if (mb_stripos($text, $w) !== false) $score += 2;
    }
    foreach ($positives as $w) {
        if (mb_stripos($text, $w) !== false) $score -= 1;
    }

    // punctuation intensity
    $exclam = substr_count($text, '!');
    $score += min($exclam, 2);

    // length factor
    $len = mb_strlen($text);
    if ($len > 200) $score += 1;

    $score = max(0, min(10, (int)$score));
    return $score;
}

/**
 * Map numeric score to level string
 */
function score_level(int $score): string {
    if ($score >= 8) return 'Very high';
    if ($score >= 6) return 'High';
    if ($score >= 3) return 'Moderate';
    return 'Low';
}

/**
 * Safe JSON response helper
 */
function json_response($data, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
