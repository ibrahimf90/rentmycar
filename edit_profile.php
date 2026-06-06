<?php
// ============================================
// Project : Rent a Car
// File    : edit_profile.php
// Purpose : Edit user profile + change password
// ============================================
require_once 'config.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('signin.php');
}

$userId  = currentUserId();
$success = '';
$error   = '';

// Fetch current user data
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId LIMIT 1");
$user   = mysqli_fetch_assoc($result);

// ========== UPDATE PROFILE ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // -- Update Info --
    if ($_POST['action'] === 'update_info') {
        $full_name = clean($conn, $_POST['full_name'] ?? '');
        $phone     = clean($conn, $_POST['phone']     ?? '');
        $address   = clean($conn, $_POST['address']   ?? '');

        if (empty($full_name)) {
            $error = 'Full name is required.';
        } else {
            mysqli_query($conn,
                "UPDATE users SET full_name='$full_name', phone='$phone', address='$address'
                 WHERE id = $userId"
            );
            $_SESSION['user_name'] = $full_name;
            $success = 'Profile updated successfully!';
            // Refresh user data
            $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId LIMIT 1");
            $user   = mysqli_fetch_assoc($result);
        }
    }

    // -- Change Password --
    if ($_POST['action'] === 'change_password') {
        $current  = $_POST['current_password']  ?? '';
        $new      = $_POST['new_password']       ?? '';
        $confirm  = $_POST['confirm_password']   ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'Please fill in all password fields.';
        } elseif (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id = $userId");
            $success = 'Password changed successfully!';
        }
    }
}

$pageTitle  = 'Edit Profile — MyCar';
$activePage = '';
include 'header.php';
?>

<!-- ========== PAGE HERO ========== -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <p class="section-tag">My Account</p>
      <h1>Edit <span class="gold-text">Profile</span></h1>
      <p>Update your personal information and password.</p>
    </div>
  </div>
</section>

<!-- ========== PROFILE SECTION ========== -->
<section class="section">
  <div class="container">

    <?php if ($success): ?>
      <div class="alert alert-success" style="max-width:800px; margin: 0 auto 32px;"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error" style="max-width:800px; margin: 0 auto 32px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="profile-grid">

      <!-- ===== SIDEBAR ===== -->
      <div class="profile-sidebar">
        <div class="profile-avatar-box">
          <div class="profile-avatar">👤</div>
          <h3><?= htmlspecialchars($user['full_name']) ?></h3>
          <p><?= htmlspecialchars($user['email']) ?></p>
          <span class="profile-badge">
            🚗 <?= $user['total_rents'] ?> Rental<?= $user['total_rents'] != 1 ? 's' : '' ?>
          </span>
        </div>
        <div class="profile-nav">
          <a href="edit_profile.php" class="profile-nav-item active">✏️ Edit Profile</a>
          <a href="old_rent.php"     class="profile-nav-item">🚗 My Rentals</a>
          <a href="logout.php"       class="profile-nav-item danger">🚪 Logout</a>
        </div>
      </div>

      <!-- ===== MAIN CONTENT ===== -->
      <div class="profile-content">

        <!-- Update Info -->
        <div class="profile-card">
          <h2 class="profile-card-title">Personal <span class="gold-text">Information</span></h2>
          <form method="POST" action="edit_profile.php">
            <input type="hidden" name="action" value="update_info">
            <div class="form-row">
              <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required
                       value="<?= htmlspecialchars($user['full_name']) ?>">
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                       style="opacity:0.5; cursor:not-allowed;">
                <small style="color:var(--muted); font-size:0.78rem;">Email cannot be changed.</small>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="+49 (3) 0000-0000"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Member Since</label>
                <input type="text" value="<?= date('d M Y', strtotime($user['created_at'])) ?>" disabled
                       style="opacity:0.5; cursor:not-allowed;">
              </div>
            </div>
            <div class="form-group">
              <label>Default Delivery Address</label>
              <textarea name="address" placeholder="Your default delivery address..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-form" style="max-width:200px;">Save Changes →</button>
          </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card" style="margin-top: 28px;">
          <h2 class="profile-card-title">Change <span class="gold-text">Password</span></h2>
          <form method="POST" action="edit_profile.php">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
              <label>Current Password *</label>
              <div class="input-wrap">
                <input type="password" name="current_password" id="currentPwd" placeholder="Enter current password" required>
                <button type="button" class="toggle-password" data-target="#currentPwd">👁️</button>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>New Password * <span class="form-hint">(min. 6 characters)</span></label>
                <div class="input-wrap">
                  <input type="password" name="new_password" id="newPwd" placeholder="New password" required>
                  <button type="button" class="toggle-password" data-target="#newPwd">👁️</button>
                </div>
              </div>
              <div class="form-group">
                <label>Confirm New Password *</label>
                <div class="input-wrap">
                  <input type="password" name="confirm_password" id="confirmPwd" placeholder="Repeat new password" required>
                  <button type="button" class="toggle-password" data-target="#confirmPwd">👁️</button>
                </div>
              </div>
            </div>
            <button type="submit" class="btn-form" style="max-width:220px;">Change Password →</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>