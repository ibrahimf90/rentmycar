<?php
// ============================================
// Project : Rent a Car
// File    : config.php
// Purpose : Database connection + App settings
// ============================================

// ---------- Database Settings ----------
define('DB_HOST',     'localhost');
define('DB_USER',     'root');       // change to your MySQL username
define('DB_PASS',     '');           // change to your MySQL password
define('DB_NAME',     'mycar');
define('DB_CHARSET',  'utf8mb4');

// ---------- App Settings ----------
define('APP_NAME',    'MyCar');
define('APP_URL',     'http://localhost/rentcar');  // change to your project URL
define('APP_VERSION', '1.0.0');

// ---------- Connect ----------
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="
        font-family: sans-serif;
        background: #0a0f1e;
        color: #e0b84b;
        text-align: center;
        padding: 80px;
        font-size: 20px;
    ">
        ⚠️ Database connection failed: ' . mysqli_connect_error() . '
    </div>');
}

// Set charset
mysqli_set_charset($conn, DB_CHARSET);

// ---------- Session Start ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Helper Functions ----------

/**
 * Sanitize user input
 */
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged-in user id
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Calculate duration discount percentage
 * 10+ days → 10%
 * 20+ days → 20%
 * 40+ days → 30%
 */
function getDurationDiscount($days) {
    if ($days >= 40) return 30;
    if ($days >= 20) return 20;
    if ($days >= 10) return 10;
    return 0;
}

/**
 * Generate a unique coupon code
 */
function generateCouponCode($userId, $discountPct) {
    return 'MYCAR-' . strtoupper(substr(md5($userId . $discountPct . time()), 0, 8));
}

/**
 * Check and generate loyalty coupon after a completed rent
 * 3 rents → 10% | 5 rents → 20% | 10 rents → 30%
 */
function checkAndGenerateCoupon($conn, $userId) {
    // Get total completed rents
    $result = mysqli_query($conn, "SELECT total_rents FROM users WHERE id = $userId");
    $user   = mysqli_fetch_assoc($result);
    $total  = (int) $user['total_rents'];

    $milestones = [3 => 10, 5 => 20, 10 => 30];

    if (isset($milestones[$total])) {
        $discountPct = $milestones[$total];

        // Check if coupon for this milestone already exists
        $check = mysqli_query($conn,
            "SELECT id FROM coupons
             WHERE user_id = $userId AND discount_pct = $discountPct
             LIMIT 1"
        );

        if (mysqli_num_rows($check) === 0) {
            $code = generateCouponCode($userId, $discountPct);
            mysqli_query($conn,
                "INSERT INTO coupons (user_id, code, discount_pct)
                 VALUES ($userId, '$code', $discountPct)"
            );
        }
    }
}

/**
 * Calculate final price
 */
function calculateFinalPrice($pricePerDay, $totalDays, $durationDiscount, $couponDiscount) {
    $base       = $pricePerDay * $totalDays;
    $afterDur   = $base   - ($base   * $durationDiscount / 100);
    $afterCoup  = $afterDur - ($afterDur * $couponDiscount / 100);
    return round($afterCoup, 2);
}
?>