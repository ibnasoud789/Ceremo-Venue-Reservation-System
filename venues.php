<?php
// venues.php

include "db.php";
include "navbar.php";  // this starts the session

// Determine login state
$isLoggedIn = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';

// Fetch venues with features
$stmt = $conn->prepare("
  SELECT v.id, v.name, v.type, v.city, v.area, v.capacity, v.image, v.description,
         GROUP_CONCAT(f.name) AS features
  FROM venues v
  LEFT JOIN venue_features vf ON v.id = vf.venue_id
  LEFT JOIN features f      ON vf.feature_id = f.id
  WHERE v.status = 'active'
  GROUP BY v.id
");
$stmt->execute();
$venuesRaw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch minimum food package price for each venue
$venues = [];
foreach ($venuesRaw as $venue) {
  $priceStmt = $conn->prepare("
    SELECT MIN(promotional_price) AS min_price
    FROM food_packages
    WHERE venue_id = ?
  ");
  $priceStmt->bind_param("i", $venue['id']);
  $priceStmt->execute();
  $priceRes   = $priceStmt->get_result();
  $priceRow   = $priceRes->fetch_assoc();
  $venue['min_price'] = $priceRow['min_price'] ?? null;
  $venues[] = $venue;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Venues – Ceremo</title>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="venuestyle.css" />

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

  <!-- hero header, navbar already output above -->
  <header class="hero-header">
    <div class="hero-content">
      <h1>Discover Our Beautiful Venues</h1>
      <p>Perfect venues for your perfect event</p>
    </div>
  </header>

  <section class="venues-grid">
    <?php foreach ($venues as $venue): ?>
      <div class="venue-card">
        <div class="venue-image-container">
          <img src="images/venues/<?= htmlspecialchars($venue['image']) ?>"
            alt="<?= htmlspecialchars($venue['name']) ?>"
            class="venue-image" />
        </div>
        <div class="venue-info">
          <h3 class="venue-name"><?= htmlspecialchars($venue['name']) ?></h3>
          <p><strong>Type:</strong> <?= htmlspecialchars($venue['type']) ?></p>
          <p><strong>Location:</strong> <?= htmlspecialchars($venue['area']) ?>, <?= htmlspecialchars($venue['city']) ?></p>
          <p><strong>Capacity:</strong> <?= htmlspecialchars($venue['capacity']) ?> Guests</p>

          <div class="features-section">
            <h4>Features:</h4>
            <div class="features-list">
              <?php foreach (explode(',', $venue['features']) as $feature): ?>
                <div class="feature"><?= htmlspecialchars($feature) ?></div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="price-highlight">
            <i class="fas fa-utensils"></i>
            <span>
              <?= $venue['min_price']
                ? 'Starting From: ৳' . number_format($venue['min_price']) . ' / plate'
                : 'Contact for Price' ?>
            </span>
          </div>

          <div class="buttons">
            <a href="venuedetails.php?venue_id=<?= $venue['id'] ?>"
              class="btn btn-details">View Details</a>
            <a href="booking.php?venue_id=<?= $venue['id'] ?>"
              class="btn btn-book">Book Now</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- Modal Markup -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>

      <!-- Font Awesome lock icon -->
      <i class="fas fa-user-lock fa-3x" style="color: var(--primary); margin-bottom: 1rem;"></i>

      <p><strong>You need to be logged in to proceed.</strong></p>

      <!-- Font Awesome sign-in icon in button -->
      <button id="modalLoginBtn">
        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
        Login
      </button>
    </div>
  </div>


  <!-- Intercept Book Now clicks -->
  <script>
    // Pass PHP login flag into JS
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    // Modal elements
    const modal = document.getElementById('loginModal');
    const closeModal = document.getElementById('closeModal');
    const loginBtn = document.getElementById('modalLoginBtn');

    // Show modal if not logged in
    document.querySelectorAll('.btn-book').forEach(button => {
      button.addEventListener('click', e => {
        if (!isLoggedIn) {
          e.preventDefault();
          modal.style.display = 'flex';
        }
      });
    });

    // Close modal
    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });
    window.addEventListener('click', e => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });

    // Redirect to login (preserving current page)
    loginBtn.addEventListener('click', () => {
      const current = window.location.pathname + window.location.search;
      window.location.href = 'login.php?redirect=' + encodeURIComponent(current);
    });
  </script>
</body>

</html>