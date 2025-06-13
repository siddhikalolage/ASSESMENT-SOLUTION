<?php
require_once 'functions.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['submit_email'])) {
        $email = trim($_POST['email']);
        $code = generateVerificationCode();

        // Store code temporarily
        file_put_contents(__DIR__ . "/codes/{$email}.txt", $code);

        if (sendVerificationEmail($email, $code)) {
            $message = "Verification code sent to your email.";
        } else {
            $message = "Failed to send verification code.";
        }
    }

    if (isset($_POST['verification_code']) && isset($_POST['submit_verification']) && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $enteredCode = trim($_POST['verification_code']);
        $storedCodeFile = __DIR__ . "/codes/{$email}.txt";

        if (file_exists($storedCodeFile)) {
            $storedCode = trim(file_get_contents($storedCodeFile));
            if ($enteredCode === $storedCode) {
                registerEmail($email);
                $message = "Email verified and subscribed successfully.";
                unlink($storedCodeFile);
            } else {
                $message = "Incorrect verification code.";
            }
        } else {
            $message = "Verification code not found. Please request again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>XKCD Email Subscription</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f6f8;
    }
    .container {
      max-width: 600px;
      margin-top: 50px;
    }
    .card {
      border-radius: 16px;
      padding: 24px;
    }
    .message {
      margin-top: 20px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="card shadow-sm">
    <h2 class="text-center mb-4">📩 XKCD Daily Comic Subscription</h2>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="email" class="form-label">Email address:</label>
        <input type="email" class="form-control" name="email" id="email" required>
      </div>

      <div class="mb-3">
        <label for="verification_code" class="form-label">Verification Code:</label>
        <input type="text" class="form-control" name="verification_code" maxlength="6">
      </div>

      <div class="d-flex gap-3">
        <button type="submit" name="submit_email" id="submit-email" class="btn btn-primary">Send Code</button>
        <button type="submit" name="submit_verification" id="submit-verification" class="btn btn-success">Verify</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
