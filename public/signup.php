<?php
session_start();
if (!empty($_SESSION['logged_in'])) {
    header('Location: /index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign Up — MindEase</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    body { background:#f8fafc; font-family:system-ui; }
    .box {
      max-width:420px;
      margin:80px auto;
      background:#fff;
      padding:28px;
      border-radius:12px;
      box-shadow:0 10px 30px rgba(0,0,0,.08);
    }
    input,button {
      width:100%;
      padding:12px;
      margin-top:10px;
      border-radius:8px;
      border:1px solid #ddd;
    }
    button {
      background:#111827;
      color:#fff;
      font-weight:600;
      cursor:pointer;
    }
    .error { color:#b91c1c; margin-top:12px; }
  </style>
</head>
<body>

<div class="box">
  <h2>Create Account</h2>

  <?php if (isset($_GET['error'])): ?>
    <div class="error">Email already exists</div>
  <?php endif; ?>

  <form method="POST" action="/signup_process.php">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Sign Up</button>
  </form>

  <p style="margin-top:14px;">
    Already have an account?
    <a href="/login.php">Login</a>
  </p>
</div>

</body>
</html>
