<?php
$config = include __DIR__ . '/../src/config/config.php';

// minimal direct call to openai via PHP curl
$data = [
  "model" => $config['openai_model'] ?? 'gpt-3.5-turbo',
  "messages" => [
    ["role" => "system", "content" => "You are a test."],
    ["role" => "user", "content" => "Say ok"]
  ],
  "max_tokens" => 5
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer " . ($config['openai_api_key'] ?? '')
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$res = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

header('Content-Type: application/json');
echo json_encode(['http_code' => $info['http_code'], 'curl_error' => curl_error($ch) , 'response' => json_decode($res, true)], JSON_PRETTY_PRINT);
