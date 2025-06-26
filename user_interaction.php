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

$success = false;
$error = '';

// Load SMTP config securely
$config = include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_email = $_POST['user_email'] ?? null;
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$user_email || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } else {
        // Verify user exists with role 'user'
        $stmt = $pdo->prepare("SELECT username FROM users WHERE email = ? AND role = 'user'");
        $stmt->execute([$user_email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'User not found or invalid role.';
        } else {
            // Insert message into DB
            $insert = $pdo->prepare("INSERT INTO messages (user_email, subject, message, sent_at) VALUES (?, ?, ?, NOW())");
            if (!$insert->execute([$user_email, $subject, $message])) {
                $error = 'Failed to save message.';
            } else {
                // Send email
                require 'PHPMailer/src/PHPMailer.php';
                require 'PHPMailer/src/SMTP.php';
                require 'PHPMailer/src/Exception.php';

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $config['smtp_username'];
                    $mail->Password   = $config['smtp_password'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom($config['smtp_username'], 'Online LMS');
                    $mail->addAddress($user_email, $user['username']);

                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = nl2br(htmlspecialchars($message));
                    $mail->AltBody = strip_tags($message);

                    $mail->send();
                    $success = true;
                } catch (Exception $e) {
                    $error = "Message saved but email not sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
}

// Fetch all users with role 'user'
$query = "SELECT email, username FROM users WHERE role = 'user'";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll();

// Fetch messages
$messagesQuery = "SELECT * FROM messages ORDER BY sent_at DESC";
$messagesStmt = $pdo->query($messagesQuery);
$messages = $messagesStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Interaction</title>
    <style>
        /* Reset some basics */
        * {
            box-sizing: border-box;
        }
        body {
            background: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 750px;
            margin: 40px auto;
            padding: 20px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        form {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
            margin-bottom: 40px;
        }
        label {
            font-weight: 600;
            margin-top: 15px;
            display: block;
            color: #34495e;
        }
        select, input[type="text"], textarea {
            width: 100%;
            padding: 10px 14px;
            margin-top: 6px;
            border: 1.8px solid #bdc3c7;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        select:focus, input[type="text"]:focus, textarea:focus {
            border-color: #2980b9;
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        button {
            margin-top: 25px;
            background: #2980b9;
            color: white;
            font-weight: 700;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            width: 100%;
        }
        button:hover {
            background: #1c5980;
        }
        .success-msg {
            background: #2ecc71;
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        .error-msg {
            background: #e74c3c;
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        h3 {
            border-bottom: 2px solid #2980b9;
            padding-bottom: 8px;
            color: #2980b9;
        }
        .message {
            background: #fff;
            border-radius: 6px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.08);
            color: #2c3e50;
        }
        .message strong {
            color: #34495e;
        }
        .date {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<h2>Send Message to User</h2>

<?php if ($success): ?>
    <div class="success-msg">Message sent successfully!</div>
<?php elseif ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form action="" method="POST" novalidate>
    <label for="user_email">Select User Email</label>
    <select name="user_email" id="user_email" required>
        <option value="">-- Select User --</option>
        <?php foreach ($users as $user): ?>
            <option value="<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
        <?php endforeach; ?>
    </select>

    <label for="subject">Subject</label>
    <input type="text" name="subject" id="subject" required placeholder="Enter message subject">

    <label for="message">Message</label>
    <textarea name="message" id="message" required placeholder="Write your message here..."></textarea>

    <button type="submit">Send Message</button>
</form>

<h3>Previous Messages</h3>
<?php if (empty($messages)): ?>
    <p>No messages yet.</p>
<?php else: ?>
    <?php foreach ($messages as $msg): ?>
        <div class="message">
            <div class="date"><?= htmlspecialchars($msg['sent_at']) ?></div>
            <strong><?= htmlspecialchars($msg['user_email']) ?></strong> wrote:<br>
            <strong>Subject:</strong> <?= htmlspecialchars($msg['subject']) ?><br>
            <?= nl2br(htmlspecialchars($msg['message'])) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
