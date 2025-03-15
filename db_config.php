<?php
$servername = "localhost";
$username = "admin_skola";
$password = "umYYpC5apy4jbnVjJQJj";
$dbname = "admin_skola";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
