<?php
// ============================================
// Project : Rent a Car
// File    : get_available_cars.php
// Purpose : AJAX endpoint to fetch available cars for selected dates
// ============================================

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to search for cars.'
    ]);
    exit();
}

$startDate = isset($_GET['start_date']) ? clean($conn, $_GET['start_date']) : '';
$endDate   = isset($_GET['end_date']) ? clean($conn, $_GET['end_date']) : '';

if (empty($startDate) || empty($endDate)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please select both start and end dates.'
    ]);
    exit();
}

// Convert string dates to check validity
$start = new DateTime($startDate);
$end   = new DateTime($endDate);
$today = new DateTime('today');

if ($start < $today) {
    echo json_encode([
        'success' => false,
        'message' => 'Start date cannot be in the past.'
    ]);
    exit();
}

if ($end <= $start) {
    echo json_encode([
        'success' => false,
        'message' => 'End date must be after start date.'
    ]);
    exit();
}

// Query for cars that do not overlap with any existing rentals during the requested period
$query = "SELECT * FROM cars 
          WHERE is_available = 1 
            AND id NOT IN (
              SELECT DISTINCT car_id FROM rentals 
              WHERE status != 'cancelled' 
                AND start_date <= ? 
                AND end_date >= ?
            )
          ORDER BY brand ASC";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $endDate, $startDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $cars = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $cars[] = [
            'id' => (int)$row['id'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'year' => (int)$row['year'],
            'price_per_day' => (float)$row['price_per_day'],
            'seats' => (int)$row['seats'],
            'fuel_type' => $row['fuel_type'],
            'transmission' => $row['transmission'],
            'description' => $row['description'],
            'photo' => $row['photo']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'cars' => $cars
    ]);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database query preparation failed.'
    ]);
}
exit();
?>
