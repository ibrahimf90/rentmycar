<?php
// ============================================
// Project : Rent a Car
// File    : logout.php
// Purpose : Destroy session and redirect
// ============================================

require_once 'config.php';

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to home
redirect('index.php');
?>
