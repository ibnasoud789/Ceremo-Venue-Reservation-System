<?php
include "db.php";
include 'navbar.php';
include 'customerAuthorization.php';

$venue_id = $_GET['venue_id'] ?? $_POST['venue_id'] ?? null;
$bookingDate = $_POST['bookingDate'] ?? null;
$timeSlot = $_POST['timeSlot'] ?? null;
$guests = $_POST['guests'] ?? null;
$package_name = $_POST['package_name'] ?? null;

// Fetch venue details
if ($venue_id) {
  $stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
  $stmt->bind_param("i", $venue_id);
  $stmt->execute();
  $venue = $stmt->get_result()->fetch_assoc();

  $features = [];
  $fstmt = $conn->prepare("SELECT f.name FROM features f JOIN venue_features vf ON f.id = vf.feature_id WHERE vf.venue_id = ?");
  $fstmt->bind_param("i", $venue_id);
  $fstmt->execute();
  $fres = $fstmt->get_result();
  while ($f = $fres->fetch_assoc()) {
    $features[] = $f['name'];
  }

  $food_packages = [];
  $pstmt = $conn->prepare("SELECT package_name, original_price, promotional_price, items FROM food_packages WHERE venue_id = ?");
  $pstmt->bind_param("i", $venue_id);
  $pstmt->execute();
  $food_packages = $pstmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// Fetch add-on categories and options
$add_on_categories = $conn->query("SELECT id, name FROM add_on_categories")->fetch_all(MYSQLI_ASSOC);
$add_on_options = [];
foreach ($add_on_categories as $cat) {
  $cid = $cat['id'];
  $stmt = $conn->prepare("SELECT * FROM add_on_options WHERE category_id = ?");
  $stmt->bind_param("i", $cid);
  $stmt->execute();
  $add_on_options[$cid] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// Fetch time slots
$slots = [];
if ($venue_id && $bookingDate) {
  $stmt = $conn->prepare("SELECT vs.time_slot, CASE WHEN EXISTS (SELECT 1 FROM bookings b WHERE b.venue_id = vs.venue_id AND b.booking_date = ? AND b.timeslot = vs.time_slot) THEN 0 ELSE 1 END AS is_available FROM venue_slots vs WHERE vs.venue_id = ?");
  $stmt->bind_param("si", $bookingDate, $venue_id);
  $stmt->execute();
  $slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Your Venue</title>
  <link rel="stylesheet" href="booking.css">
  <style>
    .available {
      color: green;
      font-weight: bold;
    }

    .unavailable {
      color: red;
      font-weight: bold;
    }

    input[type="radio"] {
      transform: scale(1.2);
      margin-right: 5px;
    }

    .package-card {
      background: #fdfdfd;
      border: 1px solid #ddd;
      padding: 15px;
      border-radius: 10px;
      margin: 10px 0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .package-card strong {
      font-size: 1.1rem;
      color: #2a0076;
    }

    .package-card span {
      font-weight: bold;
      color: #009933;
      display: block;
    }

    .package-card p {
      margin-top: 5px;
      font-size: 0.9rem;
      color: #444;
    }
  </style>
</head>

<body>
  <header class="hero-header">
    <div class="hero-content">
      <h1>Book Your Venue at <?= htmlspecialchars($venue['name']) ?></h1>
      <p class="subtitle"><?= htmlspecialchars($venue['area']) ?> • <?= htmlspecialchars($venue['city']) ?></p>
    </div>
  </header>

  <section class="booking-page-content">
    <div class="venue-details-section">
      <h2>Venue Details</h2>
      <div class="venue-image-container">
        <img src="images/venues/<?= htmlspecialchars($venue['image']) ?>" class="venue-image" />
      </div>
      <div class="venue-info">
        <h3 class="venue-name"><?= htmlspecialchars($venue['name']) ?></h3>
        <p><strong>Type:</strong> <?= htmlspecialchars($venue['type']) ?></p>
        <p><strong>Location:</strong><?= htmlspecialchars($venue['area']) ?>, <?= htmlspecialchars($venue['city']) ?></p>
        <p><strong>Batch:</strong> <?= htmlspecialchars($venue['Batch']) ?></p>
        <div class="tags">
          <span class="tag"><?= htmlspecialchars($venue['type']) ?></span>
          <span class="tag">Guests <?= htmlspecialchars($venue['capacity']) ?></span>
        </div>
        <p class="description"><?= htmlspecialchars($venue['description']) ?></p>
      </div>

      <div class="features">
        <strong>Features:</strong>
        <ul>
          <?php foreach ($features as $feature): ?>
            <li><?= htmlspecialchars($feature) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="food-packages">
        <h3>Food Packages</h3>
        <?php foreach ($food_packages as $package): ?>
          <div class="package-card">
            <strong><?= htmlspecialchars($package['package_name']) ?></strong>
            <span>৳<?= $package['promotional_price'] ?><?php if ($package['original_price'] > $package['promotional_price']): ?><s style="color: gray;"> ৳<?= $package['original_price'] ?></s><?php endif; ?></span>
            <p><?= htmlspecialchars($package['items']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="booking-form-section">
      <h2>Book</h2>
      <form method="POST" action="">
        <input type="hidden" name="venue_id" value="<?= $venue_id ?>">

        <?php if (!$bookingDate): ?>
          <div class="form-group">
            <label for="bookingDate">Choose a Date:</label>
            <input type="date" name="bookingDate" id="bookingDate" required>
            <button type="submit" name="action" value="check_date">Check Availability</button>
          </div>
        <?php else: ?>
          <div class="form-group">
            <p><strong>Selected Date:</strong> <?= date('d/m/Y', strtotime($bookingDate)) ?></p>
            <input type="hidden" name="bookingDate" value="<?= $bookingDate ?>">
          </div>
        <?php endif; ?>

        <?php if ($bookingDate && !$timeSlot): ?>
          <div class="form-group">
            <label>Available Time Slots on <?= htmlspecialchars($bookingDate) ?>:</label>
            <table>
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($slots as $slot): ?>
                  <tr>
                    <td><?= $slot['time_slot'] ?></td>
                    <td>
                      <?php if ($slot['is_available']): ?>
                        <label style="display: inline-flex; align-items: center; gap: 5px;" class="unavailable">
                          <input type="radio" name="timeSlot" value="<?= $slot['time_slot'] ?>" required>
                          Available
                        </label>
                      <?php else: ?>
                        <span class="unavailable">Booked</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <button type="submit" name="action" value="choose_time" style="margin-top: 10px;">Continue</button>
          </div>
        <?php endif; ?>

        <?php if ($bookingDate && $timeSlot): ?>
          <div class="form-group">
            <p><strong>Chosen Time Slot:</strong> <?= htmlspecialchars($timeSlot) ?></p>
            <input type="hidden" name="timeSlot" value="<?= $timeSlot ?>">
          </div>

          <div class="form-group">
            <label for="guests">Number of Guests:</label>
            <input type="number" name="guests" id="guests" min="1" required>
          </div>

          <div class="form-group">
            <label for="package_name">Select Food Package:</label>
            <select name="package_name" id="package_name" required>
              <option value="">-- Choose a Package --</option>
              <?php foreach ($food_packages as $pkg): ?>
                <option value="<?= htmlspecialchars($pkg['package_name']) ?>" data-price="<?= $pkg['promotional_price'] ?>">
                  <?= htmlspecialchars($pkg['package_name']) ?> (৳<?= $pkg['promotional_price'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="selected-addons-display" id="addonDisplaySection" style="display:none; margin-top: 30px;">
            <h3>🧩 Selected Add-Ons</h3>
            <ul id="addonDisplayList" style="list-style: none; padding: 0;"></ul>
          </div>


          <div class="cost-summary-card" id="costSummary" style="display: none;">
            <h3>💰 Booking Summary</h3>
            <ul>
              <li><span>Food Package Cost:</span> <strong>৳<span id="totalCost">0</span></strong></li>
              <li><span>Service Charge:</span> <strong>৳<span id="serviceCharge">0</span></strong></li>
              <li><span>Add-On Cost:</span> <strong>৳<span id="addOnCost">0</span></strong></li>
              <li class="grand"><span>Grand Total:</span> <strong>৳<span id="grandTotal">0</span></strong></li>
            </ul>
          </div>

          <button type="button" id="openAddOnModal" style="display:none; background: #6a0dad; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 1rem; margin-top: 15px; margin-bottom:10px; cursor: pointer;">
            ➕ Select Add-Ons
          </button>
          <div id="selectedAddOnsContainer"></div>



          <div class="form-group">
            <button type="submit" formaction="bookingConfirmation.php">Proceed</button>
          </div>
        <?php endif; ?>
        <!-- ADD-ON SELECTION MODAL -->
        <div id="addOnModal" class="modal" style="display: none;">
          <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
            <span class="close-btn" onclick="document.getElementById('addOnModal').style.display='none'">&times;</span>
            <h2>Select Optional Add-Ons</h2>

            <?php foreach ($add_on_categories as $cat): ?>
              <div class="addon-category" style="margin-bottom: 30px;">
                <h3 style="color: #2a0076;"><?= htmlspecialchars($cat['name']) ?></h3>
                <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                  <?php foreach ($add_on_options[$cat['id']] as $opt): ?>
                    <div class="addon-card" data-category="<?= $cat['id'] ?>" data-price="<?= $opt['price'] ?>" style="flex: 1 1 calc(45%);">
                      <input type="radio" name="addon_<?= $cat['id'] ?>" value="<?= $opt['id'] ?>" style="display: none;">
                      <strong><?= htmlspecialchars($opt['option_name']) ?> – ৳<?= number_format($opt['price']) ?></strong>
                      <p><?= htmlspecialchars($opt['features']) ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>


            <div style="text-align: right;">
              <button type="button" id="confirmAddOns" style="background: #2a0076; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer;">✔ Continue</button>
            </div>
          </div>
        </div>

      </form>

    </div>
  </section>
  <script>
    const guestsInput = document.getElementById('guests');
    const packageSelect = document.getElementById('package_name');
    const costSummary = document.getElementById('costSummary');
    const totalCost = document.getElementById('totalCost');
    const serviceCharge = document.getElementById('serviceCharge');
    const addOnCost = document.getElementById('addOnCost');
    const grandTotal = document.getElementById('grandTotal');
    const openModalBtn = document.getElementById('openAddOnModal');
    const selectedAddOnsContainer = document.getElementById('selectedAddOnsContainer');

    function calculateAddOnTotal() {
      let addOnTotal = 0;
      document.querySelectorAll('.addon-card.selected').forEach(card => {
        addOnTotal += parseFloat(card.dataset.price || 0);
      });
      return addOnTotal;
    }

    function updateCost() {
      const guests = parseInt(guestsInput.value) || 0;
      const selectedOption = packageSelect.options[packageSelect.selectedIndex];
      const price = parseFloat(selectedOption.dataset.price || 0);
      const total = guests * price;
      const service = guests * 30;
      const addons = calculateAddOnTotal();

      totalCost.textContent = total.toLocaleString();
      serviceCharge.textContent = service.toLocaleString();
      addOnCost.textContent = addons.toLocaleString();
      grandTotal.textContent = (total + service + addons).toLocaleString();

      const show = guests > 0 && price > 0;
      costSummary.style.display = show ? 'block' : 'none';
      openModalBtn.style.display = show ? 'inline-block' : 'none';
    }

    guestsInput.addEventListener('input', updateCost);
    packageSelect.addEventListener('change', updateCost);

    openModalBtn.addEventListener('click', () => {
      document.getElementById('addOnModal').style.display = 'block';
    });

    // Ensure only one selected per category
    document.querySelectorAll('.addon-card').forEach(card => {
      card.addEventListener('click', () => {
        const categoryId = card.getAttribute('data-category');
        document.querySelectorAll(`.addon-card[data-category="${categoryId}"]`).forEach(c => {
          c.classList.remove('selected');
          c.querySelector('input[type="radio"]').checked = false;
        });
        card.classList.add('selected');
        card.querySelector('input[type="radio"]').checked = true;
      });
    });

    document.getElementById('confirmAddOns').addEventListener('click', () => {
      selectedAddOnsContainer.innerHTML = '';
      const addonDisplayList = document.getElementById('addonDisplayList');
      addonDisplayList.innerHTML = ''; // 
      const seenCategories = new Set();

      document.querySelectorAll('.addon-card.selected').forEach(card => {
        const categoryId = card.getAttribute('data-category');
        if (seenCategories.has(categoryId)) return;
        seenCategories.add(categoryId);

        const radio = card.querySelector('input');
        const optionId = radio.value;
        const optionName = card.querySelector('strong').innerText.split("–")[0].trim();
        const optionPrice = parseFloat(card.dataset.price || 0);

        // Hidden inputs for form submission
        selectedAddOnsContainer.innerHTML += `
        <input type="hidden" name="addon_${categoryId}" value="${optionId}">
      `;

        // Visible list for user
        addonDisplayList.innerHTML += `
        <li><strong>${optionName}</strong> – ৳${optionPrice.toLocaleString()}</li>
      `;
      });

      document.getElementById('addonDisplaySection').style.display = seenCategories.size > 0 ? 'block' : 'none';
      document.getElementById('addOnModal').style.display = 'none';
      updateCost();
    });

    // Close modal if clicked outside
    window.onclick = function(event) {
      const modal = document.getElementById('addOnModal');
      if (event.target == modal) {
        modal.style.display = "none";
      }
    };
  </script>




</body>

</html>