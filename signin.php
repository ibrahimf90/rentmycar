<?php
// ============================================
// Project : Rent a Car
// File    : signin.php
// Purpose : Login + Register page
// ============================================
require_once 'config.php';

$redirectAfterLogin = 'index.php';
if (isset($_GET['redirect']) && $_GET['redirect'] === 'rent.php') {
    $redirectAfterLogin = 'rent.php';
}
$loginAction = 'signin.php' . ($redirectAfterLogin !== 'index.php' ? '?redirect=' . urlencode($redirectAfterLogin) : '');

// Redirect if already logged in
if (isLoggedIn()) {
    redirect($redirectAfterLogin);
}

$loginError    = '';
$loginSuccess  = '';
$regError      = '';
$regSuccess    = '';
$activeTab     = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// ========== LOGIN ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email    = clean($conn, $_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $loginError = 'Please fill in all fields.';
        $activeTab  = 'login';
    } else {
        $sql    = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email']= $user['email'];
            redirect($redirectAfterLogin);
        } else {
            $loginError = 'Invalid email or password.';
            $activeTab  = 'login';
        }
    }
}

// ========== REGISTER ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $activeTab  = 'register';
    $full_name  = clean($conn, $_POST['full_name'] ?? '');
    $email      = clean($conn, $_POST['email']     ?? '');
    $phone      = clean($conn, $_POST['phone']     ?? '');
    $password   = $_POST['password']  ?? '';
    $password2  = $_POST['password2'] ?? '';

    if (empty($full_name) || empty($email) || empty($password) || empty($password2)) {
        $regError = 'Please fill in all required fields.';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $regError = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $regError = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $regError = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $regError = 'This email is already registered. Please sign in.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql    = "INSERT INTO users (full_name, email, phone, password)
                       VALUES ('$full_name', '$email', '$phone', '$hashed')";
            if (mysqli_query($conn, $sql)) {
                $regSuccess = 'Account created successfully! You can now sign in.';
                $activeTab  = 'login';
            } else {
                $regError = 'Something went wrong. Please try again.';
            }
        }
    }
}

$pageTitle = 'Sign In — MyCar';
include 'header.php';
?>

<!-- ========== SIGNIN PAGE ========== -->
<section class="form-page">
  <div class="form-card">

    <!-- Logo -->
    <div class="form-logo">
      <a href="index.php" class="logo">
        <span class="logo-icon">⬡</span>
        <span class="logo-text">My<span class="gold">Car</span></span>
      </a>
    </div>

    <!-- Tabs -->
    <div class="form-tabs">
      <button class="tab-btn <?= $activeTab === 'login'    ? 'active' : '' ?>" data-tab="login">Sign In</button>
      <button class="tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>" data-tab="register">Register</button>
    </div>

    <!-- ===== LOGIN TAB ===== -->
    <div class="tab-content <?= $activeTab === 'login' ? 'active' : '' ?>" id="login">
      <h2 class="form-title">Welcome <span class="gold-text">Back</span></h2>
      <p class="form-sub">Sign in to your account to continue</p>

      <?php if ($loginError):   ?><div class="alert alert-error"><?=   $loginError   ?></div><?php endif; ?>
      <?php if ($regSuccess):   ?><div class="alert alert-success"><?= $regSuccess   ?></div><?php endif; ?>
      <?php if ($loginSuccess): ?><div class="alert alert-success"><?= $loginSuccess ?></div><?php endif; ?>

      <form method="POST" action="<?= $loginAction ?>">
        <input type="hidden" name="action" value="login">

        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" placeholder="your@email.com" required
                 value="<?= isset($_POST['email']) && $_POST['action']==='login' ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <div class="form-group">
          <label>Password *</label>
          <div class="input-wrap">
            <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
            <button type="button" class="toggle-password" data-target="#loginPassword">👁️</button>
          </div>
        </div>

        <button type="submit" class="btn-form">Sign In →</button>

        <p class="form-switch">
          Don't have an account?
          <a href="signin.php?tab=register" class="gold-text">Register here</a>
        </p>
      </form>
    </div>

    <!-- ===== REGISTER TAB ===== -->
    <div class="tab-content <?= $activeTab === 'register' ? 'active' : '' ?>" id="register">
      <h2 class="form-title">Create <span class="gold-text">Account</span></h2>
      <p class="form-sub">Join MyCar and start driving today</p>

      <?php if ($regError): ?><div class="alert alert-error"><?= $regError ?></div><?php endif; ?>

      <form method="POST" action="signin.php?tab=register">
        <input type="hidden" name="action" value="register">

        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="full_name" placeholder="Your full name" required
                 value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
        </div>

        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" placeholder="your@email.com" required
                 value="<?= isset($_POST['email']) && $_POST['action']==='register' ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="+1 (555) 000-0000"
                 value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
        </div>

        <div class="form-group">
          <label>Password * <span class="form-hint">(min. 6 characters)</span></label>
          <div class="input-wrap">
            <input type="password" name="password" id="regPassword" placeholder="Create a password" required>
            <button type="button" class="toggle-password" data-target="#regPassword">👁️</button>
          </div>
        </div>

        <div class="form-group">
          <label>Confirm Password *</label>
          <div class="input-wrap">
            <input type="password" name="password2" id="regPassword2" placeholder="Repeat your password" required>
            <button type="button" class="toggle-password" data-target="#regPassword2">👁️</button>
          </div>
        </div>

        <button type="submit" class="btn-form">Create Account →</button>

        <p class="form-switch">
          Already have an account?
          <a href="signin.php" class="gold-text">Sign in here</a>
        </p>
      </form>
    </div>

  </div>
</section>

<?php include 'footer.php'; ?>
