<?php
/**
 * actions.php
 * Returns list of coping actions from the DB
 *
 * Usage:
 *  GET /src/api/actions.php?type=breathing&limit=20
 *
 * This file expects:
 *  - src/config/config.php to return the config array
 *  - src/lib/helpers.php to define json_response() and any helpers used
 */

// load config
$config = include __DIR__ . '/../config/config.php';

// load helpers (json_response(), etc.)
require_once __DIR__ . '/../lib/helpers.php';

// connect to DB using config
$conn = @mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if (!$conn) {
    json_response([
        'success' => false,
        'error' => 'DB_CONNECTION_FAILED',
        'message' => mysqli_connect_error()
    ], 500);
    exit;
}

// read optional filters
$type  = isset($_GET['type']) && $_GET['type'] !== '' ? trim($_GET['type']) : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0) $limit = 50;
if ($limit > 500) $limit = 500; // safety cap

// base query
$sql = "SELECT id, slug, title, type, duration_seconds, content_json FROM actions";
$where = [];
$params = [];     // values
$types = '';      // bind types string for mysqli_stmt_bind_param

if ($type) {
    $where[] = "type = ?";
    $params[] = $type;
    $types .= 's';
}

// LIMIT is always a param (prepared)
$where_sql = count($where) ? (' WHERE ' . implode(' AND ', $where)) : '';
$sql .= $where_sql . " ORDER BY id ASC LIMIT ?";

// add limit param (integer)
$params[] = $limit;
$types .= 'i';

// prepare statement
$stmt = mysqli_prepare($conn, $sql);
if ($stmt === false) {
    json_response([
        'success' => false,
        'error' => 'DB_PREPARE_FAILED',
        'message' => mysqli_error($conn)
    ], 500);
    exit;
}

// bind params if any
if (count($params) > 0) {
    // mysqli_stmt_bind_param requires references
    // build args: [ $stmt, $types, &$params[0], &$params[1], ... ]
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        // make each element a reference
        $bind_names[$i + 1] = &$params[$i];
    }
    // call_user_func_array requires first argument to be the function name on mysqli_stmt
    // but mysqli_stmt_bind_param is a procedural function that expects the statement resource first
    array_unshift($bind_names, $stmt);
    // call the bind
    call_user_func_array('mysqli_stmt_bind_param', $bind_names);
}

// execute
$exec_ok = mysqli_stmt_execute($stmt);
if ($exec_ok === false) {
    json_response([
        'success' => false,
        'error' => 'DB_EXECUTE_FAILED',
        'message' => mysqli_stmt_error($stmt)
    ], 500);
    mysqli_stmt_close($stmt);
    exit;
}

// fetch results
$res = mysqli_stmt_get_result($stmt);
$rows = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        // decode content_json to PHP array/object (safe fallback)
        $decoded = json_decode($r['content_json'], true);
        $r['content_json'] = $decoded !== null ? $decoded : [];
        $rows[] = $r;
    }
    mysqli_free_result($res);
}

mysqli_stmt_close($stmt);

// respond
json_response([
    'success' => true,
    'data' => $rows
], 200);
