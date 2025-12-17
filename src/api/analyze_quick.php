<?php
// src/api/analyze_quick.php
// OPENAI-ONLY — NO FALLBACK, NO HEURISTIC

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

function json_fail($msg, $code = 500) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

// --------------------
// Config check
// --------------------
if (empty($config['openai_api_key'])) {
    json_fail('OPENAI_KEY_MISSING', 500);
}

// --------------------
// Input parsing
// --------------------
$raw = json_decode(file_get_contents('php://input'), true);
if (!is_array($raw)) json_fail('INVALID_INPUT', 400);

$score = null;
foreach (['score','slider','value','sliderValue'] as $k) {
    if (isset($raw[$k]) && is_numeric($raw[$k])) {
        $score = (int)$raw[$k];
        break;
    }
}

if ($score === null || $score < 0 || $score > 10) {
    json_fail('INVALID_SCORE', 400);
}

$note = isset($raw['note']) ? trim((string)$raw['note']) : 'none';
$consent = !empty($raw['consent']);

// --------------------
// OpenAI CALL (MANDATORY)
// --------------------
$prompt = <<<PROMPT
You are a mental health assistant.

Given a stress score from 0–10, return STRICT JSON ONLY with:
{
  "score": number,
  "scale": 10,
  "summary": string,
  "tone_tags": array of strings,
  "suggestion_hint": {
    "type": one of ["breathing","grounding","journal","other"],
    "duration_seconds": number
  }
}

Score: {$score}
User note: {$note}
PROMPT;

$payload = [
    'model' => $config['openai_model'] ?? 'gpt-3.5-turbo-0125',
    'messages' => [
        ['role'=>'system','content'=>'Return ONLY valid JSON. No markdown.'],
        ['role'=>'user','content'=>$prompt]
    ],
    'temperature' => 0.0
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer '.$config['openai_api_key']
    ],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200 || !$response) {
    json_fail('OPENAI_REQUEST_FAILED', 502);
}

$decoded = json_decode($response, true);
$text = $decoded['choices'][0]['message']['content'] ?? null;
if (!$text) json_fail('OPENAI_EMPTY_RESPONSE', 502);

$data = json_decode($text, true);
if (!is_array($data) || !isset($data['summary'])) {
    json_fail('OPENAI_INVALID_JSON', 502);
}

// --------------------
// DB (optional save)
// --------------------
$conn = @mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

$suggestion_id = null;
if ($conn && !empty($data['suggestion_hint']['type'])) {
    $t = $data['suggestion_hint']['type'];
    $st = mysqli_prepare($conn,"SELECT id FROM actions WHERE type=? LIMIT 1");
    if ($st) {
        mysqli_stmt_bind_param($st,'s',$t);
        mysqli_stmt_execute($st);
        mysqli_stmt_bind_result($st,$sid);
        if (mysqli_stmt_fetch($st)) $suggestion_id = (int)$sid;
        mysqli_stmt_close($st);
    }
}

if ($conn && $consent) {
    $tones = mysqli_real_escape_string($conn, json_encode($data['tone_tags'] ?? []));
    mysqli_query($conn,"
        INSERT INTO entries (method,slider_value,score,provider,scale,tone_tags,suggestion_id,created_at)
        VALUES (
            'quick',
            {$score},
            {$data['score']},
            'openai',
            10,
            '{$tones}',
            ".($suggestion_id ?? 'NULL').",
            NOW()
        )
    ");
}

// --------------------
// FINAL RESPONSE
// --------------------
echo json_encode([
    'success' => true,
    'data' => [
        'score' => $data['score'],
        'scale' => 10,
        'summary' => $data['summary'],
        'tone_tags' => $data['tone_tags'] ?? [],
        'provider' => 'openai',
        'suggestion_id' => $suggestion_id,
        'suggestion_hint' => $data['suggestion_hint'] ?? null
    ]
]);
exit;
