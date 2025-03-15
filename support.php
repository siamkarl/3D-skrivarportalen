<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['printer'], $_POST['issue'])) {
    $printer = $_POST['printer'];
    $issue = $_POST['issue'];
    $user = $_SESSION['username'];

    $stmt = $conn->prepare("INSERT INTO support_requests (user, printer, issue, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("sss", $user, $printer, $issue);
    $stmt->execute();
    $stmt->close();
}

$printers = ["Prusa Mk4S", "Creality CR-10", "Creality Ender-5 Plus", "Wanhao Duplicator"];

$supportRequests = [];
$stmt = $conn->prepare("SELECT * FROM support_requests WHERE user = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $supportRequests[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportera fel</title>
    <style>
        body {
            background-color: #1e1e2e;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .support-form {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            width: 300px;
            margin-bottom: 20px;
        }
        input, select, textarea, button {
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
        .support-requests {
            width: 80%;
            margin-top: 20px;
        }
        .support-request {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .footer {
            width: 100%;
            text-align: left;
            padding: 10px;
            font-size: 14px;
            color: #888;
            background-color: #1e1e2e;
            position: fixed;
            bottom: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="support-form">
        <h1>Rapportera fel</h1>
        <form method="POST">
            <select name="printer" required>
                <option value="">Välj 3D-skrivare</option>
                <?php foreach ($printers as $printer): ?>
                    <option value="<?= htmlspecialchars($printer) ?>"><?= htmlspecialchars($printer) ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="issue" placeholder="Beskriv problemet" required></textarea>
            <button type="submit">Skicka</button>
        </form>
    </div>
    <div class="support-requests">
        <h1>Öppna ärenden</h1>
        <?php if (empty($supportRequests)): ?>
            <p>Inga rapporterade fel ännu.</p>
        <?php else: ?>
            <?php foreach ($supportRequests as $request): ?>
                <?php if ($request['status'] !== 'Resolved'): ?>
                    <div class="support-request">
                        <h3><?= htmlspecialchars($request['printer']) ?></h3>
                        <p>Problem: <?= htmlspecialchars($request['issue']) ?></p>
                        <p>Status: <?= htmlspecialchars($request['status']) ?></p>
                        <p>Återkoppling: <?= htmlspecialchars($request['response']) ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="support-requests">
        <h1>Stängda ärenden</h1>
        <?php if (empty($supportRequests)): ?>
            <p>Inga stängda ärenden ännu.</p>
        <?php else: ?>
            <?php foreach ($supportRequests as $request): ?>
                <?php if ($request['status'] === 'Resolved'): ?>
                    <div class="support-request">
                        <h3><?= htmlspecialchars($request['printer']) ?></h3>
                        <p>Problem: <?= htmlspecialchars($request['issue']) ?></p>
                        <p>Status: <?= htmlspecialchars($request['status']) ?></p>
                        <p>Återkoppling: <?= htmlspecialchars($request['response']) ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
