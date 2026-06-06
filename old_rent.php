<?php
// ============================================
// Project : Rent a Car
// File    : old_rent.php
// Purpose : User rental history + coupon codes
// ============================================
require_once 'config.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('signin.php');
}

$userId = currentUserId();

// Fetch all rentals for this user
$rentals = mysqli_query($conn,
    "SELECT r.*, c.brand, c.model, c.photo, c.year
     FROM rentals r
     JOIN cars c ON r.car_id = c.id
     WHERE r.user_id = $userId
     ORDER BY r.created_at DESC"
);

// Fetch all coupons for this user
$coupons = mysqli_query($conn,
    "SELECT * FROM coupons WHERE user_id = $userId ORDER BY created_at DESC"
);

// Count stats
$totalRentals    = mysqli_num_rows($rentals);
$completedResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM rentals WHERE user_id=$userId AND status='completed'");
$completedCount  = mysqli_fetch_assoc($completedResult)['cnt'];

$userResult = mysqli_query($conn, "SELECT total_rents FROM users WHERE id=$userId");
$totalRents = mysqli_fetch_assoc($userResult)['total_rents'];

// Next milestone
$nextMilestone = 0;
$nextDiscount  = 0;
if ($totalRents < 3)      { $nextMilestone = 3;  $nextDiscount = 10; }
elseif ($totalRents < 5)  { $nextMilestone = 5;  $nextDiscount = 20; }
elseif ($totalRents < 10) { $nextMilestone = 10; $nextDiscount = 30; }

$pageTitle  = 'My Rentals — MyCar';
$activePage = '';
include 'header.php';
?>

<!-- ========== PAGE HERO ========== -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <p class="section-tag">My Account</p>
      <h1>My <span class="gold-text">Rentals</span></h1>
      <p>View your rental history and loyalty coupon codes.</p>
    </div>
  </div>
</section>

