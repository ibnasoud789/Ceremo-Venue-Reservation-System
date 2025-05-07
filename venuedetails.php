<?php
include "db.php";

$venue_id = $_GET['venue_id'] ?? 1;
if (!$venue_id) {
  die("Invalid venue ID.");
}

// Fetch venue
$stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$venue = $stmt->get_result()->fetch_assoc();
if (!$venue) {
  die("Venue not found.");
}
$background_image = $venue['image'];
$image_path = "images/venues/" . htmlspecialchars($background_image);

// Fetch features
$features = [];
$fstmt = $conn->prepare("SELECT f.name FROM features f
                         JOIN venue_features vf ON f.id = vf.feature_id
                         WHERE vf.venue_id = ?");
$fstmt->bind_param("i", $venue_id);
$fstmt->execute();
$fres = $fstmt->get_result();
while ($f = $fres->fetch_assoc()) {
  $features[] = $f['name'];
}

// Fetch food packages
$food_packages = [];
$pstmt = $conn->prepare("SELECT * FROM food_packages WHERE venue_id = ?");
$pstmt->bind_param("i", $venue_id);
$pstmt->execute();
$pres = $pstmt->get_result();
while ($pkg = $pres->fetch_assoc()) {
  $pkg['items'] = explode(",", $pkg['items']);
  $food_packages[] = $pkg;
}

// Fetch gallery
$gallery = [];
$gstmt = $conn->prepare("SELECT image_url FROM venue_images WHERE venue_id = ?");
$gstmt->bind_param("i", $venue_id);
$gstmt->execute();
$gres = $gstmt->get_result();
while ($g = $gres->fetch_assoc()) {
  $gallery[] = $g['image_url'];
}

// Fetch reviews
$reviews = [];
$rstmt = $conn->prepare("SELECT customer_name, comment FROM reviews WHERE venue_id = ?");
$rstmt->bind_param("i", $venue_id);
$rstmt->execute();
$rres = $rstmt->get_result();
while ($r = $rres->fetch_assoc()) {
  $reviews[] = $r;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($venue['name']) ?> - Venue Details</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="venuedetail.css" />
</head>

<body>
  <header class="navbar">
    <div class="logo">CEREMO</div>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="venues.php">Venues</a></li>
      <li><a href="#contact">Contact</a></li>
      <li><button class="btn-login" onclick="window.location.href='login.html'">Login</button></li>
    </ul>
    <div class="hamburger"><i class="fas fa-bars"></i></div>
  </header>

  <section class="venue-hero">
    <div class="hero-bg" style="background-image: url('<?= $image_path ?>');"></div>
    <div class="hero-content">
      <h1 class="animate__animated animate__fadeInDown"><?= htmlspecialchars($venue['name']) ?></h1>
      <p class="animate__animated animate__fadeInUp"><?= htmlspecialchars($venue['area']) ?>, <?= htmlspecialchars($venue['city']) ?></p>
    </div>
  </section>

  <section class="venue-details-section">
    <div class="venue-info animate__animated animate__fadeInLeft">
      <div class="info-item"><i class="fas fa-users"></i>
        <h3>Sitting Capacity</h3>
        <p><?= htmlspecialchars($venue['capacity']) ?> Guests</p>
      </div>
      <div class="info-item"><i class="fas fa-money-bill-wave"></i>
        <h3>Venue Type</h3>
        <p><?= htmlspecialchars($venue['type']) ?></p>
      </div>
      <div class="info-item"><i class="fas fa-clock"></i>
        <h3>Timings</h3>
        <p>10 AM - 10 PM</p>
      </div>
      <div class="info-item"><i class="fas fa-map-marker-alt"></i>
        <h3>Location</h3>
        <p><?= htmlspecialchars($venue['area']) ?>, <?= htmlspecialchars($venue['city']) ?></p>
      </div>
    </div>
    <div class="venue-description animate__animated animate__fadeInRight">
      <h2>About The Venue</h2>
      <p><?= htmlspecialchars($venue['description']) ?></p>
      <button class="btn-book" onclick="window.location.href='booking.php?venue_id=<?= $venue_id ?>'">Book Now</button>
    </div>
  </section>

  <section class="venue-packages animate__animated animate__zoomIn">
    <h2>Food Packages</h2>
    <div class="package-cards">
      <?php foreach ($food_packages as $pkg): ?>
        <div class="package-card">
          <?php if ($pkg['is_featured']): ?><div class="ribbon">Popular</div><?php endif; ?>
          <h3><?= htmlspecialchars($pkg['package_name']) ?></h3>
          <p class="original-price">Tk. <?= htmlspecialchars($pkg['original_price']) ?></p>
          <p class="promotional-price">Tk. <?= htmlspecialchars($pkg['promotional_price']) ?></p>
          <ul>
            <?php foreach ($pkg['items'] as $item): ?>
              <li><?= htmlspecialchars(trim($item)) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="venue-additional-features animate__animated animate__fadeInUp">
    <h2>Additional Features</h2>
    <div class="features-grid">
      <?php foreach ($features as $feature): ?>
        <div class="feature-box">
          <div class="feature-header"><i class="fas fa-check-circle feature-icon"></i>
            <h3 class="feature-title"><?= htmlspecialchars($feature) ?></h3>
          </div>
          <div class="feature-details">
            <p>Included in this venue.</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="venue-gallery animate__animated animate__fadeInUp">
    <h2>Gallery</h2>
    <div class="gallery-grid">
      <?php foreach ($gallery as $img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="Venue Image">
      <?php endforeach; ?>
    </div>
  </section>

  <section class="venue-reviews animate__animated animate__fadeInUp">
    <h2>Customer Reviews</h2>
    <?php foreach ($reviews as $r): ?>
      <div class="review">
        <p>"<?= htmlspecialchars($r['comment']) ?>"</p>
        <span>- <?= htmlspecialchars($r['customer_name']) ?></span>
      </div>
    <?php endforeach; ?>
  </section>

  <footer class="site-footer">
    <p>&copy; 2025 EventVenue. All rights reserved.</p>
  </footer>

  <script src="venuedetail.js"></script>
</body>

</html>