<?php
// src/lib/huggingface.php
// Simple HuggingFace wrapper. Attempts a quick sentiment / label classification
// and maps result to a stress score and tags. Returns array same shape as openai_analyze_text.

function huggingface_analyze_text(string $text, string $apiKey) : array {
    if (empty($apiKey)) {
        return ['success' => false, 'error' => 'NO_API_KEY'];
    }

    // Use a sentiment classification model endpoint (SST-2 style).
    // Many HF inference endpoints accept {"inputs":"..."} and return labels.
    $model = 'distilbert-base-uncased-finetuned-sst-2-english';
    $endpoint = "https://api-inference.huggingface.co/models/{$model}";

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    ]);
    $payload = json_encode(['inputs' => $text]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return ['success' => false, 'error' => 'CURL_ERROR: ' . $err];
    }
    if ($code < 200 || $code >= 300) {
        return ['success' => false, 'error' => "HTTP_{$code}", 'raw' => $resp];
    }

    $json = json_decode($resp, true);
    if (!is_array($json)) {
        return ['success' => false, 'error' => 'INVALID_JSON', 'raw' => $resp];
    }

    // Typical HF sentiment model returns array with label & score
    // e.g. [{"label":"NEGATIVE","score":0.99}]
    $label = null;
    $conf = null;
    if (isset($json[0]['label'])) {
        $label = strtoupper($json[0]['label']);
        $conf = isset($json[0]['score']) ? (float)$json[0]['score'] : null;
    }

    // Map to score and tags
    $score = 5;
    $tags = [];
    $summary = '';

    if ($label === 'NEGATIVE') {
        // negative likely indicates stress / worry -> map to higher score
        $score = $conf !== null ? (int) min(10, round(6 + $conf * 3)) : 8;
        $tags[] = 'negative';
        $summary = 'Model detected negative sentiment; possible stress.';
    } elseif ($label === 'POSITIVE') {
        $score = $conf !== null ? (int) max(0, round(2 + (1 - $conf) * 3)) : 3;
        $tags[] = 'positive';
        $summary = 'Model detected positive sentiment.';
    } else {
        // fallback generic
        $score = 5;
        $tags[] = 'neutral';
        $summary = 'No strong sentiment detected.';
    }

    return [
        'success' => true,
        'data' => [
            'score' => $score,
            'tone_tags' => $tags,
            'summary' => $summary
        ],
        'raw' => $json
    ];
}
