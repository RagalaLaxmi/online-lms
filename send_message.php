<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only allow teachers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user_interaction.php');
    exit;
}

$user_email = $_POST['user_email'] ?? null;
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$user_email || empty($subject) || empty($message)) {
    die('All fields are required.');
}

// Verify user exists with role 'user'
$stmt = $pdo->prepare("SELECT username FROM users WHERE email = ? AND role = 'user'");
$stmt->execute([$user_email]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found or invalid role.');
}

// Insert message into DB
$insert = $pdo->prepare("INSERT INTO messages (user_email, subject, message, sent_at) VALUES (?, ?, ?, NOW())");
if (!$insert->execute([$user_email, $subject, $message])) {
    die('Failed to save message.');
}

// Load PHPMailer classes
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'onlinelms2@gmail.com';  // Your email
    $mail->Password   = 'ectckbrormoprhwj';      // Your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('onlinelms2@gmail.com', 'Online LMS');
    $mail->addAddress($user_email, $user['username']);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = nl2br(htmlspecialchars($message));
    $mail->AltBody = strip_tags($message);

    $mail->send();
} catch (Exception $e) {
    die("Message saved but email not sent. Mailer Error: {$mail->ErrorInfo}");
}

// Redirect back with success flag
header('Location: user_interaction.php?success=1');
exit;
