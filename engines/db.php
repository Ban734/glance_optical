<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "glance_db";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');
$conn->query("SET time_zone = '+08:00'");
?>
