<?php
$current = basename($_SERVER['PHP_SELF']); // gets current file name like 'venues.php'
?>

<nav class="navbar">
  <div class="logo">CEREMO</div>
  <ul class="nav-links">
    <li><a href="index.php" class="<?= ($current == 'index.php') ? 'active' : '' ?>">Home</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="venues.php" class="<?= ($current == 'venues.php') ? 'active' : '' ?>">Venues</a></li>
    <li><a href="booking.html" class="<?= ($current == 'booking.html') ? 'active' : '' ?>">Book Now</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><button class="btn-login" onclick="window.location.href='login.html'">Login</button></li>
  </ul>
</nav>