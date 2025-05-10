<?php
// login_process.php
session_start();
include 'db.php';  // defines $conn

// 1) grab inputs
$userType   = $_POST['user_type']    ?? '';
$password   = $_POST['password']     ?? '';
$idInput    = (int) ($_POST['identifier'] ?? 0);
$redirectTo = $_POST['redirect']      ?? '';

// 2) decide which table and default dashboard based on user type
switch ($userType) {
  case 'admin':
    $table    = 'admins';
    $default = '/admin_dashboard.php';
    break;
  case 'customer':
    $table    = 'customers';
    $default = '/customer_dashboard.php';
    break;
  case 'venue':
    $table    = 'venues';
    $default = '/venueDashboard.php';
    break;
  default:
    die('<p style="color:red;">Invalid user type.</p>');
}

// 3) fetch credentials and name (avatar is only for customer)
if ($userType === 'venue' || $userType === 'admin') {
  $sql = "SELECT id, password, name
          FROM `$table` WHERE id = ? LIMIT 1";
} else {
  $sql = "SELECT id, password, name, avatar
          FROM `$table` WHERE id = ? LIMIT 1";
}

$stmt = $conn->prepare($sql);
if (! $stmt) {
  die('DB prepare error: ' . $conn->error);
}
$stmt->bind_param('i', $idInput);
$stmt->execute();

// bind results
if ($userType === 'venue' || $userType === 'admin') {
  $stmt->bind_result($userId, $storedPwd, $name);
} else {
  $stmt->bind_result($userId, $storedPwd, $name, $avatar);
}

if (! $stmt->fetch()) {
  $stmt->close();
  die('<p style="color:red;">No account found with that ID.</p>');
}
$stmt->close();

// 4) check plain-text password
if ($password !== $storedPwd) {
  die('<p style="color:red;">Incorrect password.</p>');
}

// 5) set session vars
$_SESSION['user_type']   = $userType;
$_SESSION['user_id']     = $userId;
$_SESSION['user_name']   = $name;
$_SESSION['user_avatar'] = ($userType === 'venue' || $userType === 'admin') ? 'default-avatar.png' : $avatar; // default avatar for non-customer users

// 6) decide final redirect (if userType is 'customer' then redirect to recent page, else to default dashboard)
if ($userType === 'customer' && preg_match('#^/[^/].*#', $redirectTo)) {
  $dest = $redirectTo;  // Redirect to the page the user was trying to access
} else {
  $dest = $default;      // Default redirect based on user type
}

// 7) send them off
header("Location: $dest");
exit;
