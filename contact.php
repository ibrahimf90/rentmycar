<?php
// ============================================
// Project : Rent a Car
// File    : contact.php
// Purpose : Contact page content - included in index.php
// ============================================

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($conn, $_POST['name']    ?? '');
    $email   = clean($conn, $_POST['email']   ?? '');
    $subject = clean($conn, $_POST['subject'] ?? '');
    $message = clean($conn, $_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $sql = "INSERT INTO contact_messages (name, email, subject, message)
                VALUES ('$name', '$email', '$subject', '$message')";
        if (mysqli_query($conn, $sql)) {
            $success = 'Your message has been sent! We will get back to you soon.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>

<!-- ========== PAGE HERO ========== -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <p class="section-tag">Get In Touch</p>
      <h1>Contact <span class="gold-text">Us</span></h1>
      <p>Have a question or need help? We're here for you 24/7.</p>
    </div>
  </div>
</section>

<!-- ========== CONTACT SECTION ========== -->
<section class="section">
  <div class="container">
    <div class="contact-grid">

      <!-- Contact Info -->
      <div class="contact-info">
        <p class="section-tag">Our Info</p>
        <h2 class="section-title">Let's <span class="gold-text">Talk</span></h2>
        <p class="section-sub" style="text-align:left; margin:0 0 40px;">
          Reach out to us through any of the channels below and our team will respond as soon as possible.
        </p>

        <div class="info-items">
          <div class="info-item">
            <div class="info-icon">📧</div>
            <div>
              <h4>Email</h4>
              <p>info@mycar.com</p>
            </div>
          </div>
          <div class="info-item">
            <div class="info-icon">📞</div>
            <div>
              <h4>Phone</h4>
              <p>+49 (3) 1234-4567</p>
            </div>
          </div>
          <div class="info-item">
            <div class="info-icon">📍</div>
            <div>
              <h4>Address</h4>
              <p>U-Bahn Straße 1, Berlin City</p>
            </div>
          </div>
          <div class="info-item">
            <div class="info-icon">🕐</div>
            <div>
              <h4>Working Hours</h4>
              <p>Mon – Sat: 8:00 AM – 8:00 PM</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="contact-form-wrap">
        <?php if ($success): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=contact" class="contact-form">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name *</label>
              <input type="text" name="name" placeholder="Your full name" required
                     value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>
            <div class="form-group">
              <label>Email Address *</label>
              <input type="email" name="email" placeholder="your@email.com" required
                     value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" placeholder="What is this about?"
                   value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
          </div>
          <div class="form-group">
            <label>Message *</label>
            <textarea name="message" placeholder="Write your message here..." required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
          </div>
          <button type="submit" class="btn-form">Send Message →</button>
        </form>
      </div>

    </div>
  </div>
</section>