<!-- ========== MAIN SECTION ========== -->
<section class="section">
  <div class="container">
    <div class="profile-grid">

      <!-- ===== SIDEBAR ===== -->
      <div class="profile-sidebar">
        <div class="profile-avatar-box">
          <div class="profile-avatar">👤</div>
          <h3><?= htmlspecialchars($_SESSION['user_name']) ?></h3>
          <p><?= htmlspecialchars($_SESSION['user_email']) ?></p>
          <span class="profile-badge">🚗 <?= $totalRents ?> Rental<?= $totalRents != 1 ? 's' : '' ?></span>
        </div>
        <div class="profile-nav">
          <a href="edit_profile.php" class="profile-nav-item">✏️ Edit Profile</a>
          <a href="old_rent.php"     class="profile-nav-item active">🚗 My Rentals</a>
          <a href="logout.php"       class="profile-nav-item danger">🚪 Logout</a>
        </div>
      </div>

      <!-- ===== MAIN CONTENT ===== -->
      <div class="profile-content">

        <!-- Stats -->
        <div class="rent-stats">
          <div class="rent-stat-card">
            <span class="rent-stat-num"><?= $totalRentals ?></span>
            <span class="rent-stat-label">Total Rentals</span>
          </div>
          <div class="rent-stat-card">
            <span class="rent-stat-num"><?= $completedCount ?></span>
            <span class="rent-stat-label">Completed</span>
          </div>
          <div class="rent-stat-card">
            <span class="rent-stat-num"><?= mysqli_num_rows($coupons) ?></span>
            <span class="rent-stat-label">Coupons Earned</span>
          </div>
        </div>

        <!-- Loyalty Progress -->
        <?php if ($nextMilestone > 0): ?>
        <div class="loyalty-box">
          <div class="loyalty-info">
            <span class="loyalty-title">🎁 Loyalty Progress</span>
            <span class="loyalty-sub">
              <?= $nextMilestone - $totalRents ?> more rental<?= ($nextMilestone - $totalRents) != 1 ? 's' : '' ?>
              to earn a <strong class="gold-text"><?= $nextDiscount ?>% coupon!</strong>
            </span>
          </div>
          <div class="loyalty-bar-wrap">
            <div class="loyalty-bar">
              <div class="loyalty-fill" style="width: <?= round(($totalRents / $nextMilestone) * 100) ?>%"></div>
            </div>
            <span class="loyalty-count"><?= $totalRents ?> / <?= $nextMilestone ?></span>
          </div>
        </div>
        <?php else: ?>
        <div class="loyalty-box">
          <span class="loyalty-title">🏆 Maximum loyalty level reached! Enjoy your 30% coupons.</span>
        </div>
        <?php endif; ?>

        <!-- Coupon Codes -->
        <?php
        // Reset pointer
        mysqli_data_seek($coupons, 0);
        $couponCount = mysqli_num_rows($coupons);
        ?>
        <?php if ($couponCount > 0): ?>
        <div class="profile-card" style="margin-top: 28px;">
          <h2 class="profile-card-title">My <span class="gold-text">Coupons</span></h2>
          <div class="coupons-grid">
            <?php while ($coupon = mysqli_fetch_assoc($coupons)): ?>
            <div class="coupon-card <?= $coupon['is_used'] ? 'used' : '' ?>">
              <div class="coupon-left">
                <span class="coupon-pct"><?= $coupon['discount_pct'] ?>%</span>
                <span class="coupon-off">OFF</span>
              </div>
              <div class="coupon-right">
                <span class="coupon-code"><?= htmlspecialchars($coupon['code']) ?></span>
                <span class="coupon-status"><?= $coupon['is_used'] ? '✅ Used' : '🟢 Available' ?></span>
                <span class="coupon-date">Earned: <?= date('d M Y', strtotime($coupon['created_at'])) ?></span>
              </div>
              <?php if (!$coupon['is_used']): ?>
              <button class="copy-btn" onclick="copyCode('<?= htmlspecialchars($coupon['code']) ?>', this)">Copy</button>
              <?php endif; ?>
            </div>
            <?php endwhile; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Rental History -->
        <div class="profile-card" style="margin-top: 28px;">
          <h2 class="profile-card-title">Rental <span class="gold-text">History</span></h2>

          <?php if ($totalRentals > 0):
            mysqli_data_seek($rentals, 0);
            while ($rent = mysqli_fetch_assoc($rentals)):
              $statusClass = [
                'pending'   => 'status-pending',
                'active'    => 'status-active',
                'completed' => 'status-completed',
                'cancelled' => 'status-cancelled',
              ][$rent['status']] ?? '';
          ?>
          <div class="rent-history-card">
            <div class="rent-car-img">
              <img src="images/<?= $rent['car_id'] ?>.jpg"
                   alt="<?= htmlspecialchars($rent['brand'] . ' ' . $rent['model']) ?>"
                   onerror="this.src='images/default.jpg'">
            </div>
            <div class="rent-car-info">
              <h3><?= htmlspecialchars($rent['brand'] . ' ' . $rent['model']) ?></h3>
              <p class="rent-dates">
                📅 <?= date('d M Y', strtotime($rent['start_date'])) ?>
                → <?= date('d M Y', strtotime($rent['end_date'])) ?>
                (<?= $rent['total_days'] ?> days)
              </p>
              <?php if ($rent['delivery_address']): ?>
              <p class="rent-address">📍 <?= htmlspecialchars($rent['delivery_address']) ?></p>
              <?php endif; ?>
              <div class="rent-discounts">
                <?php if ($rent['duration_discount'] > 0): ?>
                  <span class="disc-tag">⏱ <?= $rent['duration_discount'] ?>% Duration Discount</span>
                <?php endif; ?>
                <?php if ($rent['coupon_discount'] > 0): ?>
                  <span class="disc-tag">🎁 <?= $rent['coupon_discount'] ?>% Coupon (<?= htmlspecialchars($rent['coupon_code']) ?>)</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="rent-car-price">
              <span class="rent-total">$<?= number_format($rent['total_price'], 2) ?></span>
              <span class="rent-per-day">$<?= number_format($rent['price_per_day'], 2) ?>/day</span>
              <span class="rent-status <?= $statusClass ?>"><?= ucfirst($rent['status']) ?></span>
              
              <div class="invoice-links" style="margin-top: 10px; display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                <a href="invoice.php?id=<?= $rent['id'] ?>" class="btn-invoice view" target="_blank">📄 View Invoice</a>
                
                <?php
                $createdTime = strtotime($rent['created_at']);
                $timeDiff = time() - $createdTime;
                $canDownload = ($timeDiff >= 86400);
                if ($canDownload):
                ?>
                  <a href="invoice.php?id=<?= $rent['id'] ?>&download=1" class="btn-invoice download">📥 Download</a>
                <?php else:
                  $timeLeft = 86400 - $timeDiff;
                  $hoursLeft = ceil($timeLeft / 3600);
                ?>
                  <span style="font-size:0.68rem; color:var(--muted); text-align:right;" title="Available to download 24 hours after booking">⏳ Download in <?= $hoursLeft ?>h</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
          <?php else: ?>
          <div class="no-rentals">
            <p>🚗 You haven't made any rentals yet.</p>
            <a href="rent.php" class="btn-primary" style="margin-top:16px; display:inline-block;">Rent a Car Now</a>
          </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
function copyCode(code, btn) {
  navigator.clipboard.writeText(code).then(() => {
    btn.textContent = 'Copied!';
    btn.style.background = 'var(--gold)';
    btn.style.color = 'var(--navy)';
    setTimeout(() => {
      btn.textContent = 'Copy';
      btn.style.background = '';
      btn.style.color = '';
    }, 2000);
  });
}
</script>