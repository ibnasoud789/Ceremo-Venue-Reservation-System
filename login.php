<?php
session_start();
include 'navbar.php';
$redirectTo = $_GET['redirect'] ?? '';
// (Optionally you can validate it here: ensure it begins with "/" and contains no "http")
?>
<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="loginstyle.css" />
</head>

<body>
  <div id="container" class="container">
    <!-- FORM SECTION -->
    <div class="row">
      <!-- SIGN UP SECTION -->
      <div class="col align-items-center flex-col sign-up">
        <div class="form-wrapper align-items-center">
          <!-- Role Selection Section -->
          <div class="role-selection">
            <h3>Are you a...</h3>
            <div class="user-role-selection">
              <div class="role-option" onclick="showVenueForm('customer')">
                <span>Customer</span>
              </div>
              <div class="role-option" onclick="window.location.href='signUpVenue.php'">
                <span>Service Provider</span>
              </div>

            </div>
          </div>



          <!-- Signup Form (Visible After Role Selection) -->
          <form id="signupForm" class="form sign-up" method="post" action="signup_process.php" style="display: none;">
            <!-- Hidden Input for Role -->
            <input type="hidden" name="user_role" id="user_role" required />

            <!-- Username Input -->
            <div class="input-group">
              <i class="bx bxs-user"></i>
              <input name="username" type="text" placeholder="Username" required />
            </div>

            <!-- Email Input -->
            <div class="input-group">
              <i class="bx bx-mail-send"></i>
              <input name="email" type="email" placeholder="Email" required />
            </div>

            <!-- Password Input -->
            <div class="input-group">
              <i class="bx bxs-lock-alt"></i>
              <input name="password" type="password" placeholder="Password" required />
            </div>

            <!-- Confirm Password Input -->
            <div class="input-group">
              <i class="bx bxs-lock-alt"></i>
              <input name="confirm_password" type="password" placeholder="Confirm password" required />
            </div>

            <!-- Submit Button -->
            <button type="submit">Sign up</button>
            <p>
              <span> Already have an account? </span>
              <b onclick="toggle()" class="pointer"> Sign in here </b>
            </p>
          </form>
        </div>
      </div>
      <!-- END SIGN UP SECTION -->

      <!-- END SIGN UP -->

      <!-- SIGN IN -->
      <div class="col align-items-center flex-col sign-in">
        <div class="form-wrapper align-items-center">
          <form class="form sign-in" method="post" action="login_process.php">
            <input
              type="hidden"
              name="redirect"
              value="<?= htmlspecialchars($redirectTo, ENT_QUOTES) ?>">
            <!-- choose your role -->
            <div class="input-group">
              <select name="user_type" required>
                <option value="" disabled selected>I am a…</option>
                <option value="admin">Admin</option>
                <option value="customer">Customer</option>
                <option value="venue">Venue</option>
              </select>
            </div>

            <!-- identifier -->
            <div class="input-group">
              <i class="bx bxs-user"></i>
              <input
                type="text"
                name="identifier"
                placeholder="Enter Your ID"
                required />
            </div>

            <!-- password -->
            <div class="input-group">
              <i class="bx bxs-lock-alt"></i>
              <input
                type="password"
                name="password"
                placeholder="Password"
                required />
            </div>

            <button type="submit">Sign in</button>

            <p><b> Forgot password? </b></p>
            <p>
              <span> Don't have an account? </span>
              <b onclick="toggle()" class="pointer"> Sign up here </b>
            </p>
          </form>
        </div>
      </div>
      <!-- END SIGN IN -->
    </div>
    <!-- END FORM SECTION -->

    <!-- CONTENT SECTION (unchanged) -->
    <div class="row content-row">
      <!-- SIGN IN CONTENT -->
      <div class="col align-items-center flex-col">
        <div class="text sign-in">
          <h2>Welcome<br />CEREMO</h2>
        </div>
        <div class="img sign-in"></div>
      </div>
      <!-- SIGN UP CONTENT -->
      <div class="col align-items-center flex-col">
        <div class="img sign-up"></div>
        <div class="text sign-up">
          <h2>Join with us</h2>
        </div>
      </div>
    </div>
    <!-- END CONTENT SECTION -->
  </div>
  <script src="login.js"></script>
  <script>
    function showVenueForm(role) {
      // Hide the role selection
      document.querySelector('.role-selection').style.display = 'none';

      // Show the corresponding form based on the role selected
      if (role === 'venue') {
        // Show the Add Venue Form
        document.getElementById('addVenueForm').style.display = 'block';
        document.getElementById('signupForm').style.display = 'none'; // Ensure other forms are hidden
      } else if (role === 'customer') {
        // Show the sign-up form for customers
        document.getElementById('signupForm').style.display = 'block';
        document.getElementById('addVenueForm').style.display = 'none'; // Hide Add Venue form
      }
    }
  </script>
</body>

</html>