<?php
// generate_suggestion.php - create a suggestion from a template or AI prompt (minimal)
$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

$conn = @mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);
if (!$conn) { json_response(['success'=>false,'error'=>'DB_CONNECTION_FAILED'],500); exit; }

$raw = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$title = isset($raw['title']) ? trim($raw['title']) : null;
$type = isset($raw['type']) ? trim($raw['type']) : 'other';
$duration = isset($raw['duration_seconds']) ? (int)$raw['duration_seconds'] : 120;
$content = isset($raw['content']) ? $raw['content'] : [];
$content_json = json_encode($content);

if (!$title) json_response(['success'=>false,'error'=>'MISSING_TITLE'],400);

// simple insert
$stmt = mysqli_prepare($conn, "INSERT INTO actions (slug, title, type, duration_seconds, content_json, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
if (!$stmt) { json_response(['success'=>false,'error'=>'DB_PREPARE_FAILED'],500); exit; }
$slug = preg_replace('/[^a-z0-9]+/','-', strtolower($title));
mysqli_stmt_bind_param($stmt, 'sssis', $slug, $title, $type, $duration, $content_json);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    json_response(['success'=>false,'error'=>'DB_EXECUTE_FAILED','message'=>mysqli_stmt_error($stmt)],500);
    mysqli_stmt_close($stmt);
    exit;
}
$id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
json_response(['success'=>true,'id'=>$id]);
