<?php
// ============================================
// Project : Rent a Car
// File    : check_coupon.php
// Purpose : AJAX endpoint to validate coupon code
// ============================================

// Set header for JSON response
header('Content-Type: application/json; charset=utf-8');

// Include configuration and database connection
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to check or apply a coupon.'
    ]);
    exit();
}

$userId = currentUserId();

// Get and clean coupon code from request parameters
$code = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if JSON payload is sent
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $code = isset($input['code']) ? clean($conn, $input['code']) : '';
    } else {
        $code = isset($_POST['code']) ? clean($conn, $_POST['code']) : '';
    }
} else {
    // Fallback to GET parameters
    $code = isset($_GET['code']) ? clean($conn, $_GET['code']) : '';
}

// Convert to uppercase to match standard coupon codes
$code = strtoupper(trim($code));

if (empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a coupon code.'
    ]);
    exit();
}

// Prepare statement to avoid SQL injection
$stmt = mysqli_prepare($conn, "SELECT discount_pct FROM coupons WHERE code = ? AND user_id = ? AND is_used = 0 LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $code, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $coupon = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'discount_pct' => (float)$coupon['discount_pct'],
            'message' => 'Coupon applied successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or already used coupon code.'
        ]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database query preparation failed.'
    ]);
}
exit();
?>
