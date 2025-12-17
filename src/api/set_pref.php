<?php
// set_pref.php - set simple preferences to src/data/preferences.json
$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

$raw = json_decode(file_get_contents('php://input'), true) ?: $_POST;
if (!$raw || !is_array($raw)) json_response(['success'=>false,'error'=>'INVALID_PAYLOAD'],400);

$pref_file = __DIR__ . '/../data/preferences.json';
$current = [];
if (file_exists($pref_file)) {
    $current = json_decode(file_get_contents($pref_file), true) ?: [];
}
$merged = array_merge($current, $raw);
if (file_put_contents($pref_file, json_encode($merged, JSON_PRETTY_PRINT)) === false) {
    json_response(['success'=>false,'error'=>'WRITE_FAILED'],500);
    exit;
}
json_response(['success'=>true,'data'=>$merged]);
