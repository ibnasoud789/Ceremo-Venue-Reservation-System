<?php
include "db.php"; // Include the database connection file

// Initialize variables
$search_venue = '';
$venue_details = null;
$reservation_history = [];
$venue_features = [];

// Fetch existing venues for the list
$stmt = $conn->prepare("SELECT id,type, name, city, area, capacity, image FROM venues WHERE status = 'active'");
$stmt->execute();
$existing_venues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle Venue Search
if (isset($_POST['search'])) {
  $search_venue = $_POST['search'];

  // Fetch venue details based on search query
  $stmt = $conn->prepare("SELECT id, name, city, area, capacity, image, description FROM venues WHERE name LIKE ?");
  $search_venue = "$search_venue";
  $stmt->bind_param("s", $search_venue);
  $stmt->execute();
  $venue_details = $stmt->get_result()->fetch_assoc();

  // If venue is found, fetch its reservation history and features
  if ($venue_details) {
    $venue_id = $venue_details['id'];

    // Fetch venue features
    $stmt = $conn->prepare("SELECT f.name AS feature_name FROM features f
                            JOIN venue_features vf ON f.id = vf.feature_id
                            WHERE vf.venue_id = ?");
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $features_result = $stmt->get_result();
    while ($row = $features_result->fetch_assoc()) {
      $venue_features[] = $row['feature_name'];
    }

    // Fetch the reservation history for the venue
    $stmt = $conn->prepare("SELECT b.id, c.name AS user_name, b.booking_date, f.package_name 
                            FROM bookings b
                            JOIN customers c ON b.customer_id = c.id
                            JOIN food_packages f ON b.id = f.id
                            WHERE b.venue_id = ? ORDER BY b.booking_date DESC");
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $reservation_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}

$conn->close();  // Close the connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Venues Management</title>
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="admin_venues.css">
  <link rel="stylesheet" href="dummy_admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <style>
    /* Page Title */
    .page-title {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #333;
    }

    /* Add Venue Button */
    .btn-add {
      background: #3807a9;
      color: #fff;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 5px;
      cursor: pointer;
      margin-bottom: 1rem;
      transition: background 0.3s ease;
    }

    .btn-add:hover {
      background: #1a0253;
    }

    .form-container {
      background: #ffffff;
      border-radius: 16px;
      padding: 2rem;
      margin: 3rem auto;
      max-width: 700px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.06);
      animation: fadeInUp 0.6s ease-in-out;
      border: 1px solid #e5e7eb;
    }

    .form-container h3 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #1f2937;
      font-weight: 600;
      border-bottom: 2px solid #eee;
      padding-bottom: 0.5rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      font-weight: 500;
      color: #374151;
      margin-bottom: 0.4rem;
      font-size: 0.95rem;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 0.75rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      font-size: 1rem;
      background: #f9fafb;
      transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      border-color: #3807a9;
      outline: none;
      background: #fff;
    }

    .features-checkbox {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem;
      margin-top: 0.5rem;
    }

    .features-checkbox label {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.9rem;
      color: #374151;
    }

    .btn-submit {
      background: linear-gradient(135deg, #3807a9, #2a0076);
      color: #fff;
      border: none;
      padding: 0.85rem 1rem;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 10px;
      cursor: pointer;
      width: 100%;
      margin-top: 1.5rem;
      box-shadow: 0 6px 14px rgba(56, 7, 169, 0.2);
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      background: #e04363;
      box-shadow: 0 10px 20px rgba(224, 67, 99, 0.3);
    }

    #addPackageBtn {
      display: inline-block;
      margin-top: 1rem;
      background: #f3f4f6;
      color: #111827;
      padding: 0.6rem 1.2rem;
      font-weight: 500;
      border: 1px dashed #d1d5db;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    #addPackageBtn:hover {
      background: #3807a9;
      color: #fff;
      border: 1px solid #3807a9;
    }

    /* Venues Table */
    .venues-table-section {
      margin-top: 2rem;
    }

    .venues-table {
      width: 100%;
      border-collapse: collapse;
    }

    .venues-table thead {
      background: #3807a9;
      color: #fff;
    }

    .venues-table th,
    .venues-table td {
      padding: 0.75rem;
      border: 1px solid #ddd;
      text-align: left;
    }

    .venues-table tbody tr:hover {
      background: #f5f5f5;
    }

    /* Action Buttons */
    .btn-edit,
    .btn-delete {
      background: none;
      border: none;
      cursor: pointer;
      margin-right: 0.5rem;
      font-size: 1rem;
    }

    .btn-edit {
      color: #28a745;
    }

    .btn-delete {
      color: #dc3545;
    }

    /* Modal for editing venue features */
    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 500px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      animation: fadeInDown 0.3s ease-in-out;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar Navigation -->
  <aside class="sidebar animate__animated animate__fadeInLeft">
    <div class="sidebar-brand">
      <h2><i class="fas fa-crown"></i> Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
      <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
      <li class="active"><a href="admin_venues.php"><i class="fas fa-building"></i><span>Venues</span></a></li>
      <li><a href="admin_booking.php"><i class="fas fa-calendar-check"></i><span>Bookings</span></a></li>
      <li><a href="#"><i class="fas fa-users"></i><span>Users</span></a></li>
      <li><a href="#"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
      <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <div class="main-content animate__animated animate__fadeInUp">
    <header>
      <div class="user-wrapper">
        <i class="fas fa-bell"></i>
        <div class="admin-profile">
          <img src="images/admin.jpg" alt="Admin" />
          <div>
            <h4>Admin Name</h4>
            <small>King Soud Joy</small>
          </div>
        </div>
      </div>
    </header>

    <!-- Venue Search Section -->
    <section class="venue-search-section">
      <h2>Search Venue & View Reservation History</h2>
      <div class="searchVenue">
        <form method="POST" action="admin_venues.php">
          <input type="text" name="search" placeholder="Enter venue name..." value="<?= htmlspecialchars($search_venue) ?>" />
          <button type="submit" class="btn-add">Search Venue</button>
        </form>
      </div>

      <!-- Venue Result Section -->
      <?php if ($venue_details): ?>
        <div id="venueResult" class="venue-result">
          <div class="venue-card">
            <img src="images/venues/<?= htmlspecialchars($venue_details['image']) ?>" alt="Venue Image" />
            <div class="venue-info">
              <h3><?= htmlspecialchars($venue_details['name']) ?></h3>
              <p><strong>Location:</strong> <?= htmlspecialchars($venue_details['city']) ?>, <?= htmlspecialchars($venue_details['area']) ?></p>
              <p><strong>Capacity:</strong> <?= htmlspecialchars($venue_details['capacity']) ?> Guests</p>
              <p><strong>Description:</strong> <?= htmlspecialchars($venue_details['description']) ?></p>
              <p><strong>Features:</strong> <?= implode(", ", $venue_features) ?></p>
            </div>
          </div>

          <!-- Reservation History Section -->
          <div class="reservation-history">
            <h3>Reservation History</h3>
            <table>
              <thead>
                <tr>
                  <th>Booking ID</th>
                  <th>User</th>
                  <th>Date</th>
                  <th>Package</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reservation_history as $booking): ?>
                  <tr>
                    <td><?= $booking['id'] ?></td>
                    <td><?= $booking['user_name'] ?></td>
                    <td><?= $booking['booking_date'] ?></td>
                    <td><?= $booking['package_name'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php elseif ($search_venue): ?>
        <p>No venue found matching your search.</p>
      <?php endif; ?>
    </section>

    <!-- Add Venue Button -->
    <button id="addVenueBtn" class="btn-add animate__animated animate__fadeIn">Add Venue</button>

    <!-- Add Venue Form -->
    <div id="addVenueForm" class="form-container animate__animated animate__fadeIn" style="display: none">
      <h3>Add New Venue</h3>
      <p style="text-align:center; font-size:0.95rem; color:#6b7280; margin-bottom:1.5rem;">
        Fill in all the fields accurately to register a new venue in the system.
      </p>

      <form action="addVenueProcess.php" method="post" enctype="multipart/form-data">
        <!-- Venue Details -->
        <div class="form-group">
          <label for="venueName">Venue Name</label>
          <input type="text" id="venueName" name="venueName" required />
        </div>
        <div class="form-group">
          <label for="venueType">Venue Type</label>
          <select id="venueType" name="venueType" required>
            <option value="Conference Hall">Conference Hall</option>
            <option value="Wedding Venue">Wedding Venue</option>
            <option value="Outdoor Garden">Outdoor Garden</option>
            <option value="Restaurant">Restaurant</option>
          </select>
        </div>
        <div class="form-group">
          <label for="venueArea">Area</label>
          <input type="text" id="venueArea" name="venueArea" required />
        </div>
        <div class="form-group">
          <label for="venueCity">City</label>
          <input type="text" id="venueCity" name="venueCity" required />
        </div>
        <div class="form-group">
          <label for="venueCapacity">Capacity</label>
          <input type="number" id="venueCapacity" name="venueCapacity" required />
        </div>
        <div class="form-group">
          <label for="timeSlot1">Time Slot 1</label>
          <input type="text" id="timeSlot1" name="timeSlots[]" placeholder="14:00:00" required />
        </div>
        <div class="form-group">
          <label for="timeSlot2">Time Slot 2</label>
          <input type="text" id="timeSlot2" name="timeSlots[]" placeholder="18:00:00" required />
        </div>

        <div class="form-group">
          <label for="venueFeatures">Select Features</label><br>
          <!-- Hard-Coded Features -->
          <label>
            <input type="checkbox" name="features[]" value="1" /> WiFi
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="2" /> Parking
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="3" /> Sound System
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="4" /> Air Conditioning
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="5" /> Stage
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="6" /> Lighting
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="7" /> Catering
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="8" /> Outdoor Area
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="9" /> Projector
          </label><br>
          <label>
            <input type="checkbox" name="features[]" value="10" /> Dance Floor
          </label><br>
        </div>


        <div class="form-group">
          <label for="venueDescription">Description</label>
          <textarea id="venueDescription" name="venueDescription" rows="4" required></textarea>
        </div>
        <div class="form-group">
          <label for="venueImage">Upload Image</label>
          <input type="file" id="venueImage" name="venueImage" accept="image/*" required />
        </div>

        <!-- Food Packages Section (Dynamic) -->
        <fieldset style="border: none; margin-top: 2rem;">
          <legend style="font-weight: 600; font-size: 1.1rem; color: #374151; margin-bottom: 1rem;">Food Packages</legend>

          <div id="foodPackagesContainer">
            <div class="food-package" style="border: 1px solid #e5e7eb; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; background: #f9fafb;">
              <div class="form-group">
                <label for="packageName">Package Name</label>
                <input type="text" name="packages[0][package_name]" required />
              </div>
              <div class="form-group">
                <label for="originalPrice">Original Price (৳)</label>
                <input type="number" name="packages[0][original_price]" required />
              </div>
              <div class="form-group">
                <label for="promotionalPrice">Promotional Price (optional)</label>
                <input type="number" name="packages[0][promotional_price]" />
              </div>
              <div class="form-group">
                <label for="items">Menu Items</label>
                <textarea name="packages[0][items]" rows="4" placeholder="List items included in this package..." required></textarea>
              </div>
              <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="packages[0][is_featured]" id="isFeatured0" />
                <label for="isFeatured0" style="margin: 0;">Mark as Featured</label>
              </div>
            </div>
          </div>

          <button type="button" id="addPackageBtn" class="btn-add">+ Add Another Package</button>
        </fieldset>
        <button type="submit" class="btn-submit">Add Venue</button>
      </form>
    </div>


    <!-- Existing Venues List Section -->
    <section class="venues-table-section animate__animated animate__fadeInUp">
      <h3>Existing Venues</h3>
      <table class="venues-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Location</th>
            <th>Capacity</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($existing_venues as $venue): ?>
            <tr>
              <td><?= $venue['id'] ?></td>
              <td><?= $venue['name'] ?></td>
              <td><?= $venue['type'] ?></td>
              <td><?= $venue['city'] ?>, <?= $venue['area'] ?></td>
              <td><?= $venue['capacity'] ?> Guests</td>
              <td>
                <button class="btn-edit"><i class="fas fa-edit"></i></button>
                <button class="btn-delete"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </div>

  <script>
    const addVenueBtn = document.getElementById("addVenueBtn");
    const addVenueForm = document.getElementById("addVenueForm");

    addVenueBtn.addEventListener("click", () => {
      if (addVenueForm.style.display === "none") {
        addVenueForm.style.display = "block";
        addVenueBtn.textContent = "Close Form";
      } else {
        addVenueForm.style.display = "none";
        addVenueBtn.textContent = "Add Venue";
      }
    });

    // Open the edit features modal
    const editVenueBtn = document.getElementById("editVenueFeaturesBtn");
    const editVenueModal = document.getElementById("editVenueModal");
    const closeModalSpan = document.querySelector(".close");

    editVenueBtn.addEventListener("click", () => {
      editVenueModal.style.display = "block";
    });

    closeModalSpan.addEventListener("click", () => {
      editVenueModal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
      if (e.target === editVenueModal) {
        editVenueModal.style.display = "none";
      }
    });
  </script>
  <script>
    let packageCount = 1;

    document.getElementById("addPackageBtn").addEventListener("click", function() {
      const container = document.getElementById("foodPackagesContainer");

      const newPackageDiv = document.createElement("div");
      newPackageDiv.classList.add("food-package");
      newPackageDiv.style.cssText = "border: 1px solid #e5e7eb; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; background: #f9fafb; position: relative;";

      newPackageDiv.innerHTML = `
      <div class="form-group">
        <label for="packageName${packageCount}">Package Name</label>
        <input type="text" id="packageName${packageCount}" name="packages[${packageCount}][package_name]" required />
      </div>
      <div class="form-group">
        <label for="originalPrice${packageCount}">Original Price (৳)</label>
        <input type="number" id="originalPrice${packageCount}" name="packages[${packageCount}][original_price]" required />
      </div>
      <div class="form-group">
        <label for="promotionalPrice${packageCount}">Promotional Price (optional)</label>
        <input type="number" id="promotionalPrice${packageCount}" name="packages[${packageCount}][promotional_price]" />
      </div>
      <div class="form-group">
        <label for="items${packageCount}">Menu Items</label>
        <textarea id="items${packageCount}" name="packages[${packageCount}][items]" rows="4" placeholder="List items included..." required></textarea>
      </div>
      <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
        <input type="checkbox" id="isFeatured${packageCount}" name="packages[${packageCount}][is_featured]" />
        <label for="isFeatured${packageCount}" style="margin: 0;">Mark as Featured</label>
      </div>
      <button type="button" class="remove-package-btn" style="
        position: absolute;
        top: 10px;
        right: 10px;
        background: #e11d48;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 0.85rem;
        cursor: pointer;
      ">Remove</button>
    `;

      // Append to container
      container.appendChild(newPackageDiv);

      // Add remove functionality
      newPackageDiv.querySelector('.remove-package-btn').addEventListener('click', () => {
        newPackageDiv.remove();
      });

      packageCount++;
    });
  </script>


</body>

</html>