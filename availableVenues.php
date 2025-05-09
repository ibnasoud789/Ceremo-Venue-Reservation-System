<?php
include "db.php";
include 'navbar.php';

// Fetch filters
$type = $_GET['type'] ?? '';
$city = $_GET['city'] ?? '';
$area = $_GET['area'] ?? '';
$guests = $_GET['guests'] ?? '';
$date = $_GET['date'] ?? '';

// Build query
$sql = "SELECT * FROM venues WHERE 1=1";
$params = [];
$types = "";

if (!empty($type)) {
  $sql .= " AND type = ?";
  $params[] = $type;
  $types .= "s";
}
if (!empty($city)) {
  $sql .= " AND city = ?";
  $params[] = $city;
  $types .= "s";
}
if (!empty($area)) {
  $sql .= " AND area LIKE ?";
  $params[] = "%$area%";
  $types .= "s";
}
if (!empty($guests)) {
  $sql .= " AND capacity >= ?";
  $params[] = (int)$guests;
  $types .= "i";
}

// Execute query
$stmt = $conn->prepare($sql);
if ($types !== "") {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$venues = [];

while ($row = $result->fetch_assoc()) {
  $fid = $row['id'];

  // Fetch features
  $features = [];
  $fstmt = $conn->prepare("
    SELECT f.name FROM venue_features vf
    JOIN features f ON f.id = vf.feature_id
    WHERE vf.venue_id = ?
  ");
  $fstmt->bind_param("i", $fid);
  $fstmt->execute();
  $fres = $fstmt->get_result();
  while ($f = $fres->fetch_assoc()) {
    $features[] = $f['name'];
  }

  // Fetch minimum food package price
  $priceStmt = $conn->prepare("
    SELECT MIN(promotional_price) AS min_price 
    FROM food_packages 
    WHERE venue_id = ?
  ");
  $priceStmt->bind_param("i", $fid);
  $priceStmt->execute();
  $priceRes = $priceStmt->get_result();
  $priceData = $priceRes->fetch_assoc();
  $min_price = $priceData['min_price'] ?? null;

  $row['features'] = $features;
  $row['min_price'] = $min_price;
  $venues[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Venue Search Results</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* Global */
    /* Global Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }



    /* Hero Section */
    .hero {
      background: linear-gradient(135deg, #2d6a4f, #00aaff);
      color: #fff;
      text-align: center;
      padding: 120px 30px;
      position: relative;
      overflow: hidden;
      border-bottom-left-radius: 60% 25%;
      border-bottom-right-radius: 60% 25%;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 600;
      margin-bottom: 15px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
    }

    .hero p {
      font-size: 1.25rem;
      font-weight: 300;
      opacity: 0.8;
    }

    /* Wave Divider */
    .wave-divider {
      position: absolute;
      bottom: -40px;
      left: 0;
      width: 100%;
      overflow: hidden;
      line-height: 0;
    }

    .wave-divider svg {
      display: block;
      width: 100%;
      height: 150px;
      filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    }

    /* Venue Listings */
    .venue-listings {
      padding: 80px 20px;
      background-color: #ffffff;
    }

    .venue-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 30px;
      max-width: 1300px;
      margin: 0 auto;
      justify-items: center;
    }

    /* Venue Card */
    .venue-card {
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
      cursor: pointer;
    }

    .venue-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .venue-image img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .venue-card:hover .venue-image img {
      transform: scale(1.05);
    }

    /* Venue Details */
    .venue-details {
      padding: 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .venue-details h3 {
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 12px;
      color: #2c3e50;
    }

    .venue-details p {
      font-size: 1rem;
      color: #7f8c8d;
      margin-bottom: 16px;
    }

    .desc {
      font-size: 0.95rem;
      color: #475569;
      margin: 12px 0;
      opacity: 0.8;
    }

    /* Tags & Features */
    .tags .tag {
      background: #ecfeff;
      color: #06b6d4;
      padding: 8px 14px;
      border-radius: 50px;
      margin-right: 8px;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .features {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 12px 0;
    }

    .features span {
      background: #f3f4f6;
      color: #334155;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 0.9rem;
    }

    /* Bottom Row */
    .bottom-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 16px;
      margin-top: auto;
    }

    .price {
      color: #ef4444;
      font-weight: 700;
      font-size: 1.2rem;
    }

    .btn-group {
      display: flex;
      gap: 12px;
    }

    .book-btn,
    .details-btn {
      padding: 12px 24px;
      font-size: 1.05rem;
      font-weight: 600;
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.25s ease-in-out;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    }

    .book-btn {
      background: linear-gradient(135deg, #f43f5e, #e11d48);
      color: white;
    }

    .details-btn {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
    }

    .book-btn:hover,
    .details-btn:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
      .venue-listings {
        padding: 60px 15px;
      }

      .hero h1 {
        font-size: 2.5rem;
      }

      .venue-container {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }

      .venue-card {
        max-width: 90%;
      }
    }

    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Responsive */
    @media (max-width: 600px) {
      .venue-container {
        grid-template-columns: 1fr;
        padding: 0 10px;
      }

      .hero-overlay h1 {
        font-size: 2rem;
      }

      .hero-overlay p {
        font-size: 1rem;
      }
    }
  </style>
</head>

<body>
  <section class="hero">
    <div class="hero-overlay">
      <h1>Find the Perfect <?php echo htmlspecialchars($type); ?></h1>
      <p><?php echo htmlspecialchars($area); ?>, <?php echo htmlspecialchars($city); ?></p>
    </div>
  </section>

  <!-- VENUE LISTINGS -->
  <section class="venue-listings">
    <div class="venue-container">
      <?php foreach ($venues as $venue): ?>
        <div class="venue-card">
          <div class="venue-image">
            <img src="images/venues/<?= htmlspecialchars($venue['image']) ?>" alt="<?= htmlspecialchars($venue['name']) ?>">
          </div>
          <div class="venue-details">
            <h3><?= htmlspecialchars($venue['name']) ?></h3>
            <p><?= htmlspecialchars($venue['area']) ?> • <?= htmlspecialchars($venue['city']) ?></p>
            <div class="tags">
              <span class="tag"><?= htmlspecialchars($venue['type']) ?></span>
              <span class="tag">Capacity <?= htmlspecialchars($venue['capacity']) ?></span>
            </div>
            <p class="desc"><?= htmlspecialchars($venue['description']) ?></p>
            <div class="features">
              <?php foreach ($venue['features'] as $feature): ?>
                <span><?= htmlspecialchars($feature) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="bottom-row">
              <span class="price">
                <?= $venue['min_price'] ? 'Starting from ৳' . number_format($venue['min_price']) . '/plate' : 'Contact for Price' ?>
              </span>

              <div class="btn-group">
                <a href="booking.php?venue_id=<?= $venue['id'] ?>" class="book-btn">Book Now</a>
                <a href="venuedetails.php?venue_id=<?= $venue['id'] ?>" class="details-btn">View Details</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</body>

</html>