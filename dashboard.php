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

// Load print jobs from the database
$printJobs = [];
$result = $conn->query("SELECT * FROM print_jobs");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $printJobs[] = $row;
    }
}

// Load bookings from the database and filter out old ones (older than 2 hours past the scheduled end time)
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

        // Kontrollera om bokningen är äldre än två timmar
        if (time() - $endTime <= 2 * 60 * 60) {  // 2 timmar i sekunder
            $bookings[] = $row;
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Function to translate weather descriptions to Swedish
function translateWeather($description) {
    $translations = [
        'clear sky' => 'klar himmel',
        'few clouds' => 'få moln',
        'scattered clouds' => 'spridda moln',
        'broken clouds' => 'brutna moln',
        'shower rain' => 'regnskurar',
        'rain' => 'regn',
        'thunderstorm' => 'åskväder',
        'snow' => 'snö',
        'mist' => 'dimma'
    ];
    return $translations[strtolower($description)] ?? $description;
}

// Fetch weather data (replace 'YOUR_API_KEY' with your actual API key)
$weatherApiKey = 'd862730dafdb40b5e98e2a04f202c2ea';
$city = 'Kristianstad';
$weatherApiUrl = "http://api.openweathermap.org/data/2.5/weather?q=Kristianstad&appid=d862730dafdb40b5e98e2a04f202c2ea&units=metric";
$weatherData = json_decode(file_get_contents($weatherApiUrl), true);

// Extract full name from username
$fullName = str_replace('.', ' ', $_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D-skrivarportalen</title>
    <style>
        :root {
            --background-color: #1e1e2e;
            --text-color: #ffffff;
            --form-background-color: #1e1e1e;
            --border-color: #333;
            --input-background-color: #333;
        }
        [data-theme="light"] {
            --background-color: #ffffff;
            --text-color: #000000;
            --form-background-color: #f0f0f0;
            --border-color: #ccc;
            --input-background-color: #ffffff;
        }
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
        }
        .sidebar {
            width: 25%;
            background-color: var(--form-background-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            margin-right: 20px;
        }
        .content {
            width: 75%;
        }
        .job, .booking {
            background-color: var(--form-background-color);
            border: 1px solid var(--border-color);
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
        }
        .progress-bar {
            width: 100%;
            background-color: var(--input-background-color);
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
        .error {
            color: red;
        }
        input, button {
            background-color: var(--input-background-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
        button {
            cursor: pointer;
        }
        button:hover {
            background-color: #555;
        }
        .buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .button {
            background-color: #4caf50;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .weather {
            text-align: center;
        }
        .weather h2 {
            margin: 0;
        }
        .weather p {
            margin: 5px 0;
        }
        .theme-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #4caf50;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .theme-toggle:hover {
            background-color: #45a049;
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
            background-color: var(--background-color);
        }
    </style>
    <script>
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Välkommen, <?= htmlspecialchars(ucwords($fullName)) ?></h2>
            <div class="buttons">
                <a href="?logout" class="button">Logga ut</a>
                <a href="calendar.php" class="button">Boka 3D-skrivare</a>
                <a href="view_bookings.php" class="button">Visa bokningar</a>
            </div>
            <div class="weather">
                <h2>Väder i <?= htmlspecialchars($city) ?></h2>
                <p>Temperatur: <?= htmlspecialchars($weatherData['main']['temp']) ?>°C</p>
                <p>Väder: <?= htmlspecialchars(translateWeather($weatherData['weather'][0]['description'])) ?></p>
            </div>
        </div>
        <div class="content">
            <h1>3D-skrivare - Utskriftsstatus</h1>
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php foreach ($printJobs as $job): ?>
                <div class="job">
                    <h3><?= htmlspecialchars($job['name']) ?></h3>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?= $job['progress'] ?>%">
                            <?= $job['progress'] ?>%
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $job['id'] ?>">
                        <input type="number" name="progress" min="0" max="100" value="<?= $job['progress'] ?>">
                        <button type="submit">Uppdatera</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <h1>Bokningar</h1>
            <?php if (empty($bookings)): ?>
                <p>Inga aktuella bokningar.</p>
            <?php endif; ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking">
                    <h3><?= htmlspecialchars($booking['printer']) ?></h3>
                    <p>Användare: <?= htmlspecialchars($booking['user']) ?></p>
                    <p>Datum: <?= htmlspecialchars($booking['date']) ?></p>
                    <p>Tid: <?= htmlspecialchars(date('H:i', strtotime($booking['time']))) ?> - Slut: <?= htmlspecialchars($booking['end_time']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
