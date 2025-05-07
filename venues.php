<?php
include "db.php";
include 'navbar.php';


// Fetch venues with features
$stmt = $conn->prepare("SELECT v.id, v.name, v.type, v.city, v.area, v.capacity, v.image, v.description, GROUP_CONCAT(f.name) AS features
                        FROM venues v
                        LEFT JOIN venue_features vf ON v.id = vf.venue_id
                        LEFT JOIN features f ON vf.feature_id = f.id
                        WHERE v.status = 'active'
                        GROUP BY v.id");
$stmt->execute();
$venuesRaw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch minimum food package price for each venue
$venues = [];
foreach ($venuesRaw as $venue) {
  $venueId = $venue['id'];

  $priceStmt = $conn->prepare("SELECT MIN(promotional_price) as min_price FROM food_packages WHERE venue_id = ?");
  $priceStmt->bind_param("i", $venueId);
  $priceStmt->execute();
  $priceRes = $priceStmt->get_result();
  $priceRow = $priceRes->fetch_assoc();

  $venue['min_price'] = $priceRow['min_price'] ?? null;
  $venues[] = $venue;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Venues</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="venuestyle.css" />
</head>

<body>

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
          <img src="images/venues/<?= htmlspecialchars($venue['image']) ?>" alt="<?= htmlspecialchars($venue['name']) ?>" class="venue-image" />
        </div>
        <div class="venue-info">
          <h3 class="venue-name"><?= htmlspecialchars($venue['name']) ?></h3>
          <p><strong>Type:</strong> <?= htmlspecialchars($venue['type']) ?></p>
          <p><strong>Location:</strong> <?= htmlspecialchars($venue['city']) ?>, <?= htmlspecialchars($venue['area']) ?></p>
          <p><strong>Capacity:</strong> <?= htmlspecialchars($venue['capacity']) ?> Guests</p>
          <!-- Features Section -->
          <div class="features-section">
            <h4>Features:</h4>
            <div class="features-list">
              <?php
              $features = explode(',', $venue['features']);
              foreach ($features as $feature):
              ?>
                <div class="feature"><?= htmlspecialchars($feature); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="price-highlight">
            <i class="fas fa-utensils"></i>
            <span>Starting From: <?= $venue['min_price'] ? '৳' . number_format($venue['min_price']) . ' / plate' : 'Contact for Price' ?></span>
          </div>


          <div class="buttons">
            <a href="venuedetails.php?venue_id=<?= $venue['id'] ?>" class="btn btn-details">View Details</a>
            <a href="booking.php?venue_id=<?= $venue['id'] ?>" class="btn btn-book">Book Now</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

</body>

</html>