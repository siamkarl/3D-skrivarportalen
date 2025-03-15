<?php
require 'db_config.php';
session_start();

// Set session timeout duration (30 minutes)
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET verified = 1, token = NULL WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $message = "E-postadressen har bekräftats! Du kan nu logga in.";
    } else {
        $message = "Ogiltig verifieringslänk.";
    }

    $stmt->close();
    $conn->close();
} else {
    $message = "Ingen verifieringslänk tillhandahölls.";
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifiera e-post</title>
    <style>
        body {
            background-color: #1e1e2e;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            width: 300px;
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
    <div class="message">
        <h1>Verifiera e-post</h1>
        <p><?= htmlspecialchars($message) ?></p>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
