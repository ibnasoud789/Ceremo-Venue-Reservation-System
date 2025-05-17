<?php
session_start();
include 'db.php';  // defines $conn

// 1) Authentication check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'venue') {
  header('Location: login.php');
  exit;
}
$venue_id = $_SESSION['user_id'];

// 2) Fetch venue details
$stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$venue_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3) Fetch existing food packages
$stmt = $conn->prepare("
    SELECT id, package_name, original_price, promotional_price, items, is_featured
    FROM food_packages
    WHERE venue_id = ?
");
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$foodPackages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 4) Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 4a) Save venue details
  if (isset($_POST['save_venue'])) {
    $name             = $_POST['name'];
    $description      = $_POST['description'];
    $holding_no       = $_POST['holding_no'];
    $city             = $_POST['city'];
    $area             = $_POST['area'];
    $zip              = $_POST['zip'];
    $contact_number   = $_POST['contact_number'];
    $email            = $_POST['email'];
    $capacity         = $_POST['capacity'];

    $u = $conn->prepare("
            UPDATE venues
               SET name = ?, description = ?, HoldingNo = ?, city = ?, area = ?, ZIP = ?, ContactNumber = ?, Email = ?, capacity = ?
             WHERE id = ?
        ");
    $u->bind_param(
      "ssssssssii",
      $name,
      $description,
      $holding_no,
      $city,
      $area,
      $zip,
      $contact_number,
      $email,
      $capacity,
      $venue_id
    );
    $u->execute();
    $u->close();

    echo "<script>alert('Venue details saved successfully');</script>";
  }

  // 4b) Add new food package
  if (isset($_POST['add_food_package'])) {
    $package_name      = $_POST['package_name'];
    $original_price    = $_POST['original_price'];
    $promotional_price = $_POST['promotional_price'];
    $items             = $_POST['items'];
    $is_featured       = $_POST['is_featured'];

    $i = $conn->prepare("
            INSERT INTO food_packages
                (venue_id, package_name, original_price, promotional_price, items, is_featured)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
    $i->bind_param(
      "isdssi",
      $venue_id,
      $package_name,
      $original_price,
      $promotional_price,
      $items,
      $is_featured
    );
    $i->execute();
    $i->close();

    echo "<script>alert('New food package added');</script>";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Venue & Food Packages</title>
  <link rel="stylesheet" href="venueEdit.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <aside class="sidebar animate__animated animate__fadeInLeft">
        <div class="sidebar-brand">
          <h2><i class="fas fa-user-tie"></i> Venue</h2>
        </div>
        <ul class="sidebar-menu">
          <li>
            <a href="venueDashboard.php">
              <i class="fas fa-tachometer-alt"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a href="#">
              <i class="fas fa-calendar-check"></i>
              <span>Reservations</span>
            </a>
          </li>
          <li class="active">
            <a href="venueEdit.php">
              <i class="fas fa-edit"></i>
              <span>Edit Venue</span>
            </a>
          </li>
          <li>
            <a href="#">
              <i class="fas fa-chart-line"></i>
              <span>Reports</span>
            </a>
          </li>
          <li>
            <a href="#">
              <i class="fas fa-cog"></i>
              <span>Settings</span>
            </a>
          </li>
          <li>
            <a href="logout.php" class="logout-btn">
              <i class="fas fa-sign-out-alt"></i>
              <span>Logout</span>
            </a>
          </li>
        </ul>
      </aside>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <header>
        <h1>Edit Venue</h1>
      </header>

      <form action="venueEdit.php" method="POST" class="edit-venue-form">
        <!-- Address -->
        <section class="section">
          <h2><i class="fas fa-map-marker-alt"></i> Address</h2>
          <div class="input-group">
            <label for="holdingNo">Holding No:</label>
            <input type="text" id="holdingNo" name="holding_no"
              value="<?= htmlspecialchars($venue_data['HoldingNo']) ?>" required>
          </div>
          <div class="input-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city"
              value="<?= htmlspecialchars($venue_data['city']) ?>" required>
          </div>
          <div class="input-group">
            <label for="area">Area:</label>
            <input type="text" id="area" name="area"
              value="<?= htmlspecialchars($venue_data['area']) ?>" required>
          </div>
          <div class="input-group">
            <label for="zip">ZIP:</label>
            <input type="text" id="zip" name="zip"
              value="<?= htmlspecialchars($venue_data['ZIP']) ?>" required>
          </div>
        </section>

        <!-- Contact -->
        <section class="section">
          <h2><i class="fas fa-phone-alt"></i> Contact</h2>
          <div class="input-group">
            <label for="contactNumber">Contact Number:</label>
            <input type="text" id="contactNumber" name="contact_number"
              value="<?= htmlspecialchars($venue_data['ContactNumber']) ?>" required>
          </div>
          <div class="input-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
              value="<?= htmlspecialchars($venue_data['Email']) ?>" required>
          </div>
        </section>

        <!-- Venue Info -->
        <section class="section">
          <h2><i class="fas fa-info-circle"></i> Venue Details</h2>
          <div class="input-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name"
              value="<?= htmlspecialchars($venue_data['name']) ?>" required>
          </div>
          <div class="input-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($venue_data['description']) ?></textarea>
          </div>
          <div class="input-group">
            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity"
              value="<?= htmlspecialchars($venue_data['capacity']) ?>" required>
          </div>
        </section>

        <!-- Existing Food Packages -->
        <section class="section">
          <h2><i class="fas fa-utensils"></i> Current Food Packages</h2>
          <?php if (empty($foodPackages)): ?>
            <p>No packages added yet.</p>
          <?php else: ?>
            <ul class="package-list">
              <?php foreach ($foodPackages as $pkg): ?>
                <li>
                  <strong><?= htmlspecialchars($pkg['package_name']) ?></strong><br>
                  Original: <?= number_format($pkg['original_price'], 2) ?>,
                  Promo: <?= number_format($pkg['promotional_price'], 2) ?><br>
                  Items: <?= htmlspecialchars($pkg['items']) ?><br>
                  Featured: <?= $pkg['is_featured'] ? 'Yes' : 'No' ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

        <!-- Add New Food Package -->
        <section class="section">
          <h2><i class="fas fa-plus-circle"></i> Add New Food Package</h2>
          <div class="input-group">
            <label for="package_name">Package Name:</label>
            <input type="text" id="package_name" name="package_name" required>
          </div>
          <div class="input-group">
            <label for="original_price">Original Price:</label>
            <input type="number" step="0.01" id="original_price" name="original_price" required>
          </div>
          <div class="input-group">
            <label for="promotional_price">Promotional Price:</label>
            <input type="number" step="0.01" id="promotional_price" name="promotional_price" required>
          </div>
          <div class="input-group">
            <label for="items">Items (comma-separated):</label>
            <textarea id="items" name="items" rows="3" required></textarea>
          </div>
          <div class="input-group">
            <label for="is_featured">Featured:</label>
            <select id="is_featured" name="is_featured">
              <option value="1">Yes</option>
              <option value="0" selected>No</option>
            </select>
          </div>
          <button type="submit" name="add_food_package" class="btn-add">Add Package</button>
        </section>

        <!-- Save Venue Changes -->
        <button type="submit" name="save_venue" class="submit-btn">Save Changes</button>
      </form>
    </div>
  </div>
</body>

</html>