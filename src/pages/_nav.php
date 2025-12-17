<?php
// src/pages/_nav.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = !empty($_SESSION['logged_in']);
$userEmail = $_SESSION['user_email'] ?? null;
?>

<nav style="background:#0f172a;padding:14px 20px;">
  <div style="max-width:1100px;margin:auto;display:flex;align-items:center;justify-content:space-between;color:#fff;">

    <!-- LEFT -->
    <div style="display:flex;gap:16px;align-items:center;">
      <a href="/index.php"
         style="color:#fff;text-decoration:none;font-weight:700;">
        MindEase
      </a>

      <?php if ($isLoggedIn): ?>
        <a href="/quick_check.php" style="color:#cbd5f5;text-decoration:none;">Quick Check</a>
        <a href="/text_check.php"  style="color:#cbd5f5;text-decoration:none;">Text Check</a>
        <a href="/history.php"     style="color:#cbd5f5;text-decoration:none;">History</a>
      <?php endif; ?>
    </div>

    <!-- RIGHT -->
    <div style="display:flex;gap:14px;align-items:center;">
      <?php if ($isLoggedIn): ?>
        <span style="color:#94a3b8;font-size:14px;">
          Logged in as <strong>User #<?= htmlspecialchars((string)$userEmail) ?></strong>
        </span>

        <form action="/logout.php" method="post" style="margin:0;">
          <button type="submit"
            style="background:#ef4444;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;">
            Logout
          </button>
        </form>
      <?php else: ?>
        <a href="/login.php" style="color:#fff;text-decoration:none;">Login</a>
      <?php endif; ?>
    </div>

  </div>
</nav>
