<?php
$current = basename($_SERVER['PHP_SELF']); // gets current file name like 'venues.php'
?>
<style>
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
<nav class="navbar">
  <div class="logo">CEREMO</div>
  <ul class="nav-links">
    <li><a href="index.php" class="<?= ($current == 'index.php') ? 'active' : '' ?>">Home</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="venues.php" class="<?= ($current == 'venues.php') ? 'active' : '' ?>">Venues</a></li>
    <li><a href="bookNow.php" class="<?= ($current == 'bookNow.php') ? 'active' : '' ?>">Book Now</a></li>
    <li><a href="addOns.php" class="<?= ($current == 'addOns.php') ? 'active' : '' ?>">Services</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><button class="btn-login" onclick="window.location.href='login.html'">Login</button></li>
  </ul>
</nav>