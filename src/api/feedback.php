<?php
// feedback.php - store user feedback about suggestions or UI
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
$entry_id = isset($raw['entry_id']) ? (int)$raw['entry_id'] : null;
$rating = isset($raw['rating']) ? (int)$raw['rating'] : null;
$comment = isset($raw['comment']) ? $raw['comment'] : null;

if ($entry_id === null || $rating === null) json_response(['success'=>false,'error'=>'MISSING_FIELDS'],400);

$stmt = mysqli_prepare($conn, "INSERT INTO feedback (entry_id, rating, comment, created_at) VALUES (?, ?, ?, NOW())");
if (!$stmt) { json_response(['success'=>false,'error'=>'DB_PREPARE_FAILED'],500); exit; }
mysqli_stmt_bind_param($stmt, 'iis', $entry_id, $rating, $comment);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) { json_response(['success'=>false,'error'=>'DB_EXECUTE_FAILED'],500); exit; }
$id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
json_response(['success'=>true,'id'=>$id]);
