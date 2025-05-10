<?php
session_start();
// Assuming user data is stored in session
$userName = $_SESSION['user_name'] ?? 'Customer';
$userAvatar = $_SESSION['user_avatar'] ?? 'default-avatar.png';
$isLoggedIn = isset($_SESSION['user_type']);
$userType   = $_SESSION['user_type'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard - Ceremo</title>
  <link rel="stylesheet" href="navbar.css" />
  <link rel="stylesheet" href="customer.css" />
  <style>
    .nav-right {
      display: flex;
      align-items: center;
    }

    /* Avatar styling */
    .nav-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 0.5rem;
      object-fit: cover;
    }

    /* Hamburger in navbar */
    .nav-hamburger {
      background: none;
      border: none;
      display: flex;
      flex-direction: column;
      gap: 4px;
      padding: 0;
      cursor: pointer;
    }

    .nav-hamburger span {
      display: block;
      width: 20px;
      height: 2px;
      background: var(--text-dark);
    }

    /* Dropdown */
    .nav-dropdown {
      position: absolute;
      right: 1rem;
      top: 60px;
      background: #fff;
      border-radius: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transform-origin: top right;
      transition: opacity 0.2s, transform 0.2s;
    }

    .nav-dropdown.hidden {
      opacity: 0;
      transform: scale(0.95);
      pointer-events: none;
    }

    .nav-dropdown ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .nav-dropdown li a {
      display: block;
      padding: 0.75rem 1.5rem;
      color: var(--text-dark);
      text-decoration: none;
    }

    .nav-dropdown li a:hover {
      background: var(--bg-light);
    }

    :root {
      --primary: #ff4d6d;
      --secondary: #1f2937;
      --accent: #a855f7;
    }

    body {
      font-family: "Poppins", sans-serif;
      background-color: #f1f1f1;
      color: #333;
      overflow-x: hidden;
    }

    .logo {
      font-weight: 800;
      font-size: 1.8rem;
      color: var(--primary);
    }

    .navbar {
      background-color: #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .nav-links {
      display: flex;
      gap: 1.5rem;
      list-style: none;
    }

    .nav-links a {
      text-decoration: none;
      color: #374151;
      font-weight: 500;
      font-size: 1rem;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    .btn-login {
      background-color: var(--primary);
      color: #fff;
      padding: 0.5rem 1.2rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }

    .nav-links a.active {
      color: #ff4d6d;
      font-weight: bold;
      border-bottom: 2px solid #ff4d6d;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <header class="dashboard-header">
    <div class="container flex-between">
      <div class="welcome-text">
        <h1>Welcome, <?php echo htmlspecialchars($userName); ?> 👋</h1>
        <p>Manage your bookings effortlessly</p>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="bookings-overview">
      <h2>Your Upcoming Bookings</h2>
      <div class="card-grid">
        <!-- Example Booking Card -->
        <div class="booking-card">
          <h3>Grand Hall</h3>
          <p><strong>Date:</strong> 2025-06-15</p>
          <p><strong>Time Slot:</strong> 10:00 AM - 2:00 PM</p>
          <p><strong>Status:</strong> Confirmed</p>
          <a href="booking_details.php?id=123" class="btn btn-primary">View Details</a>
        </div>
        <!-- Repeat cards dynamically -->
      </div>
    </section>
  </main>

  <script>
    // Hamburger menu toggle
    const toggle = document.getElementById('menuToggle');
    const dropdown = document.getElementById('dropdownMenu');
    toggle.addEventListener('click', () => {
      dropdown.classList.toggle('hidden');
    });
  </script>
  <script>
    // Toggle nav dropdown
    const navBtn = document.getElementById('navHamburger');
    const navDropdown = document.getElementById('navDropdown');
    if (navBtn) {
      navBtn.addEventListener('click', () => {
        navDropdown.classList.toggle('hidden');
      });
    }
  </script>
</body>

</html>