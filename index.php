<?php
// ============================================
// Project : Rent a Car
// File    : index.php
// Purpose : Master file - includes all pages
// ============================================
require_once 'config.php';

// Get current page - only allow letters a-z
$page = isset($_GET['page']) ? preg_replace('/[^a-z]/', '', strtolower($_GET['page'])) : 'home';

// Allowed pages only
$allowed = ['home', 'about', 'contact', 'services'];
if (!in_array($page, $allowed)) {
    $page = 'home';
}

// Set page title
$titles = [
    'home'     => 'MyCar — Premium Car Rental',
    'about'    => 'About Us — MyCar',
    'contact'  => 'Contact Us — MyCar',
    'services' => 'Our Services — MyCar',
];
$pageTitle  = $titles[$page];
$activePage = $page;

// Include header
include 'header.php';

// Include the correct page content
include $page . '.php';

// Include footer
include 'footer.php';
?>