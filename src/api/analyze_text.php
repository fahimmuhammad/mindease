<?php
// src/api/analyze_text.php
// FINAL — OpenAI-only, deterministic score, guaranteed activity

header('Content-Type: application/json');

$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

/* ===================== DB ===================== */
$conn = @mysqli_connect(
    $config['db']['host'] ?? 'localhost',
    $config['db']['user'] ?? 'root',
    $config['db']['pass'] ?? '',
    $config['db']['name'] ?? 'mindease'
);

if (!$conn) {
    json_response(['success'=>false,'error'=>'DB_CONNECTION_FAILED']);
    exit;
}
mysqli_set_charset($conn,'utf8mb4');

/* ===================== INPUT ===================== */
$raw = json_decode(file_get_contents('php://input'), true);
if (!is_array($raw)) $raw = $_POST ?? [];

$text = trim((string)($raw['text'] ?? ''));
if ($text === '') {
    json_response(['success'=>false,'error'=>'EMPTY_TEXT']);
    exit;
}

$useAI   = !empty($raw['useAI']) || !empty($raw['use_ai']);
$consent = !empty($raw['consent']) || !empty($raw['saveHistory']) || !empty($raw['save_history']);

if (!$useAI) {
    json_response(['success'=>false,'error'=>'AI_DISABLED']);
    exit;
}

/* ===================== OPENAI REQUIRED ===================== */
if (empty($config['openai_api_key'])) {
    json_response(['success'=>false,'error'=>'OPENAI_KEY_MISSING']);
    exit;
}

/* ===================== SYSTEM PROMPT ===================== */
$system = <<<SYS
Return ONLY valid JSON.

Score = DISTRESS LEVEL:
0 = calm / relaxed
10 = extreme distress / suicidal ideation

Rules:
- relaxed, calm → 0–2
- neutral → 3–4
- anxious / stressed → 5–7
- sad / depressed → 6–8
- suicide / self-harm → 9–10

Schema:
{
  "score": number,
  "summary": string,
  "tone_tags": ["tag1","tag2"]
}

NO extra text.
SYS;

/* ===================== OPENAI REQUEST ===================== */
$payload = [
    'model' => $config['openai_model'] ?? 'gpt-3.5-turbo-0125',
    'messages' => [
        ['role'=>'system','content'=>$system],
        ['role'=>'user','content'=>$text]
    ],
    'temperature' => 0.0,
    'max_tokens' => 200
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
    CURLOPT_TIMEOUT => 20
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200 || !$response) {
    json_response(['success'=>false,'error'=>'OPENAI_FAILED']);
    exit;
}

$decoded = json_decode($response, true);
$content = $decoded['choices'][0]['message']['content'] ?? '';
$ai = json_decode($content, true);

if (!is_array($ai)) {
    json_response(['success'=>false,'error'=>'OPENAI_BAD_JSON','raw'=>$content]);
    exit;
}

/* ===================== NORMALIZE ===================== */
$score = is_numeric($ai['score'] ?? null) ? (int)$ai['score'] : 5;
$score = max(0, min(10, $score));

$summary = trim($ai['summary'] ?? 'Emotional state identified.');
$tone_tags = is_array($ai['tone_tags'] ?? null) ? array_values($ai['tone_tags']) : ['neutral'];

/* ===================== ACTIVITY LOGIC ===================== */
/*
 Distress → Activity mapping
*/
if ($score >= 9) {
    $action_type = 'grounding';
    $duration = 300;
}
elseif ($score >= 7) {
    $action_type = 'breathing';
    $duration = 180;
}
elseif ($score >= 4) {
    $action_type = 'journal';
    $duration = 300;
}
else {
    $action_type = 'relaxation';
    $duration = 240;
}

/* ===================== ACTION LOOKUP ===================== */
$suggestion_id = null;

$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM actions WHERE type = ? ORDER BY ABS(duration_seconds - ?) ASC LIMIT 1"
);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'si', $action_type, $duration);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $sid);
    if (mysqli_stmt_fetch($stmt)) {
        $suggestion_id = (int)$sid;
    }
    mysqli_stmt_close($stmt);
}

/* HARD GUARANTEE */
if (!$suggestion_id) {
    $fallback = mysqli_query($conn,"SELECT id FROM actions LIMIT 1");
    if ($fallback && ($row = mysqli_fetch_assoc($fallback))) {
        $suggestion_id = (int)$row['id'];
    }
}

/* ===================== SAVE ===================== */
if ($consent) {
    $text_sql = mysqli_real_escape_string($conn,$text);
    $tone_sql = mysqli_real_escape_string($conn,json_encode($tone_tags));

    mysqli_query($conn,"
        INSERT INTO entries
        (method,input_text,score,provider,scale,tone_tags,suggestion_id,created_at)
        VALUES
        ('text','$text_sql',$score,'openai',10,'$tone_sql',$suggestion_id,NOW())
    ");
}

/* ===================== RESPONSE ===================== */
json_response([
    'success'=>true,
    'data'=>[
        'score'=>$score,
        'scale'=>10,
        'summary'=>$summary,
        'tone_tags'=>$tone_tags,
        'provider'=>'openai',
        'suggestion_id'=>$suggestion_id,
        'suggestion_hint'=>[
            'type'=>$action_type,
            'duration_seconds'=>$duration
        ]
    ]
]);

exit;
