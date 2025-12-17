<?php
// src/api/save_entry.php
// Save a user entry (quick/text) to entries table

$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php'; // must define json_response()

// DB connect
$conn = @mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);
if (!$conn) {
    json_response(['success' => false, 'error' => 'DB_CONNECTION_FAILED', 'detail' => mysqli_connect_error()], 500);
    exit;
}

// Read input (JSON preferred)
$body = file_get_contents('php://input');
$in = json_decode($body, true);
if (!is_array($in)) {
    $in = $_POST;
}

// Normalize fields. Frontend may send different keys depending on quick/text.
$method = isset($in['method']) ? (string)$in['method'] : (isset($in['type']) ? (string)$in['type'] : null);
if (!$method) {
    // try to guess from presence of fields
    if (isset($in['score']) && !isset($in['text'])) $method = 'quick';
    elseif (isset($in['text']) || isset($in['input_text'])) $method = 'text';
    else $method = 'unknown';
}

// map input_text: frontend might send 'text', 'input_text', 'note' etc
$input_text = null;
if (isset($in['input_text'])) $input_text = $in['input_text'];
elseif (isset($in['text'])) $input_text = $in['text'];
elseif (isset($in['note'])) $input_text = $in['note'];

// slider/score mapping
$slider_value = null;
if (isset($in['slider_value'])) $slider_value = is_numeric($in['slider_value']) ? (int)$in['slider_value'] : null;
elseif (isset($in['slider'])) $slider_value = is_numeric($in['slider']) ? (int)$in['slider'] : null;
elseif (isset($in['score'])) $slider_value = is_numeric($in['score']) ? (int)$in['score'] : null;

// consistent score value (0..scale)
$score = $slider_value; // historically they keep same
$scale = isset($in['scale']) && is_numeric($in['scale']) ? (int)$in['scale'] : 10;

// other fields
$note = isset($in['note']) ? $in['note'] : null; // optional free note
$provider = isset($in['provider']) ? $in['provider'] : 'local-heuristic';
$tone_tags = isset($in['tone_tags']) ? $in['tone_tags'] : []; // array or string
if (!is_string($tone_tags)) {
    // store as JSON string
    $tone_tags_json = json_encode($tone_tags);
} else {
    // already string, keep as string (but ensure JSON-like)
    $tone_tags_json = $tone_tags;
}

// suggestion id may be null or numeric
$suggestion_id = null;
if (isset($in['suggestion_id']) && $in['suggestion_id'] !== '' && is_numeric($in['suggestion_id'])) {
    $suggestion_id = (int)$in['suggestion_id'];
}

// extra small sanitizations
$method = substr($method, 0, 64);
$provider = substr($provider, 0, 64);

// Prepare insert. Because suggestion_id can be NULL, use two queries for simplicity.
if ($suggestion_id !== null) {
    $sql = "INSERT INTO entries 
        (user_hash, method, input_text, slider_value, score, scale, type, note, provider, tone_tags, suggestion_id, feedback, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        json_response(['success' => false, 'error' => 'DB_PREPARE_FAILED', 'detail' => mysqli_error($conn)], 500);
        exit;
    }
    // user_hash not used in prototype -> NULL
    $user_hash = null;
    $feedback = null;
    // bind: s (user_hash), s(method), s(input_text), i(slider), i(score), i(scale),
    // s(type), s(note), s(provider), s(tone_tags), i(suggestion_id)
    mysqli_stmt_bind_param($stmt,
        'ssssiiisssi',
        $user_hash,
        $method,
        $input_text,
        $slider_value,
        $score,
        $scale,
        $method,    // type: keep same as method ('quick' or 'text')
        $note,
        $provider,
        $tone_tags_json,
        $suggestion_id
    );
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) {
        json_response(['success' => false, 'error' => 'DB_EXEC_FAILED', 'detail' => mysqli_stmt_error($stmt)], 500);
        exit;
    }
    $insert_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
} else {
    // Insert with suggestion_id = NULL
    $sql = "INSERT INTO entries 
        (user_hash, method, input_text, slider_value, score, scale, type, note, provider, tone_tags, suggestion_id, feedback, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        json_response(['success' => false, 'error' => 'DB_PREPARE_FAILED', 'detail' => mysqli_error($conn)], 500);
        exit;
    }
    $user_hash = null;
    mysqli_stmt_bind_param($stmt,
        'ssssiiisss',
        $user_hash,
        $method,
        $input_text,
        $slider_value,
        $score,
        $scale,
        $method,
        $note,
        $provider,
        $tone_tags_json
    );
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) {
        json_response(['success' => false, 'error' => 'DB_EXEC_FAILED', 'detail' => mysqli_stmt_error($stmt)], 500);
        exit;
    }
    $insert_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
}

// success
json_response([
    'success' => true,
    'saved_id' => $insert_id,
    'data_received' => $in,
    'mapped' => [
        'method' => $method,
        'input_text' => $input_text,
        'slider_value' => $slider_value,
        'score' => $score,
        'scale' => $scale,
        'suggestion_id' => $suggestion_id
    ]
]);
