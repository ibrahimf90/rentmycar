<?php
// ============================================
// Project : Rent a Car
// File    : home.php
// Purpose : Home page content - included in index.php
// ============================================

// Fetch featured cars from DB
$carsResult = mysqli_query($conn, "SELECT * FROM cars WHERE is_available = 1 LIMIT 6");
?>

<!-- ========== HERO ========== -->
<section class="hero">
  <div class="hero-bg">
    <div class="hero-overlay"></div>
    <div class="hero-grid"></div>
  </div>
  <div class="hero-content">
    <p class="hero-tag">Premium Car Rental Service</p>
    <h1 class="hero-title">
      Drive Your <br><span class="gold-text">Dream Car</span><br> Today
    </h1>
    <p class="hero-sub">
      Luxury, comfort, and freedom — delivered to your door.<br>
      Book in minutes, drive in style.
    </p>
    <div class="hero-actions">
      <a href="rent.php" class="btn-primary">Reserve Now</a>
      <a href="index.php?page=services" class="btn-outline">Our Fleet</a>
    </div>

    <!-- Quick Stats -->
    <div class="hero-stats">
      <div class="stat">
        <span class="stat-num">200+</span>
        <span class="stat-label">Cars Available</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">15K+</span>
        <span class="stat-label">Happy Clients</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">98%</span>
        <span class="stat-label">Satisfaction</span>
      </div>
    </div>
  </div>

  <!-- Scroll hint -->
  <div class="scroll-hint">
    <span>Scroll</span>
    <div class="scroll-line"></div>
  </div>
</section>

<!-- ========== FEATURED CARS ========== -->
<section class="section cars-section">
  <div class="container">
    <div class="section-head">
      <p class="section-tag">Our Fleet</p>
      <h2 class="section-title">Featured <span class="gold-text">Cars</span></h2>
      <p class="section-sub">Choose from our premium selection of vehicles</p>
    </div>

    <div class="cars-grid">
      <?php if (mysqli_num_rows($carsResult) > 0):
        while ($car = mysqli_fetch_assoc($carsResult)): ?>
        <div class="car-card">
          <div class="car-img-wrap">
            <img
              src="uploads/cars/<?= htmlspecialchars($car['photo']) ?>"
              alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>"
              onerror="this.src='images/<?= $car['id'] ?>.jpg';"
            >
            <span class="car-badge">Available</span>
          </div>
          <div class="car-info">
            <h3 class="car-name"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
            <p class="car-year"><?= $car['year'] ?> · <?= htmlspecialchars($car['transmission']) ?> · <?= htmlspecialchars($car['fuel_type']) ?></p>
            <div class="car-meta">
              <span>🪑 <?= $car['seats'] ?> Seats</span>
              <span>⛽ <?= htmlspecialchars($car['fuel_type']) ?></span>
            </div>
            <div class="car-footer">
              <div class="car-price">
                <span class="price-num">$<?= number_format($car['price_per_day'], 2) ?></span>
                <span class="price-label">/ day</span>
              </div>
              <a href="rent.php?car_id=<?= $car['id'] ?>" class="btn-rent">Rent Now</a>
            </div>
          </div>
        </div>
      <?php endwhile;
      else: ?>
        <p class="no-cars">No cars available at the moment. Please check back soon.</p>
      <?php endif; ?>
    </div>

    <div class="center-btn">
      <a href="rent.php" class="btn-outline">View All Cars →</a>
    </div>
  </div>
</section>

<!-- ========== WHY CHOOSE US ========== -->
<section class="section why-section">
  <div class="container">
    <div class="section-head">
      <p class="section-tag">Why MyCar</p>
      <h2 class="section-title">The <span class="gold-text">Premium</span> Experience</h2>
    </div>
    <div class="why-grid">
      <div class="why-card">
        <div class="why-icon">🚗</div>
        <h3>Premium Fleet</h3>
        <p>200+ luxury and economy cars maintained to the highest standards.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">📍</div>
        <h3>Door Delivery</h3>
        <p>We deliver the car directly to your address — no pickup hassle.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">💰</div>
        <h3>Loyalty Rewards</h3>
        <p>Rent more, save more. Earn coupon codes after 3, 5, and 10 rentals.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">🛡️</div>
        <h3>Full Insurance</h3>
        <p>Every rental comes with comprehensive insurance coverage included.</p>
      </div>
    </div>
  </div>
</section>

<!-- ========== DISCOUNT BANNER ========== -->
<section class="discount-banner">
  <div class="container">
    <div class="discount-inner">
      <div class="discount-text">
        <p class="section-tag">Long-Term Savings</p>
        <h2>Rent Longer, <span class="gold-text">Save More</span></h2>
        <p>The longer you rent, the bigger your discount — automatically applied at checkout.</p>
      </div>
      <div class="discount-cards">
        <div class="disc-card">
          <span class="disc-days">10+ Days</span>
          <span class="disc-pct">10% OFF</span>
        </div>
        <div class="disc-card">
          <span class="disc-days">20+ Days</span>
          <span class="disc-pct">20% OFF</span>
        </div>
        <div class="disc-card highlight">
          <span class="disc-days">40+ Days</span>
          <span class="disc-pct">30% OFF</span>
        </div>
      </div>
    </div>
  </div>
</section>