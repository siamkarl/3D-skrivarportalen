<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db_config.php';

// Set session timeout duration (30 minutes)
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Hantera bokningsavbokning
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];

    // Kolla om användaren har rätt att avboka bokningen
    if ($_SESSION['role'] === 'admin' || $_SESSION['username'] === getUsernameByBookingId($booking_id, $conn)) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        header('Location: view_bookings.php'); // Ompeka för att undvika att formuläret skickas igen
        exit;
    }
}

// Ta bort förfallna bokningar
deleteExpiredBookings($conn);

// Funktion för att ta bort förfallna bokningar
function deleteExpiredBookings($conn) {
    $currentDateTime = date('Y-m-d H:i:s'); // Aktuell tid och datum

    // Ta bort bokningar där bokningens tid är före den aktuella tiden
    $stmt = $conn->prepare("DELETE FROM bookings WHERE CONCAT(date, ' ', time) < ?");
    $stmt->bind_param("s", $currentDateTime);
    $stmt->execute();
}

// Hämta bokningar från databasen
$bookings = [];
if ($_SESSION['role'] === 'admin') {
    $result = $conn->query("SELECT * FROM bookings");
} else {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Beräkna sluttid genom att lägga till antalet timmar på starttiden
        $startTime = strtotime($row['date'] . ' ' . $row['time']);
        $endTime = strtotime("+{$row['hours']} hours", $startTime);
        $row['end_time'] = date('H:i', $endTime);

        $bookings[] = $row;
    }
}

// Funktion för att hämta användarnamnet baserat på bokningens ID
function getUsernameByBookingId($booking_id, $conn) {
    $stmt = $conn->prepare("SELECT user FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['user'];
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa bokningar</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .bookings {
            margin: 20px auto;
            width: 80%;
        }
        .booking {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .back-link {
            background-color: #4caf50;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .back-link:hover {
            background-color: #45a049;
        }
        .cancel-btn {
            background-color: #f44336;
            color: #ffffff;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .cancel-btn:hover {
            background-color: #e53935;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: left;
            padding: 10px;
            font-size: 14px;
            color: #888;
            background-color: #121212;
        }
    </style>
</head>
<body>
    <h1>Bokningar</h1>
    <a href="index.php" class="back-link">Tillbaka</a>
    <div class="bookings">
        <?php if (empty($bookings)): ?>
            <p>Inga bokningar ännu.</p>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking">
                    <h3><?= htmlspecialchars($booking['printer']) ?></h3>
                    <p>Användare: <?= htmlspecialchars($booking['user']) ?></p>
                    <p>Datum: <?= htmlspecialchars($booking['date']) ?></p>
                    <p>Tid: <?= htmlspecialchars(date('H:i', strtotime($booking['time']))) ?> - Slut: <?= htmlspecialchars($booking['end_time']) ?></p>
                    <a href="?cancel=<?= $booking['id'] ?>" class="cancel-btn">Avboka</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
