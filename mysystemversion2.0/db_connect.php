<?php
$host = "localhost";
$user = "root";
$pass = ""; // Laragon default
$dbname = "mytaskmanager";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
