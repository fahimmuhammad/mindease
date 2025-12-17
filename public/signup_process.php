<?php
session_start();

$config = include __DIR__ . '/../src/config/config.php';

$conn = mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if (!$conn) {
    die('DB error');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: /signup.php?error=1');
    exit;
}

/* Check if email exists */
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    header('Location: /signup.php?error=1');
    exit;
}

/* Create user */
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (email, password, created_at) VALUES (?, ?, NOW())"
);
mysqli_stmt_bind_param($stmt, 'ss', $email, $hash);
mysqli_stmt_execute($stmt);

/* Auto-login */
$_SESSION['user_id'] = mysqli_insert_id($conn);
$_SESSION['logged_in'] = true;

header('Location: /index.php');
exit;
