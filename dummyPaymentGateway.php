<?php
session_start();
$total = $_POST['total'] ?? 0;
$advance = $_POST['advance'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Payment Gateway</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f9f9fb;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
    }

    .gateway-wrapper {
      max-width: 900px;
      width: 100%;
      display: flex;
      background: white;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      border-radius: 16px;
      overflow: hidden;
    }

    .summary-section {
      flex: 1;
      background: #f2f0ff;
      padding: 30px;
      border-right: 1px solid #e0dff5;
    }

    .summary-section h2 {
      margin-bottom: 20px;
      color: #6a0dad;
    }

    .summary-section p {
      font-size: 1rem;
      margin: 8px 0;
    }

    .summary-section .amount {
      font-size: 1.5rem;
      color: #000;
      font-weight: 600;
      margin-top: 20px;
    }

    .payment-section {
      flex: 2;
      padding: 30px;
    }

    .payment-section h3 {
      color: #2a0076;
      margin-bottom: 20px;
    }

    .tabs {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .tab {
      flex: 1;
      padding: 12px;
      text-align: center;
      background: #f5f5f9;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: 0.3s;
    }

    .tab.active {
      background: #6a0dad;
      color: white;
    }

    .payment-form {
      display: none;
      margin-top: 20px;
    }

    .payment-form.active {
      display: block;
    }

    .card-input {
      width: 100%;
      padding: 14px;
      font-size: 1rem;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .btn {
      background: linear-gradient(to right, #6a0dad, #8a2be2);
      color: #fff;
      padding: 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
      margin-top: 20px;
    }

    .btn:hover {
      background: linear-gradient(to right, #53179f, #731dc4);
    }

    .logo-row {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }

    .logo-row img {
      height: 30px;
    }

    .note {
      font-size: 0.85rem;
      color: #777;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="gateway-wrapper">
    <div class="summary-section">
      <h2>Payment Summary</h2>
      <p>Total Bill: ৳<?= number_format($total) ?></p>
      <p>Advance (40%):</p>
      <p class="amount">৳<?= number_format($advance) ?></p>
      <div class="note">Pay now to confirm your booking.</div>
    </div>

    <div class="payment-section">
      <h3>Select Payment Method</h3>

      <div class="tabs">
        <div class="tab active" data-tab="card">💳 Card</div>
        <div class="tab" data-tab="wallet">📱 Mobile Wallet</div>
        <div class="tab" data-tab="bank">🏦 Net Banking</div>
      </div>

      <!-- Card Form -->
      <form class="payment-form active" id="card" action="paymentSuccess.php" method="POST">
        <input type="hidden" name="paid" value="<?= $advance ?>">
        <input type="text" name="card_number" class="card-input" placeholder="Card Number" required>
        <input type="text" name="card_name" class="card-input" placeholder="Cardholder Name" required>
        <input type="text" name="expiry" class="card-input" placeholder="MM/YY" required>
        <input type="text" name="cvv" class="card-input" placeholder="CVV" required>
        <button type="submit" class="btn">Pay ৳<?= number_format($advance) ?> Now</button>
      </form>

      <!-- Mobile Wallet -->
      <form class="payment-form" id="wallet" action="paymentSuccess.php" method="POST">
        <input type="hidden" name="paid" value="<?= $advance ?>">
        <select class="card-input" name="wallet_type" required>
          <option value="">Select Wallet</option>
          <option value="bkash">bKash</option>
          <option value="nagad">Nagad</option>
          <option value="rocket">Rocket</option>
        </select>
        <input type="text" name="wallet_number" class="card-input" placeholder="Wallet Number" required>
        <button type="submit" class="btn">Pay ৳<?= number_format($advance) ?> via Wallet</button>
      </form>

      <!-- Bank -->
      <form class="payment-form" id="bank">
        <p style="color: #999;">Net Banking feature is coming soon!</p>
      </form>

      <div class="logo-row">
        <img src="images/payment_methods/visainc.png">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Mastercard_2019_logo.svg/120px-Mastercard_2019_logo.svg.png">
        <img src="images/payment_methods/BKash-Logo.wine.svg">
        <img src="images/payment_methods/Nagad-Logo.wine.svg">
        <img src="images/payment_methods/rocket.jpeg">
      </div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.payment-form').forEach(f => f.classList.remove('active'));

        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
      });
    });
  </script>
</body>

</html>