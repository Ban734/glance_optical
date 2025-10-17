<?php
function connectDB() {
    $conn = new mysqli("localhost", "root", "", "glance_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set timezone to Philippines
    date_default_timezone_set('Asia/Manila');
    $conn->query("SET time_zone = '+08:00'");

    return $conn;
}
?>
