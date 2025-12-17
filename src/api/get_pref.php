<?php
// get_pref.php - reads preferences.json (simple)
$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

$pref_file = __DIR__ . '/../data/preferences.json';
if (!file_exists($pref_file)) {
    json_response(['success'=>true,'data'=>new stdClass()]);
    exit;
}
$raw = file_get_contents($pref_file);
$data = json_decode($raw, true) ?: new stdClass();
json_response(['success'=>true,'data'=>$data]);
