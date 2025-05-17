<?php
include "db.php";
include 'navbar.php';   // starts session and outputs navbar

// Determine login state
$isLoggedIn = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';

// Get and validate venue_id
$venue_id = isset($_GET['venue_id']) ? (int)$_GET['venue_id'] : 0;
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
$image_path = "images/venues/" . htmlspecialchars($venue['image']);

// Fetch features
$features = [];
$fstmt = $conn->prepare("
  SELECT f.name
  FROM features f
  JOIN venue_features vf ON f.id = vf.feature_id
  WHERE vf.venue_id = ?
");
$fstmt->bind_param("i", $venue_id);
$fstmt->execute();
$fres = $fstmt->get_result();
while ($f = $fres->fetch_assoc()) {
  $features[] = $f['name'];
}

// Fetch slots
$slots = [];
$sstmt = $conn->prepare("SELECT time_slot FROM venue_slots WHERE venue_id = ?");
$sstmt->bind_param("i", $venue_id);
$sstmt->execute();
$sres = $sstmt->get_result();
while ($slot = $sres->fetch_assoc()) {
  $slots[] = $slot['time_slot'];
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


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($venue['name']) ?> – Venue Details</title>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="venuedetail.css" />

  <!-- Modal styles -->
  <style>
    :root {
      --primary: #ff4d6d;
      --bg-light: #f9fafb;
      --text-dark: #1f2937;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1001;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      max-width: 400px;
      width: 90%;
      text-align: center;
      position: relative;
    }

    .modal-content .close {
      position: absolute;
      top: 0.5rem;
      right: 0.75rem;
      font-size: 1.5rem;
      color: var(--text-dark);
      cursor: pointer;
    }

    .modal-content i.fa-user-lock {
      display: block;
      margin: 0 auto 1rem;
      color: var(--primary);
    }

    .modal-content p {
      margin: 1.5rem 0;
      font-size: 1.1rem;
      color: var(--text-dark);
    }

    .modal-content button {
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 0.5rem;
      font-size: 1rem;
      cursor: pointer;
    }

    .modal-content button:hover {
      opacity: 0.9;
    }
  </style>
</head>

<body>
  <section class="venue-hero">
    <div class="hero-bg" style="background-image: url('<?= $image_path ?>');"></div>
    <div class="hero-content">
      <h1 class="animate__animated animate__fadeInDown"><?= htmlspecialchars($venue['name']) ?></h1>
      <p class="animate__animated animate__fadeInUp"><?= htmlspecialchars($venue['area']) ?>, <?= htmlspecialchars($venue['city']) ?></p>
    </div>
  </section>

  <section class="venue-details-section">
    <div class="venue-info animate__animated animate__fadeInLeft">
      <div class="info-item"><i class="fas fa-chair"></i>
        <h3>Batch</h3>
        <p><?= htmlspecialchars($venue['Batch']) ?> </p>
      </div>
      <div class="info-item"><i class="fas fa-users"></i>
        <h3>Guest</h3>
        <p><?= htmlspecialchars($venue['capacity']) ?> </p>
      </div>
      <div class="info-item"><i class="fas fa-money-bill-wave"></i>
        <h3>Venue Type</h3>
        <p><?= htmlspecialchars($venue['type']) ?></p>
      </div>
      <div class="info-item"><i class="fas fa-clock"></i>
        <h3>Timings</h3>
        <?php if (!empty($slots)): ?>
          <?php foreach ($slots as $time): ?>
            <p><?= htmlspecialchars($time) ?></p>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Not Available</p>
        <?php endif; ?>
      </div>

      <div class="info-item"><i class="fas fa-map-marker-alt"></i>
        <h3>Location</h3>
        <p><?= htmlspecialchars($venue['area']) ?>, <?= htmlspecialchars($venue['city']) ?></p>
      </div>
      <div class="info-item"><i class="fas fa-ruler-combined"></i>
        <h3>Space</h3>
        <p><?= htmlspecialchars($venue['space']) ?> sq m</p>
      </div>

    </div>
    <div class="venue-description animate__animated animate__fadeInRight">
      <h2>About The Venue</h2>
      <p><?= htmlspecialchars($venue['description']) ?></p>
      <a href="booking.php?venue_id=<?= $venue_id ?>"
        class="btn-book">
        Book Now
      </a>
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


  <section class="venue-contact-address animate__animated animate__fadeInUp">
    <div class="section-wrapper">
      <!-- Address Section -->
      <div class="section-block">
        <div class="side-title">ADDRESS</div>
        <div class="info-card">
          <div class="info-item"><i class="fas fa-map"></i>
            <h3>Holding Number</h3>
            <p><?= htmlspecialchars($venue['HoldingNo']) ?></p>
          </div>
          <div class="info-item"><i class="fas fa-street-view"></i>
            <h3>Area</h3>
            <p><?= htmlspecialchars($venue['area']) ?></p>
          </div>
          <div class="info-item"><i class="fas fa-city"></i>
            <h3>City</h3>
            <p><?= htmlspecialchars($venue['city']) ?></p>
          </div>
          <div class="info-item"><i class="fas fa-mail-bulk"></i>
            <h3>ZIP Code</h3>
            <p><?= htmlspecialchars($venue['ZIP']) ?></p>
          </div>
        </div>
      </div>

      <!-- Contact Section -->
      <div class="section-block">
        <div class="side-title">CONTACT</div>
        <div class="info-card">
          <div class="info-item"><i class="fas fa-phone-alt"></i>
            <h3>Contact Number</h3>
            <p><?= htmlspecialchars($venue['ContactNumber']) ?></p>
          </div>
          <div class="info-item"><i class="fas fa-envelope"></i>
            <h3>Email</h3>
            <p><?= htmlspecialchars($venue['Email']) ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Modal Markup -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <i class="fas fa-user-lock fa-3x"></i>
      <p>You need to be logged in to proceed.</p>
      <button id="modalLoginBtn">
        <i class="fas fa-sign-in-alt" style="margin-right:.5rem;"></i>
        Login
      </button>
    </div>
  </div>



  <footer class="site-footer">
    <p>&copy; 2025 Ceremo. All rights reserved.</p>
  </footer>

  <script src="venuedetail.js"></script>
  <script>
    // pass PHP flag to JS
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    // modal elements
    const modal = document.getElementById('loginModal');
    const closeModal = document.getElementById('closeModal');
    const loginBtn = document.getElementById('modalLoginBtn');

    // intercept Book Now
    document.querySelectorAll('.btn-book').forEach(btn => {
      btn.addEventListener('click', e => {
        if (!isLoggedIn) {
          e.preventDefault();
          modal.style.display = 'flex';
        }
      });
    });

    // close modal
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });

    // Login redirect preserving page+query
    loginBtn.addEventListener('click', () => {
      const current = window.location.pathname + window.location.search;
      window.location.href = 'login.php?redirect=' + encodeURIComponent(current);
    });
  </script>
</body>

</html>