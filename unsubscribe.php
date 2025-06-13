<?php
require_once 'functions.php';

$message = "";
$email = "";

// Handle Unsubscribe Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["unsubscribe_email"])) {
        $email = trim($_POST["unsubscribe_email"]);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            // Save code to /codes/ directory
            if (!is_dir(__DIR__ . '/codes')) {
                mkdir(__DIR__ . '/codes', 0777, true);
            }
            file_put_contents(__DIR__ . "/codes/" . md5($email) . ".txt", $code);
            sendVerificationEmail($email, $code);
            $message = "Verification code sent to your email.";
        } else {
            $message = "Invalid email format.";
        }
    }

    // Handle Verification Code Submission
    if (isset($_POST["verification_code"])) {
        $email = $_POST["email_hidden"] ?? '';
        $enteredCode = trim($_POST["verification_code"]);
        $storedCodeFile = __DIR__ . "/codes/" . md5($email) . ".txt";

        if (file_exists($storedCodeFile)) {
            $storedCode = trim(file_get_contents($storedCodeFile));
            if ($enteredCode === $storedCode) {
                unsubscribeEmail($email);
                unlink($storedCodeFile);
                $message = "You have been successfully unsubscribed.";
            } else {
                $message = "Invalid verification code.";
            }
        } else {
            $message = "No verification code found. Please request again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 360px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #c0392b;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            margin-top: 15px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Unsubscribe from XKCD Emails</h2>

    <!-- Unsubscribe Form -->
    <form method="POST">
        <label for="unsubscribe_email">Enter your email:</label>
        <input type="email" name="unsubscribe_email" required>
        <button id="submit-unsubscribe" type="submit">Unsubscribe</button>
    </form>

    <!-- Verification Code Form -->
    <form method="POST">
        <label for="verification_code">Enter Verification Code:</label>
        <input type="text" name="verification_code" maxlength="6" required>
        <input type="hidden" name="email_hidden" value="<?= htmlspecialchars($email) ?>">
        <button id="submit-verification" type="submit">Verify</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</div>
</body>
</html>