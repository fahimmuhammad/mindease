<?php
// src/lib/auth_guard.php
session_start();

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
