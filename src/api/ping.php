<?php
// ping.php - quick health check
$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';

$conn = @mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);
$ok = $conn ? true : false;
json_response(['success'=>true,'db_connected'=>$ok,'server_time'=>date('c')]);
