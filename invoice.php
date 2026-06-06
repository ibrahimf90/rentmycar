<?php
// ============================================
// Project : Rent a Car
// File    : invoice.php
// Purpose : View and download individual rental invoice
// ============================================
require_once 'config.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('signin.php');
}

$userId = currentUserId();
$rentalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($rentalId <= 0) {
    die("Invalid invoice ID.");
}

// Fetch rental details, ensuring it belongs to the logged-in user
$query = "SELECT r.*, c.brand, c.model, c.year, c.fuel_type, c.transmission, u.full_name, u.email, u.phone
          FROM rentals r
          JOIN cars c ON r.car_id = c.id
          JOIN users u ON r.user_id = u.id
          WHERE r.id = $rentalId AND r.user_id = $userId
          LIMIT 1";

$result = mysqli_query($conn, $query);
$rent = mysqli_fetch_assoc($result);

if (!$rent) {
    die("Invoice not found or access denied.");
}

// Check if 24 hours have passed since the booking creation
$createdTime = strtotime($rent['created_at']);
$timeDiff = time() - $createdTime;
$canDownload = ($timeDiff >= 86400);

// If download is requested, set attachment headers
$isDownload = isset($_GET['download']) && $_GET['download'] == 1;
if ($isDownload) {
    if (!$canDownload) {
        die("Download not allowed. You must wait 24 hours from the booking time to download the invoice.");
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="invoice-' . $rent['id'] . '.html"');
}

// Helper to format currency
function formatPrice($val) {
    return '$' . number_format($val, 2);
}

// Generate the self-contained invoice HTML (so it can be printed or downloaded directly)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice #<?= $rent['id'] ?> — <?= htmlspecialchars($rent['brand'] . ' ' . $rent['model']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy-bg: #060d1a;
      --navy-card: #0b1528;
      --navy-3: #111e38;
      --gold: #e0b84b;
      --gold-light: #f0d080;
      --white: #ffffff;
      --off-white: #f3f5f9;
      --muted: #8c9bb4;
      --border: rgba(224, 184, 75, 0.15);
      --radius: 12px;
      --font-body: 'Inter', sans-serif;
      --font-head: 'Outfit', sans-serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background-color: var(--navy-bg);
      color: var(--off-white);
      font-family: var(--font-body);
      line-height: 1.6;
      padding: 40px 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .invoice-container {
      width: 100%;
      max-width: 800px;
    }

    .actions-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .btn-back {
      color: var(--muted);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    .btn-back:hover {
      color: var(--gold);
    }

    .actions-buttons {
      display: flex;
      gap: 12px;
    }

    .btn-action {
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.88rem;
      cursor: pointer;
      border: none;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-print {
      background: var(--navy-3);
      color: var(--white);
      border: 1px solid var(--border);
    }

    .btn-print:hover {
      background: rgba(224, 184, 75, 0.05);
      border-color: var(--gold);
    }

    .btn-download-action {
      background: var(--gold);
      color: var(--navy-bg);
    }

    .btn-download-action:hover {
      background: var(--gold-light);
      transform: translateY(-1px);
    }

    .btn-download-disabled {
      background: var(--navy-3);
      color: var(--muted);
      border: 1px solid rgba(255, 255, 255, 0.05);
      cursor: not-allowed;
      opacity: 0.7;
    }

    /* Invoice Card design */
    .invoice-card {
      background: var(--navy-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 48px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
      position: relative;
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 40px;
      padding-bottom: 30px;
      border-bottom: 1px solid rgba(224, 184, 75, 0.1);
    }

    .logo-brand {
      font-family: var(--font-head);
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--white);
    }

    .logo-brand span {
      color: var(--gold);
    }

    .invoice-meta {
      text-align: right;
    }

    .invoice-meta h1 {
      font-family: var(--font-head);
      font-size: 1.8rem;
      color: var(--gold);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 6px;
    }

    .invoice-meta p {
      font-size: 0.9rem;
      color: var(--muted);
    }

    .invoice-details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-bottom: 40px;
    }

    .details-block h3 {
      font-family: var(--font-head);
      font-size: 1rem;
      color: var(--gold);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
      border-bottom: 1px solid rgba(224, 184, 75, 0.1);
      padding-bottom: 6px;
    }

    .details-block p {
      font-size: 0.92rem;
      margin-bottom: 6px;
    }

    .details-block strong {
      color: var(--white);
    }

    /* Items Table */
    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 40px;
    }

    .invoice-table th {
      font-family: var(--font-head);
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
      color: var(--gold);
      text-align: left;
      padding: 12px 16px;
      border-bottom: 2px solid rgba(224, 184, 75, 0.15);
    }

    .invoice-table td {
      padding: 16px;
      font-size: 0.92rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      vertical-align: top;
    }

    .invoice-table tr:last-child td {
      border-bottom: none;
    }

    .item-desc strong {
      color: var(--white);
      display: block;
      font-size: 1rem;
      margin-bottom: 4px;
    }

    .item-desc span {
      color: var(--muted);
      font-size: 0.8rem;
    }

    /* Invoice Breakdown */
    .invoice-summary {
      display: flex;
      justify-content: flex-end;
      margin-top: 20px;
    }

    .summary-box {
      width: 100%;
      max-width: 320px;
      border-top: 1px solid rgba(224, 184, 75, 0.15);
      padding-top: 12px;
    }

    .summary-line {
      display: flex;
      justify-content: space-between;
      padding: 6px 0;
      font-size: 0.92rem;
    }

    .summary-line.discount {
      color: #48c78e;
    }

    .summary-line.total {
      margin-top: 10px;
      padding-top: 12px;
      border-top: 2px solid var(--gold);
      font-size: 1.15rem;
      font-weight: 700;
    }

    .summary-line.total .val {
      color: var(--gold);
      font-family: var(--font-head);
      font-size: 1.3rem;
    }

    .invoice-footer {
      margin-top: 60px;
      text-align: center;
      border-top: 1px solid rgba(255,255,255,0.05);
      padding-top: 30px;
      color: var(--muted);
      font-size: 0.82rem;
    }

    /* Print Styles */
    @media print {
      body {
        background: #fff !important;
        color: #000 !important;
        padding: 0 !important;
      }
      .actions-bar {
        display: none !important;
      }
      .invoice-card {
        background: #fff !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        color: #000 !important;
      }
      .logo-brand, .logo-brand span, .invoice-meta h1, .details-block h3, .invoice-table th, .summary-line.total .val {
        color: #000 !important;
      }
      .details-block strong, .item-desc strong {
        color: #000 !important;
      }
      .invoice-header, .details-block h3, .invoice-table th, .summary-box, .summary-line.total {
        border-color: #ccc !important;
      }
      .invoice-table td {
        border-bottom-color: #eee !important;
      }
      .summary-line.discount {
        color: #1b8555 !important;
      }
    }
  </style>
</head>
<body>

<div class="invoice-container">
  
  <!-- ACTIONS BAR (Hidden when downloading as file or printing) -->
  <?php if (!$isDownload): ?>
  <div class="actions-bar">
    <a href="old_rent.php" class="btn-back">← Back to My Rentals</a>
    
    <div class="actions-buttons">
      <button onclick="window.print()" class="btn-action btn-print">🖨️ Print Invoice</button>
      
      <?php if ($canDownload): ?>
        <a href="invoice.php?id=<?= $rent['id'] ?>&download=1" class="btn-action btn-download-action">📥 Download Invoice</a>
      <?php else: 
        $timeLeft = 86400 - $timeDiff;
        $hoursLeft = ceil($timeLeft / 3600);
      ?>
        <button class="btn-action btn-download-disabled" title="Available to download 24 hours after booking">
          ⏳ Download in <?= $hoursLeft ?>h
        </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- INVOICE CARD -->
  <div class="invoice-card">
    
    <!-- Invoice Header -->
    <div class="invoice-header">
      <div class="logo-brand">My<span>Car</span></div>
      <div class="invoice-meta">
        <h1>Invoice</h1>
        <p><strong>Invoice #:</strong> MYCAR-INV-<?= str_pad($rent['id'], 6, '0', STR_PAD_LEFT) ?></p>
        <p><strong>Booking Date:</strong> <?= date('d M Y, H:i', strtotime($rent['created_at'])) ?></p>
        <p><strong>Status:</strong> <?= ucfirst($rent['status']) ?></p>
      </div>
    </div>

    <!-- Details Grid -->
    <div class="invoice-details-grid">
      <div class="details-block">
        <h3>Billed To</h3>
        <p><strong><?= htmlspecialchars($rent['full_name']) ?></strong></p>
        <p>Email: <?= htmlspecialchars($rent['email']) ?></p>
        <?php if ($rent['phone']): ?>
          <p>Phone: <?= htmlspecialchars($rent['phone']) ?></p>
        <?php endif; ?>
      </div>
      
      <div class="details-block">
        <h3>Delivery Location</h3>
        <p>Our service is exclusive to Germany.</p>
        <p><strong>Address:</strong></p>
        <p style="white-space: pre-line; color: var(--white); font-weight: 500;"><?= htmlspecialchars($rent['delivery_address']) ?></p>
      </div>
    </div>

    <!-- Items Table -->
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Rental Description</th>
          <th style="text-align: center;">Duration</th>
          <th style="text-align: right;">Rate / Day</th>
          <th style="text-align: right;">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="item-desc">
            <strong><?= htmlspecialchars($rent['brand'] . ' ' . $rent['model']) ?> (<?= $rent['year'] ?>)</strong>
            <span>Transmission: <?= htmlspecialchars($rent['transmission']) ?> | Fuel: <?= htmlspecialchars($rent['fuel_type']) ?></span>
            <div style="margin-top: 8px; font-size: 0.82rem; color: var(--muted);">
              Rental Period: <?= date('d M Y', strtotime($rent['start_date'])) ?> to <?= date('d M Y', strtotime($rent['end_date'])) ?>
            </div>
          </td>
          <td style="text-align: center; font-weight: 600;"><?= $rent['total_days'] ?> Day<?= $rent['total_days'] != 1 ? 's' : '' ?></td>
          <td style="text-align: right;"><?= formatPrice($rent['price_per_day']) ?></td>
          <td style="text-align: right; font-weight: 600;"><?= formatPrice($rent['price_per_day'] * $rent['total_days']) ?></td>
        </tr>
      </tbody>
    </table>

    <!-- Invoice Breakdown -->
    <div class="invoice-summary">
      <div class="summary-box">
        <div class="summary-line">
          <span>Subtotal</span>
          <span><?= formatPrice($rent['price_per_day'] * $rent['total_days']) ?></span>
        </div>
        
        <?php if ($rent['duration_discount'] > 0): 
          $base = $rent['price_per_day'] * $rent['total_days'];
          $durAmount = $base * ($rent['duration_discount'] / 100);
        ?>
          <div class="summary-line discount">
            <span>Duration Discount (<?= (float)$rent['duration_discount'] ?>%)</span>
            <span>-<?= formatPrice($durAmount) ?></span>
          </div>
        <?php endif; ?>

        <?php if ($rent['coupon_discount'] > 0): 
          $base = $rent['price_per_day'] * $rent['total_days'];
          $afterDur = $base - ($base * ($rent['duration_discount'] / 100));
          $coupAmount = $afterDur * ($rent['coupon_discount'] / 100);
        ?>
          <div class="summary-line discount">
            <span>Coupon Discount (<?= (float)$rent['coupon_discount'] ?>% off)</span>
            <span>-<?= formatPrice($coupAmount) ?></span>
          </div>
        <?php endif; ?>

        <div class="summary-line total">
          <span>Total Paid</span>
          <span class="val"><?= formatPrice($rent['total_price']) ?></span>
        </div>
      </div>
    </div>

    <!-- Invoice Footer -->
    <div class="invoice-footer">
      <p>Thank you for choosing MyCar! If you have any questions, please contact support@mycar.de</p>
      <p style="margin-top: 8px; font-size: 0.75rem; color: var(--muted);">MyCar GmbH | Berlin, Germany</p>
    </div>

  </div>

</div>

</body>
</html>
