<?php
// ============================================
// Project : Rent a Car
// File    : footer.php
// Purpose : Main footer included in index.php
// ============================================
?>

<!-- ========== FOOTER ========== -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="index.php" class="logo">
          <span class="logo-icon">⬡</span>
          <span class="logo-text">My<span class="gold">Car</span></span>
        </a>
        <p>Premium car rental service delivering luxury and comfort to your doorstep.</p>
      </div>

      <!-- Quick Links -->
      <div class="footer-links">
        <h4>Quick Links</h4>
        <a href="index.php?page=home">Home</a>
        <a href="index.php?page=about">About</a>
        <a href="index.php?page=services">Services</a>
        <a href="index.php?page=contact">Contact</a>
        <a href="rent.php">Rent a Car</a>
      </div>

      <!-- Account Links -->
      <div class="footer-links">
        <h4>Account</h4>
        <?php if (isLoggedIn()): ?>
          <a href="edit_profile.php">Edit Profile</a>
          <a href="old_rent.php">My Rentals</a>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="signin.php">Sign In</a>
          <a href="signin.php?tab=register">Register</a>
        <?php endif; ?>
      </div>

      <!-- Contact Info -->
      <div class="footer-contact">
        <h4>Contact</h4>
        <p>📧 info@mycar.com</p>
        <p>📞 +49 (3) 1234-4567</p>
        <p>📍 U-Bahn Straße 1, Berlin City</p>
      </div>

    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
      <div class="footer-bottom-inner">
        <p>© <?= date('Y') ?> MyCar. All rights reserved.</p>
        <p class="developed-by">
          Developed by
          <a href="https://github.com/ibrahimf90" target="_blank" class="github-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
            </svg>
            Ibrahim Fayyad
          </a>
        </p>
      </div>
    </div>
  </div>
</footer>

</body>
</html>
