<?php
include "db.php";
include 'navbar.php';
$stmt = $conn->query("SELECT id, name FROM add_on_categories");
$categories = $stmt->fetch_all(MYSQLI_ASSOC);

// Custom images for categories
$images = [
  'Stage Decoration' => 'stage.jpg',
  'Wedding Photography' => 'photography.jpg',
  'Videography' => 'videography.jpg',
  'DJ' => 'dj.jpg',
  'Lighting' => 'lighting.jpg',
  'Sound System' => 'sound.jpg',
  'Floral Arrangement' => 'floral.jpg',
  'Kids Corner' => 'kids.jpg',
  'Corporate Setup' => 'corporate.jpg'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Explore Add-On Services</title>
  <style>
    h1 {
      text-align: center;
      font-size: 3.2rem;
      margin-bottom: 60px;
      color: royalblue;
      text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 40px;
      max-width: 1300px;
      margin: 0 auto;
    }

    .card {
      position: relative;
      height: 250px;
      border-radius: 20px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
      background: #000;
    }

    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6);
    }

    .card a {
      text-decoration: none;
      display: block;
      width: 100%;
      height: 100%;
      position: relative;
      color: #fff;
    }

    .card-bg {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-size: cover;
      background-position: center;
      transition: transform 0.5s ease;
      z-index: 1;
    }

    .card:hover .card-bg {
      transform: scale(1.07);
    }

    .gradient-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 0, 150, 0.6), rgba(0, 229, 255, 0.5));
      opacity: 0.7;
      z-index: 2;
      mix-blend-mode: overlay;
      transition: opacity 0.4s ease;
    }

    .card-content {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      padding: 20px;
      z-index: 3;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.05));
    }


    .card-content h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: bold;
      color: #fff;
    }
  </style>
</head>

<body>

  <h1>Explore Add-On Services</h1>

  <div class="card-grid">
    <?php foreach ($categories as $cat):
      $img = $images[$cat['name']] ?? 'default.jpg'; ?>
      <div class="card">
        <a href="addOnOptions.php?category_id=<?= $cat['id'] ?>">
          <div class="card-bg" style="background-image: url('images/addons/<?= $img ?>');"></div>
          <div class="gradient-overlay"></div>
          <div class="card-content">
            <h2><?= htmlspecialchars($cat['name']) ?></h2>
          </div>
        </a>
      </div>

    <?php endforeach; ?>
  </div>

</body>

</html>