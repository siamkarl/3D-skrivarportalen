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

$supportRequests = [];
if ($_SESSION['role'] === 'admin') {
    $result = $conn->query("SELECT * FROM support_requests");
} else {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT * FROM support_requests WHERE user = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supportRequests[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'], $_POST['response'], $_POST['status'])) {
        $id = $_POST['id'];
        $response = $_POST['response'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE support_requests SET response = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $response, $status, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: view_support.php');
        exit;
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        $stmt = $conn->prepare("DELETE FROM support_requests WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        header('Location: view_support.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa supportärenden</title>
    <style>
        body {
            background-color: #1e1e2e;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .support-requests {
            margin: 20px auto;
            width: 80%;
        }
        .support-request {
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
        .delete-button {
            background-color: #f44336;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            color: #ffffff;
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
    </style>
</head>
<body>
    <h1>Supportärenden</h1>
    <a href="dashboard.php" class="back-link">Tillbaka</a>
    <div class="support-requests">
        <?php if (empty($supportRequests)): ?>
            <p>Inga supportärenden ännu.</p>
        <?php else: ?>
            <?php foreach ($supportRequests as $request): ?>
                <div class="support-request">
                    <h3><?= htmlspecialchars($request['printer']) ?></h3>
                    <p>Användare: <?= htmlspecialchars($request['user']) ?></p>
                    <p>Problem: <?= htmlspecialchars($request['issue']) ?></p>
                    <p>Status: <?= htmlspecialchars($request['status']) ?></p>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $request['id'] ?>">
                            <textarea name="response" placeholder="Återkoppling"><?= htmlspecialchars($request['response']) ?></textarea>
                            <select name="status">
                                <option value="Pending" <?= $request['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= $request['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Resolved" <?= $request['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                            </select>
                            <button type="submit">Uppdatera</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_id" value="<?= $request['id'] ?>">
                            <button type="submit" class="delete-button">Ta bort</button>
                        </form>
                    <?php else: ?>
                        <p>Återkoppling: <?= htmlspecialchars($request['response']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
