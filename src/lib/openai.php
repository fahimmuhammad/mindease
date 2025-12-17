<?php
// src/lib/openai.php
// Simple OpenAI wrapper using curl. Returns associative array:
// ['success'=>bool, 'data'=>['score'=>int,'tone_tags'=>array,'summary'=>string], 'error'=>string]

function openai_analyze_text(string $text, string $apiKey) : array {
    if (empty($apiKey)) {
        return ['success' => false, 'error' => 'NO_API_KEY'];
    }

    $endpoint = "https://api.openai.com/v1/chat/completions";
    $model = "gpt-3.5-turbo";

    // Prompt asks for a strict JSON output
    $system = "You are an emotion and sentiment analyzer. When given a user's short free-form text, " .
              "return ONLY a single JSON object (no extra explanation) with these fields: " .
              "\"score\" (integer 0-10, where 10 = extremely stressed), " .
              "\"tone_tags\" (an array of short tags like [\"anxious\",\"sad\"]), and " .
              "\"summary\" (one-sentence neutral summary). Respond in valid JSON only.";

    $user = "Analyze this text for stress: " . $text;

    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user]
        ],
        'temperature' => 0.2,
        'max_tokens' => 300,
        'top_p' => 1,
        'n' => 1,
        'presence_penalty' => 0,
        'frequency_penalty' => 0
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

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
        // try to extract JSON substring from text if present
        $textBody = $resp;
        if (preg_match('/\{.*\}/s', $textBody, $m)) {
            $maybe = json_decode($m[0], true);
            if (is_array($maybe)) $json = ['choices'=>[['message'=>['content'=>$m[0]]]]];
        }
    }

    // get the assistant content
    $content = null;
    if (isset($json['choices'][0]['message']['content'])) {
        $content = trim($json['choices'][0]['message']['content']);
    } elseif (isset($json['choices'][0]['text'])) {
        $content = trim($json['choices'][0]['text']);
    }

    if (!$content) {
        return ['success' => false, 'error' => 'NO_CONTENT', 'raw' => $resp];
    }

    // try decode content as JSON
    $parsed = json_decode($content, true);
    if (!is_array($parsed)) {
        // attempt to salvage JSON inside content
        if (preg_match('/\{.*\}/s', $content, $m)) {
            $parsed = json_decode($m[0], true);
        }
    }

    if (!is_array($parsed)) {
        return ['success' => false, 'error' => 'INVALID_JSON_FROM_MODEL', 'raw_content' => $content];
    }

    // normalize fields
    $score = isset($parsed['score']) ? (int)$parsed['score'] : null;
    $tags = isset($parsed['tone_tags']) && is_array($parsed['tone_tags']) ? $parsed['tone_tags'] : [];
    $summary = isset($parsed['summary']) ? trim($parsed['summary']) : '';

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
