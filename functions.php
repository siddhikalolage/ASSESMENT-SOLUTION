<?php

// Ensure required folders exist
$codesDir = __DIR__ . '/codes';
if (!file_exists($codesDir)) {
    mkdir($codesDir, 0777, true);
}

// Log file path
define('MAIL_LOG_FILE', __DIR__ . '/mail_log.txt');

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body = "<p>Your verification code is: <strong>{$code}</strong></p>";

    // Save to log
    file_put_contents(MAIL_LOG_FILE, "Sending to $email - Code: $code\n", FILE_APPEND);

    return mail($email, $subject, $body, $headers);
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';

    // Prevent duplicates
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);
        return true;
    }
    return false;
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;

    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $updated = array_filter($emails, fn($e) => trim($e) !== trim($email));

    file_put_contents($file, implode(PHP_EOL, $updated) . PHP_EOL);
    return true;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    $latest = json_decode(file_get_contents("https://xkcd.com/info.0.json"), true);
    $max = $latest['num'];
    $rand = rand(1, $max);

    $comic = json_decode(file_get_contents("https://xkcd.com/{$rand}/info.0.json"), true);

    $img = $comic['img'];
    $html = "<h2>XKCD Comic</h2><img src=\"$img\" alt=\"XKCD Comic\">";
    $html .= "<p><a href='http://localhost/xkcd-siddhikalolage/src/unsubscribe.php' id='unsubscribe-button'>Unsubscribe</a></p>";

    return $html;
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $content = fetchAndFormatXKCDData();

    $subject = "Your XKCD Comic";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    foreach ($emails as $email) {
        mail($email, $subject, $content, $headers);
        file_put_contents(MAIL_LOG_FILE, "XKCD sent to $email\n", FILE_APPEND);
    }
}
