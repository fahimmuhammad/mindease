<?php
// public/login_process.php

session_start();

$config = include __DIR__ . '/../src/config/config.php';

/* -------------------- DB CONNECTION -------------------- */
$conn = mysqli_connect(
    $config['db']['host'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['name']
);

if (!$conn) {
    die('Database connection failed');
}

mysqli_set_charset($conn, 'utf8mb4');

/* -------------------- INPUT -------------------- */
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: /login.php?error=1');
    exit;
}

/* -------------------- QUERY -------------------- */
$sql = "SELECT id, password FROM users WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die('Prepare failed');
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

/* -------------------- CHECK USER EXISTS -------------------- */
if (mysqli_stmt_num_rows($stmt) !== 1) {
    header('Location: /login.php?error=1');
    exit;
}

/* -------------------- FETCH DATA -------------------- */
mysqli_stmt_bind_result($stmt, $user_id, $password_hash);
mysqli_stmt_fetch($stmt);

/* -------------------- VERIFY PASSWORD -------------------- */
if (!is_string($password_hash) || !password_verify($password, $password_hash)) {
    header('Location: /login.php?error=1');
    exit;
}

/* -------------------- LOGIN SUCCESS -------------------- */
session_regenerate_id(true);

$_SESSION['user_id']  = $user_id;
$_SESSION['user_email'] = $email;
$_SESSION['logged_in'] = true;

header('Location: /index.php');
exit;
