<?php
// ============================================
// Project : Rent a Car
// File    : services.php
// Purpose : Services page content - included in index.php
// ============================================

// Fetch all available cars
$carsResult = mysqli_query($conn, "SELECT * FROM cars WHERE is_available = 1 ORDER BY price_per_day ASC");
?>

<!-- ========== PAGE HERO ========== -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <p class="section-tag">What We Offer</p>
      <h1>Our <span class="gold-text">Services</span></h1>
      <p>From economy to luxury — find the perfect car for every journey.</p>
    </div>
  </div>
</section>

<!-- ========== SERVICES CARDS ========== -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <p class="section-tag">What We Provide</p>
      <h2 class="section-title">Everything You <span class="gold-text">Need</span></h2>
    </div>
    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon">🚗</div>
        <h3>Short-Term Rental</h3>
        <p>Need a car for a day or a few days? We've got you covered with flexible short-term options.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">📅</div>
        <h3>Long-Term Rental</h3>
        <p>Rent for 10, 20, or 40+ days and enjoy automatic discounts up to 30% off.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">📍</div>
        <h3>Door Delivery</h3>
        <p>We deliver your chosen car directly to your address — no need to visit any office.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">🛡️</div>
        <h3>Full Insurance</h3>
        <p>All rentals include comprehensive insurance so you can drive with complete peace of mind.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">💎</div>
        <h3>Luxury Fleet</h3>
        <p>Choose from a hand-picked selection of premium and luxury vehicles for special occasions.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">🎁</div>
        <h3>Loyalty Rewards</h3>
        <p>Complete 3, 5, or 10 rentals and earn exclusive coupon codes for extra savings.</p>
      </div>
    </div>
  </div>
</section>

<!-- ========== ALL CARS ========== -->
<section class="section cars-section">
  <div class="container">
    <div class="section-head">
      <p class="section-tag">Browse Our Fleet</p>
      <h2 class="section-title">All Available <span class="gold-text">Cars</span></h2>
      <p class="section-sub">Every car is maintained, insured, and ready for your next trip.</p>
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