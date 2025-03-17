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

$printers = ["Prusa Mk4S", "Creality CR-10", "Creality Ender-5 Plus", "Wanhao Duplicator"];
$selectedPrinter = isset($_GET['printer']) ? $_GET['printer'] : $printers[0];

function getFilamentGuide($printer) {
    switch ($printer) {
        case "Prusa Mk4S":
            return [
                "Värm upp skrivaren till den rekommenderade temperaturen för det filament du använder.",
                "Ta bort det gamla filamentet genom att trycka på 'Unload' på skrivaren.",
                "Klipp av änden på det nya filamentet för att säkerställa att det är rent och rakt.",
                "Mata in det nya filamentet i extrudern och tryck på 'Load' på skrivaren.",
                "Vänta tills filamentet börjar extrudera jämnt från munstycket."
            ];
        case "Creality CR-10":
            return [
                "Värm upp skrivaren till den rekommenderade temperaturen för det filament du använder.",
                "Tryck på 'Filament Change' på skrivarens meny.",
                "Ta bort det gamla filamentet genom att dra ut det försiktigt.",
                "Klipp av änden på det nya filamentet för att säkerställa att det är rent och rakt.",
                "Mata in det nya filamentet i extrudern och tryck på 'Load' på skrivaren.",
                "Vänta tills filamentet börjar extrudera jämnt från munstycket."
            ];
        case "Creality Ender-5 Plus":
            return [
                "Värm upp skrivaren till den rekommenderade temperaturen för det filament du använder.",
                "Tryck på 'Change Filament' på skrivarens meny.",
                "Ta bort det gamla filamentet genom att dra ut det försiktigt.",
                "Klipp av änden på det nya filamentet för att säkerställa att det är rent och rakt.",
                "Mata in det nya filamentet i extrudern och tryck på 'Load' på skrivaren.",
                "Vänta tills filamentet börjar extrudera jämnt från munstycket."
            ];
        case "Wanhao Duplicator":
            return [
                "Värm upp skrivaren till den rekommenderade temperaturen för det filament du använder.",
                "Tryck på 'Unload Filament' på skrivarens meny.",
                "Ta bort det gamla filamentet genom att dra ut det försiktigt.",
                "Klipp av änden på det nya filamentet för att säkerställa att det är rent och rakt.",
                "Mata in det nya filamentet i extrudern och tryck på 'Load Filament' på skrivaren.",
                "Vänta tills filamentet börjar extrudera jämnt från munstycket."
            ];
        default:
            return [];
    }
}

$guideSteps = getFilamentGuide($selectedPrinter);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide</title>
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
        select {
            background-color: #333;
            color: #ffffff;
            border: 1px solid #555;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 16px;
        }
    </style>
    <script>
        function updateGuide() {
            const printer = document.getElementById('printerSelect').value;
            window.location.href = `guide.php?printer=${printer}`;
        }
    </script>
</head>
<body>
    <div class="content">
        <h1>Guide - Byta Filament</h1>
        <label for="printerSelect">Välj 3D-skrivare:</label>
        <select id="printerSelect" onchange="updateGuide()">
            <?php foreach ($printers as $printer): ?>
                <option value="<?= $printer ?>" <?= $printer == $selectedPrinter ? 'selected' : '' ?>><?= $printer ?></option>
            <?php endforeach; ?>
        </select>
        <ol>
            <?php foreach ($guideSteps as $step): ?>
                <li><?= htmlspecialchars($step) ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
