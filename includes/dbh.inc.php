<?php
// Database configuration
$servername = "localhost";
$dBUsername = "root";
$dBPassword = "";  // Default XAMPP password is empty
$dBName = "loginsys";

// Create MySQLi connection
$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to prevent encoding issues
mysqli_set_charset($conn, "utf8mb4");