<?php
session_start();
require 'db_config.php';
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set session timeout duration (30 minutes)
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['printer'], $_POST['start_date'], $_POST['end_date'])) {
        $printer = $_POST['printer'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Check for existing bookings during the maintenance period
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE printer = ? AND date BETWEEN ? AND ?");
        $stmt->bind_param("sss", $printer, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Send email notification to the user
            $userEmail = getUserEmail($row['user'], $conn);
            sendMaintenanceNotification($userEmail, $row['user'], $printer, $start_date, $end_date);
        }

        $stmt->close();

        // Insert maintenance period into the database
        $stmt = $conn->prepare("INSERT INTO maintenance (printer, start_date, end_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $printer, $start_date, $end_date);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['id'], $_POST['action'])) {
        $id = $_POST['id'];
        if ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM maintenance WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'update' && isset($_POST['new_start_date'], $_POST['new_end_date'])) {
            $new_start_date = $_POST['new_start_date'];
            $new_end_date = $_POST['new_end_date'];
            $stmt = $conn->prepare("UPDATE maintenance SET start_date = ?, end_date = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_start_date, $new_end_date, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function getUserEmail($username, $conn) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
    return $email;
}

function sendMaintenanceNotification($email, $username, $printer, $start_date, $end_date) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'mail.ntiare.se'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'sender@ntiare.se'; // Replace with your SMTP username
        $mail->Password = '6dBdsfMUAC6qXZGKm9Cv'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email content
        $mail->setFrom('sender@ntiare.se', '3D-skrivarportalen');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Underhållsarbete på 3D-skrivare';
        $mail->Body = "Hej $username,<br><br>Vi vill informera dig om att 3D-skrivaren $printer kommer att vara under underhåll från $start_date till $end_date. Din bokning kan påverkas.<br><br>Vänliga hälsningar,<br>3D-skrivarportalen";

        $mail->send();
    } catch (Exception $e) {
        echo "E-post kunde inte skickas. Felmeddelande: {$mail->ErrorInfo}";
    }
}

$printers = ["Prusa Mk4S", "Creality CR-10", "Creality Ender-5 Plus", "Wanhao Duplicator"];
$maintenancePeriods = [];
$result = $conn->query("SELECT * FROM maintenance");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $maintenancePeriods[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera underhåll</title>
    <style>
        body {
            background-color: #1e1e2e;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }
        .maintenance-form {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            width: 300px;
            margin-bottom: 20px;
        }
        .maintenance-list {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            width: 80%; /* Increased width */
            margin-bottom: 20px;
        }
        input, select, button {
            background-color: #333;
            color: #ffffff;
            border: 1px solid #555;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            width: calc(100% - 22px);
            font-size: 16px;
        }
        button {
            cursor: pointer;
            background-color: #4caf50;
            border: none;
        }
        button:hover {
            background-color: #45a049;
        }
        .delete-button {
            background-color: #f44336;
            margin-left: 10px;
        }
        .delete-button:hover {
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
            background-color: #1e1e2e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #555;
        }
        th {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="maintenance-form">
        <h1>Hantera underhåll</h1>
        <form method="POST">
            <select name="printer" required>
                <option value="">Välj 3D-skrivare</option>
                <?php foreach ($printers as $printer): ?>
                    <option value="<?= htmlspecialchars($printer) ?>"><?= htmlspecialchars($printer) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="end_date"></label>
            <label for="start_date">Från:</label>
            <input type="date" name="start_date" id="start_date" required>
            <label for="end_date"></label>
            <label for="end_date">Till:</label>
            <input type="date" name="end_date" id="end_date" required>
            <button type="submit">Lägg till underhåll</button>
        </form>
    </div>
    <div class="maintenance-list">
        <h1>Aktuella underhåll</h1>
        <?php if (empty($maintenancePeriods)): ?>
            <p>Inga underhållsperioder ännu.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>3D-skrivare</th>
                    <th>Startdatum</th>
                    <th>Slutdatum</th>
                    <th>Åtgärder</th>
                </tr>
                <?php foreach ($maintenancePeriods as $maintenance): ?>
                    <tr>
                        <td><?= htmlspecialchars($maintenance['printer']) ?></td>
                        <td><?= htmlspecialchars($maintenance['start_date']) ?></td>
                        <td><?= htmlspecialchars($maintenance['end_date']) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $maintenance['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="delete-button">Ta bort</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
