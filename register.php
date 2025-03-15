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

$registerSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'], $_POST['email'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (strpos($email, '@elev.ga.ntig.se') !== false) {
        // Extract username from email
        $username = explode('@', $email)[0];

        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Generate verification token
            $token = bin2hex(random_bytes(16));

            // Register new user with verification token
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, token, verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $username, $password, $email, $token);
            $stmt->execute();

            // Send verification email
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
                $mail->Subject = 'Bekräfta din e-postadress';
                $mail->Body = "Klicka på länken för att bekräfta din e-postadress: <a href='https://ntiare.se/verify.php?token=$token'>Bekräfta e-post</a>";

                $mail->send();
                $registerSuccess = true;
            } catch (Exception $e) {
                echo "E-post kunde inte skickas. Felmeddelande: {$mail->ErrorInfo}";
            }
        } else {
            $registerError = "Användarnamn eller e-postadress är redan registrerad!";
        }

        $stmt->close();
    } else {
        $registerError = "Endast @elev.ga.ntig.se e-postadresser är tillåtna!";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrera</title>
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
        .register-form {
            background-color: #1e1e1e;
            border: 1px solid #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            width: 300px;
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
        .error {
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
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
    <div class="register-form">
        <img src="logo.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
        <h1>Registrera</h1>
        <?php if ($registerSuccess): ?>
            <p class="success">Registrering lyckades! Kontrollera din e-post för att bekräfta din e-postadress.</p>
        <?php endif; ?>
        <?php if (isset($registerError)): ?>
            <p class="error"><?= htmlspecialchars($registerError) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="E-postadress" required>
            <input type="password" name="password" placeholder="Lösenord" required>
            <button type="submit">Registrera</button>
        </form>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>
