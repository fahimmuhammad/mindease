<?php
// public/login.php
session_start();

// If already logged in, redirect away
if (!empty($_SESSION['logged_in'])) {
    header('Location: /index.php');
    exit;
}

$error = isset($_GET['error']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login — MindEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/css/style.css">

    <style>
        body {
            background: #f8fafc;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            margin: 0;
            color: #0f172a;
        }
        .container {
            max-width: 420px;
            margin: 80px auto;
            padding: 0 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(2,6,23,0.08);
            border: 1px solid rgba(15,23,42,0.05);
        }
        h1 {
            margin: 0 0 16px;
            font-size: 28px;
            text-align: center;
        }
        label {
            font-size: 14px;
            font-weight: 600;
            display: block;
            margin-top: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 15px;
        }
        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: #111827;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            margin-top: 14px;
            color: #b91c1c;
            font-size: 14px;
            text-align: center;
        }
        .small {
            margin-top: 18px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Login</h1>

        <form method="post" action="/login_process.php" autocomplete="off">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Sign In</button>
        </form>

        <div style="margin-top:16px; text-align:center;">
            <a href="/signup.php" style="color:#2563eb; text-decoration:none; font-weight:500;">
            Create a new account
            </a>
        </div>


        <?php if ($error): ?>
            <div class="error">
                Invalid email or password.
            </div>
        <?php endif; ?>

        <div class="small">
            MindEase — Secure Access
        </div>
    </div>
</div>

</body>
</html>
