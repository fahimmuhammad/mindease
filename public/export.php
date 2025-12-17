<?php
// public/export.php
// Export entries as CSV (no raw input_text included)

require_once __DIR__ . '/../src/config/config.php';

// Simple auth gate: only allow from localhost (basic safety for local dev)
$allowedHosts = ['127.0.0.1', '::1', 'localhost'];
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, $allowedHosts)) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

// Optional query params
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;
$since = isset($_GET['since']) ? trim($_GET['since']) : null;

// Build query
$sql = "SELECT id, method, score, tone_tags, suggestion_id, feedback, created_at FROM entries";
$params = [];
$types = '';
if ($since) {
    $sql .= " WHERE created_at >= ?";
    $params[] = $since;
    $types .= 's';
}
$sql .= " ORDER BY created_at DESC LIMIT ?";
$params[] = $limit;
$types .= 'i';

// Prepare statement
$stmt = mysqli_prepare($conn, $sql);
if ($stmt === false) {
    http_response_code(500);
    echo "DB prepare failed";
    exit;
}

// Bind params if any
if (count($params) === 2) {
    mysqli_stmt_bind_param($stmt, $types, $params[0], $params[1]);
} elseif (count($params) === 1) {
    mysqli_stmt_bind_param($stmt, $types, $params[0]);
}

// Execute
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($res === false) {
    http_response_code(500);
    echo "DB query failed";
    exit;
}

// Prepare CSV
$filename = "mindease_export_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');

// Header row
fputcsv($out, ['id','method','score','tone_tags','suggestion_id','feedback','created_at']);

// Rows
while ($row = mysqli_fetch_assoc($res)) {
    // tone_tags may be stored as JSON/text; ensure it's a simple string
    $tags = $row['tone_tags'];
    if (is_string($tags)) {
        $dec = json_decode($tags, true);
        if (is_array($dec)) {
            $tags = implode('|', $dec);
        }
    } elseif (is_array($tags)) {
        $tags = implode('|', $tags);
    } else {
        $tags = '';
    }

    fputcsv($out, [
        $row['id'],
        $row['method'],
        $row['score'],
        $tags,
        $row['suggestion_id'],
        $row['feedback'],
        $row['created_at']
    ]);
}

fclose($out);
exit;
