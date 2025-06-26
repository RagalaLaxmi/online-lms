<?php
session_start();
include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: teacher_courses.php');
    exit;
}

// Delete only if the course belongs to this teacher
$stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND user_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);

header('Location: teacher_courses.php');
exit;
