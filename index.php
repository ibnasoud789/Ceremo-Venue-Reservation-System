<?php
include 'db.php';
include 'navbar.php';



$stmt = $conn->prepare("  SELECT v.id, v.name, v.capacity, v.image, MIN(fp.promotional_price) AS min_promotional_price
  FROM bookings b
  JOIN venues v ON b.venue_id = v.id
  JOIN food_packages fp ON v.id = fp.venue_id
  WHERE b.status = 'Active'
  GROUP BY v.id
  ORDER BY COUNT(b.id) DESC
  LIMIT 3");
$stmt->execute();
$popularVenues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EventVenue</title>
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    :root {
      --primary: #ff4d6d;
      --secondary: #1f2937;
      --accent: #a855f7;
    }

    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f3f4f6;
      color: var(--secondary);
    }

    /* Navbar */
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

    .logo {
      font-weight: 800;
      font-size: 1.8rem;
      color: var(--primary);
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

    /* Retain original hero */
    .hero {
      position: relative;
      height: 100vh;
      background: url("images/pexels-rosario-fernandes-26325-3835638.jpg") no-repeat center center/cover;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      text-align: center;
      color: white;
    }

    .hero .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
    }

    .hero-content {
      position: relative;
      z-index: 1;
    }

    .hero-content h1 {
      font-size: 3.5rem;
      margin-bottom: 0.5rem;
    }

    .hero-content p {
      font-size: 1.2rem;
    }

    /* Search Box */
    .search-box {
      position: absolute;
      bottom: 15%;
      background: var(--primary);
      padding: 25px 30px;
      border-radius: 15px;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
      z-index: 2;
      box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
    }

    .search-box input,
    .search-box select,
    .search-box button {
      padding: 10px;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
    }

    .search-box button {
      background-color: #fff;
      color: var(--primary);
      font-weight: bold;
      transition: 0.3s;
    }

    .search-box button:hover {
      background-color: #f3f4f6;
    }

    /* Venues */
    .venues {
      text-align: center;
      padding: 5rem 2rem;
      background: linear-gradient(to bottom, #fff, #f1f5f9);
    }

    .venues h2 {
      font-size: 2.5rem;
      margin-bottom: 2rem;
      color: var(--secondary);
    }

    .venue-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: auto;
    }

    .card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease;
      padding: 1rem;
      /* Add padding inside the card */
      display: flex;
      flex-direction: column;
      /* Stack the elements vertically */
      justify-content: space-between;
      /* Space out the elements evenly */
    }

    .card:hover {
      transform: translateY(-8px);
    }

    .card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .card h3 {
      margin: 1rem 0 0.5rem;
      color: var(--primary);
    }

    .card p {
      font-size: 0.95rem;
      color: #555;
    }

    .btn-book {
      background: var(--accent);
      color: white;
      padding: 0.6rem 1.5rem;
      margin: 1rem 0;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      transition: 0.3s;
    }

    .btn-book:hover {
      background: #7c3aed;
    }

    /* About */
    .about {
      padding: 5rem 2rem;
      background: #0f172a;
      color: #fff;
      text-align: center;
    }

    .about h2 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }

    .about p {
      max-width: 800px;
      margin: auto;
      font-size: 1rem;
      color: #cbd5e1;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 3rem;
    }

    .feature-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(12px);
      border-radius: 12px;
      padding: 2rem 1rem;
      text-align: center;
      color: #fff;
      transition: all 0.3s;
    }

    .feature-card:hover {
      transform: scale(1.05);
      box-shadow: 0 15px 30px rgba(255, 255, 255, 0.1);
    }

    .feature-card i {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: var(--primary);
    }

    /* Contact */
    .contact {
      background: #fff;
      padding: 4rem 2rem;
      text-align: center;
    }

    .contact h2 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: var(--secondary);
    }

    .contact-form {
      max-width: 600px;
      margin: auto;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .contact-form input,
    .contact-form textarea {
      padding: 0.8rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
    }

    .btn-submit {
      background: var(--primary);
      color: white;
      padding: 0.8rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: bold;
    }

    .btn-submit:hover {
      background: #dc3a56;
    }

    /* Additional sections */
    .testimonial-section,
    .cta-banner,
    .faq-section {
      padding: 4rem 2rem;
      background: #fff;
      text-align: center;
    }

    .testimonial-section h2,
    .cta-banner h2,
    .faq-section h2 {
      font-size: 2.5rem;
      margin-bottom: 2rem;
    }

    .testimonials {
      display: flex;
      gap: 2rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .testimonial {
      background: #f9fafb;
      padding: 1.5rem;
      border-radius: 10px;
      max-width: 300px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .testimonial p {
      font-style: italic;
      font-size: 0.95rem;
    }

    .testimonial h4 {
      margin-top: 1rem;
      font-size: 1.1rem;
      color: var(--primary);
    }

    .cta-banner {
      background: linear-gradient(to right, #ff758f, #a855f7);
      color: white;
      border-radius: 12px;
    }

    .cta-banner button {
      margin-top: 1.5rem;
      padding: 0.8rem 2rem;
      font-size: 1.1rem;
      border: none;
      border-radius: 8px;
      background: #fff;
      color: var(--primary);
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    .cta-banner button:hover {
      background: #f3f4f6;
    }

    .faq-item {
      max-width: 700px;
      margin: 1rem auto;
      text-align: left;
    }

    .faq-item h4 {
      margin-bottom: 0.5rem;
      color: var(--primary);
    }

    .faq-item p {
      color: #555;
      font-size: 0.95rem;
    }

    /* Footer */
    .footer {
      background: #1e293b;
      color: #cbd5e1;
      text-align: center;
      padding: 2rem;
    }

    .social-icons a {
      color: #cbd5e1;
      margin: 0 0.5rem;
      font-size: 1.3rem;
    }
  </style>
</head>

<body>
  <!-- Hero (unchanged) -->
  <section id="home" class="hero">
    <div class="overlay"></div>
    <div class="hero-content">
      <h1>Explore The Perfect Venue</h1>
      <p>Find and book the ideal space for your next big event.</p>
    </div>
    <div class="search-box">
      <form action="availableVenues.php" method="GET">
        <label>Venue Type</label>
        <select name="type">
          <option value="">Any</option>
          <option value="Conference Hall">Conference Hall</option>
          <option value="Wedding Venue">Wedding Venue</option>
          <option value="Outdoor Garden">Outdoor Garden</option>
          <option value="Restaurant">Restaurant</option>
        </select>

        <label>City</label>
        <select name="city">
          <option value="">Any</option>
          <option value="Dhaka">Dhaka</option>
          <option value="Chittagong">Chittagong</option>
          <option value="Sylhet">Sylhet</option>
          <option value="Rajshahi">Rajshahi</option>
        </select>

        <label>Area</label>
        <input type="text" name="area" placeholder="Enter area" required />

        <label>Guests</label>
        <input type="number" name="guests" placeholder="Enter No. of Guests" required />

        <label>Date</label>
        <input type="date" name="date" required />

        <button type="submit">Search</button>
      </form>
    </div>
  </section>

  <section class="venues">
    <h2>Popular Venues</h2>
    <div class="venue-cards">
      <?php foreach ($popularVenues as $venue): ?>
        <div class="card">
          <img src="images/venues/<?php echo htmlspecialchars($venue['image']); ?>" alt="<?php echo htmlspecialchars($venue['name']); ?>" />
          <h3><?php echo htmlspecialchars($venue['name']); ?></h3>
          <p class="capacity">Capacity: <?php echo htmlspecialchars($venue['capacity']); ?></p>
          <p class="price">Starting from: BDT <?php echo number_format($venue['min_promotional_price'], 2); ?>/set</p>
          <button class="btn-book" onclick="window.location.href='venuedetails.php?venue_id=<?= $venue['id'] ?>'">Details</button>
        </div>
      <?php endforeach; ?>

    </div>
  </section>

  <section id="about" class="about">
    <h2>About Us</h2>
    <p>We help you find the perfect venue for your dream event. With our easy-to-use platform, you can browse venues, check availability, and book seamlessly.</p>
    <div class="features-grid">
      <div class="feature-card">
        <i class="fas fa-calendar-check"></i>
        <h4>Real-Time Availability</h4>
        <p>Check instantly and avoid overlaps.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-tags"></i>
        <h4>Budget Friendly</h4>
        <p>Prices tailored for every celebration.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-map-marker-alt"></i>
        <h4>Wide Coverage</h4>
        <p>Explore venues in your city & beyond.</p>
      </div>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <i class="fas fa-gem"></i>
        <h4>Decoration Services</h4>
        <p>Customize your event ambiance with elegant decor setups.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-camera-retro"></i>
        <h4>Photography & Videography</h4>
        <p>Capture timeless memories with our media professionals.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-headphones-alt"></i>
        <h4>DJ & Music</h4>
        <p>Electrify your event with top-quality sound and DJ sets.</p>
      </div>
    </div>
  </section>
  <section class="testimonial-section">
    <h2>What Our Users Say</h2>
    <div class="testimonials">
      <div class="testimonial">
        <p>"Booking my wedding venue was so smooth. I loved the UI and responsiveness!"</p>
        <h4>- Sarah H.</h4>
      </div>
      <div class="testimonial">
        <p>"Great service, excellent support, and a huge variety of venues. Highly recommend!"</p>
        <h4>- Imran A.</h4>
      </div>
      <div class="testimonial">
        <p>"It saved me a lot of time planning our company event. Super helpful platform."</p>
        <h4>- Mahmud R.</h4>
      </div>
    </div>
  </section>

  <section class="cta-banner">
    <h2>Ready to Find Your Perfect Venue?</h2>
    <p>Book in minutes and plan with ease. Start exploring now!</p>
    <button onclick="window.location.href='venues.php'">Explore Venues</button>
  </section>

  <section class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-item">
      <h4>How do I book a venue?</h4>
      <p>You can browse available venues, select your preferred one, and follow the steps to confirm your booking.</p>
    </div>
    <div class="faq-item">
      <h4>Can I modify or cancel a booking?</h4>
      <p>Yes, after logging in, you can manage your bookings from your dashboard.</p>
    </div>
    <div class="faq-item">
      <h4>Is there any refund policy?</h4>
      <p>Refund policies vary by venue. Please review the terms during booking or contact support for help.</p>
    </div>
  </section>

  <section id="contact" class="contact">
    <h2>Contact Us</h2>
    <form class="contact-form">
      <input type="text" placeholder="Your Name" required />
      <input type="email" placeholder="Your Email" required />
      <textarea placeholder="Your Message" rows="5" required></textarea>
      <button type="submit" class="btn-submit">Send Message</button>
    </form>
  </section>

  <footer class="footer">
    <p>&copy; 2025 Ceremo. All rights reserved.</p>
    <div class="social-icons">
      <a href="#"><i class="fab fa-facebook"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
    </div>
  </footer>
  <!-- Chatbot Icon -->
  <div id="chatbot-icon" class="chatbot-icon">
    <i class="fas fa-comments"></i>
  </div>

  <!-- Chatbot Window -->
  <div id="chatbot-window" class="chatbot-window">
    <div class="chat-header">
      <h4>Chat with Us</h4>
      <button id="close-chat" class="close-chat">&times;</button>
    </div>
    <div id="chat-content" class="chat-content">
      <!-- Chat messages will appear here -->
    </div>
    <div class="chat-input">
      <textarea id="chat-message" placeholder="Type your message..." rows="3"></textarea>
      <button id="send-chat" onclick="sendMessage()">Send</button>
    </div>
  </div>
  <script>
    // Open/Close the chat window
    document.getElementById('chatbot-icon').onclick = function() {
      document.getElementById('chatbot-window').style.display = 'flex';
    };

    document.getElementById('close-chat').onclick = function() {
      document.getElementById('chatbot-window').style.display = 'none';
    };

    // Function to send message to the backend (PHP) and display response
    function sendMessage() {
      var message = document.getElementById('chat-message').value;
      if (message.trim() === '') return;

      // Display the user's message in the chat window
      var chatContent = document.getElementById('chat-content');
      chatContent.innerHTML += `<div class="user-message">${message}</div>`;

      // Clear the input field
      document.getElementById('chat-message').value = '';

      // Make an AJAX request to send the message to the PHP backend
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'chatbot.php', true); // Replace with your PHP backend path
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          chatContent.innerHTML += `<div class="bot-message">${response.response}</div>`;
          chatContent.scrollTop = chatContent.scrollHeight; // Scroll to the bottom
        }
      };
      xhr.send('query=' + encodeURIComponent(message));
    }
  </script>


</body>

</html>