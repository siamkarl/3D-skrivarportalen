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

$sql = "CREATE TABLE IF NOT EXISTS maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    printer VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table maintenance created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
