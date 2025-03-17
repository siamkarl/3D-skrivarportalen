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
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Städregler</title>
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
    <div class="content">
        <h1>Städregler</h1>
        <p>För att säkerställa att alla användare har en trevlig upplevelse, vänligen följ dessa städregler efter att du har använt 3D-skrivarna:</p>
        <ul>
            <li>Ta bort alla utskrifter och skräp från byggplattan.</li>
            <li>Rengör byggplattan med isopropylalkohol om det behövs.</li>
            <li>Se till att inga filamentrester finns kvar i skrivaren.</li>
            <li>Stäng av skrivaren om du är den sista användaren för dagen.</li>
            <li>Rapportera eventuella problem eller skador till administratören.</li>
        </ul>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
