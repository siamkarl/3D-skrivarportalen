<?php
session_start();
require 'db_config.php';
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect to dashboard if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Set session timeout duration (30 minutes)
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header('Location: dashboard.php'); // Redirect to dashboard.php after login
        exit;
    } else {
        $loginError = "Fel användarnamn eller lösenord!";
    }

    $stmt->close();
    $conn->close();
}

// Google OAuth login
if (isset($_GET['oauth']) && $_GET['oauth'] === 'google') {
    // Include Google Client Library for PHP autoload file
    require_once 'vendor/autoload.php';

    // Make object of Google API Client for call Google API
    $google_client = new Google_Client();

    // Set the OAuth 2.0 Client ID
    $google_client->setClientId('188139691364-vrd6qru16v0s7b9htktcic23apq3r0bn.apps.googleusercontent.com');

    // Set the OAuth 2.0 Client Secret key
    $google_client->setClientSecret('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');

    // Set the OAuth 2.0 Redirect URI
    $google_client->setRedirectUri('https://music1.mckingcraft.net/index.php?oauth=google');

    // Add scopes
    $google_client->addScope('email');
    $google_client->addScope('profile');

    if (!isset($_GET['code'])) {
        // Create a URL to obtain user authorization
        $auth_url = $google_client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    } else {
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
        $google_client->setAccessToken($token['access_token']);

        // Get profile info from Google
        $google_service = new Google_Service_Oauth2($google_client);
        $data = $google_service->userinfo->get();

        if (strpos($data['email'], '@elev.ntig.se') !== false) {
            $username = $data['email'];
            $role = 'user';

            // Check if user already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                // Register new user
                $stmt = $conn->prepare("INSERT INTO users (username, role) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $role);
                $stmt->execute();
            }

            // Log in the user
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header('Location: dashboard.php'); // Redirect to dashboard.php after login
            exit;
        } else {
            $loginError = "Endast @elev.ga.ntig.se e-postadresser är tillåtna!";
        }
    }
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_email'])) {
    $email = $_POST['reset_email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Generate password reset token
        $token = bin2hex(random_bytes(16));

        // Store the token in the database
        $stmt = $conn->prepare("UPDATE users SET token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Send password reset email
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
            $mail->Subject = 'Återställ lösenord';
            $mail->Body = "Klicka på länken för att återställa ditt lösenord: <a href='https://ntiare.se/reset_password.php?token=$token'>Återställ lösenord</a>";

            $mail->send();
            $resetSuccess = "Ett e-postmeddelande har skickats för att återställa ditt lösenord.";
        } catch (Exception $e) {
            $resetError = "E-post kunde inte skickas. Felmeddelande: {$mail->ErrorInfo}";
        }
    } else {
        $resetError = "E-postadressen finns inte i systemet.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logga in</title>
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
        .login-form {
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
        .google-login-button {
            display: inline-block;
            background-color: #dd4b39;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }
        .google-login-button:hover {
            background-color: #c23321;
        }
        .register-button {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }
        .register-button:hover {
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
            background-color: #1e1e2e;
        }
    </style>
    <script>
        function promptResetEmail() {
            const email = prompt("Ange din e-postadress för att återställa lösenordet:");
            if (email) {
                document.getElementById('reset_email').value = email;
                document.getElementById('resetForm').submit();
            }
        }
    </script>
</head>
<body>
    <div class="login-form">
        <img src="logo.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
        <h1>Logga in</h1>
        <?php if (isset($loginError)): ?>
            <p class="error"><?= htmlspecialchars($loginError) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Användarnamn" required>
            <input type="password" name="password" placeholder="Lösenord" required>
            <button type="submit">Logga in</button>
        </form>
        <button onclick="promptResetEmail()">Återställ lösenord</button>
        <form method="POST" id="resetForm" style="display: none;">
            <input type="hidden" name="reset_email" id="reset_email">
        </form>
        <a href="register.php" class="register-button">Registera konto</a>
        <?php if (isset($resetSuccess)): ?>
            <p class="success"><?= htmlspecialchars($resetSuccess) ?></p>
        <?php endif; ?>
        <?php if (isset($resetError)): ?>
            <p class="error"><?= htmlspecialchars($resetError) ?></p>
        <?php endif; ?>
    </div>
    <div class="footer">
        Hemsidan är gjort av Siam Karlsson | version: 1.0 | senaste uppdatering 2025-03-15 | &copy; 2025
    </div>
</body>
</html>