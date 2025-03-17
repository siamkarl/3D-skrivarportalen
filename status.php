<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Handle form submissions for adding, updating, and deleting print jobs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && isset($_POST['name'], $_POST['progress'])) {
            $name = $_POST['name'];
            $progress = $_POST['progress'];
            $stmt = $conn->prepare("INSERT INTO print_jobs (name, progress) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $progress);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'update' && isset($_POST['id'], $_POST['name'], $_POST['progress'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $progress = $_POST['progress'];
            $stmt = $conn->prepare("UPDATE print_jobs SET name = ?, progress = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $progress, $id);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM print_jobs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: status.php');
        exit;
    }
}

// Load print jobs from the database
$printJobs = [];
$result = $conn->query("SELECT * FROM print_jobs");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $printJobs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utskriftsstatus</title>
    <style>
        body {
            background-color: #1e1e2e;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .content {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            max-width: 800px;
            margin: auto;
        }
        .job {
            background-color: #2e2e3e;
            border: 1px solid #444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
        }
        .progress-bar {
            width: 100%;
            background-color: #333;
            border-radius: 5px;
            overflow: hidden;
        }
        .progress {
            height: 20px;
            background-color: #4caf50;
            text-align: center;
            color: white;
            line-height: 20px;
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
        input, button {
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
    </style>
</head>
<body>
    <div class="content">
        <h1>Utskriftsstatus</h1>
        <?php if (empty($printJobs)): ?>
            <p>Inga utskriftsjobb för närvarande.</p>
        <?php else: ?>
            <?php foreach ($printJobs as $job): ?>
                <div class="job">
                    <h3><?= htmlspecialchars($job['name']) ?></h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= $job['progress'] ?>%">
                            <?= $job['progress'] ?>%
                        </div>
                    </div>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $job['id'] ?>">
                            <input type="text" name="name" value="<?= htmlspecialchars($job['name']) ?>" required>
                            <input type="number" name="progress" min="0" max="100" value="<?= $job['progress'] ?>" required>
                            <button type="submit" name="action" value="update">Uppdatera</button>
                            <button type="submit" name="action" value="delete" class="delete-button">Ta bort</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <h2>Lägg till nytt utskriftsjobb</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Jobbnamn" required>
                <input type="number" name="progress" min="0" max="100" placeholder="Framsteg (%)" required>
                <button type="submit" name="action" value="add">Lägg till</button>
            </form>
        <?php endif; ?>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
