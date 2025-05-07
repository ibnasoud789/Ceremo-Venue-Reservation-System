<?php
include "db.php";

$category_id = $_GET['category_id'] ?? null;

if (!$category_id) {
  echo "Category not specified.";
  exit;
}

$stmt = $conn->prepare("SELECT option_name, price, features FROM add_on_options WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$options = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$catNameQuery = $conn->prepare("SELECT name FROM add_on_categories WHERE id = ?");
$catNameQuery->bind_param("i", $category_id);
$catNameQuery->execute();
$catName = $catNameQuery->get_result()->fetch_assoc()['name'] ?? 'Add-On';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($catName) ?> Options</title>
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #1a1c2d, #2f284b);
      padding: 50px 30px;
      color: #fff;
    }

    h1 {
      text-align: center;
      font-size: 3rem;
      margin-bottom: 40px;
      color: #fff;
      text-shadow: 1px 1px 8px rgba(0, 0, 0, 0.6);
    }

    .options-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      max-width: 1300px;
      margin: 0 auto;
    }

    .option-card {
      backdrop-filter: blur(15px);
      background: linear-gradient(to top, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
      transition: all 0.3s ease-in-out;
    }

    .option-card:hover {
      transform: scale(1.03);
      box-shadow: 0 20px 60px rgba(255, 255, 255, 0.2);
    }

    .option-name {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 10px;
      color: #ffd86b;
    }

    .price {
      font-size: 1.1rem;
      color: #c4f0ff;
      margin-bottom: 15px;
    }

    .features {
      list-style: none;
      padding-left: 0;
    }

    .features li {
      margin: 8px 0;
      padding-left: 25px;
      position: relative;
      color: #eee;
    }

    .features li::before {
      content: '✔';
      position: absolute;
      left: 0;
      color: #5effb5;
    }

    .back-btn {
      text-align: center;
      margin-top: 50px;
    }

    .back-btn a {
      color: #fff;
      padding: 12px 25px;
      background: linear-gradient(to right, #ff416c, #ff4b2b);
      text-decoration: none;
      border-radius: 30px;
      font-weight: 600;
      transition: background 0.3s ease;
    }

    .back-btn a:hover {
      background: linear-gradient(to right, #ff4b2b, #ff416c);
    }
  </style>
</head>

<body>

  <h1><?= htmlspecialchars($catName) ?> Options</h1>

  <div class="options-grid">
    <?php foreach ($options as $opt): ?>
      <div class="option-card">
        <div class="option-name"><?= htmlspecialchars($opt['option_name']) ?></div>
        <div class="price">৳<?= number_format($opt['price']) ?></div>
        <ul class="features">
          <?php foreach (explode(',', $opt['features']) as $feature): ?>
            <li><?= htmlspecialchars(trim($feature)) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="back-btn">
    <a href="addOns.php">← Back to AddOn Categories</a>
  </div>

</body>

</html>