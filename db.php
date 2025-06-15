<?php
$host = "localhost";
$user = "root";         // Your MySQL username
$pass = "";             // Your MySQL password
$db   = "internship";   // Your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
