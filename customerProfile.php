<?php
// profile.php
session_start();
include 'db.php';

$customer_id = $_SESSION['user_id'] ?? null;
if (!$customer_id) {
  header('Location: login.php');
  exit;
}

// Fetch current profile details
$stmt = $conn->prepare("SELECT name, email, area, city, avatar FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $area = $_POST['area'];
  $city = $_POST['city'];
  $avatar = $_FILES['avatar']['name'] ?? null;

  if ($avatar) {
    $avatarPath = 'images/avatar/' . $avatar;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
  } else {
    $avatarPath = $customer['avatar'];  // Keep old avatar if not uploaded
  }

  // Update customer details in the database
  $updateStmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, area = ?, city = ?, avatar = ? WHERE id = ?");
  $updateStmt->bind_param("sssssi", $name, $email, $area, $city, $avatarPath, $customer_id);
  $updateStmt->execute();

  $_SESSION['user_name'] = $name;  // Update session variable
  $_SESSION['user_avatar'] = $avatarPath;  // Update session variable

  header('Location: customerProfile.php'); // Redirect to profile page after update
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Ceremo</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* Layout Fix for Profile */
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f4f7fa;
      color: #333;
      margin: 0;
      padding: 0;
      display: block;
      min-height: 100vh;
    }

    .container {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      padding: 40px;
      width: 100%;
      max-width: 650px;
      text-align: center;
      margin-top: 50px;
      margin-bottom: 50px;
      margin-left: auto;
      margin-right: auto;
    }

    h1 {
      font-size: 36px;
      margin-bottom: 20px;
      color: #2c3e50;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    input[type="text"],
    input[type="email"],
    input[type="file"],
    textarea {
      padding: 14px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      width: 100%;
      transition: all 0.3s ease;
      background-color: #f8f9fa;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    textarea:focus {
      border-color: #4CAF50;
      background-color: #ffffff;
      outline: none;
    }

    textarea {
      height: 120px;
    }

    button {
      padding: 14px 24px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 18px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button:hover {
      background-color: #45a049;
    }

    .avatar-profile {
      position: relative;
      display: inline-block;
      margin-top: 20px;
    }

    .avatar-profile img {
      border-radius: 50%;
      width: 120px;
      height: 120px;
      object-fit: cover;
      border: 2px solid #ddd;
    }

    .avatar-profile label {
      position: absolute;
      bottom: 0;
      right: 0;
      background-color: #4CAF50;
      padding: 8px;
      border-radius: 50%;
      cursor: pointer;
    }

    .avatar-profile label:hover {
      background-color: #45a049;
    }

    .profile-info {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      gap: 30px;
      margin-top: 40px;
      flex-wrap: wrap;
    }

    .profile-info div {
      text-align: left;
    }

    .profile-info div span {
      font-weight: bold;
      color: #4CAF50;
    }

    .profile-info div p {
      color: #777;
      margin: 5px 0;
    }

    .update-button {
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 14px 28px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .update-button:hover {
      background-color: #45a049;
    }
  </style>
</head>

<body>

  <?php include 'navbar.php' ?>

  <div class="container">
    <h1>Edit Your Profile</h1>

    <!-- Profile Information Section -->
    <div class="profile-info">
      <div>
        <p><span>Name:</span> <?= htmlspecialchars($customer['name']) ?></p>
        <p><span>Email:</span> <?= htmlspecialchars($customer['email']) ?></p>
        <p><span>Area:</span> <?= htmlspecialchars($customer['area']) ?></p>
        <p><span>City:</span> <?= htmlspecialchars($customer['city']) ?></p>
      </div>
      <div class="avatar-profile">
        <img src="/images/avatar/<?= htmlspecialchars($customer['avatar']) ?>" alt="Profile Avatar">
      </div>
    </div>

    <form action="customerProfile.php" method="POST" enctype="multipart/form-data">
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required placeholder="Full Name"><br>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required placeholder="Email"><br>
      <input type="text" id="area" name="area" value="<?= htmlspecialchars($customer['area']) ?>" required placeholder="Area"><br>
      <input type="text" id="city" name="city" value="<?= htmlspecialchars($customer['city']) ?>" required placeholder="City"><br>



      <button type="submit" class="update-button">Update Profile</button>
    </form>
  </div>

</body>

</html>