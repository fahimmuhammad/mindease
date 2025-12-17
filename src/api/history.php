<?php require_once __DIR__ . '/../src/lib/auth_guard.php'; ?>
<?php
// history.php - returns recent entries (user-agnostic simple history)
$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

$conn = @mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);
if (!$conn) { json_response(['success'=>false,'error'=>'DB_CONNECTION_FAILED'],500); exit; }

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
if ($limit<=0) $limit = 20;
if ($limit>200) $limit = 200;

$sql = "SELECT id, score, scale, type, text_input, note, provider, suggestion_id, created_at FROM entries ORDER BY created_at DESC LIMIT ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) { json_response(['success'=>false,'error'=>'DB_PREPARE_FAILED'],500); exit; }

mysqli_stmt_bind_param($stmt, 'i', $limit);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    if ($r['suggestion_id']) {
        $sid = (int)$r['suggestion_id'];
        $q2 = mysqli_prepare($conn, "SELECT id, slug, title, type, duration_seconds, content_json FROM actions WHERE id = ? LIMIT 1");
        if ($q2) {
            mysqli_stmt_bind_param($q2, 'i', $sid);
            mysqli_stmt_execute($q2);
            $r2 = mysqli_stmt_get_result($q2);
            if ($r2 && $act = mysqli_fetch_assoc($r2)) {
                $act['content_json'] = json_decode($act['content_json'], true) ?: [];
                $r['suggestion'] = $act;
            }
            mysqli_stmt_close($q2);
        }
    }
    $rows[] = $r;
}
mysqli_stmt_close($stmt);
json_response(['success'=>true,'data'=>$rows]);
