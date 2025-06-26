<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'teacher' || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT teacher_status FROM users WHERE id = ?");
$stmt->execute([$userId]);
if ($stmt->fetchColumn() !== 'approved') {
    die("Your account is not approved.");
}

$courses = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$courses->execute([$userId]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage My Courses</title>
    <link rel="stylesheet" href="styles12.css">
</head>
<body>
<h1>My Courses</h1>
<a href="add_course.php">Add New Course</a><br><br>
<table>
<tr><th>Name</th><th>Description</th><th>Video</th><th>Actions</th></tr>
<?php foreach ($courses as $c): ?>
<tr>
  <td><?= htmlspecialchars($c['course_name']) ?></td>
  <td><?= nl2br(htmlspecialchars($c['course_description'])) ?></td>
  <td>
    <iframe src="<?= htmlspecialchars($c['video_path']) ?>" width="280" height="160" frameborder="0" allowfullscreen></iframe>
  </td>
  <td>
    <a href="edit_course.php?id=<?= $c['id'] ?>">Edit</a> |
    <a href="delete_course.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
  </td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
