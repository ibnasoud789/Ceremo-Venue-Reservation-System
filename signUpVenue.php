<?php
session_start();
include 'navbar.php'; // Include the navbar if necessary
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Provider Signup</title>
  <link rel="stylesheet" href="provider_signup.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
  <div id="addVenueForm" class="form-container animate__animated animate__fadeIn">
    <h3>Venue Signup</h3>
    <p class="form-description">
      Fill in all the fields accurately to register in the system.
    </p>

    <form action="newVenueAddProcess.php" method="post" enctype="multipart/form-data">
      <!-- Venue Details -->
      <div class="form-group">
        <label for="venueName">Venue Name</label>
        <input type="text" id="venueName" name="venueName" placeholder="Enter venue name" required />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="text" id="password" name="password" placeholder="Enter your password" required />
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
        <label for="venueholding">Holding</label>
        <input type="number" id="venueholding" name="venueholding" placeholder="Enter holding number" required />
      </div>
      <div class="form-group">
        <label for="venueArea">Area</label>
        <input type="text" id="venueArea" name="venueArea" placeholder="Enter area" required />
      </div>

      <div class="form-group">
        <label for="venueCity">City</label>
        <input type="text" id="venueCity" name="venueCity" placeholder="Enter city" required />
      </div>
      <div class="form-group">
        <label for="venuezip">Zip</label>
        <input type="number" id="venuezip" name="venuezip" placeholder="Enter Zip" required />
      </div>

      <div class="form-group">
        <label for="venueCapacity">Capacity</label>
        <input type="number" id="venueCapacity" name="venueCapacity" placeholder="Enter capacity" required />
      </div>
      <div class="form-group">
        <label for="venueBatch">Batch</label>
        <input type="number" id="venueBatch" name="venueBatch" placeholder="Enter batch" required />
      </div>
      <div class="form-group">
        <label for="venueSize">Space (sq m)</label>
        <input type="number" id="venueSize" name="venueSize" placeholder="Enter size of your venue" required />
      </div>
      <div class="form-group">
        <label for="venuecontact">Contact No.</label>
        <input type="number" id="venuecontact" name="venuecontact" placeholder="Enter your contact number" required />
      </div>
      <div class="form-group">
        <label for="venueemail">Email</label>
        <input type="text" id="venueemail" name="venueemail" placeholder="Enter your email" required />
      </div>

      <!-- Time Slots -->
      <div class="form-group">
        <label for="timeSlot1">Time Slot 1</label>
        <input type="text" id="timeSlot1" name="timeSlots[]" placeholder="14:00:00" required />
      </div>

      <div class="form-group">
        <label for="timeSlot2">Time Slot 2</label>
        <input type="text" id="timeSlot2" name="timeSlots[]" placeholder="18:00:00" required />
      </div>

      <!-- Features -->
      <div class="form-group">
        <label for="venueFeatures">Select Features</label><br>
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

      <!-- Venue Description -->
      <div class="form-group">
        <label for="venueDescription">Venue Description</label>
        <textarea id="venueDescription" name="venueDescription" rows="4" placeholder="Describe the venue..." required></textarea>
      </div>

      <!-- Image Upload -->
      <div class="form-group">
        <label for="venueImage">Upload Venue Image</label>
        <input type="file" id="venueImage" name="venueImage" accept="image/*" required />
      </div>

      <!-- Food Packages Section (Dynamic) -->
      <fieldset class="food-packages-section">
        <legend>Food Packages</legend>

        <div id="foodPackagesContainer">
          <div class="food-package">
            <div class="form-group">
              <label for="packageName">Package Name</label>
              <input type="text" name="packages[0][package_name]" placeholder="Enter package name" required />
            </div>
            <div class="form-group">
              <label for="originalPrice">Original Price (৳)</label>
              <input type="number" name="packages[0][original_price]" placeholder="Enter original price" required />
            </div>
            <div class="form-group">
              <label for="promotionalPrice">Promotional Price</label>
              <input type="number" name="packages[0][promotional_price]" placeholder="Enter promotional price (optional)" />
            </div>
            <div class="form-group">
              <label for="items">Menu Items</label>
              <textarea name="packages[0][items]" rows="4" placeholder="List items included in this package..." required></textarea>
            </div>
            <div class="form-group">
              <label for="isFeatured">Mark as Featured</label>
              <input type="checkbox" name="packages[0][is_featured]" />
            </div>
          </div>
        </div>

        <button type="button" id="addPackageBtn" class="btn-add">+ Add Another Package</button>
      </fieldset>

      <!-- Submit Button -->
      <button type="submit" class="btn-submit">Add Venue</button>
    </form>
  </div>
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

<style>
  /* General Reset */
  * {
    font-family: "Poppins", sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  /* Container Styling */
  .form-container {
    width: 100%;
    max-width: 1000px;
    padding: 2rem;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    margin: auto;
    background: linear-gradient(135deg, #00c6ff, #0072ff);
    color: white;
  }

  h3 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
  }

  .form-description {
    text-align: center;
    font-size: 0.95rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
  }

  /* Input Field Styling */
  .form-group {
    margin-bottom: 1.5rem;
  }

  label {
    font-size: 1rem;
    color: #333;
  }

  input[type="text"],
  input[type="number"],
  textarea,
  select {
    width: 100%;
    padding: 12px;
    font-size: 1rem;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 8px;
    transition: 0.3s ease;
  }

  input[type="text"]:focus,
  input[type="number"]:focus,
  select:focus,
  textarea:focus {
    outline: none;
    border-color: #0072ff;
  }

  textarea {
    resize: vertical;
    min-height: 150px;
  }

  /* Buttons */
  button {
    padding: 12px;
    width: 100%;
    background-color: #0072ff;
    color: white;
    font-size: 1.1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 1.5rem;
  }

  button:hover {
    background-color: #005bb5;
  }

  /* Food Packages Section Styling */
  fieldset {
    border: 1px solid #ddd;
    padding: 2rem;
    border-radius: 10px;
    background-color: #f9fafb;
    margin-bottom: 1.5rem;
  }

  legend {
    font-weight: 600;
    font-size: 1.1rem;
    color: #374151;
  }

  .food-package {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
    margin-bottom: 1.5rem;
  }

  /* Add Another Package Button */
  #addPackageBtn {
    background-color: #28a745;
    padding: 12px;
    font-size: 1.1rem;
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 1.5rem;
  }

  #addPackageBtn:hover {
    background-color: #218838;
  }
</style>