<?php
// ============================================
// Project : Rent a Car
// File    : header.php
// Purpose : Main header included in index.php
// ============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= isset($pageTitle) ? $pageTitle : 'MyCar — Premium Car Rental' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>

<!-- ========== HEADER ========== -->
<header class="header" id="header">
  <div class="header-inner">

    <!-- Logo -->
    <a href="index.php" class="logo">
      <span class="logo-icon">⬡</span>
      <span class="logo-text">My<span class="gold">Car</span></span>
    </a>

    <!-- Nav -->
    <nav class="nav" id="nav">
      <a href="index.php?page=home"     class="nav-link <?= (!isset($activePage) || $activePage == 'home')     ? 'active' : '' ?>">Home</a>
      <a href="index.php?page=about"    class="nav-link <?= (isset($activePage) && $activePage == 'about')    ? 'active' : '' ?>">About</a>
      <a href="index.php?page=services" class="nav-link <?= (isset($activePage) && $activePage == 'services') ? 'active' : '' ?>">Services</a>
      <a href="index.php?page=contact"  class="nav-link <?= (isset($activePage) && $activePage == 'contact')  ? 'active' : '' ?>">Contact</a>
      <a href="rent.php"                class="nav-link nav-rent">Rent a Car</a>
    </nav>

    <!-- Auth Area -->
    <div class="auth-area">
      <?php if (isLoggedIn()): ?>
        <div class="user-menu">
          <button class="user-btn" id="userBtn">
            <span class="user-avatar">👤</span>
            <span class="user-name">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <span class="chevron">▾</span>
          </button>
          <div class="dropdown" id="dropdown">
            <a href="edit_profile.php" class="drop-item">✏️ Edit Profile</a>
            <a href="old_rent.php"     class="drop-item">🚗 My Rentals</a>
            <a href="logout.php"       class="drop-item drop-logout">🚪 Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="signin.php" class="btn-signin">Sign In</a>
      <?php endif; ?>
    </div>

    <!-- Hamburger -->
    <button class="hamburger" id="hamburger">
      <span></span><span></span><span></span>
    </button>

  </div>
</header>
