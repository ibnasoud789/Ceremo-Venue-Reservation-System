<?php
include "db.php"; // Include the database connection

// Handle venue data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Handle Venue Insert
  $venueName = $_POST['venueName'];
  $venueArea = $_POST['venueArea'];
  $venueCity = $_POST['venueCity'];
  $venueType = $_POST['venueType'];
  $venueCapacity = $_POST['venueCapacity'];
  $venueDescription = $_POST['venueDescription'];
  $venueImage = $_FILES['venueImage']['name'];

  // Move the uploaded image to the desired folder
  move_uploaded_file($_FILES['venueImage']['tmp_name'], "images/venues/" . $venueImage);
  // Insert the venue details
  $stmt = $conn->prepare("INSERT INTO venues (name,type, city, area, capacity, image, description, status) VALUES (?,?, ?, ?, ?, ?, ?, 'active')");
  $stmt->bind_param("ssssiss", $venueName, $venueType, $venueCity, $venueArea, $venueCapacity, $venueImage, $venueDescription); // 6 's' for string parameters
  $stmt->execute();
  $venue_id = $conn->insert_id; // Get the last inserted venue ID

  // Handle time slot inserts
  if (isset($_POST['timeSlots'])) {
    foreach ($_POST['timeSlots'] as $slot) {
      if (!empty(trim($slot))) {
        $stmt = $conn->prepare("INSERT INTO venue_slots (venue_id, slot_time) VALUES (?, ?)");
        $stmt->bind_param("is", $venue_id, $slot);
        $stmt->execute();
      }
    }
  }


  // Handle food package inserts
  if (isset($_POST['packages'])) {
    foreach ($_POST['packages'] as $index => $package) {
      $packageName = $package['package_name'];
      $originalPrice = $package['original_price'];
      $promotionalPrice = $package['promotional_price'];
      $items = $package['items'];
      $isFeatured = isset($package['is_featured']) ? 1 : 0;

      $stmt = $conn->prepare("INSERT INTO food_packages (venue_id, package_name, original_price, promotional_price, items, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("isdssi", $venue_id, $packageName, $originalPrice, $promotionalPrice, $items, $isFeatured);
      $stmt->execute();
    }
  }
  // Handle feature inserts
  if (isset($_POST['features'])) {
    foreach ($_POST['features'] as $feature_id) {
      // Insert feature into the venue_features table
      $stmt = $conn->prepare("INSERT INTO venue_features (venue_id, feature_id) VALUES (?, ?)");
      $stmt->bind_param("ii", $venue_id, $feature_id);
      $stmt->execute();
    }
  }

  $conn->close(); // Close the connection
  header("Location: admin_venues.php"); // Redirect to the venues page after insert
}
