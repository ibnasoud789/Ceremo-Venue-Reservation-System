<?php
// auth_customer.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Only allow customers through
if (! isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
  // Remember where they were trying to go
  $redirect = $_SERVER['REQUEST_URI'];
  header('Location: index.php?redirect=' . urlencode($redirect));
  exit;
}
