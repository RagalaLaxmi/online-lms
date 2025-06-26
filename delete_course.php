<?php
session_start();
include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_courses.php');
    exit;
}

$id = (int)$_GET['id'];

// Delete the course
$stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
$stmt->execute([$id]);

header('Location: admin_courses.php');
exit;
