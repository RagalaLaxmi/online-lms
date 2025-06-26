<?php
// login.php

require_once 'db.php'; // Ensure this is correctly including your database connection
session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare and execute the query to check if the username exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // Check if user is a teacher and if they are approved
        if ($user['role'] === 'teacher' && $user['teacher_status'] !== 'approved') {
            $error = "Your account is pending admin approval. Please wait until you are approved.";
        } else {
            // Password is correct and either not a teacher or approved teacher
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Store the role in session
            $_SESSION['user_id'] = $user['id']; 

            if ($user['role'] === 'teacher') {
                $_SESSION['teacher_id'] = $user['id'];
            }

            // Redirect based on the role
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin_dashboard.php');
                    break;
                case 'teacher':
                    header('Location: teacher_dashboard.php');
                    break;
                default:
                    header('Location: user_dashboard.php');
                    break;
            }
            exit;
        }
    } else {
        // Invalid login credentials
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles2.css">
</head>
<body>

<div class="container">
    <h1>Login</h1>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <footer>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </footer>
</div>

</body>
</html>
