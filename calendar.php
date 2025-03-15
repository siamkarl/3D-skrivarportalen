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

// Om formuläret skickas (bokning görs)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $printer = $_POST['printer'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $hours = $_POST['hours'];
    $user = $_SESSION['username'];

    // Kontrollera om skrivaren är otillgänglig
    $stmt = $conn->prepare("SELECT * FROM printers WHERE name = ? AND available = 0");
    $stmt->bind_param("s", $printer);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>alert('Denna skrivare är för närvarande otillgänglig.'); window.location.href='dashboard.php';</script>";
        exit;
    }
    $stmt->close();

    // Kontrollera om skrivaren är under underhåll
    $stmt = $conn->prepare("SELECT * FROM maintenance WHERE printer = ? AND ? BETWEEN start_date AND end_date");
    $stmt->bind_param("ss", $printer, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>alert('Denna skrivare är under underhåll under den valda perioden.'); window.location.href='dashboard.php';</script>";
        exit;
    }
    $stmt->close();

    // Kontrollera om tiden redan är bokad
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE printer = ? AND date = ?");
    $stmt->bind_param("ss", $printer, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $isBooked = false;
    while ($row = $result->fetch_assoc()) {
        $startTime = strtotime($row['time']);
        $endTime = strtotime("+{$row['hours']} hours", $startTime);
        $newStartTime = strtotime($time);
        $newEndTime = strtotime("+{$hours} hours", $newStartTime);

        if (($newStartTime < $endTime) && ($newEndTime > $startTime)) {
            $isBooked = true;
            break;
        }
    }

    if (!$isBooked) {
        // Lägg till bokningen i databasen
        $stmt = $conn->prepare("INSERT INTO bookings (user, printer, date, time, hours) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $user, $printer, $date, $time, $hours);
        $stmt->execute();
        echo "<script>alert('Bokning bekräftad!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Denna tid är redan bokad!'); window.location.href='dashboard.php';</script>";
    }
    $stmt->close();
}

// Hämta valda parametrar
$currentWeek = date("W"); // Aktuell vecka
$selectedWeek = isset($_GET['week']) ? $_GET['week'] : $currentWeek;
$selectedDay = isset($_GET['day']) ? $_GET['day'] : date("Y-m-d");
$selectedPrinter = isset($_GET['printer']) ? $_GET['printer'] : 'Prusa Mk4S';

// Lista över 3D-skrivare
$printers = ["Prusa Mk4S", "Creality CR-10", "Creality Ender-5 Plus", "Wanhao Duplicator"];

// Hämta bokningar
$bookings = [];
$stmt = $conn->prepare("SELECT * FROM bookings WHERE printer = ? AND date = ?");
$stmt->bind_param("ss", $selectedPrinter, $selectedDay);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

function isBooked($printer, $date, $time, $bookings) {
    $newStartTime = strtotime($time);
    foreach ($bookings as $booking) {
        $startTime = strtotime($booking['time']);
        $endTime = strtotime("+{$booking['hours']} hours", $startTime);
        $newEndTime = strtotime("+1 hour", $newStartTime);

        if (($newStartTime < $endTime) && ($newEndTime > $startTime)) {
            return true;
        }
    }
    return false;
}

function isUnderMaintenance($printer, $date, $time, $conn) {
    $stmt = $conn->prepare("SELECT * FROM maintenance WHERE printer = ? AND ? BETWEEN start_date AND end_date");
    $stmt->bind_param("ss", $printer, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $isUnderMaintenance = false;
    while ($row = $result->fetch_assoc()) {
        $startTime = strtotime($row['start_date']);
        $endTime = strtotime($row['end_date'] . ' 23:59:59');
        $newStartTime = strtotime($date . ' ' . $time);
        $newEndTime = strtotime("+1 hour", $newStartTime);

        if (($newStartTime < $endTime) && ($newEndTime > $startTime)) {
            $isUnderMaintenance = true;
            break;
        }
    }
    $stmt->close();
    return $isUnderMaintenance;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boka 3D-skrivare</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #1e1e2e; color: #ffffff; text-align: center; }
        h1 { color: #ffcc00; }
        select, button { padding: 10px; margin: 10px; background: #2e2e3e; color: #fff; border: 1px solid #444; }
        table { width: 60%; margin: auto; border-collapse: collapse; background: #2e2e3e; border-radius: 8px; }
        th, td { padding: 15px; border: 1px solid #444; }
        th { background-color: #444; }
        .booked { background-color: #ff4444; color: #fff; }
        .available { background-color: #44ff44; color: #000; }
        .maintenance { background-color: #ffa500; color: #000; }
        button { background-color: #ffcc00; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #ffaa00; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: left;
            padding: 10px;
            font-size: 14px;
            color: #888;
            background-color: #1e1e2e;
        }
    </style>
    <script>
        function updatePage() {
            const week = document.getElementById('weekSelect').value;
            const day = document.getElementById('daySelect').value;
            const printer = document.getElementById('printerSelect').value;
            window.location.href = `?week=${week}&day=${day}&printer=${printer}`;
        }

        function confirmBooking(printer, date, time) {
            const hours = prompt("Hur många timmar vill du boka? (1-8)", "1");
            if (hours !== null && hours >= 1 && hours <= 8) {
                if (confirm(`Vill du boka ${printer} den ${date} kl ${time} för ${hours} timmar?`)) {
                    document.getElementById('printer').value = printer;
                    document.getElementById('date').value = date;
                    document.getElementById('time').value = time;
                    document.getElementById('hours').value = hours;
                    document.getElementById('bookingForm').submit();
                }
            } else {
                alert("Ogiltigt antal timmar. Vänligen ange ett värde mellan 1 och 8.");
            }
        }
    </script>
</head>
<body>
    <h1>Boka 3D-skrivare</h1>

    <form method="POST" id="bookingForm">
        <input type="hidden" name="printer" id="printer">
        <input type="hidden" name="date" id="date">
        <input type="hidden" name="time" id="time">
        <input type="hidden" name="hours" id="hours">
    </form>

    <label>Välj vecka:</label>
    <select id="weekSelect" onchange="updatePage()">
        <?php 
            $currentYear = date("Y");
            for ($i = $currentWeek; $i <= 52; $i++): 
                $startOfWeek = date("Y-m-d", strtotime("{$currentYear}-W{$i}-1"));
                $selected = ($i == $selectedWeek) ? 'selected' : '';
        ?>
            <option value="<?= $i ?>" <?= $selected ?>>Vecka <?= $i ?> (Start: <?= $startOfWeek ?>)</option>
        <?php endfor; ?>
    </select>

    <label>Välj dag:</label>
    <select id="daySelect" onchange="updatePage()">
        <?php 
            $firstDayOfWeek = date("Y-m-d", strtotime("{$currentYear}-W{$selectedWeek}-1"));
            for ($i = 0; $i < 5; $i++): 
                $day = date("Y-m-d", strtotime("+$i days", strtotime($firstDayOfWeek)));
                $selected = ($day == $selectedDay) ? 'selected' : '';
        ?>
            <option value="<?= $day ?>" <?= $selected ?>><?= $day ?></option>
        <?php endfor; ?>
    </select>

    <label>Välj 3D-skrivare:</label>
    <select id="printerSelect" onchange="updatePage()">
        <?php foreach ($printers as $printer): ?>
            <option value="<?= $printer ?>" <?= $printer == $selectedPrinter ? 'selected' : '' ?>><?= $printer ?></option>
        <?php endforeach; ?>
    </select>

    <table>
        <tr>
            <th>Tid</th>
            <th>Status</th>
            <th>Åtgärd</th>
        </tr>
        <?php for ($hour = 8; $hour < 16; $hour++): ?>
            <tr>
                <td><?= sprintf('%02d:00', $hour) ?></td>
                <?php
                    $time = sprintf('%02d:00', $hour);
                    $isBooked = isBooked($selectedPrinter, $selectedDay, $time, $bookings);
                    $isUnderMaintenance = isUnderMaintenance($selectedPrinter, $selectedDay, $time, $conn);
                ?>
                <td class="<?= $isBooked ? 'booked' : ($isUnderMaintenance ? 'maintenance' : 'available') ?>">
                    <?= $isBooked ? 'Bokad' : ($isUnderMaintenance ? 'Underhållsarbete pågår' : 'Tillgänglig') ?>
                </td>
                <td>
                    <?php if (!$isBooked && !$isUnderMaintenance): ?>
                        <button onclick="confirmBooking('<?= $selectedPrinter ?>', '<?= $selectedDay ?>', '<?= $time ?>')">Boka</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endfor; ?>
    </table>
    <div class="footer">
    Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
