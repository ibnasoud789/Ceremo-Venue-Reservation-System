<?php
include "db.php";

// Handle venue data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Handle Venue Insert
  $venueName = $_POST['venueName'];
  $venuepass = $_POST['password'];
  $venueArea = $_POST['venueArea'];
  $venueCity = $_POST['venueCity'];
  $venuezip = $_POST['venuezip'];
  $venueholding = $_POST['venueholding'];
  $venueType = $_POST['venueType'];
  $venuespace = $_POST['venueSize'];
  $venueBatch = $_POST['venueBatch'];
  $venueCapacity = $_POST['venueCapacity'];
  $venueDescription = $_POST['venueDescription'];
  $venueImage = $_FILES['venueImage']['name'];
  $venuecontact = $_POST['venuecontact'];
  $venueemail = $_POST['venueemail'];

  // Move the uploaded image to the desired folder
  move_uploaded_file($_FILES['venueImage']['tmp_name'], "images/venues/" . $venueImage);
  // Insert the venue details
  $stmt = $conn->prepare("INSERT INTO venues (name,password, type,HoldingNo, city, area, ZIP,Batch, capacity, image, description, status, ContactNumber, Email) VALUES (?,?,?,?,?, ?,?, ?, ?, ?, ?, 'active',?,?)");
  $stmt->bind_param("sssissiiissis", $venueName, $password, $venueType, $venueholding, $venueCity, $venueArea, $venuezip, $venueBatch, $venueCapacity, $venueImage, $venueDescription, $venuecontact, $venueemail);
  $stmt->execute();
  $venue_id = $conn->insert_id;

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
  header("Location: login.php");
}
